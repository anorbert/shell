<?php

namespace App\\Services;

use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Http;
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

            if (!is_string($symbol) || !isset($allowed[$symbol]) || !is_string($price) || !is_numeric($price) || (float) $price <= 0) {
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
        $quote = DB::table('market_quotes')->where('symbol', $symbol)->first();

        if (!$quote || now()->subSeconds((int) config('shell.market.max_age_seconds', 90))->gt($quote->captured_at)) {
            throw new RuntimeException('No fresh market quote is available for this pair. Trading is blocked.');
        }

        return $quote;
    }
}
