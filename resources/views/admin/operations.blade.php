@extends('layouts.app')
@section('title','Trading operations')
@section('eyebrow','ADMINISTRATION / CONTROL ROOM')
@section('heading','Trading operations')

@section('content')
<style>
    .ops-intro{border:1px solid rgba(59,130,246,.24);border-left:4px solid #3b82f6;background:linear-gradient(110deg,rgba(37,99,235,.12),rgba(15,23,42,.2));}
    .ops-intro .notice-icon{background:rgba(59,130,246,.18);color:#60a5fa}
    .ops-metrics .metric-card{position:relative;overflow:hidden;border-top:3px solid var(--ops-accent);background:linear-gradient(145deg,var(--ops-tint),rgba(15,23,42,.18));}
    .ops-metrics .metric-card:after{content:"";position:absolute;width:76px;height:76px;border-radius:50%;right:-28px;top:-28px;background:var(--ops-tint)}
    .ops-metrics .metric-label{display:flex;align-items:center;gap:8px;color:var(--ops-accent)}
    .ops-metrics .metric-card strong{font-variant-numeric:tabular-nums}
    .ops-section{padding:0;overflow:hidden;border-top:1px solid var(--ops-border);border-left:3px solid var(--ops-accent)}
    .ops-section>summary{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:19px 22px;cursor:pointer;list-style:none;user-select:none;background:linear-gradient(100deg,var(--ops-tint),transparent 75%)}
    .ops-section>summary::-webkit-details-marker{display:none}
    .ops-section>summary:after{content:"";width:9px;height:9px;flex:0 0 9px;border-right:2px solid var(--ops-accent);border-bottom:2px solid var(--ops-accent);transform:rotate(45deg);transition:transform .18s ease;margin:0 5px 5px 0}
    .ops-section[open]>summary:after{transform:rotate(225deg);margin-top:9px}
    .ops-section>summary:hover{background:var(--ops-tint)}
    .ops-section .panel-head{margin:0;min-width:0;flex:1}
    .ops-section .panel-head h2{display:flex;align-items:center;gap:10px}
    .ops-heading-icon{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 34px;border-radius:10px;color:var(--ops-accent);background:var(--ops-tint);border:1px solid var(--ops-border)}
    .ops-heading-icon svg,.ops-metric-icon svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .ops-section .table-wrap,.ops-section .pagination-wrap{margin:0 20px 16px}
    .ops-section .table-wrap{border-radius:10px}
    .ops-section .data-table thead th{color:var(--ops-accent)}
    .ops-section .table-status{font-weight:750;letter-spacing:.04em}
    .ops-section .table-status.verified{background:rgba(16,185,129,.13);color:#34d399;border:1px solid rgba(16,185,129,.25)}
    .ops-section .table-status.pending{background:rgba(245,158,11,.13);color:#fbbf24;border:1px solid rgba(245,158,11,.25)}
    .ops-section .table-status.failed,.ops-section .table-status.danger{background:rgba(239,68,68,.13);color:#f87171;border:1px solid rgba(239,68,68,.25)}
    .ops-section .table-status.neutral{background:rgba(148,163,184,.12);color:#cbd5e1;border:1px solid rgba(148,163,184,.2)}
    .ops-account-real{color:#60a5fa;font-weight:750}.ops-account-demo{color:#a78bfa;font-weight:750}
    .ops-direction-up{color:#34d399;font-weight:750}.ops-direction-down{color:#f87171;font-weight:750}
    .ops-outcome-won{color:#34d399;font-weight:750}.ops-outcome-lost{color:#f87171;font-weight:750}.ops-outcome-void{color:#fbbf24;font-weight:750}
    @media(max-width:700px){.ops-section>summary{padding:15px}.ops-section .table-wrap,.ops-section .pagination-wrap{margin-left:12px;margin-right:12px}.ops-section .panel-head .muted{display:none}}
</style>

<div class="notice-banner ops-intro">
    <span class="notice-icon"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/></svg></span>
    <div><strong>Platform operations</strong><p>Monitor market-data freshness, trade settlement, conversion activity and security events. Financial records remain ledger-controlled to preserve a reliable audit trail.</p></div>
    <span class="mode-tag">MONITORING</span>
</div>

<div class="metric-grid ops-metrics">
    <article class="metric-card" style="--ops-accent:#f59e0b;--ops-tint:rgba(245,158,11,.10)"><span class="metric-label"><span class="ops-metric-icon"><svg viewBox="0 0 24 24"><path d="M12 3 2.8 20h18.4L12 3Z"/><path d="M12 9v5m0 3h.01"/></svg></span>STALE QUOTES</span><strong>{{ $staleQuoteCount }}</strong><small>Configured markets without a fresh quote</small></article>
    <article class="metric-card" style="--ops-accent:#ef4444;--ops-tint:rgba(239,68,68,.10)"><span class="metric-label"><span class="ops-metric-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>OVERDUE POSITIONS</span><strong>{{ $overdueTradeCount }}</strong><small>Positions awaiting settlement</small></article>
    <article class="metric-card" style="--ops-accent:#3b82f6;--ops-tint:rgba(59,130,246,.10)"><span class="metric-label"><span class="ops-metric-icon"><svg viewBox="0 0 24 24"><path d="M3 12h18M12 3v18"/><circle cx="12" cy="12" r="9"/></svg></span>OPEN POSITIONS</span><strong>{{ $openTradeCount }}</strong><small>{{ $openDemoTradeCount }} demo · {{ $openRealTradeCount }} real</small></article>
    <article class="metric-card" style="--ops-accent:#a78bfa;--ops-tint:rgba(167,139,250,.10)"><span class="metric-label"><span class="ops-metric-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l4 2"/></svg></span>LAST MARKET REFRESH</span><strong>{{ $marketLastSuccess ? $marketLastSuccess->diffForHumans() : 'Never' }}</strong><small>Last successful refresh</small></article>
</div>

<details class="panel ops-section" style="--ops-accent:#a78bfa;--ops-tint:rgba(167,139,250,.09);--ops-border:rgba(167,139,250,.22)">
    <summary><div class="panel-head"><div><span class="eyebrow">SCHEDULED TASKS</span><h2><span class="ops-heading-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h3m-3 3h8"/></svg></span>Background job health</h2></div><span class="muted">Server scheduler must run every minute</span></div></summary>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>JOB</th><th>STATUS</th><th>LAST STARTED</th><th>LAST SUCCESS</th><th>DURATION</th><th>MESSAGE</th></tr></thead><tbody>
    @forelse($jobRuns as $job)<tr><td>{{ $job->job_key }}</td><td><span class="table-status {{ $job->status==='success'?'verified':($job->status==='failed'?'failed':'neutral') }}">{{ strtoupper($job->status) }}</span></td><td>{{ $job->last_started_at??'—' }}</td><td>{{ $job->last_success_at??'Never' }}</td><td>{{ $job->duration_ms===null?'—':$job->duration_ms.' ms' }}</td><td>{{ $job->message }}</td></tr>
    @empty<tr><td colspan="6" class="table-empty">No job heartbeat recorded yet.</td></tr>@endforelse
    </tbody></table></div>
</details>

<details class="panel ops-section" style="--ops-accent:#38bdf8;--ops-tint:rgba(56,189,248,.09);--ops-border:rgba(56,189,248,.22)">
    <summary><div class="panel-head"><div><span class="eyebrow">MARKET PROVIDER</span><h2><span class="ops-heading-icon"><svg viewBox="0 0 24 24"><path d="M3 17 9 11l4 4 8-9"/><path d="M15 6h6v6"/></svg></span>Quote freshness</h2></div><span class="muted">Freshness limit: {{ config('shell.market.max_age_seconds') }} seconds</span></div></summary>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>PRICE</th><th>PROVIDER</th><th>CAPTURED AT</th><th>HEALTH</th></tr></thead><tbody>
    @forelse($quotes as $quote)<tr><td><strong>{{ $quote->symbol }}</strong></td><td>@decimal($quote->price) USDT</td><td>{{ $quote->provider }}</td><td>{{ $quote->captured_at }}</td><td><span class="table-status {{ $quote->is_fresh?'verified':'pending' }}">{{ $quote->is_fresh?'FRESH':'STALE' }}</span></td></tr>
    @empty<tr><td colspan="5" class="table-empty">No market quotes have been captured yet.</td></tr>@endforelse
    </tbody></table></div>
</details>

<details class="panel ops-section" style="--ops-accent:#3b82f6;--ops-tint:rgba(59,130,246,.09);--ops-border:rgba(59,130,246,.22)">
    <summary><div class="panel-head"><div><span class="eyebrow">SETTLEMENT QUEUE</span><h2><span class="ops-heading-icon"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5M8 17h3"/></svg></span>Open trading positions</h2></div></div></summary>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>ACCOUNT</th><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPENED</th><th>EXPIRES</th></tr></thead><tbody>
    @forelse($openTrades as $trade)<tr><td><strong>{{ $trade->client_name }}</strong><small>{{ $trade->client_email }}</small></td><td><span class="{{ ($trade->account_type ?? 'demo')==='real'?'ops-account-real':'ops-account-demo' }}">{{ strtoupper($trade->account_type ?? 'demo') }}</span></td><td>{{ $trade->symbol }}</td><td><span class="{{ strtolower($trade->direction)==='up'?'ops-direction-up':'ops-direction-down' }}">{{ strtoupper($trade->direction) }}</span></td><td>@money($trade->stake) {{ $trade->wallet_currency ?? config('shell.paper.currency','USD') }}</td><td>{{ $trade->opened_at }}</td><td>{{ $trade->expires_at }}</td></tr>
    @empty<tr><td colspan="7" class="table-empty">There are no open positions.</td></tr>@endforelse
    </tbody></table></div><div class="pagination-wrap">{{ $openTrades->links() }}</div>
</details>

<details class="panel ops-section" style="--ops-accent:#10b981;--ops-tint:rgba(16,185,129,.09);--ops-border:rgba(16,185,129,.22)">
    <summary><div class="panel-head"><div><span class="eyebrow">SETTLEMENT RECORDS</span><h2><span class="ops-heading-icon"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="m7 12 3 3 7-7"/></svg></span>Recent settlements</h2></div></div></summary>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>ACCOUNT</th><th>PAIR</th><th>OPEN</th><th>CLOSE</th><th>STAKE</th><th>OUTCOME</th><th>SETTLED AT</th></tr></thead><tbody>
    @forelse($recentSettlements as $trade)<tr><td>{{ $trade->client_name }}</td><td><span class="{{ ($trade->account_type ?? 'demo')==='real'?'ops-account-real':'ops-account-demo' }}">{{ strtoupper($trade->account_type ?? 'demo') }}</span></td><td>{{ $trade->symbol }}</td><td>@decimal($trade->opening_price)</td><td>@decimal($trade->closing_price)</td><td>@money($trade->stake) {{ $trade->wallet_currency ?? config('shell.paper.currency','USD') }}</td><td><span class="ops-outcome-{{ $trade->status }}">{{ strtoupper($trade->status) }}</span></td><td>{{ $trade->settled_at }}</td></tr>
    @empty<tr><td colspan="8" class="table-empty">No settlements recorded yet.</td></tr>@endforelse
    </tbody></table></div>
</details>

<details class="panel ops-section" style="--ops-accent:#f59e0b;--ops-tint:rgba(245,158,11,.09);--ops-border:rgba(245,158,11,.22)">
    <summary><div class="panel-head"><div><span class="eyebrow">PAPER CONVERSIONS</span><h2><span class="ops-heading-icon"><svg viewBox="0 0 24 24"><path d="M4 7h14l-3-3m5 13H6l3 3"/><path d="M18 7v5M6 12v5"/></svg></span>Recent simulated conversions</h2></div></div></summary>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>FROM</th><th>TO</th><th>RATE</th><th>DATE</th></tr></thead><tbody>
    @forelse($recentConversions as $conversion)<tr><td>{{ $conversion->client_name }}</td><td>@decimal($conversion->amount_in) {{ $conversion->from_currency }}</td><td>@decimal($conversion->amount_out) {{ $conversion->to_currency }}</td><td>@decimal($conversion->source_price) / @decimal($conversion->target_price)</td><td>{{ $conversion->created_at }}</td></tr>
    @empty<tr><td colspan="5" class="table-empty">No paper conversions recorded.</td></tr>@endforelse
    </tbody></table></div>
</details>

<details class="panel ops-section" style="--ops-accent:#f87171;--ops-tint:rgba(248,113,113,.09);--ops-border:rgba(248,113,113,.22)">
    <summary><div class="panel-head"><div><span class="eyebrow">ADMIN AUDIT TRAIL</span><h2><span class="ops-heading-icon"><svg viewBox="0 0 24 24"><path d="M12 3 4 6v5c0 5 3.4 8.3 8 10 4.6-1.7 8-5 8-10V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span>Recent administrator access</h2></div></div></summary>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>ADMIN</th><th>ACTION</th><th>METHOD</th><th>PATH</th><th>IP</th><th>TIME</th></tr></thead><tbody>
    @forelse($auditLogs as $log)<tr><td>{{ $log->actor_name??'Deleted account' }}<small>{{ $log->actor_email }}</small></td><td>{{ $log->action }}</td><td>{{ $log->method }}</td><td>{{ $log->path }}</td><td>{{ $log->ip_address }}</td><td>{{ $log->created_at }}</td></tr>
    @empty<tr><td colspan="6" class="table-empty">No audit records yet.</td></tr>@endforelse
    </tbody></table></div><div class="pagination-wrap">{{ $auditLogs->links() }}</div>
</details>
@endsection
