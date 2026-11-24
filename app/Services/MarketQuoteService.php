<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use RuntimeException;

class MarketQuoteService
{
    public function refresh(): int
    {
        $response = Http::acceptJson()
            ->retry(2, 250)
            ->timeout(12)
            ->get(rtrim(config('shell.market.base_url'), '/') . '/api/v3/ticker/price');

        if (!$response->successful() || !is_array($response->json())) {
            throw new RuntimeException('Market provider returned invalid ticker data.');
        }

        $allowed = array_flip(config('shell.market.symbols', []));
        // Record server receipt time after the provider response, not before
        // the network request. This prevents snapshots being timestamped early.
        $capturedAt = now('UTC');
        $count = 0;

        foreach ($response->json() as $item) {
            $symbol = $item['symbol'] ?? null;
            $price = $item['price'] ?? null;

            if (!is_string($symbol) || !isset($allowed[$symbol]) || !is_string($price) || !preg_match('/^[0-9]{1,16}(?:\.[0-9]{1,12})?$/', $price) || !preg_match('/[1-9]/', $price)) {
                continue;
            }

            DB::table('market_quotes')->updateOrInsert(
                ['symbol' => $symbol],
                [
                    'price' => $price,
                    'provider' => 'Binance Spot',
                    'captured_at' => $capturedAt,
                    'created_at' => $capturedAt,
                    'updated_at' => $capturedAt,
                ]
            );

            // Preserve a time-series snapshot so settlement uses the first
            // server-captured market price at or immediately after expiry.
            DB::table('market_quote_snapshots')->insert([
                'symbol' => $symbol,
                'price' => $price,
                'captured_at' => $capturedAt,
            ]);

            $count++;
        }

        if ($count === 0) {
            throw new RuntimeException('Market provider returned no configured market quotes.');
        }

        return $count;
    }

    /**
     * Return provider OHLCV candles for the same configured spot symbols used by trading.
     *
     * Candle data is display-only. Trade opening and settlement continue to use the
     * server-captured ticker snapshots in market_quotes.
     */
    public function candles(string $symbol, string $interval = '1m', int $limit = 100): array
    {
        if (!in_array($symbol, config('shell.market.symbols', []), true)) {
            throw new RuntimeException('Unsupported market pair.');
        }

        if (!in_array($interval, ['1m', '5m', '15m', '1h', '4h', '1d'], true)) {
            throw new RuntimeException('Unsupported chart timeframe.');
        }

        $limit = max(20, min(200, $limit));

        $response = Http::acceptJson()
            ->retry(2, 250)
            ->timeout(10)
            ->get(rtrim(config('shell.market.base_url'), '/') . '/api/v3/klines', [
                'symbol' => $symbol,
                'interval' => $interval,
                'limit' => $limit,
            ]);

        if (!$response->successful() || !is_array($response->json())) {
            throw new RuntimeException('Chart data is temporarily unavailable.');
        }

        $candles = [];
        foreach ($response->json() as $row) {
            if (!is_array($row) || count($row) < 6) {
                continue;
            }

            $values = array_slice($row, 0, 6);
            if (!is_numeric($values[0]) || !is_numeric($values[1]) || !is_numeric($values[2])
                || !is_numeric($values[3]) || !is_numeric($values[4]) || !is_numeric($values[5])) {
                continue;
            }

            $candles[] = [
                'time' => (int) $values[0],
                'open' => (string) $values[1],
                'high' => (string) $values[2],
                'low' => (string) $values[3],
                'close' => (string) $values[4],
                'volume' => (string) $values[5],
            ];
        }

        if ($candles === []) {
            throw new RuntimeException('Chart provider returned no valid candles.');
        }

        return $candles;
    }

    public function freshQuotes()
    {
        return DB::table('market_quotes')
            ->where('captured_at', '>=', now('UTC')->subSeconds((int) config('shell.market.max_age_seconds', 90)))
            ->whereIn('symbol', config('shell.market.symbols', []))
            ->orderBy('symbol')
            ->get();
    }

