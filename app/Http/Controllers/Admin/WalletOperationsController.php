<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ManualWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class WalletOperationsController extends Controller
{
    public function index()
    {
        $requests = DB::table('wallet_requests')->join('users', 'users.id', '=', 'wallet_requests.user_id')
            ->select('wallet_requests.*', 'users.name as client_name', 'users.email as client_email')
            ->latest('wallet_requests.created_at')->paginate(30);
        $methods = DB::table('crypto_payment_methods')->orderBy('asset')->orderBy('network')->get();
        $kycSubmissions = DB::table('kyc_submissions')->join('users', 'users.id', '=', 'kyc_submissions.user_id')
            ->select('kyc_submissions.*', 'users.name as client_name', 'users.email as client_email')
            ->latest('kyc_submissions.created_at')->paginate(20, ['*'], 'kyc_page');
        return view('admin.wallet-operations', compact('requests', 'methods', 'kycSubmissions'));
    }

    public function saveMethod(Request $request)
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'exists:crypto_payment_methods,id'],
            'asset' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
            'network' => ['required', 'string', 'max:40'],
            'wallet_address' => ['required', 'string', 'max:255'],
            'memo_label' => ['nullable', 'string', 'max:80'],
            'memo_value' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:2000'],
        ]);
        $values = [
            'asset' => strtoupper($data['asset']), 'network' => $data['network'],
            'wallet_address' => $data['wallet_address'], 'memo_label' => $data['memo_label'] ?? null,
            'memo_value' => $data['memo_value'] ?? null, 'instructions' => $data['instructions'] ?? null,
            'updated_at' => now(),
        ];
        if (!empty($data['id'])) DB::table('crypto_payment_methods')->where('id', $data['id'])->update($values);
        else DB::table('crypto_payment_methods')->insert($values + [
            'deposits_enabled' => false, 'withdrawals_enabled' => false, 'created_at' => now(),
        ]);
        return back()->with('status', 'Crypto payment method saved. Verify the network and address before enabling it.');
    }

    public function toggleMethod(Request $request, int $method)
    {
        $data = $request->validate([
            'deposits_enabled' => ['required', 'boolean'],
            'withdrawals_enabled' => ['required', 'boolean'],
        ]);
        DB::table('crypto_payment_methods')->where('id', $method)->update($data + ['updated_at' => now()]);
        return back()->with('status', 'Crypto method availability updated.');
    }

    public function confirmDeposit(Request $request, int $walletRequest, ManualWalletService $wallets)
    {
        $data = $request->validate([
            'amount_usd' => ['required', 'regex:/^(?:[1-9][0-9]{0,7})(?:\.[0-9]{1,8})?$/'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        try {
            $wallets->confirmDeposit($walletRequest, $request->user(), $data['amount_usd'], $data['admin_note'] ?? null);
            return back()->with('status', 'Deposit verified and credited to the client real wallet.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['wallet_request' => $exception->getMessage()]);
        }
    }

    public function reject(Request $request, int $walletRequest, ManualWalletService $wallets)
    {
        $data = $request->validate(['admin_note' => ['required', 'string', 'min:3', 'max:2000']]);
        try {
            $wallets->reject($walletRequest, $request->user(), $data['admin_note']);
            return back()->with('status', 'Wallet request rejected. Any withdrawal reserve has been released.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['wallet_request' => $exception->getMessage()]);
        }
    }

    public function markPaid(Request $request, int $walletRequest, ManualWalletService $wallets)
    {
        $data = $request->validate([
            'transaction_hash' => ['required', 'string', 'min:12', 'max:255'],
            'admin_note' => ['required', 'string', 'min:3', 'max:2000'],
        ]);
        try {
            $wallets->markWithdrawalPaid($walletRequest, $request->user(), $data['transaction_hash'], $data['admin_note']);
            return back()->with('status', 'Withdrawal marked paid and ledger reserve settled.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['wallet_request' => $exception->getMessage()]);
        }
    }

    public function reviewKyc(Request $request, int $submission)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:verified,rejected'],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $record = DB::table('kyc_submissions')->where('id', $submission)->where('status', 'pending')->first();
        abort_unless($record, 404);
        DB::transaction(function () use ($request, $record, $data) {
            DB::table('kyc_submissions')->where('id', $record->id)->update([
                'status' => $data['decision'], 'review_note' => $data['review_note'] ?? null,
                'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'updated_at' => now(),
            ]);
            $user = User::findOrFail($record->user_id);
            app(ManualWalletService::class)->notifyUser($user, 'kyc-reviewed:'.$record->id,
                $data['decision'] === 'verified' ? 'KYC verified' : 'KYC review update',
                $data['decision'] === 'verified'
                    ? 'Your identity documents were verified. You may now request withdrawals, subject to account and balance checks.'
                    : 'Your KYC submission was not approved. Review the admin note and submit corrected documents. '.$data['review_note'],
                ['kyc_submission_id' => $record->id, 'status' => $data['decision']]);
        });
        return back()->with('status', 'KYC decision saved.');
    }

    public function kycDocument(int $submission, string $kind)
    {
        abort_unless(in_array($kind, ['document', 'selfie'], true), 404);
        $record = DB::table('kyc_submissions')->where('id', $submission)->first();
        abort_unless($record, 404);
        $path = $kind === 'document' ? $record->document_path : $record->selfie_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->download($path);
    }
}
