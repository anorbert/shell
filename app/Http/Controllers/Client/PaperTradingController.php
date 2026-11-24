<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use App\Services\MarketQuoteService;
use App\Services\PaperTradingService;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use InvalidArgumentException;

class PaperTradingController extends Controller
{
    public function index(Request $request, LedgerService $ledger, MarketQuoteService $markets)
    {
        $currency = config('shell.paper.currency', 'USD');
        $wallet = $ledger->provisionClientWallets($request->user(), $currency)['demo'];

        return view('client.trading', [
            'balance' => Decimal::fromUnits($ledger->balanceUnits($wallet)),
            'currency' => $currency,
            'payoutPercentage' => number_format((int) config('shell.paper.payout_basis_points', 18000) / 100, 2),
            'quotes' => $markets->freshQuotes(),
            'trades' => DB::table('paper_trades')->where('user_id', $request->user()->id)->latest()->paginate(12),
            'symbols' => config('shell.market.symbols'),
            'durations' => config('shell.paper.durations'),
        ]);
    }

    public function store(Request $request, PaperTradingService $service)
    {
        $values = $request->validate([
            'symbol' => ['required', 'string', 'max:24'],
            'direction' => ['required', 'in:UP,DOWN'],
            'stake' => ['required', 'regex:/^(0|[1-9][0-9]{0,9})(\.[0-9]{1,8})?$/'],
            'duration' => ['required', 'integer'],
            'request_key' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $id = $service->open(
                $request->user(),
                strtoupper($values['symbol']),
                $values['direction'],
                $stake,
                (int) $values['duration'],
                $values['request_key'] ?: Str::uuid()->toString()
            );

            return response()->json(['message' => 'Paper trade opened.', 'trade_id' => $id], 201);
        } catch (RuntimeException | InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
