@extends('layouts.app')
@section('title', 'Account settings')
@section('eyebrow', 'ACCOUNT')
@section('heading', 'Account settings')
@section('content')
<section class="panel form-panel"><div class="panel-head"><div><span class="eyebrow">PERSONAL INFORMATION</span><h2>Profile details</h2></div></div>@if(session('status'))
    <div class="alert alert-success">Changes saved.</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif<form method="POST" action="{{ route('profile.update') }}" class="form-stack">@csrf @method('PATCH')<label>Full name<input name="name" value="{{ old('name', auth()->user()->name) }}" required></label><label>Email address<input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></label><button class="button button-primary" type="submit">Save changes</button></form><div class="settings-divider"></div><div class="panel-head"><div><span class="eyebrow">SECURITY</span><h2>Password</h2></div></div><form method="POST" action="{{ route('password.update') }}" class="form-stack">@csrf @method('PUT')<label>Current password<input type="password" name="current_password" required></label><label>New password<input type="password" name="password" required></label><label>Confirm new password<input type="password" name="password_confirmation" required></label><button class="button button-quiet" type="submit">Update password</button></form></section>
<section class="panel form-panel"><div class="panel-head"><div><span class="eyebrow">INTERNATIONAL CONTACT</span><h2>Country and phone</h2><p class="muted">These details were captured during registration.</p></div><span class="table-status {{ auth()->user()->phone_verified_at ? 'verified' : 'pending' }}">{{ auth()->user()->phone_verified_at ? 'PHONE VERIFIED' : 'NOT VERIFIED' }}</span></div><div class="content-grid"><div><span class="eyebrow">COUNTRY / REGION</span><p><strong>{{ auth()->user()->country_code ?? 'Not provided' }}</strong></p></div><div><span class="eyebrow">INTERNATIONAL PHONE</span><p><strong>{{ auth()->user()->phone_e164 ?? 'Not provided' }}</strong></p></div></div><p class="muted">Phone ownership is not verified by registration. Do not use this number for security-sensitive SMS actions until a phone verification process is enabled.</p></section>
@endsection
