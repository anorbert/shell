<?php

namespace App\Services;

use App\Models\User;
use App\Support\Decimal;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaperTradingService
{
    public function __construct(
        private LedgerService $ledger,
        private MarketQuoteService $markets
    ) {}

    public function open(User $user, string $symbol, string $direction, string $stake, int $duration, string $key): int
    {
        if (!config('shell.paper.enabled')) {
            throw new RuntimeException('Paper trading is disabled.');
        }

        if (!in_array($symbol, config('shell.market.symbols', []), true)) {
            throw new RuntimeException('Unsupported market pair.');
        }

        if (!in_array($direction, ['UP', 'DOWN'], true)) {
            throw new RuntimeException('Invalid trade direction.');
        }

        if (!in_array($duration, config('shell.paper.durations', []), true)) {
            throw new RuntimeException('Invalid trade duration.');
        }

        if ($key === '' || strlen($key) > 100) {
            throw new RuntimeException('A valid request key is required.');
        }

        $amount = Decimal::units($stake);

        if ($amount <= 0 || $amount > Decimal::units((string) config('shell.paper.max_stake'))) {
            throw new RuntimeException('Stake is outside paper limits.');
        }

        // Idempotency must be checked before any provider I/O. If the first
        // request committed but its HTTP response was lost, a retry must return
        // that saved position even when the market provider is currently down.
        $existing = DB::table('paper_trades')->where('open_idempotency_key', $key)->first();

        if ($existing) {
            if ((int) $existing->user_id !== $user->id) {
                throw new RuntimeException('Request key already used.');
            }

            if ($existing->symbol !== $symbol || $existing->direction !== $direction
                || Decimal::compare((string) $existing->stake, $stake) !== 0
                || (int) $existing->duration_seconds !== $duration) {
                throw new RuntimeException('Request key was already used for a different trade.');
            }

            return (int) $existing->id;
        }

        // Reject stale data before reserving any balance.
        $quote = $this->markets->freshExecutionQuote($symbol);
        $currency = config('shell.paper.currency', 'USD');

        return DB::transaction(function () use ($user, $symbol, $direction, $stake, $duration, $key, $quote, $currency) {
            $existing = DB::table('paper_trades')->where('open_idempotency_key', $key)->first();

            if ($existing) {
                if ((int) $existing->user_id !== $user->id) {
                    throw new RuntimeException('Request key already used.');
                }

                if ($existing->symbol !== $symbol || $existing->direction !== $direction
                    || Decimal::compare((string) $existing->stake, $stake) !== 0
                    || (int) $existing->duration_seconds !== $duration) {
                    throw new RuntimeException('Request key was already used for a different trade.');
                }
                return (int) $existing->id;
            }

            // Recheck quote age after acquiring the transaction. A provider
            // response that was fresh before waiting on ledger locks must not
            // authorize an execution after it has become stale.
            $quoteCapturedAt = strtotime((string) $quote->captured_at);
            if ($quoteCapturedAt === false
                || $quoteCapturedAt > now()->timestamp
                || $quoteCapturedAt < now()->subSeconds(10)->timestamp) {
                throw new RuntimeException(
                    'The verified market quote expired while your trade was being prepared. No stake was deducted; please retry.'
                );
            }

            $wallet = $this->ledger->provisionClientWallets($user, $currency)['demo'];
            // LedgerService locks all affected accounts in a consistent order
            // and enforces the non-negative wallet invariant atomically.

            $escrow = $this->ledger->system('PAPER_ESCROW', $currency);
            $this->ledger->post(
                'paper-open:' . $key,
                'Paper trade stake reserved',
                [
                    ['account_id' => $wallet, 'side' => 'debit', 'amount' => $stake],
                    ['account_id' => $escrow, 'side' => 'credit', 'amount' => $stake],
                ],
                ['user_id' => $user->id, 'symbol' => $symbol, 'environment' => 'paper']
            );

            // Another request may have completed while this request waited for
            // the same ledger accounts. Return its trade rather than duplicating it.
            $existing = DB::table('paper_trades')->where('open_idempotency_key', $key)->first();
            if ($existing) {
                if ((int) $existing->user_id !== $user->id) {
                    throw new RuntimeException('Request key already used.');
                }
                if ($existing->symbol !== $symbol || $existing->direction !== $direction
                    || Decimal::compare((string) $existing->stake, $stake) !== 0
                    || (int) $existing->duration_seconds !== $duration) {
                    throw new RuntimeException('Request key was already used for a different trade.');
                }
                return (int) $existing->id;
            }

            return (int) DB::table('paper_trades')->insertGetId([
                'user_id' => $user->id,
                'symbol' => $symbol,
                'direction' => $direction,
                'stake' => $stake,
                'wallet_currency' => $currency,
                'opening_price' => $quote->price,
                'duration_seconds' => $duration,
                'status' => 'open',
                'open_idempotency_key' => $key,
                'opened_at' => now(),
                'expires_at' => now()->addSeconds($duration),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, 3);
    }

    public function settleDue(): int
    {
        $dueTrades = DB::table('paper_trades')
            ->where('status', 'open')
            ->where('expires_at', '<=', now())
            ->orderBy('expires_at')
            ->get();

        $settled = 0;

        // Refresh only symbols with expired positions that still lack a
        // post-expiry snapshot. Pulling the full ticker catalogue here added
        // unnecessary latency to settlement and could leave the modal waiting
        // on unrelated markets.
        foreach ($dueTrades->pluck('symbol')->unique() as $symbol) {
            $symbolTrades = $dueTrades->where('symbol', $symbol);
            $needsRefresh = $symbolTrades->contains(function ($trade) {
                return !DB::table('market_quote_snapshots')
                    ->where('symbol', $trade->symbol)
                    ->where('captured_at', '>=', $trade->expires_at)
                    ->exists()
                    && !DB::table('market_quotes')
                        ->where('symbol', $trade->symbol)
                        ->where('captured_at', '>=', $trade->expires_at)
                        ->exists();
            });

            if ($needsRefresh) {
                try {
                    $this->markets->refreshSymbolQuote($symbol);
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        }

        foreach ($dueTrades as $candidate) {
            // Use the first server-received snapshot at or after expiry. A delayed
            // scheduler must not make an expired trade permanently un-settleable.
            $quote = DB::table('market_quote_snapshots')
                ->where('symbol', $candidate->symbol)
                ->where('captured_at', '>=', $candidate->expires_at)
                ->where('captured_at', '<=', now())
                ->orderBy('captured_at')
                ->first();

            if (!$quote) {
                $liveQuote = DB::table('market_quotes')
                    ->where('symbol', $candidate->symbol)
                    ->orderByDesc('captured_at')
                    ->first();

                if ($liveQuote) {
                    $capturedTimestamp = strtotime((string) $liveQuote->captured_at);
                    $expiryTimestamp = strtotime((string) $candidate->expires_at);

                    if ($capturedTimestamp >= $expiryTimestamp && $capturedTimestamp <= time()) {
                        $quote = $liveQuote;
                    }
                }
            }

            // Never strand an expired position because the scheduler or quote
            // provider was delayed beyond an arbitrary grace window. Use the
            // earliest verified server snapshot at/after expiry. If no such
            // snapshot exists yet, keep the position pending and retry shortly.
            if (!$quote) {
                continue;
            }

            DB::transaction(function () use ($candidate, $quote, &$settled) {
                $trade = DB::table('paper_trades')->where('id', $candidate->id)->lockForUpdate()->first();

                if (!$trade || $trade->status !== 'open' || now()->lt($trade->expires_at)) {
                    return;
                }

                // Recheck that this is a server-captured post-expiry quote
                // after acquiring the trade lock. There is intentionally no
                // arbitrary maximum delay: that old cutoff could permanently
                // strand a trade when the scheduler was offline for too long.
                if ($quote->captured_at < $trade->expires_at) {
                    return;
                }

                $comparison = Decimal::compare((string) $quote->price, (string) $trade->opening_price);
                $won = ($trade->direction === 'UP' && $comparison > 0)
                    || ($trade->direction === 'DOWN' && $comparison < 0);
                $tie = $comparison === 0;

                $currency = $trade->wallet_currency ?: config('shell.paper.currency', 'USD');
                $wallet = $this->ledger->demoWallet(User::findOrFail($trade->user_id), $currency);
                $escrow = $this->ledger->system('PAPER_ESCROW', $currency);
                $entries = [['account_id' => $escrow, 'side' => 'debit', 'amount' => $trade->stake]];

                if ($tie) {
                    $entries[] = ['account_id' => $wallet, 'side' => 'credit', 'amount' => $trade->stake];
                    $status = 'void';
                } elseif ($won) {
                    $payout = Decimal::multiplyRatio(
                        (string) $trade->stake,
                        (int) config('shell.paper.payout_basis_points', 18000),
                        10000
                    );
                    $profit = Decimal::fromUnits(Decimal::units($payout) - Decimal::units((string) $trade->stake));
                    $rewards = $this->ledger->system('PAPER_REWARDS', $currency, 'debit');
                    $entries[] = ['account_id' => $rewards, 'side' => 'debit', 'amount' => $profit];
                    $entries[] = ['account_id' => $wallet, 'side' => 'credit', 'amount' => $payout];
                    $status = 'won';
                } else {
                    $revenue = $this->ledger->system('PAPER_REVENUE', $currency);
                    $entries[] = ['account_id' => $revenue, 'side' => 'credit', 'amount' => $trade->stake];
                    $status = 'lost';
                }

                $key = 'paper-settle:' . $trade->id;
                $this->ledger->post(
                    $key,
                    'Paper trade settlement',
                    $entries,
                    ['trade_id' => $trade->id, 'outcome' => $status, 'environment' => 'paper']
                );

                DB::table('paper_trades')->where('id', $trade->id)->update([
                    'closing_price' => $quote->price,
                    'status' => $status,
                    'settlement_idempotency_key' => $key,
                    'settled_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('user_notifications')->insertOrIgnore([
                    'user_id' => $trade->user_id,
                    'event_key' => 'paper-trade-settled:' . $trade->id,
                    'type' => 'paper_trade_settled',
                    'title' => 'Paper trade settled: ' . $trade->symbol,
                    'body' => 'Your ' . $trade->direction . ' paper trade settled as ' . strtoupper($status) . '. This is a simulation result, not a cash return.',
                    'data' => json_encode(['trade_id' => $trade->id, 'status' => $status], JSON_THROW_ON_ERROR),
                    'read_at' => null, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $settled++;
            }, 3);
        }

        return $settled;
    }
}
