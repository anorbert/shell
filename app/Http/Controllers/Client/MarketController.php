<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\MarketQuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MarketController extends Controller
{
    public function index(Request $request, MarketQuoteService $markets)
    {
        $favorites = DB::table('market_favorites')->where('user_id', $request->user()->id)->pluck('symbol')->all();

        return view('client.markets', [
            'quotes' => $markets->freshQuotes(),
            'favorites' => $favorites,
        ]);
    }

    public function data(Request $request, MarketQuoteService $markets)
    {
        $favorites = DB::table('market_favorites')->where('user_id', $request->user()->id)->pluck('symbol')->all();
        $search = strtoupper(trim((string) $request->query('search', '')));
        $onlyFavorites = $request->boolean('favorites');
        $sort = $request->query('sort', 'symbol');

        $quotes = $markets->freshQuotes()->filter(fn ($quote) => ($search === '' || str_contains(strtoupper($quote->symbol), $search))
            && (!$onlyFavorites || in_array($quote->symbol, $favorites, true)));

        $quotes = $sort === 'price_asc'
            ? $quotes->sortBy(fn ($quote) => (float) $quote->price)
            : ($sort === 'price_desc' ? $quotes->sortByDesc(fn ($quote) => (float) $quote->price) : $quotes->sortBy('symbol'));

        return response()->json([
            'quotes' => $quotes->values()->map(fn ($quote) => [
                'symbol' => $quote->symbol,
                'price' => $quote->price,
                'provider' => $quote->provider,
                'captured_at' => $quote->captured_at,
                'favorite' => in_array($quote->symbol, $favorites, true),
            ]),
            'stale_after_seconds' => config('shell.market.max_age_seconds'),
        ]);
    }

    public function candles(Request $request, MarketQuoteService $markets)
    {
        $values = $request->validate([
            'symbol' => ['required', 'string', 'max:24'],
            'interval' => ['nullable', 'in:1m,5m,15m,1h,4h,1d'],
            'limit' => ['nullable', 'integer', 'min:20', 'max:200'],
        ]);

        try {
            return response()->json([
                'symbol' => strtoupper($values['symbol']),
                'interval' => $values['interval'] ?? '1m',
                'provider' => 'Binance Spot',
                'candles' => $markets->candles(
                    strtoupper($values['symbol']),
                    $values['interval'] ?? '1m',
                    (int) ($values['limit'] ?? 100)
                ),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function toggleFavorite(Request $request, string $symbol)
    {
        abort_unless(in_array($symbol, config('shell.market.symbols', []), true), 404);

        $favorite = DB::table('market_favorites')
            ->where('user_id', $request->user()->id)
            ->where('symbol', $symbol)
            ->first();

        if ($favorite) {
            DB::table('market_favorites')->where('id', $favorite->id)->delete();
            $saved = false;
        } else {
            DB::table('market_favorites')->insertOrIgnore([
                'user_id' => $request->user()->id,
                'symbol' => $symbol,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $saved = true;
        }

        return response()->json(['symbol' => $symbol, 'favorite' => $saved]);
    }
}
