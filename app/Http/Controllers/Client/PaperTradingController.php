<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use App\Services\MarketQuoteService;
use App\Services\PaperTradingService;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            'expiredTradeId' => DB::table('paper_trades')
                ->where('user_id', $request->user()->id)
                ->where('status', 'open')
                ->where('expires_at', '<=', now())
                ->orderByDesc('expires_at')
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
    public function settleExpired(Request $request, PaperTradingService $service, LedgerService $ledger)
    {
        try {
            // This is a recovery trigger, not the authority for settlement. The
            // scheduler runs the same service even when no client is connected.
            $settledCount = $service->settleDue();
            $hasDue = DB::table('paper_trades')
                ->where('user_id', $request->user()->id)
                ->where('status', 'open')
                ->where('expires_at', '<=', now())
                ->exists();

            $currency = config('shell.paper.currency', 'USD');
            $wallet = $ledger->demoWallet($request->user(), $currency);

            return response()->json([
                'settled_count' => $settledCount,
                'has_due' => $hasDue,
                'wallet_balance' => Decimal::fromUnits($ledger->balanceUnits($wallet)),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'Settlement is temporarily unavailable. Your expired position remains recorded and will be retried automatically.',
            ], 503);
        }
    }

    public function settlePosition(Request $request, int $trade, PaperTradingService $service, LedgerService $ledger)
    {
        $owned = DB::table('paper_trades')
            ->where('id', $trade)
            ->where('user_id', $request->user()->id)
            ->exists();

        abort_unless($owned, 404);

        try {
            // The service refreshes a server-side quote when needed and settles
            // eligible expired positions in this same request. It never trusts
            // the browser's WebSocket price for a financial ledger decision.
            $settledCount = $service->settleDue();
            $position = DB::table('paper_trades')->where('id', $trade)->first();

            if (!$position) {
                return response()->json(['message' => 'Position not found.'], 404);
            }

            $currency = $position->wallet_currency ?: config('shell.paper.currency', 'USD');
            $payout = '0.00000000';
            $profit = '0.00000000';
            $walletBalance = null;

            if ($position->status === 'won') {
                $payout = Decimal::multiplyRatio(
                    (string) $position->stake,
                    (int) config('shell.paper.payout_basis_points', 18000),
                    10000
                );
                $profit = Decimal::fromUnits(
                    Decimal::units($payout) - Decimal::units((string) $position->stake)
                );
            } elseif ($position->status === 'void') {
                $payout = (string) $position->stake;
            } elseif ($position->status === 'lost') {
                $profit = '-' . (string) $position->stake;
            }

            if ($position->status !== 'open') {
                $wallet = $ledger->demoWallet($request->user(), $currency);
                $walletBalance = Decimal::fromUnits($ledger->balanceUnits($wallet));
            }

            return response()->json([
                'trade' => [
                    'id' => (int) $position->id,
                    'symbol' => $position->symbol,
                    'direction' => $position->direction,
                    'stake' => (string) $position->stake,
                    'currency' => $currency,
                    'opening_price' => (string) $position->opening_price,
                    'duration_seconds' => (int) $position->duration_seconds,
                    'opened_at' => $position->opened_at !== null ? Carbon::parse((string) $position->opened_at, 'UTC')->toIso8601String() : null,
                    'expires_at' => $position->expires_at !== null ? Carbon::parse((string) $position->expires_at, 'UTC')->toIso8601String() : null,
                    'status' => $position->status,
                    'closing_price' => $position->closing_price !== null ? (string) $position->closing_price : null,
                    'settled_at' => $position->settled_at !== null ? Carbon::parse((string) $position->settled_at, 'UTC')->toIso8601String() : null,
                    'payout_amount' => $payout,
                    'profit_amount' => $profit,
                ],
                'wallet_balance' => $walletBalance,
                'settled_count' => $settledCount,
                'settled' => $position->status !== 'open',
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Settlement is temporarily unavailable. The position remains recorded and will retry automatically.',
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
                'opened_at' => $position->opened_at !== null ? Carbon::parse((string) $position->opened_at, 'UTC')->toIso8601String() : null,
                'expires_at' => $position->expires_at !== null ? Carbon::parse((string) $position->expires_at, 'UTC')->toIso8601String() : null,
                'status' => $position->status,
                'closing_price' => $position->closing_price !== null ? (string) $position->closing_price : null,
                'settled_at' => $position->settled_at !== null ? Carbon::parse((string) $position->settled_at, 'UTC')->toIso8601String() : null,
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
            'request_key' => ['required', 'string', 'max:100'],
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

            // PaperTradingService performs the authoritative balance check and
            // debit atomically in the ledger. Avoid a pre-check here: it can
            // incorrectly reject an idempotent retry after the original request
            // already reserved the stake.
            $id = $service->open(
                $request->user(),
                strtoupper($values['symbol']),
                $values['direction'],
                $stake,
                (int) $values['duration'],
                $values['request_key']
            );

            // Return the persisted position in the opening response. The client
            // can display the live trade immediately without a second hydration
            // request that could fail after the stake has already been reserved.
            $position = DB::table('paper_trades')
                ->where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$position) {
                throw new RuntimeException('The trade was reserved but its position record could not be loaded.');
            }

            return response()->json([
                'message' => 'Your demo trade has been opened successfully.',
                'trade_id' => $id,
                'wallet_balance' => Decimal::fromUnits($ledger->balanceUnits($demoWallet)),
                'trade' => [
                    'id' => (int) $position->id,
                    'symbol' => $position->symbol,
                    'direction' => $position->direction,
                    'stake' => (string) $position->stake,
                    'currency' => $position->wallet_currency ?: $currency,
                    'opening_price' => (string) $position->opening_price,
                    'duration_seconds' => (int) $position->duration_seconds,
                    'opened_at' => $position->opened_at !== null ? Carbon::parse((string) $position->opened_at, 'UTC')->toIso8601String() : null,
                    'expires_at' => $position->expires_at !== null ? Carbon::parse((string) $position->expires_at, 'UTC')->toIso8601String() : null,
                    'status' => $position->status,
                    'closing_price' => null,
                    'settled_at' => null,
                ],
            ], 201);
        } catch (RuntimeException | InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'Trade placement is temporarily unavailable. Your request is protected against duplicate processing; please retry with the same request key.',
            ], 503);
        }
    }
}
