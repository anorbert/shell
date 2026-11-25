@extends('layouts.app')
@section('title', 'Identity verification')
@section('eyebrow', 'ACCOUNT VERIFICATION')
@section('heading', 'Know Your Customer (KYC)')
@section('content')
<style>
.kyc-page{display:grid;gap:18px;max-width:980px}.kyc-page .panel{border:1px solid var(--line,#26364a);border-radius:16px;overflow:hidden}.kyc-page .panel-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:19px 21px;border-bottom:1px solid var(--line,#26364a)}.kyc-page .panel-body{padding:22px}.kyc-page .muted{color:var(--muted,#9aabc0);font-size:13px;line-height:1.7}.kyc-page .status{padding:7px 11px;border-radius:99px;font-size:10px;font-weight:800;letter-spacing:.06em;background:rgba(148,163,184,.12);color:#bdc8d5}.kyc-page .status.verified{background:rgba(64,190,132,.12);color:#67d6a1}.kyc-page .status.pending{background:rgba(231,174,73,.12);color:#e9bd69}.kyc-page .status.rejected{background:rgba(225,90,100,.12);color:#f08c95}.kyc-page .form-stack{display:grid;gap:14px}.kyc-page label{display:grid;gap:7px;color:var(--muted,#a1afc0);font-size:12px;font-weight:700}.kyc-page input,.kyc-page select{width:100%;min-height:44px;border:1px solid var(--line,#33445b);border-radius:10px;padding:10px 12px;background:var(--panel,#101b2b);color:inherit}.kyc-page .button{min-height:44px}.kyc-page .alert{padding:13px 15px;border-radius:11px;font-size:13px}.kyc-page .alert-success{background:rgba(64,190,132,.12);color:#75d9aa}.kyc-page .alert-error{background:rgba(225,90,100,.12);color:#f08c95}.kyc-page .security-note{padding:14px;border:1px solid var(--line,#26364a);border-radius:12px;background:rgba(79,151,197,.07)}
</style>
<div class="kyc-page">
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">IDENTITY & SECURITY</span><h2 style="margin:4px 0">Verification status</h2></div>
        @if($kycVerified)<span class="status verified">VERIFIED</span>
        @elseif(($kyc?->status ?? '') === 'pending')<span class="status pending">UNDER REVIEW</span>
        @elseif(($kyc?->status ?? '') === 'rejected')<span class="status rejected">ACTION REQUIRED</span>
        @else<span class="status">NOT SUBMITTED</span>@endif
    </div>
    <div class="panel-body">
        @if($kycVerified)
            <strong>Your identity has been verified.</strong><p class="muted">Your account is eligible to request withdrawals, subject to available balance, enabled payout networks and operations review.</p>
        @elseif(($kyc?->status ?? '') === 'pending')
            <strong>Your documents are being reviewed.</strong><p class="muted">Your submission is in the review queue. You cannot submit another application while this review is pending. Withdrawals remain locked until approval.</p>
        @elseif(($kyc?->status ?? '') === 'rejected')
            <strong>Your previous submission needs attention.</strong><p class="muted">{{ $kyc->review_note ?: 'Your previous KYC submission was not approved. Review your details and submit a corrected application.' }}</p>
        @else
            <strong>Verify your identity to enable withdrawals.</strong><p class="muted">KYC is available at any time. You do not need to deposit funds first. Submit your identity document and selfie for administrator review. Withdrawals remain locked until approval.</p>
        @endif
        <div class="security-note muted" style="margin-top:16px">Your documents are stored securely and are only used for identity verification. Never submit someone else's identity documents.</div>
    </div>
</section>
@if(!$kycVerified && ($kyc?->status ?? '') !== 'pending')
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">SECURE DOCUMENT SUBMISSION</span><h2 style="margin:4px 0">{{ ($kyc?->status ?? '') === 'rejected' ? 'Resubmit your documents' : 'Submit identity documents' }}</h2></div></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('kyc.submit') }}" enctype="multipart/form-data" class="form-stack">@csrf
            <label>Document type<select name="document_type" required><option value="national_id">National ID</option><option value="passport">Passport</option><option value="driving_licence">Driving licence</option></select></label>
            <label>Document number<input name="document_number" maxlength="120" required autocomplete="off"></label>
            <label>Identity document (JPG, PNG or PDF; max 10 MB)<input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required></label>
            <label>Selfie (JPG or PNG; max 5 MB)<input type="file" name="selfie" accept=".jpg,.jpeg,.png" required></label>
            <button class="button button-primary" type="submit">Submit for verification</button>
        </form>
    </div>
</section>
@endif
<div class="page-action-row"><a class="button button-secondary" href="{{ route('wallet-requests.index', ['account' => 'real']) }}">Return to wallet</a></div>
</div>
@endsection
