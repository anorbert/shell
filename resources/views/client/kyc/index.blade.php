@extends('layouts.app')
@section('title', 'Identity verification')
@section('eyebrow', 'ACCOUNT VERIFICATION')
@section('heading', 'Know Your Customer (KYC)')
@section('content')
<style>
.kyc-page{display:grid;gap:18px;max-width:980px}.kyc-page .panel{border:1px solid var(--line,#26364a);border-radius:16px;overflow:hidden}.kyc-page .panel-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:19px 21px;border-bottom:1px solid var(--line,#26364a)}.kyc-page .panel-body{padding:22px}.kyc-page .muted{color:var(--muted,#9aabc0);font-size:13px;line-height:1.7}.kyc-page .status{padding:7px 11px;border-radius:99px;font-size:10px;font-weight:800;letter-spacing:.06em;background:rgba(148,163,184,.12);color:#bdc8d5;white-space:nowrap}.kyc-page .status.verified{background:rgba(64,190,132,.12);color:#67d6a1}.kyc-page .status.pending{background:rgba(231,174,73,.12);color:#e9bd69}.kyc-page .status.rejected{background:rgba(225,90,100,.12);color:#f08c95}.kyc-page .form-stack{display:grid;gap:17px}.kyc-page label.field{display:grid;gap:7px;color:var(--muted,#a1afc0);font-size:12px;font-weight:700}.kyc-page input:not([type=file]),.kyc-page select{width:100%;min-height:46px;border:1px solid var(--line,#33445b);border-radius:10px;padding:10px 12px;background:var(--panel,#101b2b);color:inherit}.kyc-page .button{min-height:44px}.kyc-page .alert{padding:13px 15px;border-radius:11px;font-size:13px}.kyc-page .alert-success{background:rgba(64,190,132,.12);color:#75d9aa}.kyc-page .alert-error{background:rgba(225,90,100,.12);color:#f08c95}.kyc-page .security-note{padding:14px;border:1px solid var(--line,#26364a);border-radius:12px;background:rgba(79,151,197,.07)}.kyc-page .upload-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.kyc-page .upload-field{display:grid;gap:8px;min-width:0}.kyc-page .upload-label{font-size:12px;font-weight:800;color:var(--text,#e8edf5)}.kyc-page .upload-hint{font-size:11px;color:var(--muted,#9aabc0);line-height:1.5}.kyc-page .dropzone{position:relative;display:grid;place-items:center;gap:9px;min-height:174px;padding:18px;border:1.5px dashed #52657d;border-radius:13px;background:rgba(100,140,180,.045);text-align:center;cursor:pointer;transition:border-color .16s,background .16s}.kyc-page .dropzone:hover,.kyc-page .dropzone:focus-within,.kyc-page .dropzone.is-dragging{border-color:#65aee5;background:rgba(70,150,210,.10)}.kyc-page .dropzone.is-invalid{border-color:#e87983}.kyc-page .dropzone-icon{width:40px;height:40px;display:grid;place-items:center;border-radius:12px;background:rgba(90,155,210,.13);color:#83c4f2}.kyc-page .dropzone strong{font-size:13px;color:var(--text,#e8edf5)}.kyc-page .dropzone em{font-style:normal;color:#83c4f2}.kyc-page .file-input{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;clip-path:inset(50%)!important;border:0!important}.kyc-page .file-meta{display:flex;align-items:center;gap:9px;min-width:0;padding:10px 11px;border:1px solid var(--line,#33445b);border-radius:10px;background:rgba(100,140,180,.05)}.kyc-page .file-meta[hidden]{display:none}.kyc-page .file-symbol{width:30px;height:30px;flex:0 0 30px;display:grid;place-items:center;border-radius:8px;background:rgba(90,155,210,.13);color:#83c4f2;font-size:10px;font-weight:900}.kyc-page .file-details{min-width:0;flex:1}.kyc-page .file-name{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;font-weight:700;color:var(--text,#e8edf5)}.kyc-page .file-size{display:block;margin-top:2px;font-size:10px;color:var(--muted,#9aabc0)}.kyc-page .file-remove{width:30px;height:30px;flex:0 0 30px;border:0;border-radius:8px;background:rgba(225,90,100,.10);color:#f08c95;cursor:pointer;font-size:17px}.kyc-page .upload-error{min-height:15px;color:#f08c95;font-size:11px}.kyc-page .submit-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding-top:4px}.kyc-page .submit-row .muted{max-width:490px;font-size:11px}.kyc-page .steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:17px}.kyc-page .step{padding:12px;border:1px solid var(--line,#26364a);border-radius:11px}.kyc-page .step b{display:block;font-size:12px;margin-bottom:4px}.kyc-page .step span{font-size:11px;color:var(--muted,#9aabc0);line-height:1.5}@media(max-width:680px){.kyc-page .panel-head,.kyc-page .panel-body{padding:16px}.kyc-page .upload-grid{grid-template-columns:1fr}.kyc-page .dropzone{min-height:145px}.kyc-page .steps{grid-template-columns:1fr}.kyc-page .submit-row{align-items:stretch;flex-direction:column}.kyc-page .submit-row .button{width:100%}}
</style>
<div class="kyc-page">
@if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-error" role="alert">Please review the highlighted fields: {{ $errors->first() }}</div>@endif
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">IDENTITY & SECURITY</span><h2 style="margin:4px 0">Verification status</h2></div>
        @if($kycVerified)<span class="status verified">VERIFIED</span>
        @elseif(($kyc?->status ?? '') === 'pending')<span class="status pending">UNDER REVIEW</span>
        @elseif(($kyc?->status ?? '') === 'rejected')<span class="status rejected">ACTION REQUIRED</span>
        @else<span class="status">NOT SUBMITTED</span>@endif
    </div>
    <div class="panel-body">
        @if($kycVerified)
            <strong>Your identity has been verified.</strong><p class="muted">Your account is eligible to request withdrawals, subject to available balance, enabled payout networks and any required operational checks.</p>
        @elseif(($kyc?->status ?? '') === 'pending')
            <strong>Your documents are being reviewed.</strong><p class="muted">Your submission is in the review queue. You cannot submit another application while this review is pending. Withdrawals remain locked until approval.</p>
        @elseif(($kyc?->status ?? '') === 'rejected')
            <strong>Your previous submission needs attention.</strong><p class="muted">{{ $kyc->review_note ?: 'Your previous KYC submission was not approved. Review your details and submit a corrected application.' }}</p>
        @else
            <strong>Verify your identity to enable withdrawals.</strong><p class="muted">KYC is available at any time. You do not need to deposit funds first. Submit your identity document and a clear selfie for administrator review. Withdrawals remain locked until approval.</p>
        @endif
        <div class="security-note muted" style="margin-top:16px"><strong>Privacy & security</strong><br>Your documents are encrypted before storage and are used for identity verification. Only submit documents that belong to you. Never share your password, PIN or one-time codes.</div>
    </div>
</section>
@if(!$kycVerified && ($kyc?->status ?? '') !== 'pending')
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">SECURE DOCUMENT SUBMISSION</span><h2 style="margin:4px 0">{{ ($kyc?->status ?? '') === 'rejected' ? 'Resubmit your documents' : 'Submit identity documents' }}</h2><p class="muted" style="margin:5px 0 0">Complete each step. Accepted formats and size limits are shown below each upload area.</p></div></div>
    <div class="panel-body">
        <form id="kyc-form" method="POST" action="{{ route('kyc.submit') }}" enctype="multipart/form-data" class="form-stack" novalidate>@csrf
            <label class="field" for="document_type">Document type<select id="document_type" name="document_type" required><option value="national_id" @selected(old('document_type', 'national_id') === 'national_id')>National ID</option><option value="passport" @selected(old('document_type') === 'passport')>Passport</option><option value="driving_licence" @selected(old('document_type') === 'driving_licence')>Driving licence</option></select>@error('document_type')<span class="field-error">{{ $message }}</span>@enderror</label>
            <label class="field" for="document_number">Document number<input id="document_number" name="document_number" type="text" maxlength="120" minlength="4" value="{{ old('document_number') }}" required autocomplete="off" placeholder="Enter the number shown on your document"><span class="upload-hint">Enter the number exactly as it appears on your original document.</span>@error('document_number')<span class="field-error">{{ $message }}</span>@enderror</label>
            <div class="upload-grid">
                <div class="upload-field">
                    <span class="upload-label">1. Identity document <span style="color:#f08c95">*</span></span>
                    <label class="dropzone" for="kyc-document" id="document-dropzone" tabindex="0" role="button" aria-describedby="document-help">
                        <input class="file-input" id="kyc-document" name="document" type="file" accept=".jpg,.jpeg,.png,.pdf, image/jpeg, image/png, application/pdf" required>
                        <span class="dropzone-icon" aria-hidden="true"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></svg></span>
                        <strong>Drop your document here</strong><span class="muted">or <em>browse files</em></span>
                        <span class="upload-hint" id="document-help">JPG, PNG or PDF · Maximum 10 MB</span>
                    </label>
                    <div class="file-meta" id="document-meta" hidden><span class="file-symbol" id="document-symbol">FILE</span><span class="file-details"><span class="file-name" id="document-name"></span><span class="file-size" id="document-size"></span></span><button class="file-remove" type="button" data-clear="document" aria-label="Remove identity document">×</button></div>
                    <div class="upload-error" id="document-error" aria-live="polite">@error('document'){{ $message }}@enderror</div>
                </div>
                <div class="upload-field">
                    <span class="upload-label">2. Live selfie <span style="color:#f08c95">*</span></span>
                    <label class="dropzone" for="kyc-selfie" id="selfie-dropzone" tabindex="0" role="button" aria-describedby="selfie-help">
                        <input class="file-input" id="kyc-selfie" name="selfie" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
                        <span class="dropzone-icon" aria-hidden="true"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M8 15l2-2 2 2 3-3 3 3"/></svg></span>
                        <strong>Drop your selfie here</strong><span class="muted">or <em>browse files</em></span>
                        <span class="upload-hint" id="selfie-help">JPG or PNG · Maximum 5 MB</span>
                    </label>
                    <div class="file-meta" id="selfie-meta" hidden><span class="file-symbol">IMG</span><span class="file-details"><span class="file-name" id="selfie-name"></span><span class="file-size" id="selfie-size"></span></span><button class="file-remove" type="button" data-clear="selfie" aria-label="Remove selfie">×</button></div>
                    <div class="upload-error" id="selfie-error" aria-live="polite">@error('selfie'){{ $message }}@enderror</div>
                </div>
            </div>
            <div class="steps" aria-label="What happens next">
                <div class="step"><b>1. Submit securely</b><span>Upload your own valid identity document and selfie.</span></div>
                <div class="step"><b>2. Review</b><span>The verification team checks your submitted information.</span></div>
                <div class="step"><b>3. Get verified</b><span>Once approved, the KYC withdrawal restriction is lifted.</span></div>
            </div>
            <div class="submit-row"><p class="muted">By submitting, you confirm that the information is accurate and the documents belong to you.</p><button id="kyc-submit" class="button button-primary" type="submit">Submit for verification</button></div>
        </form>
    </div>
</section>
@endif
<div class="page-action-row"><a class="button button-secondary" href="{{ route('wallet-requests.index', ['account' => 'real']) }}">Return to wallet</a></div>
</div>
@if(!$kycVerified && ($kyc?->status ?? '') !== 'pending')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const configs = {
        document: { input: document.getElementById('kyc-document'), zone: document.getElementById('document-dropzone'), meta: document.getElementById('document-meta'), name: document.getElementById('document-name'), size: document.getElementById('document-size'), symbol: document.getElementById('document-symbol'), error: document.getElementById('document-error'), max: 10 * 1024 * 1024, types: ['image/jpeg', 'image/png', 'application/pdf'], extensions: ['jpg', 'jpeg', 'png', 'pdf'] },
        selfie: { input: document.getElementById('kyc-selfie'), zone: document.getElementById('selfie-dropzone'), meta: document.getElementById('selfie-meta'), name: document.getElementById('selfie-name'), size: document.getElementById('selfie-size'), error: document.getElementById('selfie-error'), max: 5 * 1024 * 1024, types: ['image/jpeg', 'image/png'], extensions: ['jpg', 'jpeg', 'png'] }
    };
    const formatSize = bytes => bytes < 1024 * 1024 ? (bytes / 1024).toFixed(0) + ' KB' : (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    const showError = (key, message) => {
        const cfg = configs[key];
        cfg.error.textContent = message || '';
        cfg.zone.classList.toggle('is-invalid', !!message);
        cfg.input.setAttribute('aria-invalid', message ? 'true' : 'false');
    };
    const setFile = (key, file) => {
        const cfg = configs[key];
        if (!file) return;
        const ext = file.name.split('.').pop().toLowerCase();
        if (!cfg.extensions.includes(ext) || (file.type && !cfg.types.includes(file.type))) {
            cfg.input.value = ''; cfg.meta.hidden = true;
            showError(key, 'Unsupported file type. Please choose an accepted format.');
            return;
        }
        if (file.size > cfg.max) {
            cfg.input.value = ''; cfg.meta.hidden = true;
            showError(key, 'This file is too large. Maximum allowed size is ' + formatSize(cfg.max) + '.');
            return;
        }
        const transfer = new DataTransfer();
        transfer.items.add(file);
        cfg.input.files = transfer.files;
        cfg.name.textContent = file.name;
        cfg.size.textContent = formatSize(file.size);
        if (cfg.symbol) cfg.symbol.textContent = ext.toUpperCase().slice(0, 4);
        cfg.meta.hidden = false;
        showError(key, '');
    };
    Object.entries(configs).forEach(([key, cfg]) => {
        cfg.input.addEventListener('change', () => setFile(key, cfg.input.files[0]));
        cfg.zone.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); cfg.input.click(); }
        });
        ['dragenter', 'dragover'].forEach(name => cfg.zone.addEventListener(name, event => {
            event.preventDefault(); event.stopPropagation(); cfg.zone.classList.add('is-dragging');
        }));
        ['dragleave', 'drop'].forEach(name => cfg.zone.addEventListener(name, event => {
            event.preventDefault(); event.stopPropagation(); cfg.zone.classList.remove('is-dragging');
        }));
        cfg.zone.addEventListener('drop', event => setFile(key, event.dataTransfer.files[0]));
    });
    document.querySelectorAll('[data-clear]').forEach(button => button.addEventListener('click', () => {
        const key = button.dataset.clear, cfg = configs[key];
        cfg.input.value = ''; cfg.meta.hidden = true; showError(key, '');
    }));
    document.getElementById('kyc-form')?.addEventListener('submit', event => {
        let valid = true;
        Object.entries(configs).forEach(([key, cfg]) => {
            if (!cfg.input.files.length) { showError(key, 'Please choose a file before submitting.'); valid = false; }
        });
        const number = document.getElementById('document_number');
        if (!number.value.trim() || number.value.trim().length < 4) { number.setCustomValidity('Enter a valid document number (at least 4 characters).'); number.reportValidity(); valid = false; }
        else number.setCustomValidity('');
        if (!valid) event.preventDefault();
    });
});
</script>
@endif
@endsection
