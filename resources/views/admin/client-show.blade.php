@extends('layouts.app')
@section('title','Client profile')
@section('eyebrow','ADMINISTRATION / CLIENT ACCOUNTS')
@section('heading','Client profile')
@section('content')
<style>
    .client-profile{--cp-border:rgba(148,163,184,.2);--cp-muted:var(--muted,#94a3b8);display:grid;gap:20px}
    .client-profile .panel{border:1px solid var(--cp-border);overflow:hidden}
    .client-hero{position:relative;background:linear-gradient(120deg,rgba(37,99,235,.12),rgba(15,23,42,.04) 58%,rgba(245,158,11,.08));border-color:rgba(59,130,246,.28)!important}
    .client-hero:before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:linear-gradient(180deg,#3b82f6,#f59e0b)}
    .client-hero .panel-head{align-items:flex-start;padding:22px 22px 16px}
    .client-identity{display:flex;gap:14px;align-items:flex-start}
    .client-avatar{width:48px;height:48px;flex:0 0 48px;border-radius:14px;display:grid;place-items:center;background:rgba(59,130,246,.16);color:#60a5fa;font-size:19px;font-weight:800;border:1px solid rgba(96,165,250,.28)}
    .client-identity h2{margin:0 0 5px}
    .client-identity p{margin:4px 0}
    .client-flags{display:flex;flex-wrap:wrap;gap:7px;margin-top:12px}
    .cp-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.055em;border:1px solid transparent;white-space:nowrap}
    .cp-good{color:#34d399;background:rgba(16,185,129,.11);border-color:rgba(16,185,129,.25)}
    .cp-warn{color:#fbbf24;background:rgba(245,158,11,.11);border-color:rgba(245,158,11,.27)}
    .cp-info{color:#60a5fa;background:rgba(59,130,246,.11);border-color:rgba(59,130,246,.25)}
    .cp-neutral{color:var(--cp-muted);background:rgba(148,163,184,.09);border-color:var(--cp-border)}
    .cp-danger{color:#fb7185;background:rgba(244,63,94,.11);border-color:rgba(244,63,94,.25)}
    .client-metrics{padding:0 22px 20px}
    .client-metrics .metric-card{position:relative;overflow:hidden;border:1px solid var(--cp-border);background:linear-gradient(145deg,rgba(148,163,184,.055),transparent);border-radius:13px}
    .client-metrics .metric-card:before{content:"";position:absolute;inset:0 auto 0 0;width:3px;background:var(--metric-accent,#64748b)}
    .client-metrics .metric-card:nth-child(1){--metric-accent:#3b82f6}
    .client-metrics .metric-card:nth-child(2){--metric-accent:{{ $user->email_verified_at ? '#10b981' : '#f59e0b' }}}
    .client-metrics .metric-card:nth-child(3){--metric-accent:#8b5cf6}
    .client-metrics .metric-card:nth-child(4){--metric-accent:#f59e0b}
    .client-metrics .metric-card strong{font-variant-numeric:tabular-nums}
    .cp-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px}
    .cp-section-head .eyebrow{display:flex;align-items:center;gap:8px}
    .cp-dot{width:8px;height:8px;border-radius:50%;display:inline-block;background:var(--section-accent,#64748b);box-shadow:0 0 0 4px color-mix(in srgb,var(--section-accent,#64748b) 15%,transparent)}
    .cp-ledger{--section-accent:#3b82f6}.cp-finance{--section-accent:#06b6d4}.cp-trades{--section-accent:#8b5cf6}.cp-convert{--section-accent:#0d9488}.cp-support{--section-accent:#f59e0b}.cp-security{--section-accent:#f43f5e}
    .client-profile .data-table tbody tr:hover{background:rgba(59,130,246,.045)}
    .cp-cell-main{font-weight:700;color:var(--text,#e2e8f0)}
    .cp-amount{font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
    .cp-credit{color:#34d399}.cp-debit{color:#fb7185}
    .cp-direction{display:inline-flex;padding:4px 8px;border-radius:6px;font-size:10px;font-weight:800;letter-spacing:.05em;background:rgba(148,163,184,.1);color:var(--cp-muted)}
    .cp-direction.up,.cp-direction.call,.cp-direction.buy{background:rgba(16,185,129,.12);color:#34d399}
    .cp-direction.down,.cp-direction.put,.cp-direction.sell{background:rgba(244,63,94,.12);color:#fb7185}
    .cp-status{display:inline-flex;padding:4px 8px;border-radius:6px;font-size:10px;font-weight:800;letter-spacing:.04em;background:rgba(148,163,184,.1);color:var(--cp-muted)}
    .cp-status.open,.cp-status.pending{background:rgba(245,158,11,.12);color:#fbbf24}
    .cp-status.won,.cp-status.completed,.cp-status.closed,.cp-status.resolved{background:rgba(16,185,129,.12);color:#34d399}
    .cp-status.lost,.cp-status.failed,.cp-status.rejected{background:rgba(244,63,94,.12);color:#fb7185}
    .cp-status.active{background:rgba(59,130,246,.12);color:#60a5fa}
    .cp-audit-note{margin:0 22px 20px;padding:11px 13px;border:1px solid rgba(59,130,246,.2);border-radius:10px;background:rgba(59,130,246,.06);color:var(--cp-muted);font-size:12px}
    .cp-empty{padding:24px!important;text-align:center;color:var(--cp-muted)}
    @media(max-width:700px){.client-hero .panel-head{padding:17px}.client-identity{gap:10px}.client-avatar{width:40px;height:40px;flex-basis:40px}.client-metrics{padding:0 14px 16px}.cp-audit-note{margin:0 14px 16px}.cp-section-head{align-items:flex-start}}
</style>
<div class="client-profile">
    <div class="panel client-hero">
        <div class="panel-head">
            <div class="client-identity">
                <div class="client-avatar" aria-hidden="true">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                <div>
                    <span class="eyebrow">CLIENT ACCOUNT · #{{ $user->id }}</span>
                    <h2>{{ $user->name }}</h2>
                    <p class="muted">{{ $user->email }}</p>
                    <p class="muted">Country: {{ $user->country_code ?? 'Not provided' }} · Phone: {{ $user->phone_e164 ?? 'Not provided' }}</p>
                    <div class="client-flags">
                        <span class="cp-badge {{ $user->email_verified_at ? 'cp-good' : 'cp-warn' }}">{{ $user->email_verified_at ? '✓ EMAIL VERIFIED' : '! EMAIL UNVERIFIED' }}</span>
                        <span class="cp-badge {{ $user->phone_verified_at ? 'cp-good' : 'cp-warn' }}">{{ $user->phone_verified_at ? '✓ PHONE VERIFIED' : '! PHONE NOT VERIFIED' }}</span>
                        <span class="cp-badge cp-info">READ-ONLY PROFILE</span>
                    </div>
                </div>
            </div>
            <span class="cp-badge {{ $user->email_verified_at ? 'cp-good' : 'cp-warn' }}">{{ $user->email_verified_at ? 'VERIFIED' : 'ACTION REQUIRED' }}</span>
        </div>
        <div class="metric-grid client-metrics">
            <article class="metric-card"><span class="metric-label">REGISTERED</span><strong>{{ $user->created_at->format('d M Y') }}</strong><small>Client since</small></article>
            <article class="metric-card"><span class="metric-label">EMAIL STATUS</span><strong>{{ $user->email_verified_at ? 'Verified' : 'Unverified' }}</strong><small>{{ $user->email_verified_at ? 'Email confirmed' : 'Verification outstanding' }}</small></article>
            <article class="metric-card"><span class="metric-label">TRADING HISTORY</span><strong>{{ $realTrades->count() + $demoTrades->count() }}</strong><small>Real and demo separated below</small></article>
            <article class="metric-card"><span class="metric-label">SUPPORT TICKETS</span><strong>{{ $tickets->count() }}</strong><small>Latest 10 shown below</small></article>
        </div>
        <p class="cp-audit-note">Account inspection only. This page does not provide controls to alter wallet balances or trade outcomes.</p>
    </div>

    <section class="panel cp-ledger"><div class="panel-head cp-section-head"><div><span class="eyebrow"><i class="cp-dot"></i> LEDGER ACCOUNTS</span><h2>Wallet balances</h2></div><span class="cp-badge cp-info">{{ $accounts->count() }} ACCOUNTS</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>ACCOUNT</th><th>CURRENCY</th><th>NORMAL SIDE</th><th>LEDGER BALANCE</th><th>STATEMENT</th></tr></thead><tbody>
    @forelse($accounts as $account)<tr><td class="cp-cell-main">{{ $account->code }}</td><td>{{ $account->currency }}</td><td><span class="cp-badge cp-neutral">{{ strtoupper($account->normal_side) }}</span></td><td class="cp-amount">@money($account->balance)</td><td><a class="btn btn-secondary" href="{{ route('admin.clients.wallets.statement', ['client' => $user->id, 'account' => $account->id]) }}">View statement</a></td></tr>@empty<tr><td colspan="5" class="table-empty cp-empty">No ledger accounts provisioned.</td></tr>@endforelse
    </tbody></table></div></section>

    <section class="panel cp-finance"><div class="panel-head cp-section-head"><div><span class="eyebrow"><i class="cp-dot"></i> FINANCIAL AUDIT</span><h2>Recent ledger entries</h2></div><span class="cp-badge cp-info">LAST 50 ENTRIES</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>DATE</th><th>DESCRIPTION</th><th>ACCOUNT</th><th>ENTRY</th><th>AMOUNT</th><th>REFERENCE</th></tr></thead><tbody>
    @forelse($transactions as $entry)<tr><td>{{ $entry->posted_at }}</td><td class="cp-cell-main">{{ $entry->description }}</td><td>{{ $entry->code }} · {{ $entry->currency }}</td><td><span class="cp-badge {{ $entry->side === 'credit' ? 'cp-good' : 'cp-danger' }}">{{ strtoupper($entry->side) }}</span></td><td class="cp-amount {{ $entry->side === 'credit' ? 'cp-credit' : 'cp-debit' }}">@money($entry->amount)</td><td><small>{{ $entry->idempotency_key }}</small></td></tr>@empty<tr><td colspan="6" class="table-empty cp-empty">No ledger activity found.</td></tr>@endforelse
    </tbody></table></div></section>

    <section class="panel cp-convert"><div class="panel-head cp-section-head"><div><span class="eyebrow"><i class="cp-dot"></i> CONVERSION HISTORY</span><h2>Recent simulated conversions</h2></div><span class="cp-badge cp-info">LATEST {{ $conversions->count() }}</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>DATE</th><th>FROM</th><th>TO</th><th>RATE</th></tr></thead><tbody>
    @forelse($conversions as $conversion)<tr><td>{{ $conversion->created_at }}</td><td class="cp-amount">@decimal($conversion->amount_in) {{ $conversion->from_currency }}</td><td class="cp-amount cp-credit">@decimal($conversion->amount_out) {{ $conversion->to_currency }}</td><td>@decimal($conversion->source_price) / @decimal($conversion->target_price)</td></tr>@empty<tr><td colspan="4" class="table-empty cp-empty">No conversions found.</td></tr>@endforelse
    </tbody></table></div></section>

    <section class="panel cp-support"><div class="panel-head cp-section-head"><div><span class="eyebrow"><i class="cp-dot"></i> CUSTOMER SUPPORT</span><h2>Recent tickets</h2></div><span class="cp-badge cp-warn">{{ $tickets->count() }} RECENT TICKETS</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>SUBJECT</th><th>CATEGORY</th><th>STATUS</th><th>LAST MESSAGE</th></tr></thead><tbody>
    @forelse($tickets as $ticket)@php($ticketStatus = strtolower((string) $ticket->status))<tr><td class="cp-cell-main">{{ $ticket->subject }}</td><td>{{ $ticket->category }}</td><td><span class="cp-status {{ $ticketStatus }}">{{ strtoupper($ticketStatus) }}</span></td><td>{{ $ticket->last_message_at }}</td></tr>@empty<tr><td colspan="4" class="table-empty cp-empty">No support tickets found.</td></tr>@endforelse
    </tbody></table></div></section>

    <section class="panel cp-security"><div class="panel-head cp-section-head"><div><span class="eyebrow"><i class="cp-dot"></i> SECURITY HISTORY</span><h2>Recent login events</h2></div><span class="cp-badge cp-danger">{{ $loginEvents->count() }} RECENT EVENTS</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>EVENT</th><th>IP ADDRESS</th><th>USER AGENT</th><th>DATE</th></tr></thead><tbody>
    @forelse($loginEvents as $event)<tr><td><span class="cp-badge cp-neutral">{{ strtoupper($event->event_type ?? 'LOGIN') }}</span></td><td class="cp-cell-main">{{ $event->ip_address ?? '—' }}</td><td><small>{{ $event->user_agent ?? '—' }}</small></td><td>{{ $event->created_at }}</td></tr>@empty<tr><td colspan="4" class="table-empty cp-empty">No login events found.</td></tr>@endforelse
    </tbody></table></div></section>
</div>
@endsection
