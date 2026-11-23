@extends('layouts.app')
@section('title', 'Administration')
@section('eyebrow', 'ADMINISTRATION')
@section('heading', 'Platform overview')
@section('content')
<div class="notice-banner"><span class="notice-icon">!</span><div><strong>Simulation environment only</strong><p>Real-money deposits, withdrawals, custody, conversions and live execution remain disabled. Paper balances have no cash value.</p></div><span class="mode-tag">PAPER ONLY</span></div>
<div class="metric-grid">
    <article class="metric-card"><span class="metric-label">CLIENT ACCOUNTS</span><strong>{{ number_format($clientCount) }}</strong><small>Registered clients</small></article>
    <article class="metric-card"><span class="metric-label">ADMIN ACCOUNTS</span><strong>{{ number_format($adminCount) }}</strong><small>Privileged operators</small></article>
    <article class="metric-card"><span class="metric-label">FRESH MARKET PAIRS</span><strong>{{ number_format($freshQuoteCount) }}/{{ number_format($configuredMarketCount) }}</strong><small>Quotes within freshness limit</small></article>
    <article class="metric-card"><span class="metric-label">OPEN PAPER TRADES</span><strong>{{ number_format($openTradeCount) }}</strong><small>{{ number_format($settledTradeCount) }} settled overall</small></article>
</div>
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">OPERATIONS</span><h2>Administration tools</h2></div></div>
    <div class="quick-links">
        <a href="{{ route('admin.clients') }}"><span class="quick-icon">◎</span><span><strong>Client accounts</strong><small>Review account status and access</small></span><b>→</b></a>
        <a href="{{ route('admin.operations') }}"><span class="quick-icon">⌁</span><span><strong>Trading operations</strong><small>Inspect quote freshness, open positions and settlements</small></span><b>→</b></a>
    </div>
</section>
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">LATEST ACTIVITY</span><h2>Recent paper trades</h2></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPENED</th><th>STATUS</th></tr></thead><tbody>
    @forelse($latestTrades as $trade)
        <tr><td><strong>{{ $trade->client_name }}</strong><small>{{ $trade->client_email }}</small></td><td>{{ $trade->symbol }}</td><td>{{ $trade->direction }}</td><td>{{ $trade->stake }} USDT</td><td>{{ $trade->opened_at }}</td><td><span class="table-status {{ $trade->status === 'open' ? 'pending' : 'verified' }}">{{ strtoupper($trade->status) }}</span></td></tr>
    @empty
        <tr><td colspan="6" class="table-empty">No paper trades have been opened.</td></tr>
    @endforelse
    </tbody></table></div>
</section>
@endsection
