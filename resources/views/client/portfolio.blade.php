@extends('layouts.app')
@section('title','Portfolio')
@section('eyebrow','WALLET CENTER')
@section('heading','My wallets')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Two independent wallets</strong><p>Demo and real balances are stored in separate ledger accounts. Demo credits have no cash value. Real-money deposits, withdrawals and trading remain disabled until approved payment and custody integrations are connected.</p></div><span class="mode-tag">SEPARATE ACCOUNTS</span></div>
<div class="metric-grid">
    <article class="metric-card"><span class="metric-label">DEMO WALLET · {{ $currency }}</span><strong>{{ $demoBalance }}</strong><small>Simulation credits only</small></article>
    <article class="metric-card"><span class="metric-label">REAL WALLET · {{ $currency }}</span><strong>{{ $realBalance }}</strong><small>Real funds · deposits not connected</small></article>
    <article class="metric-card"><span class="metric-label">REAL DEPOSITS</span><strong>Disabled</strong><small>Payment provider not connected</small></article>
    <article class="metric-card"><span class="metric-label">REAL WITHDRAWALS</span><strong>Disabled</strong><small>Custody and compliance controls pending</small></article>
</div>
<div class="content-grid">
    <section class="panel">
        <div class="panel-head"><div><span class="eyebrow">DEMO ENVIRONMENT</span><h2>Demo wallet</h2></div><span class="table-status verified">ACTIVE</span></div>
        <p class="muted">Used by demo trading, paper conversions, simulated stakes and settlement. Its balance cannot be transferred to the real wallet.</p>
        <div class="metric-card"><span class="metric-label">AVAILABLE {{ $currency }}</span><strong>{{ $demoBalance }}</strong></div>
        <a class="text-link" href="{{ route('trading.index') }}">Open demo trading ↗</a>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>ASSET</th><th>DEMO BALANCE</th><th>TYPE</th></tr></thead><tbody>
        @forelse($demoBalances as $asset)<tr><td><strong>{{ $asset['currency'] }}</strong></td><td>{{ $asset['balance'] }}</td><td><span class="table-status verified">DEMO</span></td></tr>@empty<tr><td colspan="3" class="table-empty">No demo balances yet.</td></tr>@endforelse
        </tbody></table></div>
        <a class="text-link" href="{{ route('convert.index') }}">Convert demo assets ↗</a>
    </section>
    <section class="panel">
        <div class="panel-head"><div><span class="eyebrow">REAL-MONEY ENVIRONMENT</span><h2>Real wallet</h2></div><span class="table-status pending">NOT FUNDED</span></div>
        <p class="muted">A separate real-wallet ledger account is provisioned for this client. It is not connected to the demo trading engine.</p>
        <div class="metric-card"><span class="metric-label">AVAILABLE {{ $currency }}</span><strong>{{ $realBalance }}</strong></div>
        <div class="notice-banner"><span class="notice-icon">!</span><div><strong>Real transactions are not enabled</strong><p>Do not send funds to Shell. Deposit, withdrawal, custody and real trading workflows are unavailable until a regulated provider and required controls are integrated.</p></div></div>
    </section>
</div>
@endsection