    /**
     * Refresh and return one server-verified pair quote. Expiry settlement uses
     * this targeted path rather than downloading the entire market catalogue.
     */
    public function refreshSymbolQuote(string $symbol): object
    {
        if (!in_array($symbol, config('shell.market.symbols', []), true)) {
            throw new RuntimeException('Unsupported market pair.');
        }

        $this->refreshSymbol($symbol);
        $quote = DB::table('market_quotes')->where('symbol', $symbol)->first();

        if (!$quote) {
            throw new RuntimeException('Market provider did not persist a verified quote for ' . $symbol . '.');
        }

        return $quote;
    }

    /**
     * Refresh one tradable pair on demand. This is intentionally lighter and
     * faster than downloading the entire ticker catalogue during a button click.
     */
    private function refreshSymbol(string $symbol): void
    {
        $response = Http::acceptJson()
            ->retry(1, 150)
            ->timeout(5)
            ->get(rtrim(config('shell.market.base_url'), '/') . '/api/v3/ticker/price', [
                'symbol' => $symbol,
            ]);

        $item = $response->json();
        $price = is_array($item) ? ($item['price'] ?? null) : null;

        if (!$response->successful()
            || !is_array($item)
            || ($item['symbol'] ?? null) !== $symbol
            || !is_string($price)
            || !preg_match('/^[0-9]{1,16}(?:\\.[0-9]{1,12})?$/', $price)
            || !preg_match('/[1-9]/', $price)) {
            throw new RuntimeException('Market provider did not return a valid live price for ' . $symbol . '.');
        }

        $capturedAt = now('UTC');
        DB::table('market_quotes')->updateOrInsert(
            ['symbol' => $symbol],
            [
                'price' => $price,
                'provider' => 'Binance Spot',
                'captured_at' => $capturedAt,
                'created_at' => $capturedAt,
                'updated_at' => $capturedAt,
            ]
        );

        DB::table('market_quote_snapshots')->insert([
            'symbol' => $symbol,
            'price' => $price,
            'captured_at' => $capturedAt,
        ]);
    }

    /**
     * Execution quotes must come from a successful provider response in this
     * request. Cached display/conversion quotes are never accepted for a trade.
     */
    public function freshExecutionQuote(string $symbol): object
    {
        if (!in_array($symbol, config('shell.market.symbols', []), true)) {
            throw new RuntimeException('Unsupported market pair.');
        }

        try {
            $quote = $this->refreshSymbolQuote($symbol);
        } catch (\Throwable $exception) {
            report($exception);
            throw new RuntimeException(
                'A fresh verified market quote is unavailable for ' . $symbol . '. Your demo stake has not been deducted. Please retry when the market feed is restored.',
                0,
                $exception
            );
        }

        $capturedAt = \Illuminate\Support\Carbon::parse((string) $quote->captured_at, 'UTC');
        if ($capturedAt->greaterThan(now('UTC')) || $capturedAt->lessThan(now('UTC')->subSeconds(10))) {
            throw new RuntimeException(
                'The market quote for ' . $symbol . ' is stale or has an invalid timestamp. Your demo stake has not been deducted.'
            );
        }

        return $quote;
    }

    /**
     * A recent server-captured quote is sufficient for an indicative paper
     * conversion preview. Conversion confirmation independently checks that
     * the quoted snapshot has not changed.
     */
    public function freshQuote(string $symbol): object
    {
        if (!in_array($symbol, config('shell.market.symbols', []), true)) {
            throw new RuntimeException('Unsupported market pair.');
        }

        $maxAge = max(5, (int) config('shell.market.max_age_seconds', 90));
        $quote = DB::table('market_quotes')->where('symbol', $symbol)->first();
        $minimumCapturedAt = now('UTC')->subSeconds($maxAge);

        if (!$quote || \Illuminate\Support\Carbon::parse((string) $quote->captured_at, 'UTC')->lessThan($minimumCapturedAt) || \Illuminate\Support\Carbon::parse((string) $quote->captured_at, 'UTC')->greaterThan(now('UTC'))) {
            throw new RuntimeException('A recent server market quote is unavailable for ' . $symbol . '.');
        }

        return $quote;
    }
}
