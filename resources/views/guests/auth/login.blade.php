@extends('layouts.guest')
@section('title', 'Sign in · Shell')
@section('content')
<div class="auth-shell"><a class="brand auth-brand" href="{{ route('home') }}"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">FINANCIAL TERMINAL</span></span></a><div class="auth-card"><span class="eyebrow">WELCOME BACK</span><h1>Sign in to Shell</h1><p class="muted">Access your trading workspace.</p>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('login') }}" class="form-stack">@csrf<label>Email address<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><div class="form-between"><label class="check-label"><input type="checkbox" name="remember"> Remember me</label><a href="{{ route('password.request') }}">Forgot password?</a></div><button class="button button-primary button-full" type="submit">Sign in</button></form><div class="auth-bottom">New to Shell? <a href="{{ route('register') }}">Create an account</a></div></div><p class="auth-disclaimer">Secure account access. Never share your password or verification codes.</p></div>
@endsection
