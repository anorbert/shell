@extends('layouts.app')
@section('title', $title)
@section('eyebrow', 'CLIENT WORKSPACE')
@section('heading', $title)
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Module foundation is in place</strong><p>This area is reserved for the {{ strtolower($title) }} workflow. It will not display invented balances, prices, orders or returns.</p></div><span class="mode-tag">SETUP</span></div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">NEXT CONFIGURATION</span><h2>{{ $title }}</h2></div></div><div class="empty-state"><div class="empty-icon">⌁</div><strong>Waiting for verified service configuration</strong><p>Provider credentials, business rules, persistence and test coverage must be completed before this module can process activity.</p></div></section>
@endsection
