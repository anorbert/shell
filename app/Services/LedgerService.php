<?php

namespace App\Services;

use App\Models\User;
use App\Support\Decimal;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LedgerService
{
    public function account(string $owner, string $code, string $currency, string $normal): int
    {
        DB::table('ledger_accounts')->insertOrIgnore([
            'owner_key' => $owner, 'code' => $code, 'currency' => $currency,
            'normal_side' => $normal, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $id = DB::table('ledger_accounts')
            ->where(['owner_key' => $owner, 'code' => $code, 'currency' => $currency])->value('id');
        if (!$id) throw new RuntimeException('Unable to initialize ledger account.');
        return (int) $id;
    }

    public function demoWallet(User $user, string $currency = 'USD'): int
    {
        return $this->account('USER:'.$user->id, 'PAPER_WALLET', $currency, 'credit');
    }

    public function realWallet(User $user, string $currency = 'USD'): int
    {
        return $this->account('USER:'.$user->id, 'REAL_WALLET', $currency, 'credit');
    }

    /** @deprecated Use demoWallet() in new code. */
    public function wallet(User $user, string $currency = 'USD'): int
    {
        return $this->demoWallet($user, $currency);
    }

    /** Every client gets one $20,000 USD demo welcome grant and an empty real wallet. */
    public function provisionClientWallets(User $user, string $currency = 'USD'): array
    {
        return DB::transaction(function () use ($user, $currency) {
            $demo = $this->demoWallet($user, $currency);
            $real = $this->realWallet($user, $currency);
            $welcomeKey = 'demo-welcome:'.$user->id.':'.$currency.':v1';
            $existing = DB::table('ledger_transactions')->where('idempotency_key', $welcomeKey)->exists();

            if (!$existing) {
                $capital = $this->system('DEMO_PROMOTIONAL_CAPITAL', $currency, 'debit');
                $this->post($welcomeKey, 'Initial demo wallet welcome credit', [
                    ['account_id' => $capital, 'side' => 'debit', 'amount' => (string) config('shell.paper.welcome_balance', '20000.00000000')],
                    ['account_id' => $demo, 'side' => 'credit', 'amount' => (string) config('shell.paper.welcome_balance', '20000.00000000')],
                ], ['user_id' => $user->id, 'environment' => 'demo', 'grant_type' => 'welcome', 'cash_value' => false]);
            }

            return ['demo' => $demo, 'real' => $real];
        }, 3);
    }

    public function system(string $code, string $currency = 'USD', string $normal = 'credit'): int
    {
        return $this->account('SYSTEM', $code, $currency, $normal);
    }

    public function balanceUnits(int $accountId): int
    {
        $sum = 0;
        foreach (DB::table('ledger_entries')->where('ledger_account_id', $accountId)->get(['side', 'amount']) as $entry) {
            $units = Decimal::units((string) $entry->amount);
            $sum += $entry->side === 'credit' ? $units : -$units;
        }
        return $sum;
    }

    public function post(string $key, string $description, array $entries, array $metadata = []): int
    {
        if ($key === '' || strlen($key) > 120) throw new RuntimeException('Invalid ledger idempotency key.');

        return DB::transaction(function () use ($key, $description, $entries, $metadata) {
            $existing = $this->existingPostingId($key, $description, $entries, $metadata);
            if ($existing !== null) return $existing;

            $accountIds = collect($entries)->pluck('account_id')->map(fn ($id) => (int) $id)->unique()->sort()->values();
            if ($accountIds->isEmpty()) throw new RuntimeException('A ledger transaction requires entries.');

            $accounts = DB::table('ledger_accounts')->whereIn('id', $accountIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($accounts->count() !== $accountIds->count()) throw new RuntimeException('Ledger account missing.');
            $existing = $this->existingPostingId($key, $description, $entries, $metadata);
            if ($existing !== null) return $existing;

            $debit = 0; $credit = 0; $currency = null;
            foreach ($entries as $entry) {
                $account = $accounts->get((int) $entry['account_id']);
                $currency ??= $account->currency;
                if ($currency !== $account->currency) throw new RuntimeException('Mixed ledger currencies are forbidden.');
                $amount = Decimal::units((string) $entry['amount']);
                if ($amount <= 0) throw new RuntimeException('Ledger amounts must be positive.');
                if ($entry['side'] === 'debit') $debit += $amount;
                elseif ($entry['side'] === 'credit') $credit += $amount;
                else throw new RuntimeException('Invalid ledger side.');
            }
            if ($debit !== $credit || $debit === 0) throw new RuntimeException('Unbalanced ledger transaction.');

            foreach ($accounts as $account) {
                if (!in_array($account->code, ['PAPER_WALLET', 'REAL_WALLET'], true) || $account->normal_side !== 'credit') continue;
                $netChange = 0;
                foreach ($entries as $entry) {
                    if ((int) $entry['account_id'] !== (int) $account->id) continue;
                    $units = Decimal::units((string) $entry['amount']);
                    $netChange += $entry['side'] === 'credit' ? $units : -$units;
                }
                if ($this->balanceUnits((int) $account->id) + $netChange < 0) {
                    throw new RuntimeException($account->code === 'PAPER_WALLET'
                        ? 'Insufficient paper balance; ledger posting would create a negative wallet balance.'
                        : 'Insufficient real wallet balance; ledger posting would create a negative balance.');
                }
            }

            $transactionId = DB::table('ledger_transactions')->insertGetId([
                'idempotency_key' => $key, 'description' => $description,
                'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR), 'posted_at' => now(),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($entries as $entry) {
                DB::table('ledger_entries')->insert([
                    'ledger_transaction_id' => $transactionId, 'ledger_account_id' => $entry['account_id'],
                    'side' => $entry['side'], 'amount' => $entry['amount'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            return (int) $transactionId;
        }, 3);
    }

    /**
     * Idempotency means "same key, same posting", not merely "same key".
     * Reject a reused key whose account movements or metadata differ.
     */
    private function existingPostingId(string $key, string $description, array $entries, array $metadata): ?int
    {
        $transaction = DB::table('ledger_transactions')->where('idempotency_key', $key)->first();
        if (!$transaction) {
            return null;
        }

        $canonicalEntries = static function (array $rows): array {
            $totals = [];
            foreach ($rows as $row) {
                $row = (array) $row;
                $accountId = (int) ($row['account_id'] ?? $row['ledger_account_id'] ?? 0);
                $side = (string) ($row['side'] ?? '');
                $amount = (string) ($row['amount'] ?? '0');
                if ($accountId <= 0 || !in_array($side, ['debit', 'credit'], true)) {
                    throw new RuntimeException('Invalid ledger entry in idempotency comparison.');
                }
                $identity = $accountId . ':' . $side;
                $totals[$identity] = ($totals[$identity] ?? 0) + Decimal::units($amount);
            }
            ksort($totals);
            return $totals;
        };

        $storedEntries = DB::table('ledger_entries')
            ->where('ledger_transaction_id', $transaction->id)
            ->get(['ledger_account_id', 'side', 'amount'])
            ->all();

        $canonicalize = static function (&$value) use (&$canonicalize): void {
            if (!is_array($value)) {
                return;
            }
            foreach ($value as &$child) {
                $canonicalize($child);
            }
            unset($child);
            if (array_keys($value) !== range(0, count($value) - 1)) {
                ksort($value);
            }
        };

        $storedMetadata = json_decode((string) $transaction->metadata, true) ?: [];
        $expectedMetadata = $metadata;
        $canonicalize($storedMetadata);
        $canonicalize($expectedMetadata);

        if ($transaction->description !== $description
            || $canonicalEntries($storedEntries) !== $canonicalEntries($entries)
            || $storedMetadata !== $expectedMetadata) {
            throw new RuntimeException('Ledger idempotency key was already used for a different posting.');
        }

        return (int) $transaction->id;
    }

    public function addDemoFunds(User $user, string $amount, string $requestKey, string $currency = 'USD'): int
    {
        if (!preg_match('/^[0-9a-f-]{36}$/i', $requestKey)) throw new RuntimeException('Invalid demo top-up request key.');
        $units = Decimal::units($amount);
        if ($units < Decimal::units((string) config('shell.paper.topup_min', '100.00000000')) || $units > Decimal::units((string) config('shell.paper.topup_max', '20000.00000000'))) {
            throw new RuntimeException('Demo top-ups must be between $100 and $20,000.');
        }

        return DB::transaction(function () use ($user, $amount, $requestKey, $currency) {
            $key = 'demo-topup:'.$user->id.':'.$requestKey;
            $existing = DB::table('ledger_transactions')->where('idempotency_key', $key)->first();
            if ($existing) {
                $meta = json_decode((string) $existing->metadata, true) ?: [];
                if ((int) ($meta['user_id'] ?? 0) !== $user->id || Decimal::compare((string) ($meta['amount'] ?? '0'), $amount) !== 0) {
                    throw new RuntimeException('This demo top-up request key was already used.');
                }
                return (int) $existing->id;
            }
            $wallets = $this->provisionClientWallets($user, $currency);
            $wallet = $wallets['demo'];
            $capital = $this->system('DEMO_TOPUP_CAPITAL', $currency, 'debit');
            return $this->post($key, 'Client-requested demo wallet top-up', [
                ['account_id' => $capital, 'side' => 'debit', 'amount' => $amount],
                ['account_id' => $wallet, 'side' => 'credit', 'amount' => $amount],
            ], ['user_id' => $user->id, 'amount' => $amount, 'environment' => 'demo', 'cash_value' => false]);
        }, 3);
    }

    public function creditPaperWallet(User $user, string $amount, string $currency = 'USD'): int
    {
        $wallet = $this->demoWallet($user, $currency);
        $capital = $this->system('PAPER_CAPITAL', $currency, 'debit');
        return $this->post('paper-credit:'.$user->id.':'.now()->timestamp.':'.bin2hex(random_bytes(8)),
            'Administrator-issued simulation credits', [
                ['account_id' => $capital, 'side' => 'debit', 'amount' => $amount],
                ['account_id' => $wallet, 'side' => 'credit', 'amount' => $amount],
            ], ['user_id' => $user->id, 'environment' => 'demo', 'cash_value' => false]);
    }
}
