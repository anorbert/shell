<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use App\Services\MarketQuoteService;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request, MarketQuoteService $markets, LedgerService $ledger)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'admin'], true), 403);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $currency = config('shell.paper.currency', 'USD');
        $wallets = $ledger->provisionClientWallets($user, $currency);
        $quotes = $markets->freshQuotes();

        $tradeQuery = DB::table('paper_trades')->where('user_id', $user->id);
        $tradeStats = (clone $tradeQuery)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as won, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as lost, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as open', ['won', 'lost', 'open'])
            ->first();

        $wonTrades = (int) ($tradeStats->won ?? 0);
        $lostTrades = (int) ($tradeStats->lost ?? 0);
        $resolvedTrades = $wonTrades + $lostTrades;
        $winRate = $resolvedTrades > 0 ? round(($wonTrades / $resolvedTrades) * 100, 1) : null;

        $sevenDayStart = now()->startOfDay()->subDays(6);
        $dailyRows = (clone $tradeQuery)
            ->where('opened_at', '>=', $sevenDayStart)
            ->selectRaw('DATE(opened_at) as trade_day, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as wins', ['won'])
            ->groupByRaw('DATE(opened_at)')
            ->get()
            ->keyBy('trade_day');

        $dailyActivity = collect();
        for ($offset = 6; $offset >= 0; $offset--) {
            $day = now()->startOfDay()->subDays($offset);
            $row = $dailyRows->get($day->toDateString());
            $dailyActivity->push([
                'label' => $day->format('D'),
                'date' => $day->toDateString(),
                'total' => (int) ($row->total ?? 0),
                'wins' => (int) ($row->wins ?? 0),
            ]);
        }
        $dailyMax = max(1, (int) $dailyActivity->max('total'));

        $recentTrades = (clone $tradeQuery)
            ->select(['id', 'symbol', 'direction', 'stake', 'wallet_currency', 'opening_price', 'closing_price', 'status', 'opened_at', 'expires_at', 'settled_at'])
            ->orderByDesc('opened_at')
            ->limit(6)
            ->get();

        $pendingWalletRequests = DB::table('wallet_requests')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $pendingDeposits = (int) ($pendingWalletRequests->get('deposit')->total ?? 0);
        $pendingWithdrawals = (int) ($pendingWalletRequests->get('withdrawal')->total ?? 0);

        return view('client.dashboard', [
            'user' => $user,
            'quotes' => $quotes,
            'marketConnected' => $quotes->isNotEmpty(),
            'demoBalance' => Decimal::fromUnits($ledger->balanceUnits($wallets['demo'])),
            'realBalance' => Decimal::fromUnits($ledger->balanceUnits($wallets['real'])),
            'walletCurrency' => $currency,
            'tradeStats' => $tradeStats,
            'winRate' => $winRate,
            'dailyActivity' => $dailyActivity,
            'dailyMax' => $dailyMax,
            'recentTrades' => $recentTrades,
            'pendingDeposits' => $pendingDeposits,
            'pendingWithdrawals' => $pendingWithdrawals,
        ]);
    }
}
