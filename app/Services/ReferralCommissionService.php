<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReferralCommissionService
{
    public function recordForConfirmedDeposit(int $walletRequestId, bool $isFirstDeposit): void
    {
        $deposit = DB::table('wallet_requests')->where('id', $walletRequestId)->first();
        if (!$deposit || $deposit->type !== 'deposit' || $deposit->status !== 'confirmed' || !$deposit->ledger_key) return;

        $referred = User::find($deposit->user_id);
        if (!$referred || !$referred->referred_by_user_id || $referred->referred_by_user_id === $referred->id) return;

        $settings = DB::table('referral_settings')->where('id', 1)->first();
        if (!$settings || !$settings->enabled || (float) $deposit->amount_usd < (float) $settings->minimum_deposit_usd) return;

        $percent = (float) ($isFirstDeposit ? $settings->first_deposit_percent : $settings->recurring_deposit_percent);
        if ($percent <= 0) return;

        $amount = number_format(((float) $deposit->amount_usd * $percent) / 100, 8, '.', '');
        if ((float) $amount <= 0) return;

        DB::table('referral_commissions')->insertOrIgnore([
            'referrer_user_id' => $referred->referred_by_user_id,
            'referred_user_id' => $referred->id,
            'wallet_request_id' => $walletRequestId,
            'deposit_amount_usd' => $deposit->amount_usd,
            'commission_percent' => number_format($percent, 4, '.', ''),
            'commission_amount_usd' => $amount,
            'commission_type' => $isFirstDeposit ? 'first' : 'recurring',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function approve(int $commissionId, User $admin, ?string $note = null): void
    {
        DB::transaction(function () use ($commissionId, $admin, $note) {
            $commission = DB::table('referral_commissions')->where('id', $commissionId)->lockForUpdate()->first();
            if (!$commission || $commission->status !== 'pending') {
                throw new RuntimeException('This referral commission is no longer pending review.');
            }
            $deposit = DB::table('wallet_requests')->where('id', $commission->wallet_request_id)->first();
            if (!$deposit || $deposit->status !== 'confirmed' || !$deposit->ledger_key) {
                throw new RuntimeException('The qualifying deposit is not confirmed. Commission cannot be approved.');
            }

            $ledger = app(LedgerService::class);
            $referrer = User::findOrFail($commission->referrer_user_id);
            $wallet = $ledger->realWallet($referrer, 'USD');
            $expense = $ledger->system('REFERRAL_COMMISSION_EXPENSE', 'USD', 'debit');
            $key = 'referral-commission:' . $commission->id;
            $ledger->post($key, 'Referral commission for deposit #' . $commission->wallet_request_id, [
                ['account_id' => $expense, 'side' => 'debit', 'amount' => $commission->commission_amount_usd],
                ['account_id' => $wallet, 'side' => 'credit', 'amount' => $commission->commission_amount_usd],
            ], [
                'commission_id' => $commission->id,
                'referrer_user_id' => $commission->referrer_user_id,
                'referred_user_id' => $commission->referred_user_id,
                'wallet_request_id' => $commission->wallet_request_id,
                'type' => 'referral_commission',
            ]);

            DB::table('referral_commissions')->where('id', $commission->id)->update([
                'status' => 'paid', 'ledger_key' => $key, 'reviewed_by' => $admin->id,
                'reviewed_at' => now(), 'admin_note' => $note, 'updated_at' => now(),
            ]);
            app(ManualWalletService::class)->notifyUser(
                $referrer, 'referral-commission-paid:' . $commission->id,
                'Referral commission credited',
                'A referral commission of $' . $commission->commission_amount_usd . ' USD has been credited to your real wallet.',
                ['commission_id' => $commission->id]
            );
        }, 3);
    }

    public function reject(int $commissionId, User $admin, string $note): void
    {
        DB::transaction(function () use ($commissionId, $admin, $note) {
            $commission = DB::table('referral_commissions')->where('id', $commissionId)->lockForUpdate()->first();
            if (!$commission || $commission->status !== 'pending') {
                throw new RuntimeException('This referral commission is no longer pending review.');
            }
            DB::table('referral_commissions')->where('id', $commission->id)->update([
                'status' => 'rejected', 'reviewed_by' => $admin->id,
                'reviewed_at' => now(), 'admin_note' => $note, 'updated_at' => now(),
            ]);
        }, 3);
    }
}
