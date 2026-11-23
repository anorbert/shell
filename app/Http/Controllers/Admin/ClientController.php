<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use App\\Models\\User;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\DB;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $clients = User::query()->where('role', 'client')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })->latest()->paginate(20)->withQueryString();

        return view('admin.clients', compact('clients'));
    }

    public function show(int $client)
    {
        $user = User::query()->where('role', 'client')->findOrFail($client);
        $accounts = DB::table('ledger_accounts')->where('owner_key', 'USER:'.$user->id)
            ->orderBy('code')->orderBy('currency')->get()->map(function ($account) {
                $account->balance = DB::table('ledger_entries')->where('ledger_account_id', $account->id)
                    ->selectRaw("COALESCE(SUM(CASE WHEN side = 'credit' THEN amount ELSE -amount END), 0) as balance")
                    ->value('balance');
                return $account;
            });
        $transactions = DB::table('ledger_transactions')
            ->join('ledger_entries', 'ledger_entries.ledger_transaction_id', '=', 'ledger_transactions.id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'ledger_entries.ledger_account_id')
            ->where('ledger_accounts.owner_key', 'USER:'.$user->id)
            ->select('ledger_transactions.id', 'ledger_transactions.description', 'ledger_transactions.posted_at',
                'ledger_transactions.idempotency_key', 'ledger_entries.side', 'ledger_entries.amount',
                'ledger_accounts.code', 'ledger_accounts.currency')
            ->latest('ledger_transactions.posted_at')->limit(50)->get();
        $trades = DB::table('paper_trades')->where('user_id', $user->id)->latest('opened_at')->limit(25)->get();
        $conversions = DB::table('paper_conversions')->where('user_id', $user->id)->latest()->limit(20)->get();
        $tickets = DB::table('support_tickets')->where('user_id', $user->id)->latest('last_message_at')->limit(10)->get();
        $loginEvents = DB::table('user_login_events')->where('user_id', $user->id)->latest()->limit(15)->get();

        return view('admin.client-show', compact('user', 'accounts', 'transactions', 'trades', 'conversions', 'tickets', 'loginEvents'));
    }
}
