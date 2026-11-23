<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use App\Services\MarketQuoteService;
use App\Support\Decimal;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, MarketQuoteService $markets, LedgerService $ledger)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'admin'], true), 403);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $currency = config('shell.paper.currency', 'USDT');
        $wallets = $ledger->provisionClientWallets($user, $currency);
        $quotes = $markets->freshQuotes();

        return view('client.dashboard', [
            'user' => $user,
            'accountStatus' => 'active',
            'quotes' => $quotes,
            'marketConnected' => $quotes->isNotEmpty(),
            'demoBalance' => Decimal::fromUnits($ledger->balanceUnits($wallets['demo'])),
            'realBalance' => Decimal::fromUnits($ledger->balanceUnits($wallets['real'])),
            'walletCurrency' => $currency,
        ]);
    }
}
