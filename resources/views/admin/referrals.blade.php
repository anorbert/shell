@extends('layouts.app')
@section('title','Referral administration')
@section('eyebrow','ADMINISTRATION')
@section('heading','Referral program')
@section('content')
<style>
.referral-admin-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}.referral-admin-stat{background:var(--panel,#101b2b);border:1px solid var(--line,#26364a);border-radius:14px;padding:18px}.referral-admin-stat small{display:block;color:var(--muted,#91a0b5);margin-bottom:7px}.referral-admin-stat strong{font-size:24px}.referral-settings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.referral-settings-grid label{display:flex;flex-direction:column;gap:7px}.referral-settings-grid input,.referral-settings-grid select{width:100%}.referral-action-stack{display:grid;gap:8px;min-width:150px}.referral-action-stack input{width:100%}@media(max-width:700px){.referral-admin-grid,.referral-settings-grid{grid-template-columns:1fr}}
</style>
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
@if($errors->any())<div class="notice-banner"><div><strong>Action not completed</strong><p>{{ $errors->first() }}</p></div></div>@endif
<div class="referral-admin-grid">
<div class="referral-admin-stat"><small>Total commission records</small><strong>{{ number_format($stats->total ?? 0) }}</strong></div>
<div class="referral-admin-stat"><small>Awaiting review</small><strong>{{ number_format($stats->pending_count ?? 0) }}</strong></div>
<div class="referral-admin-stat"><small>Commissions credited</small><strong>&#36;{{ number_format((float) ($stats->paid_total ?? 0), 2) }}</strong></div>
</div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">PROGRAM CONTROL</span><h2>Commission configuration</h2></div><span class="count-chip">{{ $settings->enabled ? 'Enabled' : 'Disabled' }}</span></div>
<p class="muted">Rates apply to new confirmed deposits only. Existing commission records retain the percentage that was active when the deposit was confirmed.</p>
<form method="POST" action="{{ route('admin.referrals.settings') }}" class="form-stack">@csrf
<div class="referral-settings-grid">
<label>Referral program<select name="enabled" required><option value="1" @selected($settings->enabled)>Enabled</option><option value="0" @selected(!$settings->enabled)>Disabled</option></select></label>
<label>Minimum qualifying deposit (USD)<input type="number" name="minimum_deposit_usd" min="0" max="100000000" step="0.01" value="{{ $settings->minimum_deposit_usd }}" required></label>
<label>First successful deposit commission (%)<input type="number" name="first_deposit_percent" min="0" max="100" step="0.01" value="{{ $settings->first_deposit_percent }}" required></label>
<label>Subsequent deposit commission (%)<input type="number" name="recurring_deposit_percent" min="0" max="100" step="0.01" value="{{ $settings->recurring_deposit_percent }}" required></label>
</div><button class="button button-primary" type="submit">Save referral settings</button></form></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">FINANCIAL CONTROL</span><h2>Referral commission review</h2></div><span class="count-chip">{{ $commissions->total() }} records</span></div>
<p class="muted">Approve only commissions backed by confirmed real-money deposits. Approval credits the referrer's real wallet through the double-entry ledger. Rejected commissions are retained for audit.</p>
<div class="table-wrap"><table class="data-table"><thead><tr><th>REFERRER</th><th>REFERRED CLIENT</th><th>DEPOSIT / TYPE</th><th>COMMISSION</th><th>STATUS</th><th>REVIEW</th></tr></thead><tbody>
@forelse($commissions as $item)<tr>
<td><strong>{{ $item->referrer_name }}</strong><small>{{ $item->referrer_email }}</small></td>
<td><strong>{{ $item->referred_name }}</strong><small>{{ $item->referred_email }}</small></td>
<td>&#36;{{ number_format((float) $item->deposit_amount_usd, 2) }}<small>{{ ucfirst($item->commission_type) }} deposit · {{ $item->deposit_status ?: 'Unknown' }}</small></td>
<td><strong>&#36;{{ number_format((float) $item->commission_amount_usd, 2) }}</strong><small>{{ number_format((float) $item->commission_percent, 2) }}%</small></td>
<td><span class="table-status {{ $item->status === 'paid' ? 'verified' : ($item->status === 'rejected' ? 'rejected' : 'pending') }}">{{ strtoupper($item->status) }}</span></td>
<td>@if($item->status === 'pending')
<div class="referral-action-stack">
<form method="POST" action="{{ route('admin.referrals.approve', $item->id) }}">@csrf<input name="admin_note" maxlength="2000" placeholder="Optional approval note"><button class="button button-primary" type="submit">Approve & credit</button></form>
<form method="POST" action="{{ route('admin.referrals.reject', $item->id) }}">@csrf<input name="admin_note" maxlength="2000" placeholder="Reason for rejection" required><button class="button button-secondary" type="submit">Reject</button></form>
</div>
@else<small>{{ $item->reviewed_at ?: '—' }}{{ $item->admin_note ? ' · '.$item->admin_note : '' }}</small>@endif</td>
</tr>@empty<tr><td colspan="6" class="table-empty">No referral commissions have been generated yet.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $commissions->links() }}</div></section>
@endsection
