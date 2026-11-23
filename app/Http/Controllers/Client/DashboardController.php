<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use App\Services\MarketQuoteService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, MarketQuoteService $markets)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'admin'], true), 403);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $quotes = $markets->freshQuotes();

        return view('client.dashboard', [
            'user' => $user,
            'accountStatus' => 'paper',
            'quotes' => $quotes,
            'marketConnected' => $quotes->isNotEmpty(),
        ]);
    }
}
