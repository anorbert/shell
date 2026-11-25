@extends('layouts.app')
@section('title','Wallet operations')
@section('eyebrow','ADMINISTRATION')
@section('heading','Wallet operations')
@section('content')
<div class="notice-banner"><span class="notice-icon">!</span><div><strong>Real-wallet operations</strong><p>Review incoming deposits and outgoing withdrawal requests. Client-submitted deposits remain pending until an administrator independently verifies the transaction on the correct blockchain and network. Only then can the real wallet be credited.</p></div><span class="mode-tag">ADMIN REVIEW</span></div>
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
@if($errors->any())<div class="notice-banner"><div><strong>Action not completed</strong><p>{{ $errors->first() }}</p></div></div>@endif
<section class="panel"><div class="panel-head"><div><span class="eyebrow">INBOUND AND OUTBOUND</span><h2>Deposit and withdrawal queue</h2></div><span class="count-chip">{{ $requests->total() }} requests</span></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>TYPE / AMOUNT</th><th>DESTINATION / TXID</th><th>STATUS</th><th>REVIEW ACTION</th></tr></thead><tbody>
@forelse($requests as $item)<tr><td><strong>{{ $item->client_name }}</strong><small>{{ $item->client_email }} · #{{ $item->id }}</small></td><td>{{ strtoupper($item->type) }}<small>@money($item->amount_usd) USD · {{ $item->asset }}/{{ $item->network }}<small>Client crypto quantity: {{ $item->asset_amount ? \App\Support\NumberFormatter::decimal($item->asset_amount) : '—' }} {{ $item->asset }}</small></small></td><td><small>Address: {{ $item->wallet_address }}</small><small>TxID: {{ $item->transaction_hash ?: 'Not recorded' }}</small><small>Memo / tag: {{ $item->wallet_memo ?: '—' }}</small></td><td><span class="table-status {{ in_array($item->status,['confirmed','paid']) ? 'verified' : 'pending' }}">{{ strtoupper($item->status) }}</span></td><td>
@if($item->status==='pending')
@if($item->type==='deposit')
<form method="POST" action="{{ route('admin.wallet-operations.deposit.confirm',$item->id) }}" class="form-stack">@csrf
<label>Verified on-chain USD amount<input name="amount_usd" type="number" min="0.00000001" step="0.00000001" value="{{ $item->amount_usd }}" required></label>
<label>Verification record / admin note<textarea name="admin_note" minlength="10" maxlength="2000" placeholder="Record the explorer result, token contract, received amount and confirmation count." required></textarea></label>
<label class="check-row"><input type="checkbox" name="chain_verified" value="1" required> I independently verified this transaction on the correct blockchain, including destination, token contract, amount and confirmations.</label>
<button class="button button-primary" type="submit">Approve and credit real wallet</button></form>
@else
<form method="POST" action="{{ route('admin.wallet-operations.withdrawal.paid',$item->id) }}" class="form-stack">@csrf<label>On-chain payout TxID<input name="transaction_hash" maxlength="255" required></label><label>Actual crypto amount sent<input name="settlement_amount" type="number" min="0.000000000001" step="0.000000000001" required></label><label>Fee (USD, optional)<input name="fee_usd" type="number" min="0" step="0.00000001"></label><label>Admin note<input name="admin_note" maxlength="2000" required></label><button class="button button-primary" type="submit">Mark paid</button></form>
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
@forelse($methods as $method)<tr><td><strong>{{ $method->asset }}</strong><small>{{ $method->network }}</small></td><td style="overflow-wrap:anywhere">{{ $method->wallet_address }}</td><td>{{ $method->deposits_enabled?'Enabled':'Disabled' }}</td><td>{{ $method->withdrawals_enabled?'Enabled':'Disabled' }}</td><td><form method="POST" action="{{ route('admin.wallet-operations.methods.update',$method->id) }}">@csrf<label>Deposits<select name="deposits_enabled"><option value="1" {{ $method->deposits_enabled?'selected':'' }}>Enabled</option><option value="0" {{ !$method->deposits_enabled?'selected':'' }}>Disabled</option></select></label><label>Withdrawals<select name="withdrawals_enabled"><option value="1" {{ $method->withdrawals_enabled?'selected':'' }}>Enabled</option><option value="0" {{ !$method->withdrawals_enabled?'selected':'' }}>Disabled</option></select></label><button class="button button-secondary" type="submit">Save toggles</button></form><form method="POST" action="{{ route('admin.wallet-operations.methods.store') }}" class="form-stack">@csrf<input type="hidden" name="id" value="{{ $method->id }}"><label>Asset<input name="asset" value="{{ $method->asset }}" required></label><label>Network<input name="network" value="{{ $method->network }}" required></label><label>Wallet address<input name="wallet_address" value="{{ $method->wallet_address }}" required></label><label>Memo label<input name="memo_label" value="{{ $method->memo_label }}"></label><label>Memo value<input name="memo_value" value="{{ $method->memo_value }}"></label><label>Instructions<textarea name="instructions">{{ $method->instructions }}</textarea></label><button class="button button-secondary" type="submit">Update address details</button></form></td></tr>@empty<tr><td colspan="5" class="table-empty">No crypto networks configured.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">IDENTITY CONTROL</span><h2>KYC case management</h2></div><a class="button button-primary" href="{{ route('admin.kyc.index') }}">Open dedicated KYC review →</a></div>
<p class="muted">Identity documents are reviewed in a separate case workspace with search, status filters, secure document previews, client submission history and a mandatory approval checklist.</p>
</section>

<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">REAL-MONEY RISK CONTROL</span><h2>Funded payout reserve</h2></div>
        <span class="table-status {{ (float) $realPayoutReserve > 0 ? 'verified' : 'pending' }}">{{ (float) $realPayoutReserve > 0 ? 'FUNDED' : 'NOT FUNDED' }}</span>
    </div>
    <p class="muted">Available payout reserve: <strong>@money($realPayoutReserve) USD</strong>. Real trades reserve their maximum potential profit before opening. This prevents the platform from accepting more winning exposure than its recorded reserve.</p>
    <div class="notice-banner"><div><strong>Important treasury control</strong><p>Only record funds that have already been independently verified and segregated in the external treasury. This form records an accounting balance only; it does not transfer funds, verify blockchain custody, or prove that the external balance exists. Reconcile every entry against actual custody records before submitting.</p></div></div>
    <form method="POST" action="{{ route('admin.wallet-operations.reserve.fund') }}" class="form-grid">
        @csrf
        <label>Verified amount (USD)<input name="amount_usd" type="number" min="0.00000001" max="99999999" step="0.00000001" required></label>
        <label>External custody / bank reference<input name="external_reference" minlength="12" maxlength="255" required></label>
        <label class="full-width">Verification and reconciliation note<textarea name="admin_note" minlength="10" maxlength="2000" required></textarea></label>
        <button class="button button-primary" type="submit">Record verified reserve funding</button>
    </form>
</section>
@endsection
