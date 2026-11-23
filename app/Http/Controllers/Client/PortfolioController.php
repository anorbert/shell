<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortfolioController extends Controller
{
    public function __invoke(Request $request, LedgerService $ledger)
    {
        $user = $request->user();
        $currency = config('shell.paper.currency', 'USDT');
        $wallets = $ledger->provisionClientWallets($user, $currency);

        $demoAccounts = DB::table('ledger_accounts')
            ->where('owner_key', 'USER:'.$user->id)
            ->where('code', 'PAPER_WALLET')
            ->orderBy('currency')
            ->get();

        $demoBalances = $demoAccounts->map(fn ($account) => [
            'currency' => $account->currency,
            'balance' => Decimal::fromUnits($ledger->balanceUnits((int) $account->id)),
        ])->values();

        $demoBalance = Decimal::fromUnits($ledger->balanceUnits($wallets['demo']));
        $realBalance = Decimal::fromUnits($ledger->balanceUnits($wallets['real']));

        return view('client.portfolio', [
            'currency' => $currency,
            'demoBalance' => $demoBalance,
            'realBalance' => $realBalance,
            'demoBalances' => $demoBalances,
            'realWalletStatus' => 'inactive',
        ]);
    }
}
