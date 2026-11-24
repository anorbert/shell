<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        $capturedAt = now();
        $count = 0;

        foreach ($response->json() as $item) {
            $symbol = $item['symbol'] ?? null;
            $price = $item['price'] ?? null;

            if (!is_string($symbol) || !isset($allowed[$symbol]) || !is_string($price) || !preg_match('/^[0-9]{1,16}(?:\.[0-9]{1,12})?$/', $price) || (float) $price <= 0) {
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
            ->where('captured_at', '>=', now()->subSeconds((int) config('shell.market.max_age_seconds', 90)))
            ->whereIn('symbol', config('shell.market.symbols', []))
            ->orderBy('symbol')
            ->get();
    }

    public function freshQuote(string $symbol): object
    {
        if (!in_array($symbol, config('shell.market.symbols', []), true)) {
            throw new RuntimeException('Unsupported market pair.');
        }

        $isFresh = fn ($quote) => $quote
            && !now()->subSeconds((int) config('shell.market.max_age_seconds', 90))->gt($quote->captured_at);

        $quote = DB::table('market_quotes')->where('symbol', $symbol)->first();

        // The scheduled refresh is a cache warmer, not a hard dependency for trading.
        // Refresh synchronously once when the cached quote is missing or stale.
        if (!$isFresh($quote)) {
            try {
                $this->refresh();
            } catch (\\Throwable $exception) {
                report($exception);
            }

            $quote = DB::table('market_quotes')->where('symbol', $symbol)->first();
        }

        if (!$isFresh($quote)) {
            throw new RuntimeException('A live market price could not be verified right now. Please wait a moment and try again.');
        }

        return $quote;
    }
}
