@extends('layouts.app')
@section('title','Trading operations') @section('eyebrow','ADMINISTRATION / CONTROL ROOM') @section('heading','Trading operations')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Platform operations</strong><p>Monitor market-data freshness, trade settlement, conversion activity and security events. Financial records remain ledger-controlled to preserve a reliable audit trail.</p></div><span class="mode-tag">MONITORING</span></div>
<div class="metric-grid">
<article class="metric-card"><span class="metric-label">STALE QUOTES</span><strong>{{ $staleQuoteCount }}</strong><small>Configured markets without a fresh quote</small></article>
<article class="metric-card"><span class="metric-label">OVERDUE POSITIONS</span><strong>{{ $overdueTradeCount }}</strong><small>Positions awaiting settlement</small></article>
<article class="metric-card"><span class="metric-label">OPEN POSITIONS</span><strong>{{ $openTradeCount }}</strong><small>{{ $openDemoTradeCount }} demo · {{ $openRealTradeCount }} real</small></article>
<article class="metric-card"><span class="metric-label">LAST MARKET REFRESH</span><strong>{{ $marketLastSuccess ? $marketLastSuccess->diffForHumans() : 'Never' }}</strong><small>Last successful refresh</small></article>
</div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">SCHEDULED TASKS</span><h2>Background job health</h2></div><span class="muted">Server scheduler must run every minute</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>JOB</th><th>STATUS</th><th>LAST STARTED</th><th>LAST SUCCESS</th><th>DURATION</th><th>MESSAGE</th></tr></thead><tbody>
@forelse($jobRuns as $job)<tr><td>{{ $job->job_key }}</td><td><span class="table-status {{ $job->status==='success'?'verified':($job->status==='failed'?'pending':'') }}">{{ strtoupper($job->status) }}</span></td><td>{{ $job->last_started_at??'—' }}</td><td>{{ $job->last_success_at??'Never' }}</td><td>{{ $job->duration_ms===null?'—':$job->duration_ms.' ms' }}</td><td>{{ $job->message }}</td></tr>
@empty<tr><td colspan="6" class="table-empty">No job heartbeat recorded yet.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">MARKET PROVIDER</span><h2>Quote freshness</h2></div><span class="muted">Freshness limit: {{ config('shell.market.max_age_seconds') }} seconds</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>PRICE</th><th>PROVIDER</th><th>CAPTURED AT</th><th>HEALTH</th></tr></thead><tbody>
@forelse($quotes as $quote)<tr><td><strong>{{ $quote->symbol }}</strong></td><td>@decimal($quote->price) USDT</td><td>{{ $quote->provider }}</td><td>{{ $quote->captured_at }}</td><td><span class="table-status {{ $quote->is_fresh?'verified':'pending' }}">{{ $quote->is_fresh?'FRESH':'STALE' }}</span></td></tr>
@empty<tr><td colspan="5" class="table-empty">No market quotes have been captured yet.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">SETTLEMENT QUEUE</span><h2>Open trading positions</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>ACCOUNT</th><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPENED</th><th>EXPIRES</th></tr></thead><tbody>
@forelse($openTrades as $trade)<tr><td><strong>{{ $trade->client_name }}</strong><small>{{ $trade->client_email }}</small></td><td>{{ strtoupper($trade->account_type ?? "demo") }}</td><td>{{ $trade->symbol }}</td><td>{{ $trade->direction }}</td><td>@money($trade->stake) {{ $trade->wallet_currency ?? config('shell.paper.currency','USD') }}</td><td>{{ $trade->opened_at }}</td><td>{{ $trade->expires_at }}</td></tr>
@empty<tr><td colspan="6" class="table-empty">There are no open paper trades.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $openTrades->links() }}</div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">SETTLEMENT RECORDS</span><h2>Recent settlements</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>ACCOUNT</th><th>PAIR</th><th>OPEN</th><th>CLOSE</th><th>STAKE</th><th>OUTCOME</th><th>SETTLED AT</th></tr></thead><tbody>
@forelse($recentSettlements as $trade)<tr><td>{{ $trade->client_name }}</td><td>{{ strtoupper($trade->account_type ?? "demo") }}</td><td>{{ $trade->symbol }}</td><td>@decimal($trade->opening_price)</td><td>@decimal($trade->closing_price)</td><td>@money($trade->stake) {{ $trade->wallet_currency ?? config('shell.paper.currency','USD') }}</td><td><span class="table-status verified">{{ strtoupper($trade->status) }}</span></td><td>{{ $trade->settled_at }}</td></tr>
@empty<tr><td colspan="7" class="table-empty">No settlements recorded yet.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">PAPER CONVERSIONS</span><h2>Recent simulated conversions</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>FROM</th><th>TO</th><th>RATE</th><th>DATE</th></tr></thead><tbody>
@forelse($recentConversions as $conversion)<tr><td>{{ $conversion->client_name }}</td><td>@decimal($conversion->amount_in) {{ $conversion->from_currency }}</td><td>@decimal($conversion->amount_out) {{ $conversion->to_currency }}</td><td>@decimal($conversion->source_price) / @decimal($conversion->target_price)</td><td>{{ $conversion->created_at }}</td></tr>
@empty<tr><td colspan="5" class="table-empty">No paper conversions recorded.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">ADMIN AUDIT TRAIL</span><h2>Recent administrator access</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>ADMIN</th><th>ACTION</th><th>METHOD</th><th>PATH</th><th>IP</th><th>TIME</th></tr></thead><tbody>
@forelse($auditLogs as $log)<tr><td>{{ $log->actor_name??'Deleted account' }}<small>{{ $log->actor_email }}</small></td><td>{{ $log->action }}</td><td>{{ $log->method }}</td><td>{{ $log->path }}</td><td>{{ $log->ip_address }}</td><td>{{ $log->created_at }}</td></tr>
@empty<tr><td colspan="6" class="table-empty">No audit records yet.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $auditLogs->links() }}</div></section>
@endsection
