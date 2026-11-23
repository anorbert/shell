@extends('layouts.app')
@section('title','Wallet operations')
@section('eyebrow','ADMINISTRATION')
@section('heading','Wallet operations')
@section('content')
<div class="notice-banner"><span class="notice-icon">!</span><div><strong>Manual financial control</strong><p>Verify deposits independently on the correct blockchain/network before crediting. For withdrawals, send funds first, then record the transaction hash and mark the request paid. Never approve based only on a screenshot.</p></div><span class="mode-tag">ADMIN REVIEW</span></div>
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
@if($errors->any())<div class="notice-banner"><div><strong>Action not completed</strong><p>{{ $errors->first() }}</p></div></div>@endif
<section class="panel"><div class="panel-head"><div><span class="eyebrow">INBOUND AND OUTBOUND</span><h2>Deposit and withdrawal queue</h2></div><span class="count-chip">{{ $requests->total() }} requests</span></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>TYPE / AMOUNT</th><th>DESTINATION / TXID</th><th>STATUS</th><th>REVIEW ACTION</th></tr></thead><tbody>
@forelse($requests as $item)<tr><td><strong>{{ $item->client_name }}</strong><small>{{ $item->client_email }} · #{{ $item->id }}</small></td><td>{{ strtoupper($item->type) }}<small>{{ number_format((float)$item->amount_usd,8) }} USD · {{ $item->asset }}/{{ $item->network }}</small></td><td><small>Address: {{ $item->wallet_address }}</small><small>TxID: {{ $item->transaction_hash ?: 'Not recorded' }}</small></td><td><span class="table-status {{ in_array($item->status,['confirmed','paid']) ? 'verified' : 'pending' }}">{{ strtoupper($item->status) }}</span></td><td>
@if($item->status==='pending')
@if($item->type==='deposit')
<form method="POST" action="{{ route('admin.wallet-operations.deposit.confirm',$item->id) }}" class="form-stack">@csrf<label>Verified USD credit<input name="amount_usd" type="number" min="1" step="0.00000001" value="{{ $item->amount_usd }}" required></label><label>Admin note<input name="admin_note" maxlength="2000"></label><button class="button button-primary" type="submit">Verify and credit</button></form>
@else
<form method="POST" action="{{ route('admin.wallet-operations.withdrawal.paid',$item->id) }}" class="form-stack">@csrf<label>On-chain payout TxID<input name="transaction_hash" maxlength="255" required></label><label>Admin note<input name="admin_note" maxlength="2000" required></label><button class="button button-primary" type="submit">Mark paid</button></form>
@endif
<form method="POST" action="{{ route('admin.wallet-operations.reject',$item->id) }}" class="form-stack">@csrf<label>Reason for rejection<input name="admin_note" maxlength="2000" required></label><button class="button button-secondary" type="submit">Reject request</button></form>
@else<small>Reviewed {{ $item->reviewed_at ?: '—' }} · {{ $item->admin_note ?: 'No note' }}</small>@endif
</td></tr>@empty<tr><td colspan="5" class="table-empty">No wallet requests.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $requests->links() }}</div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">CRYPTO DESTINATIONS</span><h2>Deposit and withdrawal networks</h2></div></div>
<p class="muted">Configure the exact asset/network pair and official receiving address. Confirm the address with your custody operator before enabling deposits. Address edits apply to new requests; existing requests retain the submitted address.</p>
<form method="POST" action="{{ route('admin.wallet-operations.methods.store') }}" class="form-grid">@csrf
<label>Asset<input name="asset" placeholder="USDT" maxlength="20" required></label><label>Network<input name="network" placeholder="TRC20" maxlength="40" required></label>
<label class="full-width">Receiving wallet address<input name="wallet_address" maxlength="255" required></label>
<label>Memo label (optional)<input name="memo_label" placeholder="Memo / Tag"></label><label>Memo value (optional)<input name="memo_value"></label>
<label class="full-width">Client instructions<textarea name="instructions" maxlength="2000"></textarea></label><button class="button button-primary" type="submit">Add crypto network</button>
</form>
<div class="table-wrap"><table class="data-table"><thead><tr><th>ASSET / NETWORK</th><th>ADDRESS</th><th>DEPOSITS</th><th>WITHDRAWALS</th><th>UPDATE</th></tr></thead><tbody>
@forelse($methods as $method)<tr><td><strong>{{ $method->asset }}</strong><small>{{ $method->network }}</small></td><td style="overflow-wrap:anywhere">{{ $method->wallet_address }}</td><td>{{ $method->deposits_enabled?'Enabled':'Disabled' }}</td><td>{{ $method->withdrawals_enabled?'Enabled':'Disabled' }}</td><td><form method="POST" action="{{ route('admin.wallet-operations.methods.update',$method->id) }}">@csrf<label><input type="checkbox" name="deposits_enabled" value="1" {{ $method->deposits_enabled?'checked':'' }}> Deposits</label><label><input type="checkbox" name="withdrawals_enabled" value="1" {{ $method->withdrawals_enabled?'checked':'' }}> Withdrawals</label><button class="button button-secondary" type="submit">Save toggles</button></form></td></tr>@empty<tr><td colspan="5" class="table-empty">No crypto networks configured.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">IDENTITY CONTROL</span><h2>KYC review queue</h2></div></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>DOCUMENT</th><th>FILES</th><th>STATUS</th><th>DECISION</th></tr></thead><tbody>
@forelse($kycSubmissions as $kyc)<tr><td><strong>{{ $kyc->client_name }}</strong><small>{{ $kyc->client_email }}</small></td><td>{{ strtoupper(str_replace('_',' ',$kyc->document_type)) }}<small>{{ $kyc->document_number }}</small></td><td><a class="text-link" href="{{ route('admin.wallet-operations.kyc.file',[$kyc->id,'document']) }}">Identity document</a>@if($kyc->selfie_path)<small><a class="text-link" href="{{ route('admin.wallet-operations.kyc.file',[$kyc->id,'selfie']) }}">Selfie</a></small>@endif</td><td><span class="table-status {{ $kyc->status==='verified'?'verified':'pending' }}">{{ strtoupper($kyc->status) }}</span></td><td>@if($kyc->status==='pending')<form method="POST" action="{{ route('admin.wallet-operations.kyc.review',$kyc->id) }}" class="form-stack">@csrf<label>Review note<input name="review_note" maxlength="2000"></label><button class="button button-primary" name="decision" value="verified">Verify KYC</button><button class="button button-secondary" name="decision" value="rejected">Reject KYC</button></form>@else<small>{{ $kyc->review_note ?: 'Reviewed' }}</small>@endif</td></tr>@empty<tr><td colspan="5" class="table-empty">No KYC submissions.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $kycSubmissions->links() }}</div></section>
@endsection
