<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $conversions = DB::table('paper_conversions')->where('user_id', $user->id)->latest()->limit(20)->get();
        $tickets = DB::table('support_tickets')->where('user_id', $user->id)->latest('last_message_at')->limit(10)->get();
        $loginEvents = DB::table('user_login_events')->where('user_id', $user->id)->latest()->limit(15)->get();

        return view('admin.client-show', compact('user', 'accounts', 'transactions', 'conversions', 'tickets', 'loginEvents'));
    }

    public function updateTradingControls(Request $request, int $client)
    {
        $validated = $request->validate([
            'demo_trading_mode' => ['required', 'in:normal,winning,losing,blocked'],
            'real_trading_mode' => ['required', 'in:normal,blocked'],
        ]);

        $user = User::query()->where('role', 'client')->findOrFail($client);

        DB::transaction(function () use ($request, $user, $validated) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedUser->forceFill($validated)->save();

            DB::table('admin_audit_logs')->insert([
                'actor_user_id' => $request->user()->id,
                'action' => 'client.trading_controls_updated',
                'method' => $request->method(),
                'path' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 65535),
                'created_at' => now(),
            ]);
        }, 3);

        return redirect()->route('admin.clients.show', $user->id)
            ->with('status', 'Trading controls updated. Existing open trades retain their opening mode.');
    }

    public function verifyEmail(Request $request, int $client)
    {
        $user = User::query()->where('role', 'client')->findOrFail($client);

        if ($user->email_verified_at) {
            return back()->with('status', 'This client email address is already verified.');
        }

        DB::transaction(function () use ($request, $user) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->email_verified_at) {
                return;
            }

            $verifiedAt = now();
            $lockedUser->forceFill(['email_verified_at' => $verifiedAt])->save();

            DB::table('admin_audit_logs')->insert([
                'actor_user_id' => $request->user()->id,
                'action' => 'client.email_verified_manually',
                'method' => $request->method(),
                'path' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 65535),
                'created_at' => $verifiedAt,
            ]);
        }, 3);

        return redirect()->route('admin.clients.show', $user->id)
            ->with('status', 'Client email address manually verified by an administrator.');
    }

    public function walletStatement(int $client, int $account)
    {
        $user = User::query()->where('role', 'client')->findOrFail($client);
        $wallet = DB::table('ledger_accounts')->where('id', $account)
            ->where('owner_key', 'USER:'.$user->id)->first();
        abort_unless($wallet, 404);

        $wallet->balance = DB::table('ledger_entries')->where('ledger_account_id', $wallet->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN side = 'credit' THEN amount ELSE -amount END), 0) as balance")
            ->value('balance');

        $entries = DB::table('ledger_entries')
            ->join('ledger_transactions', 'ledger_transactions.id', '=', 'ledger_entries.ledger_transaction_id')
            ->where('ledger_entries.ledger_account_id', $wallet->id)
            ->select('ledger_entries.id', 'ledger_entries.side', 'ledger_entries.amount',
                'ledger_transactions.description', 'ledger_transactions.posted_at', 'ledger_transactions.idempotency_key')
            ->orderByDesc('ledger_transactions.posted_at')->orderByDesc('ledger_entries.id')
            ->paginate(50)->withQueryString();

        return view('admin.client-wallet-statement', compact('user', 'wallet', 'entries'));
    }

}
