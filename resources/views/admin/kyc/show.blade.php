@extends('layouts.app')
@section('title','KYC case #'.$kyc->id)
@section('eyebrow','COMPLIANCE OPERATIONS')
@section('heading','KYC case #'.$kyc->id)
@section('content')
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
@if($errors->any())<div class="notice-banner"><div><strong>Review not saved</strong><p>{{ $errors->first() }}</p></div></div>@endif
<div class="panel">
    <div class="panel-head"><div><span class="eyebrow">CASE OVERVIEW</span><h2>{{ $kyc->client_name }}</h2><p class="muted">{{ $kyc->client_email }} · Client ID #{{ $kyc->user_id }}</p></div><span class="table-status {{ $kyc->status==='verified'?'verified':'pending' }}">{{ strtoupper($kyc->status) }}</span></div>
    <div class="content-grid">
        <div><span class="eyebrow">SUBMISSION</span><p><strong>#{{ $kyc->id }}</strong></p><small>Submitted {{ $kyc->created_at }}</small></div>
        <div><span class="eyebrow">DOCUMENT TYPE</span><p><strong>{{ strtoupper(str_replace('_',' ',$kyc->document_type)) }}</strong></p><small>{{ $kyc->document_number ?: 'Document number not supplied' }}</small></div>
        <div><span class="eyebrow">CLIENT ACCOUNT</span><p><strong>Registered</strong></p><small>{{ $kyc->client_registered_at ?: '—' }}</small></div>
        <div><span class="eyebrow">REVIEWER</span><p><strong>{{ $kyc->reviewer_name ?: 'Not reviewed' }}</strong></p><small>{{ $kyc->reviewed_at ?: 'Awaiting decision' }}</small></div>
    </div>
</div>
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">SECURE DOCUMENT VIEWER</span><h2>Identity evidence</h2></div><a class="text-link" href="{{ route('admin.kyc.index') }}">← Back to KYC queue</a></div>
    <div class="content-grid">
        <div class="panel">
            <h3>Identity document</h3>
            @if($kyc->document_mime==='application/pdf')
                <iframe title="Identity document PDF" src="{{ route('admin.wallet-operations.kyc.file',[$kyc->id,'document']) }}" style="width:100%;height:620px;border:1px solid var(--border);border-radius:12px"></iframe>
            @elseif(in_array($kyc->document_mime,['image/jpeg','image/png'],true))
                <img src="{{ route('admin.wallet-operations.kyc.file',[$kyc->id,'document']) }}" alt="Submitted identity document" style="display:block;max-width:100%;max-height:620px;object-fit:contain;margin:auto">
            @else
                <p class="muted">No supported identity document preview is available.</p>
            @endif
            <p><a class="text-link" href="{{ route('admin.wallet-operations.kyc.file',[$kyc->id,'document']) }}" target="_blank" rel="noopener">Open document in a separate tab</a></p>
        </div>
        <div class="panel">
            <h3>Live selfie</h3>
            @if($kyc->selfie_path && in_array($kyc->selfie_mime,['image/jpeg','image/png'],true))
                <img src="{{ route('admin.wallet-operations.kyc.file',[$kyc->id,'selfie']) }}" alt="Client selfie" style="display:block;max-width:100%;max-height:620px;object-fit:contain;margin:auto">
            @else
                <p class="muted">No selfie file is attached.</p>
            @endif
            <p class="muted">Compare the selfie to the portrait on the identity document. Do not approve if the match is uncertain.</p>
        </div>
    </div>
</section>
@if($kyc->status==='pending')
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">MANDATORY REVIEW CONTROLS</span><h2>Verification checklist</h2><p class="muted">Every item must be checked before approval. Complete any required external identity, duplicate-account and risk screening separately.</p></div></div>
    <form method="POST" action="{{ route('admin.wallet-operations.kyc.review',$kyc->id) }}" class="form-stack">
        @csrf
        <label class="checkbox-row"><input type="checkbox" name="checks[identity_legible]" value="1" required> Identity document is authentic-looking, legible and all key fields are visible</label>
        <label class="checkbox-row"><input type="checkbox" name="checks[details_match]" value="1" required> Client name and identity details match the submitted account information</label>
        <label class="checkbox-row"><input type="checkbox" name="checks[document_valid]" value="1" required> Document is current, not expired, and is an accepted identity document</label>
        <label class="checkbox-row"><input type="checkbox" name="checks[selfie_match]" value="1" required> Selfie is clear and the person reasonably matches the document portrait</label>
        <label class="checkbox-row"><input type="checkbox" name="checks[duplicate_check]" value="1" required> Duplicate-account and repeat-submission checks have been completed</label>
        <label class="checkbox-row"><input type="checkbox" name="checks[risk_screened]" value="1" required> Required risk / sanctions screening has been completed using the organisation's approved process</label>
        <label>Reviewer notes (required; record evidence or rejection reason)<textarea name="review_note" rows="4" minlength="5" maxlength="2000" required>{{ old('review_note') }}</textarea></label>
        <div class="notice-banner"><div><strong>Approval is a compliance decision</strong><p>Do not tick checks you did not perform. If evidence is unclear, reject the submission and explain exactly what the client must correct. This screen does not perform automated document authenticity or sanctions checks.</p></div></div>
        <div class="content-grid">
            <button class="button button-primary" type="submit" name="decision" value="verified">Approve KYC</button>
            <button class="button button-secondary" type="submit" name="decision" value="rejected">Reject and request resubmission</button>
        </div>
    </form>
</section>
@else
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">FINAL DECISION</span><h2>{{ strtoupper($kyc->status) }}</h2></div></div>
    <p>{{ $kyc->review_note ?: 'No reviewer note was recorded.' }}</p>
    @if($kyc->review_checklist)
        <h3>Recorded verification checks</h3>
        @php($checks=json_decode($kyc->review_checklist,true) ?: [])
        <ul>@foreach($checks as $key=>$value)<li>{{ ucwords(str_replace('_',' ',$key)) }}: {{ $value ? 'Completed' : 'Not completed' }}</li>@endforeach</ul>
    @endif
</section>
@endif
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">CLIENT KYC TIMELINE</span><h2>Previous submissions</h2></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>CASE</th><th>TYPE</th><th>SUBMITTED</th><th>STATUS</th><th>REVIEW NOTE</th></tr></thead><tbody>
    @foreach($history as $entry)
        <tr><td><a class="text-link" href="{{ route('admin.kyc.show',$entry->id) }}">#{{ $entry->id }}</a></td><td>{{ strtoupper(str_replace('_',' ',$entry->document_type)) }}</td><td>{{ $entry->created_at }}</td><td><span class="table-status {{ $entry->status==='verified'?'verified':'pending' }}">{{ strtoupper($entry->status) }}</span></td><td>{{ $entry->review_note ?: '—' }}</td></tr>
    @endforeach
    </tbody></table></div>
</section>
@endsection
