@extends('layouts.guest')
@section('title', 'Confirm password · Shell')
@section('content')
<div class="auth-shell"><a class="brand auth-brand" href="{{ route('home') }}"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">FINANCIAL TERMINAL</span></span></a><div class="auth-card"><span class="eyebrow">SECURITY CHECK</span><h1>Confirm your password</h1><p class="muted">For your security, confirm your current password before continuing.</p>@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif<form method="POST" action="{{ url('/confirm-password') }}" class="form-stack">@csrf<label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="button button-primary button-full" type="submit">Confirm password</button></form></div></div>
@endsection
