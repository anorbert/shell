@extends('layouts.app')
@section('title','Portfolio') @section('eyebrow','SIMULATION WALLET') @section('heading','Portfolio')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Paper wallet only</strong><p>This ledger balance cannot be deposited, withdrawn, transferred or redeemed.</p></div><span class="mode-tag">NON-CASH</span></div>
<div class="metric-grid"><article class="metric-card"><span class="metric-label">AVAILABLE PAPER BALANCE</span><strong>{{ $balance }}</strong><small>{{ $currency }} · Simulation credits</small></article><article class="metric-card"><span class="metric-label">CASH VALUE</span><strong>0.00</strong><small>No redemption value</small></article><article class="metric-card"><span class="metric-label">DEPOSITS</span><strong>Disabled</strong><small>Not connected</small></article><article class="metric-card"><span class="metric-label">WITHDRAWALS</span><strong>Disabled</strong><small>Not connected</small></article></div>
<section class="panel"><div class="empty-state"><div class="empty-icon">◫</div><strong>Ledger-backed simulation wallet</strong><p>Paper credit issuance, reserved stakes and settlements are recorded as immutable balanced entries. Ask an administrator to provision simulation credits.</p></div></section>
@endsection
