<?php

namespace App\\Services;

use App\\Models\\User;
use App\\Support\\Decimal;
use Illuminate\\Support\\Facades\\DB;
use RuntimeException;

class LedgerService
{
    public function account(string $owner, string $code, string $currency, string $normal): int
    {
        DB::table('ledger_accounts')->insertOrIgnore([
            'owner_key' => $owner,
            'code' => $code,
            'currency' => $currency,
            'normal_side' => $normal,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = DB::table('ledger_accounts')
            ->where(['owner_key' => $owner, 'code' => $code, 'currency' => $currency])
            ->value('id');

        if (!$id) {
            throw new RuntimeException('Unable to initialize ledger account.');
        }

        return (int) $id;
    }

    public function wallet(User $user, string $currency = 'USDT'): int
    {
        return $this->account('USER:'.$user->id, 'PAPER_WALLET', $currency, 'credit');
    }

    public function system(string $code, string $currency = 'USDT', string $normal = 'credit'): int
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

    /**
     * Post a balanced transaction atomically. Account locks serialize balance
     * changes and the unique idempotency key prevents duplicate postings.
     */
    public function post(string $key, string $description, array $entries, array $metadata = []): int
    {
        if ($key === '' || strlen($key) > 120) {
            throw new RuntimeException('Invalid ledger idempotency key.');
        }

        return DB::transaction(function () use ($key, $description, $entries, $metadata) {
            $existing = DB::table('ledger_transactions')->where('idempotency_key', $key)->value('id');

            if ($existing) {
                return (int) $existing;
            }

            $accountIds = collect($entries)->pluck('account_id')->map(fn ($id) => (int) $id)->unique()->sort()->values();

            if ($accountIds->isEmpty()) {
                throw new RuntimeException('A ledger transaction requires entries.');
            }

            $accounts = DB::table('ledger_accounts')
                ->whereIn('id', $accountIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($accounts->count() !== $accountIds->count()) {
                throw new RuntimeException('Ledger account missing.');
            }

            $debit = 0;
            $credit = 0;
            $currency = null;

            foreach ($entries as $entry) {
                $account = $accounts->get((int) $entry['account_id']);
                $currency ??= $account->currency;

                if ($currency !== $account->currency) {
                    throw new RuntimeException('Mixed ledger currencies are forbidden.');
                }

                $amount = Decimal::units((string) $entry['amount']);

                if ($amount <= 0) {
                    throw new RuntimeException('Ledger amounts must be positive.');
                }

                if ($entry['side'] === 'debit') {
                    $debit += $amount;
                } elseif ($entry['side'] === 'credit') {
                    $credit += $amount;
                } else {
                    throw new RuntimeException('Invalid ledger side.');
                }
            }

            if ($debit !== $credit || $debit === 0) {
                throw new RuntimeException('Unbalanced ledger transaction.');
            }

            $transactionId = DB::table('ledger_transactions')->insertGetId([
                'idempotency_key' => $key,
                'description' => $description,
                'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
                'posted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($entries as $entry) {
                DB::table('ledger_entries')->insert([
                    'ledger_transaction_id' => $transactionId,
                    'ledger_account_id' => $entry['account_id'],
                    'side' => $entry['side'],
                    'amount' => $entry['amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return (int) $transactionId;
        }, 3);
    }

    public function creditPaperWallet(User $user, string $amount, string $currency = 'USDT'): int
    {
        $wallet = $this->wallet($user, $currency);
        $capital = $this->system('PAPER_CAPITAL', $currency, 'debit');

        return $this->post(
            'paper-credit:'. $user->id . ':' . now()->timestamp . ':' . bin2hex(random_bytes(8)),
            'Administrator-issued simulation credits',
            [
                ['account_id' => $capital, 'side' => 'debit', 'amount' => $amount],
                ['account_id' => $wallet, 'side' => 'credit', 'amount' => $amount],
            ],
            ['user_id' => $user->id, 'environment' => 'paper']
        );
    }
}
