<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ManualWalletService;
use App\Services\LedgerService;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletRequestController extends Controller
{
    public function index(Request $request, LedgerService $ledger)
    {
        $user = $request->user();
        $currency = config('shell.paper.currency', 'USD');
        $wallets = $ledger->provisionClientWallets($user, $currency);
        $selectedAccount = $request->query('account') === 'demo' ? 'demo' : 'real';
        $selectedBalance = Decimal::fromUnits($ledger->balanceUnits($wallets[$selectedAccount]));
        $demoBalance = Decimal::fromUnits($ledger->balanceUnits($wallets['demo']));
        $realBalance = Decimal::fromUnits($ledger->balanceUnits($wallets['real']));
        $methods = DB::table('crypto_payment_methods')->orderBy('asset')->orderBy('network')->get();
        $requests = DB::table('wallet_requests')->where('user_id', $user->id)->latest()->paginate(15);
        $kycVerified = app(ManualWalletService::class)->hasApprovedKyc($user);
        return view('client.wallet-requests', compact('methods', 'requests', 'kycVerified', 'selectedAccount', 'selectedBalance', 'demoBalance', 'realBalance', 'currency'));
    }

    public function deposit(Request $request, ManualWalletService $wallets)
    {
        $data = $request->validate([
            'method_id' => ['required', 'integer', 'exists:crypto_payment_methods,id'],
            'asset_amount' => ['required', 'numeric', 'gt:0', 'max:1000000000000000', 'regex:/^(?:[0-9]+)(?:\.[0-9]{1,8})?$/'],
        ]);
        $method = DB::table('crypto_payment_methods')->where('id', $data['method_id'])->where('deposits_enabled', true)->first();
        abort_unless($method && in_array(strtoupper($method->asset), ['USDT', 'USDC'], true), 404, 'This deposit method is not an enabled USD stablecoin.');
        $user = $request->user();
        // USDT and USDC are treated at a 1:1 USD reference value for this manual-deposit workflow.
        $usdAmount = $data['asset_amount'];
        $id = DB::table('wallet_requests')->insertGetId([
            'user_id' => $user->id, 'type' => 'deposit', 'status' => 'pending',
            'amount_usd' => $usdAmount, 'asset_amount' => $data['asset_amount'], 'asset' => $method->asset, 'network' => $method->network,
            'wallet_address' => $method->wallet_address, 'transaction_hash' => null,
            'client_note' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $wallets->notifyAdmins('wallet-deposit:'.$id, 'Deposit request requires confirmation',
            $user->name.' ('.$user->email.') reported a '.$data['asset_amount'].' '.$method->asset.' deposit (estimated '.$usdAmount.' USD) via '.$method->network.'. Match the deposit against the configured receiving wallet before confirming.',
            ['wallet_request_id' => $id, 'type' => 'deposit']);
        return back()->with('status', 'Stablecoin deposit request submitted.');
    }

    public function withdraw(Request $request, ManualWalletService $wallets)
    {
        $data = $request->validate([
            'amount_usd' => ['required', 'regex:/^(?:[1-9][0-9]{0,7})(?:\.[0-9]{1,8})?$/'],
            'method_id' => ['required', 'integer', 'exists:crypto_payment_methods,id'],
            'wallet_address' => ['required', 'string', 'min:12', 'max:255'],
            'wallet_memo' => ['nullable', 'string', 'max:255'],
            'client_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $method = DB::table('crypto_payment_methods')->where('id', $data['method_id'])->where('withdrawals_enabled', true)->first();
        abort_unless($method, 422, 'Withdrawals are not enabled for this asset/network.');
        try {
            $id = $wallets->requestWithdrawal($request->user(), [
                'amount_usd' => $data['amount_usd'], 'asset' => $method->asset,
                'network' => $method->network, 'wallet_address' => $data['wallet_address'],
                'wallet_memo' => $data['wallet_memo'] ?? null, 'client_note' => $data['client_note'] ?? null,
            ]);
            return back()->with('status', 'Withdrawal request #'.$id.' submitted. Funds are reserved while admin reviews it.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['withdrawal' => $exception->getMessage()]);
        }
    }
}
