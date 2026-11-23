@extends('layouts.app')
@section('title', 'Client overview')
@section('eyebrow', 'CLIENT WORKSPACE')
@section('heading', 'Good day, '.explode(' ', $user->name)[0])
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Two independent wallets</strong><p>Your demo wallet starts with $20,000 in non-withdrawable simulation credits. You can add demo funds. The real wallet starts at $0 and will only be credited after a confirmed real-money deposit.</p></div><span class="mode-tag">DUAL WALLET</span></div>
<div class="metric-grid">
    <article class="metric-card"><span class="metric-label">DEMO WALLET · USD</span><strong>{{ $demoBalance }}</strong><small>Active · simulated funds</small></article>
    <article class="metric-card"><span class="metric-label">REAL WALLET · USD</span><strong>{{ $realBalance }}</strong><small>Separate ledger · not funded</small></article>
    <article class="metric-card"><span class="metric-label">FRESH MARKET PAIRS</span><strong>{{ $quotes->count() }}</strong><small>{{ $marketConnected ? 'Provider quotes available' : 'No fresh provider quotes' }}</small></article>
    <article class="metric-card"><span class="metric-label">REAL-MONEY STATUS</span><strong>Disabled</strong><small>Deposits and withdrawals unavailable</small></article>
</div>
<div class="content-grid"><section class="panel panel-wide"><div class="panel-head"><div><span class="eyebrow">MARKET WATCH</span><h2>Market overview</h2></div><a href="{{ route('markets.index') }}" class="text-link">View markets ↗</a></div>
@if($marketConnected)<div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>PRICE (USDT)</th><th>PROVIDER</th><th>CAPTURED</th></tr></thead><tbody>@foreach($quotes->take(6) as $quote)<tr><td><strong>{{ $quote->symbol }}</strong></td><td>{{ $quote->price }}</td><td>{{ $quote->provider }}</td><td>{{ $quote->captured_at }}</td></tr>@endforeach</tbody></table></div>
@else<div class="empty-state"><div class="empty-icon">⌁</div><strong>No fresh provider quotes</strong><p>Run <code>php artisan shell:markets:refresh</code> and verify provider connectivity. Stale prices are hidden and cannot be traded.</p></div>@endif
</section><section class="panel"><div class="panel-head"><div><span class="eyebrow">QUICK ACCESS</span><h2>Your workspace</h2></div></div><div class="quick-links"><a href="{{ route('trading.index') }}"><span class="quick-icon">↗</span><span><strong>Demo trading</strong><small>Trade with simulation credits</small></span><b>→</b></a><a href="{{ route('convert.index') }}"><span class="quick-icon">⇄</span><span><strong>Demo conversion</strong><small>Convert demo balances</small></span><b>→</b></a><a href="{{ route('portfolio.index') }}"><span class="quick-icon">◫</span><span><strong>Wallet center</strong><small>View both wallets</small></span><b>→</b></a><a href="{{ route('activity.index') }}"><span class="quick-icon">↺</span><span><strong>Activity</strong><small>Review account records</small></span><b>→</b></a></div></section></div>
@endsection
