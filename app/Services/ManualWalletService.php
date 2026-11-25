<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class ManualWalletService
{
    public function __construct(private readonly LedgerService $ledger) {}

    public function notifyUser(User $user, string $key, string $title, string $body, array $data = []): void
    {
        DB::table('user_notifications')->insertOrIgnore([
            'user_id' => $user->id, 'event_key' => $key, 'type' => 'wallet_request',
            'title' => $title, 'body' => $body, 'data' => json_encode($data),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function notifyAdmins(string $key, string $title, string $body, array $data = []): void
    {
        $admins = User::query()->where('role', 'admin')->get(['id', 'email', 'name']);
        foreach ($admins as $admin) {
            $this->notifyUser($admin, $key.':admin:'.$admin->id, $title, $body, $data);
            if ($admin->email) {
                try {
                    Mail::raw($body, function ($message) use ($admin, $title) {
                        $message->to($admin->email)->subject('[Shell] '.$title);
                    });
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        }
    }

    public function requestWithdrawal(User $user, array $data): int
    {
        if (!$this->hasApprovedKyc($user)) {
            throw new RuntimeException('An approved KYC profile is required before requesting a withdrawal.');
        }

        return DB::transaction(function () use ($user, $data) {
            $requestId = DB::table('wallet_requests')->insertGetId([
                'user_id' => $user->id, 'type' => 'withdrawal', 'status' => 'pending',
                'amount_usd' => $data['amount_usd'], 'asset' => strtoupper($data['asset']),
                'network' => $data['network'], 'wallet_address' => $data['wallet_address'],
                'wallet_memo' => $data['wallet_memo'] ?? null, 'client_note' => $data['client_note'] ?? null, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $key = 'withdrawal-reserve:'.$requestId;
            $wallet = $this->ledger->realWallet($user, 'USD');
            $liability = $this->ledger->system('WITHDRAWAL_PENDING', 'USD', 'credit');
            $this->ledger->post($key, 'Reserve real wallet funds for withdrawal request #'.$requestId, [
                ['account_id' => $wallet, 'side' => 'debit', 'amount' => $data['amount_usd']],
                ['account_id' => $liability, 'side' => 'credit', 'amount' => $data['amount_usd']],
            ], ['user_id' => $user->id, 'wallet_request_id' => $requestId, 'type' => 'withdrawal_reserve']);
            DB::table('wallet_requests')->where('id', $requestId)->update(['ledger_key' => $key, 'updated_at' => now()]);
            $this->notifyAdmins('wallet-withdrawal:'.$requestId, 'Withdrawal request requires review',
                $user->name.' ('.$user->email.') requested a '.$data['amount_usd'].' USD withdrawal to '.$data['asset'].' / '.$data['network'].'.',
                ['wallet_request_id' => $requestId, 'type' => 'withdrawal']);
            return $requestId;
        }, 3);
    }

    public function confirmDeposit(
        int $requestId,
        User $admin,
        string $amount,
        ?string $note = null,
        bool $manualApproval = false,
        ?string $internalReference = null
    ): void {
        DB::transaction(function () use ($requestId, $admin, $amount, $note, $manualApproval, $internalReference) {
            $request = DB::table('wallet_requests')->where('id', $requestId)->lockForUpdate()->first();
            if (!$request || $request->type !== 'deposit' || $request->status !== 'pending') {
                throw new RuntimeException('This deposit is no longer awaiting confirmation.');
            }
            if (!$manualApproval && empty($request->transaction_hash)) {
                throw new RuntimeException('A blockchain transaction hash is required for blockchain verification.');
            }
            if ($manualApproval && !filled($internalReference)) {
                throw new RuntimeException('A unique internal reference is required for manual deposit approval.');
            }
            $user = User::query()->whereKey($request->user_id)->lockForUpdate()->firstOrFail();
            $isFirstDeposit = !DB::table('wallet_requests')->where('user_id', $user->id)->where('type', 'deposit')->where('status', 'confirmed')->whereNotNull('ledger_key')->exists();
            $wallet = $this->ledger->realWallet($user, 'USD');
            $clearing = $this->ledger->system('CRYPTO_DEPOSIT_CLEARING', 'USD', 'debit');
            $key = 'manual-deposit-credit:'.$requestId;
            $ledgerMetadata = [
                'user_id' => $user->id,
                'wallet_request_id' => $requestId,
                'admin_id' => $admin->id,
                'type' => $manualApproval ? 'admin_manual_deposit_credit' : 'admin_verified_crypto_deposit',
                'verification_method' => $manualApproval ? 'admin_manual_override' : 'blockchain',
                'transaction_hash' => $request->transaction_hash,
                'internal_reference' => $internalReference,
                'network' => $request->network,
                'asset' => $request->asset,
                'admin_note' => $note,
            ];
            $this->ledger->post(
                $key,
                $manualApproval ? 'Admin-authorized manual deposit credit #'.$requestId : 'Admin-verified blockchain deposit #'.$requestId,
                [
                    ['account_id' => $clearing, 'side' => 'debit', 'amount' => $amount],
                    ['account_id' => $wallet, 'side' => 'credit', 'amount' => $amount],
                ],
                $ledgerMetadata
            );
            $recordedNote = $manualApproval
                ? '[MANUAL ADMIN CREDIT '.$internalReference.' — NO BLOCKCHAIN HASH] '.$note
                : $note;
            DB::table('wallet_requests')->where('id', $requestId)->update([
                'status' => 'confirmed', 'amount_usd' => $amount, 'admin_note' => $recordedNote,
                'reviewed_by' => $admin->id, 'reviewed_at' => now(), 'ledger_key' => $key, 'updated_at' => now(),
            ]);
            app(ReferralCommissionService::class)->recordForConfirmedDeposit($requestId, $isFirstDeposit);
            $this->notifyUser($user, 'wallet-deposit-confirmed:'.$requestId, 'Deposit confirmed',
                'Your crypto deposit request #'.$requestId.' was verified and credited to your real wallet.',
                ['wallet_request_id' => $requestId]);
        }, 3);
    }

    public function reject(int $requestId, User $admin, string $note): void
    {
        DB::transaction(function () use ($requestId, $admin, $note) {
            $request = DB::table('wallet_requests')->where('id', $requestId)->lockForUpdate()->first();
            if (!$request || $request->status !== 'pending') throw new RuntimeException('This request is no longer pending.');
            if ($request->type === 'withdrawal') {
                $user = User::findOrFail($request->user_id);
                $wallet = $this->ledger->realWallet($user, 'USD');
                $liability = $this->ledger->system('WITHDRAWAL_PENDING', 'USD', 'credit');
                $this->ledger->post('withdrawal-release:'.$requestId, 'Release rejected withdrawal reserve #'.$requestId, [
                    ['account_id' => $liability, 'side' => 'debit', 'amount' => $request->amount_usd],
                    ['account_id' => $wallet, 'side' => 'credit', 'amount' => $request->amount_usd],
                ], ['user_id' => $user->id, 'wallet_request_id' => $requestId, 'admin_id' => $admin->id]);
            } else {
                $user = User::findOrFail($request->user_id);
            }
            DB::table('wallet_requests')->where('id', $requestId)->update([
                'status' => 'rejected', 'admin_note' => $note, 'reviewed_by' => $admin->id,
                'reviewed_at' => now(), 'updated_at' => now(),
            ]);
            $this->notifyUser($user, 'wallet-request-rejected:'.$requestId, 'Wallet request rejected',
                'Your wallet request #'.$requestId.' was rejected. Admin note: '.$note,
                ['wallet_request_id' => $requestId]);
        }, 3);
    }

    public function markWithdrawalPaid(int $requestId, User $admin, string $transactionHash, string $settlementAmount, string $note, ?string $feeUsd = null): void
    {
        DB::transaction(function () use ($requestId, $admin, $transactionHash, $settlementAmount, $note, $feeUsd) {
            $request = DB::table('wallet_requests')->where('id', $requestId)->lockForUpdate()->first();
            if (!$request || $request->type !== 'withdrawal' || $request->status !== 'pending') {
                throw new RuntimeException('This withdrawal is no longer awaiting payment.');
            }
            $liability = $this->ledger->system('WITHDRAWAL_PENDING', 'USD', 'credit');
            $settlement = $this->ledger->system('CRYPTO_WITHDRAWAL_SETTLEMENT', 'USD', 'credit');
            $this->ledger->post('withdrawal-paid:'.$requestId, 'Settle paid withdrawal #'.$requestId, [
                ['account_id' => $liability, 'side' => 'debit', 'amount' => $request->amount_usd],
                ['account_id' => $settlement, 'side' => 'credit', 'amount' => $request->amount_usd],
            ], ['user_id' => $request->user_id, 'wallet_request_id' => $requestId, 'admin_id' => $admin->id, 'transaction_hash' => $transactionHash, 'settlement_amount' => $settlementAmount, 'fee_usd' => $feeUsd]);
            DB::table('wallet_requests')->where('id', $requestId)->update([
                'status' => 'paid', 'transaction_hash' => $transactionHash, 'settlement_amount' => $settlementAmount, 'fee_usd' => $feeUsd, 'admin_note' => $note,
                'reviewed_by' => $admin->id, 'reviewed_at' => now(), 'paid_at' => now(), 'updated_at' => now(),
            ]);
            $user = User::findOrFail($request->user_id);
            $this->notifyUser($user, 'wallet-withdrawal-paid:'.$requestId, 'Withdrawal marked as paid',
                'Your withdrawal request #'.$requestId.' was marked paid by an administrator. Transaction reference: '.$transactionHash,
                ['wallet_request_id' => $requestId, 'transaction_hash' => $transactionHash]);
        }, 3);
    }

    public function hasSuccessfulDeposit(User $user): bool
    {
        $deposits = DB::table('wallet_requests')
            ->where('user_id', $user->id)
            ->where('type', 'deposit')
            ->where('status', 'confirmed')
            ->whereNotNull('ledger_key')
            ->get(['ledger_key', 'amount_usd']);

        foreach ($deposits as $deposit) {
            $credited = DB::table('ledger_transactions')
                ->join('ledger_entries', 'ledger_entries.ledger_transaction_id', '=', 'ledger_transactions.id')
                ->join('ledger_accounts', 'ledger_accounts.id', '=', 'ledger_entries.ledger_account_id')
                ->where('ledger_transactions.idempotency_key', $deposit->ledger_key)
                ->where('ledger_accounts.owner_key', 'USER:'.$user->id)
                ->where('ledger_accounts.code', 'REAL_WALLET')
                ->where('ledger_entries.side', 'credit')
                ->where('ledger_entries.amount', $deposit->amount_usd)
                ->exists();

            if ($credited) {
                return true;
            }
        }

        return false;
    }

    public function hasApprovedKyc(User $user): bool
    {
        return DB::table('kyc_submissions')->where('user_id', $user->id)->where('status', 'verified')->exists();
    }
}
