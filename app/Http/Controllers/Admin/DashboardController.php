<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $freshAfter = now()->subSeconds((int) config('shell.market.max_age_seconds', 90));

        return view('admin.dashboard', [
            'clientCount' => User::where('role', 'client')->count(),
            'adminCount' => User::where('role', 'admin')->count(),
            'verifiedCount' => User::whereNotNull('email_verified_at')->count(),
            'freshQuoteCount' => DB::table('market_quotes')->where('captured_at', '>=', $freshAfter)->count(),
            'configuredMarketCount' => count(config('shell.market.symbols', [])),
            'openTradeCount' => DB::table('paper_trades')->where('status', 'open')->count(),
            'settledTradeCount' => DB::table('paper_trades')->whereIn('status', ['won', 'lost', 'void'])->count(),
            'latestTrades' => DB::table('paper_trades')
                ->join('users', 'users.id', '=', 'paper_trades.user_id')
                ->select('paper_trades.*', 'users.name as client_name', 'users.email as client_email')
                ->latest('paper_trades.created_at')
                ->limit(8)
                ->get(),
        ]);
    }
}
