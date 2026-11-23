@extends('layouts.app')
@section('title','Client profile')
@section('eyebrow','ADMINISTRATION / CLIENT ACCOUNTS')
@section('heading','Client profile')
@section('content')
<div class="panel">
    <div class="panel-head"><div><span class="eyebrow">CLIENT #{{ $user->id }}</span><h2>{{ $user->name }}</h2><p class="muted">{{ $user->email }}</p></div><span class="table-status {{ $user->email_verified_at ? 'verified' : 'pending' }}">{{ $user->email_verified_at ? 'VERIFIED' : 'UNVERIFIED' }}</span></div>
    <div class="metric-grid">
        <article class="metric-card"><span class="metric-label">REGISTERED</span><strong>{{ $user->created_at->format('d M Y') }}</strong><small>Client since</small></article>
        <article class="metric-card"><span class="metric-label">EMAIL VERIFIED</span><strong>{{ $user->email_verified_at ? 'Yes' : 'No' }}</strong><small>Account verification</small></article>
        <article class="metric-card"><span class="metric-label">PAPER TRADES</span><strong>{{ $trades->count() }}</strong><small>Latest 25 shown below</small></article>
        <article class="metric-card"><span class="metric-label">SUPPORT TICKETS</span><strong>{{ $tickets->count() }}</strong><small>Latest 10 shown below</small></article>
    </div>
    <p class="muted">Read-only account inspection. No administrator action on this page can alter wallet balances or trade outcomes.</p>
</div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">LEDGER ACCOUNTS</span><h2>Wallet balances</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>ACCOUNT</th><th>CURRENCY</th><th>NORMAL SIDE</th><th>LEDGER BALANCE</th></tr></thead><tbody>
@forelse($accounts as $account)<tr><td>{{ $account->code }}</td><td>{{ $account->currency }}</td><td>{{ strtoupper($account->normal_side) }}</td><td>{{ $account->balance }}</td></tr>@empty<tr><td colspan="4" class="table-empty">No ledger accounts provisioned.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">FINANCIAL AUDIT</span><h2>Recent ledger entries</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>DATE</th><th>DESCRIPTION</th><th>ACCOUNT</th><th>ENTRY</th><th>AMOUNT</th><th>REFERENCE</th></tr></thead><tbody>
@forelse($transactions as $entry)<tr><td>{{ $entry->posted_at }}</td><td>{{ $entry->description }}</td><td>{{ $entry->code }} · {{ $entry->currency }}</td><td>{{ strtoupper($entry->side) }}</td><td>{{ $entry->amount }}</td><td>{{ $entry->idempotency_key }}</td></tr>@empty<tr><td colspan="6" class="table-empty">No ledger activity found.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">SIMULATED TRADING</span><h2>Recent positions</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>OPENED</th><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPEN / CLOSE</th><th>STATUS</th></tr></thead><tbody>
@forelse($trades as $trade)<tr><td>{{ $trade->opened_at }}</td><td>{{ $trade->symbol }}</td><td>{{ $trade->direction }}</td><td>{{ $trade->stake }} {{ $trade->wallet_currency ?? 'USD' }}</td><td>{{ $trade->opening_price }} / {{ $trade->closing_price ?? '—' }}</td><td>{{ strtoupper($trade->status) }}</td></tr>@empty<tr><td colspan="6" class="table-empty">No trades found.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">CONVERSION HISTORY</span><h2>Recent simulated conversions</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>DATE</th><th>FROM</th><th>TO</th><th>RATE</th></tr></thead><tbody>
@forelse($conversions as $conversion)<tr><td>{{ $conversion->created_at }}</td><td>{{ $conversion->amount_in }} {{ $conversion->from_currency }}</td><td>{{ $conversion->amount_out }} {{ $conversion->to_currency }}</td><td>{{ $conversion->source_price }} / {{ $conversion->target_price }}</td></tr>@empty<tr><td colspan="4" class="table-empty">No conversions found.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">SUPPORT</span><h2>Recent tickets</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>SUBJECT</th><th>CATEGORY</th><th>STATUS</th><th>LAST MESSAGE</th></tr></thead><tbody>
@forelse($tickets as $ticket)<tr><td>{{ $ticket->subject }}</td><td>{{ $ticket->category }}</td><td>{{ strtoupper($ticket->status) }}</td><td>{{ $ticket->last_message_at }}</td></tr>@empty<tr><td colspan="4" class="table-empty">No support tickets found.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">SECURITY HISTORY</span><h2>Recent login events</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>EVENT</th><th>IP ADDRESS</th><th>USER AGENT</th><th>DATE</th></tr></thead><tbody>
@forelse($loginEvents as $event)<tr><td>{{ $event->event_type ?? 'Login' }}</td><td>{{ $event->ip_address ?? '—' }}</td><td>{{ $event->user_agent ?? '—' }}</td><td>{{ $event->created_at }}</td></tr>@empty<tr><td colspan="4" class="table-empty">No login events found.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
