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
        // Keep page rendering independent of network-dependent settlement work.
        // The scheduled settlement command owns settlement so a slow market API
        // can never hold the trading terminal open.
        $currency = config('shell.paper.currency', 'USD');
        $wallet = $ledger->provisionClientWallets($request->user(), $currency)['demo'];

        return view('client.trading', [
            'balance' => Decimal::fromUnits($ledger->balanceUnits($wallet)),
            'currency' => $currency,
            'payoutPercentage' => number_format((int) config('shell.paper.payout_basis_points', 18000) / 100, 2),
            'quotes' => $markets->freshQuotes(),
            'trades' => DB::table('paper_trades')->where('user_id', $request->user()->id)->latest()->paginate(12),
            // Resume the latest active position automatically when the trading page is reopened.
            'activeTradeId' => DB::table('paper_trades')
                ->where('user_id', $request->user()->id)
                ->where('status', 'open')
                ->where('expires_at', '>', now())
                ->orderByDesc('opened_at')
                ->value('id'),
            'hasExpiredOpenTrades' => DB::table('paper_trades')
                ->where('user_id', $request->user()->id)
                ->where('status', 'open')
                ->where('expires_at', '<=', now())
                ->exists(),
            'symbols' => config('shell.market.symbols'),
            'durations' => config('shell.paper.durations'),
        ]);
    }



    /**
     * Trigger settlement asynchronously from the terminal when this client has
     * expired positions. The page itself never waits for market-provider I/O.
     */
    public function settleExpired(Request $request, PaperTradingService $service)
    {
        $hasDue = DB::table('paper_trades')
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->where('expires_at', '<=', now())
            ->exists();

        if (!$hasDue) {
            return response()->json(['settled_count' => 0, 'has_due' => false]);
        }

        try {
            return response()->json([
                'settled_count' => $service->settleDue(),
                'has_due' => true,
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'Settlement is temporarily unavailable. Your expired position remains recorded and will be retried.',
            ], 503);
        }
    }

    public function settlePosition(Request $request, int $trade, PaperTradingService $service)
    {
        $owned = DB::table('paper_trades')
            ->where('id', $trade)
            ->where('user_id', $request->user()->id)
            ->exists();

        abort_unless($owned, 404);

        try {
            $settledCount = $service->settleDue();

            $position = DB::table('paper_trades')->where('id', $trade)->first();

            return response()->json([
                'trade' => $position ? [
                    'id' => (int) $position->id,
                    'symbol' => $position->symbol,
                    'direction' => $position->direction,
                    'stake' => (string) $position->stake,
                    'currency' => $position->wallet_currency ?: config('shell.paper.currency', 'USD'),
                    'opening_price' => (string) $position->opening_price,
                    'duration_seconds' => (int) $position->duration_seconds,
                    'opened_at' => $position->opened_at,
                    'expires_at' => $position->expires_at,
                    'status' => $position->status,
                    'closing_price' => $position->closing_price !== null ? (string) $position->closing_price : null,
                    'settled_at' => $position->settled_at,
                ] : null,
                'settled_count' => $settledCount,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Settlement is temporarily unavailable. The position remains open and will retry automatically.',
            ], 503);
        }
    }

    public function show(Request $request, int $trade)
    {
        // Keep this endpoint read-only and dependable: settlement runs through
        // the scheduler and the trading-page request, not during modal hydration.
        $position = DB::table('paper_trades')
            ->where('id', $trade)
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($position, 404);

        return response()->json([
            'trade' => [
                'id' => (int) $position->id,
                'symbol' => $position->symbol,
                'direction' => $position->direction,
                'stake' => (string) $position->stake,
                'currency' => $position->wallet_currency ?: config('shell.paper.currency', 'USD'),
                'opening_price' => (string) $position->opening_price,
                'duration_seconds' => (int) $position->duration_seconds,
                'opened_at' => $position->opened_at,
                'expires_at' => $position->expires_at,
                'status' => $position->status,
                'closing_price' => $position->closing_price !== null ? (string) $position->closing_price : null,
                'settled_at' => $position->settled_at,
            ],
        ]);
    }

    public function store(Request $request, PaperTradingService $service, LedgerService $ledger)
    {
        $values = $request->validate([
            'symbol' => ['required', 'string', 'max:24'],
            'direction' => ['required', 'in:UP,DOWN'],
            'stake' => ['required', 'string', 'max:24'],
            'duration' => ['required', 'integer'],
            'request_key' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            // Canonicalize decimal input before fixed-point ledger arithmetic.
            // This accepts 10, 10.0, 10.00 and leading-zero input safely.
            $rawStake = trim((string) $values['stake']);
            if (!preg_match('/\\A[0-9]+(?:\\.[0-9]{1,8})?\\z/D', $rawStake)) {
                return response()->json(['message' => 'Enter a valid stake using digits and up to 8 decimal places (for example, 10 or 10.50).'], 422);
            }
            [$whole, $fraction] = array_pad(explode('.', $rawStake, 2), 2, '');
            $whole = ltrim($whole, '0');
            $whole = $whole === '' ? '0' : $whole;
            $fraction = rtrim($fraction, '0');
            $stake = $whole . ($fraction !== '' ? '.' . $fraction : '');

            if (Decimal::units($stake) <= 0) {
                return response()->json(['message' => 'Enter a stake greater than zero.'], 422);
            }

            $currency = config('shell.paper.currency', 'USD');
            $maxStake = (string) config('shell.paper.max_stake');
            if (Decimal::compare($stake, $maxStake) > 0) {
                return response()->json(['message' => 'The maximum stake is ' . $maxStake . ' ' . $currency . '.'], 422);
            }

            $demoWallet = $ledger->provisionClientWallets($request->user(), $currency)['demo'];
            $available = Decimal::fromUnits($ledger->balanceUnits($demoWallet));
            if (Decimal::compare($stake, $available) > 0) {
                return response()->json([
                    'message' => 'Insufficient demo balance. Available: ' . $available . ' ' . $currency . '. Reduce your stake or add demo funds.',
                ], 422);
            }

            $id = $service->open(
                $request->user(),
                strtoupper($values['symbol']),
                $values['direction'],
                $stake,
                (int) $values['duration'],
                $values['request_key'] ?: Str::uuid()->toString()
            );

            return response()->json([
                'message' => 'Your demo trade has been opened successfully.',
                'trade_id' => $id,
            ], 201);
        } catch (RuntimeException | InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
