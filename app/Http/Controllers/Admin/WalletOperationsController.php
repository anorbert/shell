<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ManualWalletService;
use App\Services\LedgerService;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class WalletOperationsController extends Controller
{
    public function index(LedgerService $ledger)
    {
        $requests = DB::table('wallet_requests')->join('users', 'users.id', '=', 'wallet_requests.user_id')
            ->select('wallet_requests.*', 'users.name as client_name', 'users.email as client_email')
            ->latest('wallet_requests.created_at')->paginate(30);
        $methods = DB::table('crypto_payment_methods')->orderBy('asset')->orderBy('network')->get();
        $walletStats = DB::table('wallet_requests')
            ->selectRaw("COUNT(*) as total_requests")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests")
            ->selectRaw("SUM(CASE WHEN type = 'deposit' AND status = 'pending' THEN 1 ELSE 0 END) as pending_deposits")
            ->selectRaw("SUM(CASE WHEN type = 'withdrawal' AND status = 'pending' THEN 1 ELSE 0 END) as pending_withdrawals")
            ->selectRaw("SUM(CASE WHEN type = 'deposit' AND status = 'confirmed' THEN amount_usd ELSE 0 END) as confirmed_deposits_usd")
            ->first();
        $reserveAccount = $ledger->system('REAL_PAYOUT_RESERVE', 'USD', 'credit');
        $realPayoutReserve = Decimal::fromUnits($ledger->normalBalanceUnits($reserveAccount));
        return view('admin.wallet-operations', compact('requests', 'methods', 'realPayoutReserve', 'walletStats'));
    }

    /**
     * Record only real funds already verified and ring-fenced in the platform's
     * external treasury. This ledger action does not move money or verify custody.
     */
    public function fundPayoutReserve(Request $request, LedgerService $ledger)
    {
        $data = $request->validate([
            'amount_usd' => ['required', 'numeric', 'gt:0', 'max:99999999', 'regex:/^(?:[0-9]{1,8})(?:\\.[0-9]{1,2})?$/'],
            'external_reference' => ['required', 'string', 'min:12', 'max:255'],
            'admin_note' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $amount = $data['amount_usd'];
        $key = 'real-reserve-fund:' . hash('sha256', trim($data['external_reference']));
        try {
            DB::transaction(function () use ($ledger, $request, $amount, $key, $data) {
                $reserve = $ledger->system('REAL_PAYOUT_RESERVE', 'USD', 'credit');
                $capital = $ledger->system('REAL_PAYOUT_CAPITAL', 'USD', 'debit');
                $ledger->post($key, 'Verified external funding added to real payout reserve', [
                    ['account_id' => $capital, 'side' => 'debit', 'amount' => $amount],
                    ['account_id' => $reserve, 'side' => 'credit', 'amount' => $amount],
                ], [
                    'admin_id' => $request->user()->id,
                    'external_reference' => trim($data['external_reference']),
                    'admin_note' => $data['admin_note'],
                    'type' => 'real_payout_reserve_funding',
                    'custody_verified_off_platform' => true,
                ]);
            }, 3);

            return back()->with('status', 'Payout reserve ledger updated. Reconcile this amount against segregated treasury funds.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['reserve' => $exception->getMessage()]);
        }
    }

    public function kycIndex(Request $request)
    {
        $query = DB::table('kyc_submissions')->join('users', 'users.id', '=', 'kyc_submissions.user_id')
            ->leftJoin('users as reviewers', 'reviewers.id', '=', 'kyc_submissions.reviewed_by')
            ->select('kyc_submissions.*', 'users.name as client_name', 'users.email as client_email',
                'reviewers.name as reviewer_name');
        $status = $request->query('status', 'pending');
        if (in_array($status, ['pending', 'verified', 'rejected'], true)) {
            $query->where('kyc_submissions.status', $status);
        } else {
            $status = 'all';
        }
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('users.name', 'like', '%'.$search.'%')
                    ->orWhere('users.email', 'like', '%'.$search.'%')
                    ->orWhere('kyc_submissions.document_number', 'like', '%'.$search.'%')
                    ->orWhere('kyc_submissions.id', 'like', '%'.$search.'%');
            });
        }
        $submissions = $query->orderByRaw("CASE WHEN kyc_submissions.status = 'pending' THEN 0 ELSE 1 END")
            ->latest('kyc_submissions.created_at')->paginate(20)->withQueryString();
        $counts = DB::table('kyc_submissions')->selectRaw("SUM(status = 'pending') as pending, SUM(status = 'verified') as verified, SUM(status = 'rejected') as rejected")->first();
        return view('admin.kyc.index', compact('submissions', 'status', 'search', 'counts'));
    }

    public function kycShow(int $submission)
    {
        $kyc = DB::table('kyc_submissions')->join('users', 'users.id', '=', 'kyc_submissions.user_id')
            ->leftJoin('users as reviewers', 'reviewers.id', '=', 'kyc_submissions.reviewed_by')
            ->select('kyc_submissions.*', 'users.name as client_name', 'users.email as client_email',
                'users.created_at as client_registered_at', 'reviewers.name as reviewer_name')
            ->where('kyc_submissions.id', $submission)->first();
        abort_unless($kyc, 404);
        $history = DB::table('kyc_submissions')->where('user_id', $kyc->user_id)
            ->orderByDesc('created_at')->get();
        return view('admin.kyc.show', compact('kyc', 'history'));
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
            'amount_usd' => ['required', 'numeric', 'gt:0', 'max:99999999', 'regex:/^(?:[0-9]{1,8})(?:\\.[0-9]{1,2})?$/'],
            'chain_verified' => ['required', 'accepted'],
            'admin_note' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'chain_verified.accepted' => 'Confirm that you independently verified the transaction on the correct blockchain.',
        ]);

        $deposit = DB::table('wallet_requests')->where('id', $walletRequest)
            ->where('type', 'deposit')->where('status', 'pending')->first();
        abort_unless($deposit && filled($deposit->transaction_hash), 422,
            'This deposit has no submitted transaction hash and cannot be credited.');

        try {
            $wallets->confirmDeposit($walletRequest, $request->user(), $data['amount_usd'], $data['admin_note']);
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
            'settlement_amount' => ['required', 'numeric', 'gt:0', 'max:1000000000000000', 'regex:/^(?:[0-9]+)(?:\\.[0-9]{1,12})?$/'],
            'fee_usd' => ['nullable', 'numeric', 'min:0', 'max:100000000', 'regex:/^(?:[0-9]+)(?:\\.[0-9]{1,2})?$/'],
            'admin_note' => ['required', 'string', 'min:3', 'max:2000'],
        ]);
        try {
            $wallets->markWithdrawalPaid($walletRequest, $request->user(), $data['transaction_hash'], $data['settlement_amount'], $data['admin_note'], $data['fee_usd'] ?? null);
            return back()->with('status', 'Withdrawal marked paid and ledger reserve settled.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['wallet_request' => $exception->getMessage()]);
        }
    }

    public function reviewKyc(Request $request, int $submission)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:verified,rejected'],
            'review_note' => ['required', 'string', 'min:5', 'max:2000'],
            'checks' => ['required_if:decision,verified', 'array'],
            'checks.identity_legible' => ['required_if:decision,verified', 'accepted'],
            'checks.details_match' => ['required_if:decision,verified', 'accepted'],
            'checks.document_valid' => ['required_if:decision,verified', 'accepted'],
            'checks.selfie_match' => ['required_if:decision,verified', 'accepted'],
            'checks.duplicate_check' => ['required_if:decision,verified', 'accepted'],
            'checks.risk_screened' => ['required_if:decision,verified', 'accepted'],
        ]);
        $record = DB::table('kyc_submissions')->where('id', $submission)->where('status', 'pending')->first();
        abort_unless($record, 404);
        DB::transaction(function () use ($request, $record, $data) {
            DB::table('kyc_submissions')->where('id', $record->id)->update([
                'status' => $data['decision'], 'review_note' => $data['review_note'] ?? null,
                'review_checklist' => isset($data['checks']) ? json_encode($data['checks']) : null,
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
        return redirect()->route('admin.kyc.show', $submission)->with('status', 'KYC decision saved.');
    }

    public function kycDocument(int $submission, string $kind)
    {
        abort_unless(in_array($kind, ['document', 'selfie'], true), 404);
        $record = DB::table('kyc_submissions')->where('id', $submission)->first();
        abort_unless($record, 404);
        $path = $kind === 'document' ? $record->document_path : $record->selfie_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        $mime = $kind === 'document' ? $record->document_mime : $record->selfie_mime;
        $originalPath = preg_replace('/\\.enc$/', '', $path);
        $extension = strtolower(pathinfo($originalPath, PATHINFO_EXTENSION) ?: 'bin');
        $mime ??= match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            default => null,
        };
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'application/pdf'], true), 415);
        $contents = Crypt::decryptString(Storage::disk('local')->get($path));
        $extension = pathinfo(preg_replace('/\.enc$/', '', $path), PATHINFO_EXTENSION) ?: 'bin';
        return response($contents, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="kyc-'.$submission.'-'.$kind.'.'.$extension.'"',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',

        ]);
    }
}
