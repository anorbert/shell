@extends('layouts.guest')
@section('title', 'Verify email · Shell')
@section('content')
<div class="auth-shell"><a class="brand auth-brand" href="{{ route('home') }}"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">FINANCIAL TERMINAL</span></span></a><div class="auth-card"><span class="eyebrow">ACCOUNT SECURITY</span><h1>Verify your email</h1><p class="muted">Please verify your email address using the link sent to your inbox.</p>@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif<form method="POST" action="{{ route('verification.send') }}" class="form-stack">@csrf<button class="button button-primary button-full" type="submit">Resend verification email</button></form><form method="POST" action="{{ route('logout') }}" class="form-stack">@csrf<button class="button button-quiet button-full" type="submit">Sign out</button></form></div></div>
@endsection
