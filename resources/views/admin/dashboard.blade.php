@extends('layouts.app')
@section('title', 'Administration')
@section('eyebrow', 'ADMINISTRATION')
@section('heading', 'Platform overview')
@section('content')
<div class="notice-banner"><span class="notice-icon">!</span><div><strong>Operations are in setup mode</strong><p>Real-money deposits, withdrawals, conversions and live execution remain disabled until integrations and controls are verified.</p></div><span class="mode-tag">RESTRICTED</span></div>
<div class="metric-grid"><article class="metric-card"><span class="metric-label">CLIENT ACCOUNTS</span><strong>{{ number_format($clientCount) }}</strong><small>Registered clients</small></article><article class="metric-card"><span class="metric-label">ADMIN ACCOUNTS</span><strong>{{ number_format($adminCount) }}</strong><small>Privileged operators</small></article><article class="metric-card"><span class="metric-label">VERIFIED EMAILS</span><strong>{{ number_format($verifiedCount) }}</strong><small>Across all accounts</small></article><article class="metric-card"><span class="metric-label">TRADING ENGINE</span><strong>Disabled</strong><small>Awaiting verified data source</small></article></div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">OPERATIONS</span><h2>Administration tools</h2></div></div><div class="quick-links"><a href="{{ route('admin.clients') }}"><span class="quick-icon">◎</span><span><strong>Client accounts</strong><small>Review account status and access</small></span><b>→</b></a></div></section>
@endsection
