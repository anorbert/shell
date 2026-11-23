<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ManualWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class WalletRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $methods = DB::table('crypto_payment_methods')->orderBy('asset')->orderBy('network')->get();
        $requests = DB::table('wallet_requests')->where('user_id', $user->id)->latest()->paginate(15);
        $kyc = DB::table('kyc_submissions')->where('user_id', $user->id)->latest()->first();
        $kycVerified = app(ManualWalletService::class)->hasApprovedKyc($user);
        return view('client.wallet-requests', compact('methods', 'requests', 'kyc', 'kycVerified'));
    }

    public function submitKyc(Request $request)
    {
        $data = $request->validate([
            'document_type' => ['required', 'in:national_id,passport,driving_licence'],
            'document_number' => ['nullable', 'string', 'max:120'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'selfie' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);
        $user = $request->user();
        $existing = DB::table('kyc_submissions')->where('user_id', $user->id)->whereIn('status', ['pending', 'verified'])->exists();
        if ($existing) return back()->withErrors(['kyc' => 'Your KYC is already pending or verified.']);
        $documentPath = $request->file('document')->store('kyc/'.$user->id, 'local');
        $selfiePath = $request->file('selfie')?->store('kyc/'.$user->id, 'local');
        DB::table('kyc_submissions')->insert([
            'user_id' => $user->id, 'document_type' => $data['document_type'],
            'document_number' => $data['document_number'] ?? null, 'document_path' => $documentPath,
            'selfie_path' => $selfiePath, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
        app(ManualWalletService::class)->notifyAdmins('kyc-submission:'.$user->id.':'.now()->timestamp,
            'KYC submission requires review', $user->name.' ('.$user->email.') submitted KYC documents.',
            ['user_id' => $user->id]);
        return back()->with('status', 'KYC submitted. Withdrawals remain locked until an administrator verifies your documents.');
    }

    public function deposit(Request $request, ManualWalletService $wallets)
    {
        $data = $request->validate([
            'method_id' => ['required', 'integer', 'exists:crypto_payment_methods,id'],
            'amount_usd' => ['required', 'regex:/^(?:[1-9][0-9]{0,7})(?:\.[0-9]{1,8})?$/'],
            'transaction_hash' => ['required', 'string', 'min:12', 'max:255'],
            'client_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $method = DB::table('crypto_payment_methods')->where('id', $data['method_id'])->where('deposits_enabled', true)->first();
        abort_unless($method, 404);
        $user = $request->user();
        if (DB::table('wallet_requests')->where('type', 'deposit')->where('transaction_hash', $data['transaction_hash'])->exists()) {
            return back()->withErrors(['transaction_hash' => 'This transaction hash has already been submitted.']);
        }
        $id = DB::table('wallet_requests')->insertGetId([
            'user_id' => $user->id, 'type' => 'deposit', 'status' => 'pending',
            'amount_usd' => $data['amount_usd'], 'asset' => $method->asset, 'network' => $method->network,
            'wallet_address' => $method->wallet_address, 'transaction_hash' => $data['transaction_hash'],
            'client_note' => $data['client_note'] ?? null, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $wallets->notifyAdmins('wallet-deposit:'.$id, 'Deposit request requires confirmation',
            $user->name.' ('.$user->email.') reported a '.$data['amount_usd'].' USD deposit via '.$method->asset.' / '.$method->network.'. Verify the transaction on-chain before confirming.',
            ['wallet_request_id' => $id, 'type' => 'deposit']);
        return back()->with('status', 'Deposit request submitted. Your real wallet will only be credited after admin verification.');
    }

    public function withdraw(Request $request, ManualWalletService $wallets)
    {
        $data = $request->validate([
            'amount_usd' => ['required', 'regex:/^(?:[1-9][0-9]{0,7})(?:\.[0-9]{1,8})?$/'],
            'method_id' => ['required', 'integer', 'exists:crypto_payment_methods,id'],
            'wallet_address' => ['required', 'string', 'min:12', 'max:255'],
            'client_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $method = DB::table('crypto_payment_methods')->where('id', $data['method_id'])->where('withdrawals_enabled', true)->first();
        abort_unless($method, 422, 'Withdrawals are not enabled for this asset/network.');
        try {
            $id = $wallets->requestWithdrawal($request->user(), [
                'amount_usd' => $data['amount_usd'], 'asset' => $method->asset,
                'network' => $method->network, 'wallet_address' => $data['wallet_address'],
                'client_note' => $data['client_note'] ?? null,
            ]);
            return back()->with('status', 'Withdrawal request #'.$id.' submitted. Funds are reserved while admin reviews it.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['withdrawal' => $exception->getMessage()]);
        }
    }
}
