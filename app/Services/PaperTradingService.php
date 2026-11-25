<?php

namespace App\Services;

use App\Models\User;
use App\Support\Decimal;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PaperTradingService
{
    public function __construct(
        private LedgerService $ledger,
        private MarketQuoteService $markets
    ) {}

    public function open(User $user, string $symbol, string $direction, string $stake, int $duration, string $key, string $environment = 'demo'): int
    {
        if (!config('shell.paper.enabled')) {
            throw new RuntimeException('Trading is temporarily disabled.');
        }

        if (!in_array($environment, ['demo', 'real'], true)) {
            throw new RuntimeException('Invalid trading account.');
        }

        if ($environment === 'real' && !app(ManualWalletService::class)->hasSuccessfulDeposit($user)) {
            throw new RuntimeException('Real trading unlocks after your first deposit has been verified and credited to your real wallet.');
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
            throw new RuntimeException('Stake is outside the allowed trading limits.');
        }

        // A retry must resolve to the original trade before making provider calls.
        $existing = DB::table('paper_trades')->where('open_idempotency_key', $key)->first();
        if ($existing) {
            return $this->assertSameOpenRequest($existing, $user, $symbol, $direction, $stake, $duration, $environment);
        }

        $quote = $this->markets->freshExecutionQuote($symbol);
        $currency = config('shell.paper.currency', 'USD');

        return DB::transaction(function () use ($user, $symbol, $direction, $stake, $duration, $key, $quote, $currency, $environment) {
            $existing = DB::table('paper_trades')->where('open_idempotency_key', $key)->first();
            if ($existing) {
                return $this->assertSameOpenRequest($existing, $user, $symbol, $direction, $stake, $duration, $environment);
            }

            $now = now('UTC');
            $capturedAt = \Illuminate\Support\Carbon::parse((string) $quote->captured_at, 'UTC');
            if ($capturedAt->greaterThan($now) || $capturedAt->lessThan($now->copy()->subSeconds(10))) {
                throw new RuntimeException(
                    'The verified market quote expired while your trade was being prepared. No stake was deducted; please retry.'
                );
            }

            $wallet = $this->ledger->provisionClientWallets($user, $currency)[$environment];
            $isReal = $environment === 'real';
            $escrow = $this->ledger->system($isReal ? 'REAL_TRADE_ESCROW' : 'PAPER_ESCROW', $currency);
            $entries = [
                ['account_id' => $wallet, 'side' => 'debit', 'amount' => $stake],
                ['account_id' => $escrow, 'side' => 'credit', 'amount' => $stake],
            ];
            $payoutBasisPoints = (int) config('shell.paper.payout_basis_points', 18000);
            if ($payoutBasisPoints < 10000) {
                throw new RuntimeException('The configured payout rate is invalid; real and demo trading are paused until it is corrected.');
            }
            $reservedProfit = '0.00000000';
            if ($isReal && $payoutBasisPoints > 10000) {
                $reservedProfit = Decimal::multiplyRatio($stake, $payoutBasisPoints - 10000, 10000);
                $reserve = $this->ledger->system('REAL_PAYOUT_RESERVE', $currency, 'credit');
                $profitEscrow = $this->ledger->system('REAL_PAYOUT_ESCROW', $currency, 'credit');
                // Move available reserve into a dedicated credit-normal payout escrow.
                // The reserve is reduced now, so concurrent positions cannot over-commit
                // the same funds; settlement either pays this escrow or releases it.
                $entries[] = ['account_id' => $reserve, 'side' => 'debit', 'amount' => $reservedProfit];
                $entries[] = ['account_id' => $profitEscrow, 'side' => 'credit', 'amount' => $reservedProfit];
            }

            $this->ledger->post(
                ($isReal ? 'real-open:' : 'paper-open:') . $key,
                $isReal ? 'Real trade stake and payout reserve locked' : 'Demo trade stake reserved',
                $entries,
                ['user_id' => $user->id, 'symbol' => $symbol, 'environment' => $environment, 'reserved_profit' => $reservedProfit]
            );

            // The account locks inside LedgerService serialize same-wallet opens.
            // The unique trade key is the final database-level idempotency guard.
            $existing = DB::table('paper_trades')->where('open_idempotency_key', $key)->first();
            if ($existing) {
                return $this->assertSameOpenRequest($existing, $user, $symbol, $direction, $stake, $duration, $environment);
            }

            return (int) DB::table('paper_trades')->insertGetId([
                'user_id' => $user->id,
                'symbol' => $symbol,
                'direction' => $direction,
                'stake' => $stake,
                'wallet_currency' => $currency,
                'account_type' => $environment,
                'payout_basis_points' => $payoutBasisPoints,
                'opening_price' => (string) $quote->price,
                'duration_seconds' => $duration,
                'status' => 'open',
                'open_idempotency_key' => $key,
                'opened_at' => $now,
                'expires_at' => $now->copy()->addSeconds($duration),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, 3);
    }

    private function assertSameOpenRequest(object $existing, User $user, string $symbol, string $direction, string $stake, int $duration, string $environment): int
    {
        if ((int) $existing->user_id !== $user->id) {
            throw new RuntimeException('Request key already used.');
        }

        if ($existing->symbol !== $symbol || $existing->direction !== $direction
            || Decimal::compare((string) $existing->stake, $stake) !== 0
            || (int) $existing->duration_seconds !== $duration
            || (($existing->account_type ?? 'demo') !== $environment)) {
            throw new RuntimeException('Request key was already used for a different trade.');
        }

        return (int) $existing->id;
    }

    /**
     * Settle due positions independently. A failure for one position must not
     * prevent later positions from being processed in the same scheduler run.
     *
     * Policy: after one targeted server-side refresh attempt, absence of a
     * post-expiry quote voids the trade and refunds its full stake from escrow.
     * A late quote is never allowed to turn that refunded trade into a result.
     */
    public function settleDue(): int
    {
        $settled = 0;
        $now = now('UTC');

        DB::table('paper_trades')
            ->where('status', 'open')
            ->where('expires_at', '<=', $now)
            ->orderBy('id')
            ->chunkById(100, function ($trades) use (&$settled) {
                foreach ($trades as $candidate) {
                    try {
                        $quote = $this->postExpiryQuote($candidate);
                        $closed = $this->closeTrade((int) $candidate->id, $quote);
                        if ($closed) {
                            $settled++;
                        }
                    } catch (Throwable $exception) {
                        // Isolate the failure. The next candidate still gets a
                        // settlement attempt, and this trade remains retryable.
                        report($exception);
                    }
                }
            });

        return $settled;
    }

    private function postExpiryQuote(object $trade): ?object
    {
        $expiresAt = \Illuminate\Support\Carbon::parse((string) $trade->expires_at, 'UTC');
        $deadline = $expiresAt->copy()->addSeconds(max(0, (int) config('shell.paper.settlement_quote_grace_seconds', 15)));
        $now = now('UTC');

        // A late price is not a valid expiry price. Once the bounded capture
        // window has elapsed, do not fetch a newer market value and pretend it
        // represents the earlier expiry.
        if ($now->greaterThan($deadline)) {
            return null;
        }

        $quote = DB::table('market_quote_snapshots')
            ->where('symbol', $trade->symbol)
            ->where('captured_at', '>=', $expiresAt)
            ->where('captured_at', '<=', $deadline)
            ->where('captured_at', '<=', $now)
            ->orderBy('captured_at')
            ->orderBy('id')
            ->first();

        if ($quote) {
            return $quote;
        }

        try {
            // A targeted refresh records server receipt time, never a browser
            // timestamp or a provider-supplied event timestamp.
            $this->markets->refreshSymbolQuote($trade->symbol);
        } catch (Throwable $exception) {
            report($exception);
        }

        return DB::table('market_quote_snapshots')
            ->where('symbol', $trade->symbol)
            ->where('captured_at', '>=', $expiresAt)
            ->where('captured_at', '<=', $deadline)
            ->where('captured_at', '<=', now('UTC'))
            ->orderBy('captured_at')
            ->orderBy('id')
            ->first();
    }

    private function closeTrade(int $tradeId, ?object $quote): bool
    {
        return DB::transaction(function () use ($tradeId, $quote) {
            $trade = DB::table('paper_trades')->where('id', $tradeId)->lockForUpdate()->first();

            if (!$trade || $trade->status !== 'open' || now('UTC')->lt($trade->expires_at)) {
                return false;
            }

            $expiry = \Illuminate\Support\Carbon::parse((string) $trade->expires_at, 'UTC');
            $quoteCapturedAt = $quote
                ? \Illuminate\Support\Carbon::parse((string) $quote->captured_at, 'UTC')
                : null;
            $quoteDeadline = $expiry->copy()->addSeconds(max(0, (int) config('shell.paper.settlement_quote_grace_seconds', 15)));
            $validQuote = $quote
                && $quote->symbol === $trade->symbol
                && $quoteCapturedAt->greaterThanOrEqualTo($expiry)
                && $quoteCapturedAt->lessThanOrEqualTo($quoteDeadline)
                && $quoteCapturedAt->lessThanOrEqualTo(now('UTC'));

            $currency = $trade->wallet_currency ?: config('shell.paper.currency', 'USD');
            $environment = $trade->account_type ?? 'demo';
            $isReal = $environment === 'real';
            $user = User::query()->findOrFail($trade->user_id);
            $wallet = $isReal ? $this->ledger->realWallet($user, $currency) : $this->ledger->demoWallet($user, $currency);
            $escrow = $this->ledger->system($isReal ? 'REAL_TRADE_ESCROW' : 'PAPER_ESCROW', $currency);
            $profitEscrow = $isReal ? $this->ledger->system('REAL_PAYOUT_ESCROW', $currency, 'credit') : null;
            $reserve = $isReal ? $this->ledger->system('REAL_PAYOUT_RESERVE', $currency, 'credit') : null;
            $reservedProfit = $isReal && (int) ($trade->payout_basis_points ?? 18000) > 10000
                ? Decimal::multiplyRatio((string) $trade->stake, (int) $trade->payout_basis_points - 10000, 10000)
                : '0.00000000';
            $entries = [];
            $status = 'void';
            $closingPrice = null;

            if (!$validQuote) {
                // No trustworthy expiry price: release the reserved stake in full.
                $entries[] = ['account_id' => $escrow, 'side' => 'debit', 'amount' => (string) $trade->stake];
                $entries[] = ['account_id' => $wallet, 'side' => 'credit', 'amount' => (string) $trade->stake];
                if ($isReal && Decimal::units($reservedProfit) > 0) {
                    $entries[] = ['account_id' => $profitEscrow, 'side' => 'debit', 'amount' => $reservedProfit];
                    $entries[] = ['account_id' => $reserve, 'side' => 'credit', 'amount' => $reservedProfit];
                }
            } else {
                $closingPrice = (string) $quote->price;
                $comparison = Decimal::compare($closingPrice, (string) $trade->opening_price);
                $won = ($trade->direction === 'UP' && $comparison > 0)
                    || ($trade->direction === 'DOWN' && $comparison < 0);
                $tie = $comparison === 0;

                $entries[] = ['account_id' => $escrow, 'side' => 'debit', 'amount' => (string) $trade->stake];

                if ($tie) {
                    $entries[] = ['account_id' => $wallet, 'side' => 'credit', 'amount' => (string) $trade->stake];
                    if ($isReal && Decimal::units($reservedProfit) > 0) {
                        $entries[] = ['account_id' => $profitEscrow, 'side' => 'debit', 'amount' => $reservedProfit];
                        $entries[] = ['account_id' => $reserve, 'side' => 'credit', 'amount' => $reservedProfit];
                    }
                    $status = 'void';
                } elseif ($won) {
                    $payout = Decimal::multiplyRatio(
                        (string) $trade->stake,
                        (int) ($trade->payout_basis_points ?? config('shell.paper.payout_basis_points', 18000)),
                        10000
                    );
                    $profit = Decimal::fromUnits(Decimal::units($payout) - Decimal::units((string) $trade->stake));
                    if ($isReal) {
                        if (Decimal::units($profit) > 0) {
                            // Convert the locked reserve liability into the user's real-wallet liability.
                            $entries[] = ['account_id' => $profitEscrow, 'side' => 'debit', 'amount' => $profit];
                        }
                    } else {
                        $rewards = $this->ledger->system('PAPER_REWARDS', $currency, 'debit');
                        $entries[] = ['account_id' => $rewards, 'side' => 'debit', 'amount' => $profit];
                    }
                    $entries[] = ['account_id' => $wallet, 'side' => 'credit', 'amount' => $payout];
                    $status = 'won';
                } else {
                    $revenue = $this->ledger->system($isReal ? 'REAL_TRADE_REVENUE' : 'PAPER_REVENUE', $currency);
                    $entries[] = ['account_id' => $revenue, 'side' => 'credit', 'amount' => (string) $trade->stake];
                    if ($isReal && Decimal::units($reservedProfit) > 0) {
                        $entries[] = ['account_id' => $profitEscrow, 'side' => 'debit', 'amount' => $reservedProfit];
                        $entries[] = ['account_id' => $reserve, 'side' => 'credit', 'amount' => $reservedProfit];
                    }
                    $status = 'lost';
                }
            }

            $key = 'paper-settle:' . $trade->id;
            $this->ledger->post(
                $key,
                $status === 'void' ? 'Paper trade void/refund' : 'Paper trade settlement',
                $entries,
                [
                    'trade_id' => $trade->id,
                    'outcome' => $status,
                    'environment' => $environment,
                    'void_reason' => $validQuote ? null : 'missing_post_expiry_quote',
                ]
            );

            $now = now('UTC');
            DB::table('paper_trades')->where('id', $trade->id)->update([
                'closing_price' => $closingPrice,
                'status' => $status,
                'settlement_idempotency_key' => $key,
                'settled_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('user_notifications')->insertOrIgnore([
                'user_id' => $trade->user_id,
                'event_key' => 'paper-trade-settled:' . $trade->id,
                'type' => 'paper_trade_settled',
                'title' => 'Paper trade settled: ' . $trade->symbol,
                'body' => $status === 'void'
                    ? 'Your ' . $environment . ' trade was voided and the full stake was refunded because no verified expiry quote was available.'
                    : 'Your ' . $trade->direction . ' ' . $environment . ' trade settled as ' . strtoupper($status) . '.',
                'data' => json_encode([
                    'trade_id' => $trade->id,
                    'status' => $status,
                    'closing_price' => $closingPrice,
                ], JSON_THROW_ON_ERROR),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return true;
        }, 3);
    }
}
