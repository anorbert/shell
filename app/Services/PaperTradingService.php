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

        // Reject stale data before reserving any balance.
        $quote = $this->markets->freshQuote($symbol);
        $currency = config('shell.paper.currency', 'USDT');

        return DB::transaction(function () use ($user, $symbol, $direction, $stake, $duration, $key, $quote, $currency) {
            $existing = DB::table('paper_trades')->where('open_idempotency_key', $key)->first();

            if ($existing) {
                if ((int) $existing->user_id !== $user->id) {
                    throw new RuntimeException('Request key already used.');
                }

                return (int) $existing->id;
            }

            $wallet = $this->ledger->wallet($user, $currency);
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
                return (int) $existing->id;
            }

            return (int) DB::table('paper_trades')->insertGetId([
                'user_id' => $user->id,
                'symbol' => $symbol,
                'direction' => $direction,
                'stake' => $stake,
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
            ->limit(500)
            ->get();

        $settled = 0;

        foreach ($dueTrades as $candidate) {
            // A settlement price must have been captured after this trade expired.
            $quote = DB::table('market_quotes')
                ->where('symbol', $candidate->symbol)
                ->where('captured_at', '>=', $candidate->expires_at)
                ->first();

            if (!$quote || now()->subSeconds((int) config('shell.market.max_age_seconds', 90))->gt($quote->captured_at)) {
                continue;
            }

            DB::transaction(function () use ($candidate, $quote, &$settled) {
                $trade = DB::table('paper_trades')->where('id', $candidate->id)->lockForUpdate()->first();

                if (!$trade || $trade->status !== 'open' || now()->lt($trade->expires_at)) {
                    return;
                }

                // Recheck quote freshness after acquiring the trade lock.
                if (now()->subSeconds((int) config('shell.market.max_age_seconds', 90))->gt($quote->captured_at)
                    || $quote->captured_at < $trade->expires_at) {
                    return;
                }

                $comparison = Decimal::compare((string) $quote->price, (string) $trade->opening_price);
                $won = ($trade->direction === 'UP' && $comparison > 0)
                    || ($trade->direction === 'DOWN' && $comparison < 0);
                $tie = $comparison === 0;

                $currency = config('shell.paper.currency', 'USDT');
                $wallet = $this->ledger->wallet(User::findOrFail($trade->user_id), $currency);
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

                $settled++;
            }, 3);
        }

        return $settled;
    }
}
