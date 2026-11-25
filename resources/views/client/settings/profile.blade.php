@extends('layouts.app')
@section('title', 'Account settings')
@section('eyebrow', 'ACCOUNT CENTRE')
@section('heading', 'Profile & account details')
@section('content')
<style>
.profile-page{display:grid;gap:18px}.profile-page .panel{border:1px solid var(--line,#26364a);border-radius:16px;overflow:hidden}.profile-page .panel-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:19px 21px;border-bottom:1px solid var(--line,#26364a);margin:0}.profile-page .panel-head h2{font-size:17px;margin:4px 0}.profile-page .panel-body{padding:20px}.profile-page .profile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.profile-page .form-stack{display:grid;gap:14px}.profile-page .form-stack label{display:grid;gap:7px;color:var(--muted,#9aabc0);font-size:12px;font-weight:700}.profile-page .form-stack input{width:100%;min-height:43px;border:1px solid var(--line,#33445b);border-radius:10px;padding:10px 12px;background:var(--panel,#101b2b);color:inherit}.profile-page .button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px}.profile-page .button svg{width:17px;height:17px}.profile-page .status-pill{display:inline-flex;align-items:center;gap:7px;border-radius:999px;padding:7px 11px;font-size:10px;font-weight:800;letter-spacing:.06em;white-space:nowrap;background:rgba(148,163,184,.12);color:#b9c6d5}.profile-page .status-pill:before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}.profile-page .status-pill.verified{background:rgba(64,190,132,.12);color:#67d6a1}.profile-page .status-pill.pending{background:rgba(231,174,73,.12);color:#e9bd69}.profile-page .status-pill.rejected{background:rgba(225,90,100,.12);color:#f08c95}.profile-page .kyc-card{position:relative;background:linear-gradient(135deg,rgba(38,111,157,.15),rgba(16,27,43,.94));border-color:rgba(89,153,192,.32)}.profile-page .kyc-card .panel-body{display:grid;grid-template-columns:1fr auto;align-items:center;gap:20px}.profile-page .kyc-copy{max-width:720px}.profile-page .kyc-copy p,.profile-page .muted{color:var(--muted,#9aabc0);font-size:13px;line-height:1.65}.profile-page .kyc-facts{display:flex;flex-wrap:wrap;gap:8px;margin-top:13px}.profile-page .kyc-facts span{padding:6px 9px;border-radius:8px;background:rgba(148,163,184,.09);font-size:11px;color:var(--muted,#aab8c8)}.profile-page .info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.profile-page .info-item{padding:14px;border:1px solid var(--line,#26364a);border-radius:12px}.profile-page .info-item small{display:block;color:var(--muted,#9aabc0);font-size:10px;letter-spacing:.08em;font-weight:800}.profile-page .info-item strong{display:block;margin-top:7px;font-size:14px;overflow-wrap:anywhere}.profile-page .alert{padding:12px 14px;border-radius:10px;font-size:13px}.profile-page .alert-success{background:rgba(64,190,132,.12);color:#75d9aa}.profile-page .alert-error{background:rgba(225,90,100,.12);color:#f08c95}.profile-page .section-divider{height:1px;background:var(--line,#26364a);margin:22px 0}.profile-page .icon-tile{display:grid;place-items:center;width:38px;height:38px;border-radius:11px;background:rgba(89,153,192,.13);color:#82c6ec;flex:0 0 auto}.profile-page .icon-tile svg{width:19px;height:19px}
@media(max-width:700px){.profile-page .profile-grid,.profile-page .info-grid{grid-template-columns:1fr}.profile-page .kyc-card .panel-body{grid-template-columns:1fr}.profile-page .panel-head,.profile-page .panel-body{padding:16px}.profile-page .panel-head{align-items:flex-start;flex-direction:column}}
</style>
<div class="profile-page">
@if(session('status'))<div class="alert alert-success">{{ session('status') === 'profile-updated' ? 'Your profile details were saved successfully.' : session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">PERSONAL INFORMATION</span><h2>Profile details</h2><p class="muted">Keep your account name and email address up to date.</p></div></div>
    <div class="panel-body"><form method="POST" action="{{ route('profile.update') }}" class="form-stack">@csrf @method('PATCH')
        <div class="profile-grid"><label>Full name<input name="name" value="{{ old('name', auth()->user()->name) }}" required autocomplete="name"></label><label>Email address<input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="email"></label></div>
        <div><button class="button button-primary" type="submit">Save profile changes</button></div>
    </form></div>
</section>

<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">CONTACT INFORMATION</span><h2>Country and phone</h2></div><span class="status-pill {{ auth()->user()->phone_verified_at ? 'verified' : '' }}">{{ auth()->user()->phone_verified_at ? 'PHONE VERIFIED' : 'NOT VERIFIED' }}</span></div>
    <div class="panel-body"><div class="info-grid"><div class="info-item"><small>COUNTRY / REGION</small><strong>{{ auth()->user()->country_code ?? 'Not provided' }}</strong></div><div class="info-item"><small>INTERNATIONAL PHONE</small><strong>{{ auth()->user()->phone_e164 ?? 'Not provided' }}</strong></div></div><p class="muted" style="margin-top:13px">Phone ownership is not verified by registration. Do not use this number for security-sensitive SMS actions until a phone verification process is enabled.</p></div>
</section>

<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">ACCOUNT SECURITY</span><h2>Change password</h2><p class="muted">Use a strong, unique password to protect your account.</p></div></div>
    <div class="panel-body"><form method="POST" action="{{ route('password.update') }}" class="form-stack">@csrf @method('PUT')
        <div class="profile-grid"><label>Current password<input type="password" name="current_password" required autocomplete="current-password"></label><label>New password<input type="password" name="password" required autocomplete="new-password"></label></div>
        <label>Confirm new password<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
        <div><button class="button button-secondary" type="submit">Update password</button></div>
    </form></div>
</section>
</div>
@endsection
