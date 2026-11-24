@extends('layouts.app')
@section('title','Deposits and withdrawals')
@section('eyebrow','REAL WALLET')
@section('heading','Funding requests')
@section('content')
<div class="page-action-row">
    <a href="{{ route('statement.index') }}" class="button button-secondary"><svg class="nav-icon"><use href="#i-receipt"/></svg> View wallet statement</a>
</div>
<div class="notice-banner"><span class="notice-icon">!</span><div><strong>Real-wallet funding and payouts</strong><p>Use the supported asset and network details shown on this page. Deposits are credited after transaction verification. Withdrawals are processed after identity approval and operations review.</p></div><span class="mode-tag">MANUAL REVIEW</span></div>
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
@if($errors->any())<div class="notice-banner"><div><strong>Check the submitted details</strong><p>{{ $errors->first() }}</p></div></div>@endif
<div class="content-grid">
<section class="panel">
<div class="panel-head"><div><span class="eyebrow">IDENTITY CHECK</span><h2>KYC verification</h2></div><span class="table-status {{ $kycVerified ? 'verified' : 'pending' }}">{{ $kycVerified ? 'VERIFIED' : strtoupper($kyc->status ?? 'NOT SUBMITTED') }}</span></div>
@if($kycVerified)
<p class="muted">Your identity is verified. You can submit withdrawal requests against your available real-wallet balance for processing by the operations team.</p>
@else
<p class="muted">Complete identity verification to activate withdrawal requests. Once your documents are reviewed and approved, the withdrawal form will be available.</p>
<form method="POST" action="{{ route('wallet-requests.kyc') }}" enctype="multipart/form-data" class="form-stack">@csrf
<label>Document type<select name="document_type" required><option value="national_id">National ID</option><option value="passport">Passport</option><option value="driving_licence">Driving licence</option></select></label>
<label>Document number<input name="document_number" maxlength="120"></label>
<label>Identity document (JPG, PNG or PDF; max 10 MB)<input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required></label>
<label>Selfie (required; JPG or PNG; max 5 MB)<input type="file" name="selfie" accept=".jpg,.jpeg,.png" required></label>
<button class="button button-primary" type="submit" {{ ($kyc?->status ?? '') === 'pending' ? 'disabled' : '' }}>Submit KYC for review</button>
</form>
@endif
@if($kyc?->review_note)<p class="muted">Admin review note: {{ $kyc->review_note }}</p>@endif
</section>
<section class="panel">
<div class="panel-head"><div><span class="eyebrow">ADD REAL FUNDS</span><h2>Deposit crypto</h2></div></div>
@if($methods->where('deposits_enabled', true)->isEmpty())<p class="muted">Available deposit networks will appear here. Always use the asset, network and wallet address displayed in your account before sending funds.</p>@else
<form method="POST" action="{{ route('wallet-requests.deposit') }}" class="form-stack">@csrf
<label>Asset / network<select name="method_id" id="deposit-method" required><option value="">Choose network</option>@foreach($methods->where('deposits_enabled', true) as $method)<option value="{{ $method->id }}" data-address="{{ $method->wallet_address }}" data-memo-label="{{ $method->memo_label }}" data-memo="{{ $method->memo_value }}" data-instructions="{{ $method->instructions }}">{{ $method->asset }} · {{ $method->network }}</option>@endforeach</select></label>
<div class="notice-banner" id="deposit-address-box" hidden><div><strong>Deposit address</strong><p id="deposit-address" style="overflow-wrap:anywhere"></p><p id="deposit-memo"></p><p id="deposit-instructions"></p></div><button class="button button-secondary" type="button" id="copy-deposit-address">Copy</button></div>
<label>Crypto amount sent<input name="asset_amount" type="number" min="0.00000001" step="0.000000000001" required></label>
<label>Estimated USD value<input name="amount_usd" type="number" min="1" step="0.00000001" required></label>
<label>Transaction hash / TxID<input name="transaction_hash" maxlength="255" required></label>
<label>Note (optional)<textarea name="client_note" maxlength="1000"></textarea></label>
<button class="button button-primary" type="submit">Submit deposit for confirmation</button>
</form>
@endif
</section>
<section class="panel">
<div class="panel-head"><div><span class="eyebrow">PAYOUT</span><h2>Withdraw real funds</h2></div><span class="table-status {{ $kycVerified ? 'verified' : 'pending' }}">{{ $kycVerified ? 'KYC APPROVED' : 'KYC REQUIRED' }}</span></div>
@if(!$kycVerified)<p class="muted">Complete identity verification to activate withdrawals. Your request will then be processed by the operations team.</p>
@elseif($methods->where('withdrawals_enabled', true)->isEmpty())<p class="muted">Withdrawal networks configured for your account will appear here. Contact support if you need help with an available network.</p>
@else
<form method="POST" action="{{ route('wallet-requests.withdraw') }}" class="form-stack">@csrf
<label>Asset / network<select name="method_id" required><option value="">Choose network</option>@foreach($methods->where('withdrawals_enabled', true) as $method)<option value="{{ $method->id }}">{{ $method->asset }} · {{ $method->network }}</option>@endforeach</select></label>
<label>Amount (USD)<input name="amount_usd" type="number" min="1" step="0.00000001" required></label>
<label>Your receiving wallet address<input name="wallet_address" maxlength="255" required></label>
<label>Memo / destination tag (if required by network)<input name="wallet_memo" maxlength="255"></label>
<label>Note (optional)<textarea name="client_note" maxlength="1000"></textarea></label>
<button class="button button-primary" type="submit">Request withdrawal</button>
<p class="muted">Funds are reserved when you submit. If rejected, the reserve is returned. An approved payout is marked paid only after the administrator sends crypto and records the transaction hash.</p>
</form>
@endif
</section>
</div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">REQUEST HISTORY</span><h2>Your deposit and withdrawal requests</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>REQUEST</th><th>TYPE</th><th>AMOUNT</th><th>ASSET / NETWORK</th><th>STATUS</th><th>CREATED</th><th>ADMIN NOTE</th></tr></thead><tbody>
@forelse($requests as $item)<tr><td>#{{ $item->id }}</td><td>{{ strtoupper($item->type) }}</td><td>@money($item->amount_usd) USD</td><td>{{ $item->asset }} · {{ $item->network }}</td><td><span class="table-status {{ in_array($item->status,['confirmed','paid']) ? 'verified' : 'pending' }}">{{ strtoupper($item->status) }}</span></td><td>{{ $item->created_at }}</td><td>{{ $item->admin_note ?: '—' }}</td></tr>@empty<tr><td colspan="7" class="table-empty">No requests submitted.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $requests->links() }}</div></section>
@endsection
@push('scripts')
<script>
const methodSelect=document.getElementById('deposit-method');
methodSelect?.addEventListener('change',()=>{const opt=methodSelect.selectedOptions[0];const box=document.getElementById('deposit-address-box');if(!opt?.value){box.hidden=true;return;}box.hidden=false;document.getElementById('deposit-address').textContent=opt.dataset.address||'';document.getElementById('deposit-memo').textContent=opt.dataset.memo?((opt.dataset.memoLabel||'Memo')+': '+opt.dataset.memo):'';document.getElementById('deposit-instructions').textContent=opt.dataset.instructions||'';});
document.getElementById('copy-deposit-address')?.addEventListener('click',()=>navigator.clipboard.writeText(document.getElementById('deposit-address').textContent));
</script>
@endpush
