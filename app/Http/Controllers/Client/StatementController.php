<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatementController extends Controller
{
    public function __invoke(Request $request)
    {
        $values = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'wallet' => ['nullable', 'in:PAPER_WALLET,REAL_WALLET'],
        ]);
        $accounts = DB::table('ledger_accounts')->where('owner_key', 'USER:'.$request->user()->id)
            ->when($values['wallet'] ?? null, fn ($q, $code) => $q->where('code', $code))
            ->pluck('id');
        $entries = DB::table('ledger_entries')
            ->join('ledger_transactions', 'ledger_transactions.id', '=', 'ledger_entries.ledger_transaction_id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'ledger_entries.ledger_account_id')
            ->whereIn('ledger_entries.ledger_account_id', $accounts)
            ->when($values['from'] ?? null, fn ($q, $date) => $q->whereDate('ledger_transactions.posted_at', '>=', $date))
            ->when($values['to'] ?? null, fn ($q, $date) => $q->whereDate('ledger_transactions.posted_at', '<=', $date))
            ->select('ledger_transactions.id as transaction_id', 'ledger_transactions.description',
                'ledger_transactions.idempotency_key', 'ledger_transactions.posted_at',
                'ledger_entries.side', 'ledger_entries.amount', 'ledger_accounts.code', 'ledger_accounts.currency')
            ->orderByDesc('ledger_transactions.posted_at')->paginate(30)->withQueryString();

        return view('client.statement', compact('entries'));
    }
}
