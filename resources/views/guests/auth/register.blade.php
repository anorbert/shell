@extends('layouts.guest')
@section('title', 'Create account · Shell')
@section('content')
<div class="auth-shell"><a class="brand auth-brand" href="{{ route('home') }}"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">FINANCIAL TERMINAL</span></span></a><div class="auth-card"><span class="eyebrow">GET STARTED</span><h1>Create your account</h1><p class="muted">Your Shell client workspace starts here.</p>
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('register') }}" class="form-stack">@csrf<label>Full name<input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"></label><label>Email address<input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="new-password"></label><label>Confirm password<input type="password" name="password_confirmation" required autocomplete="new-password"></label><button class="button button-primary button-full" type="submit">Create client account</button></form><div class="auth-bottom">Already registered? <a href="{{ route('login') }}">Sign in</a></div></div><p class="auth-disclaimer">Registration creates a client account. Administrator access is provisioned separately.</p></div>
@endsection
