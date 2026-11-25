@extends('layouts.app')
@section('title','Referral program')
@section('eyebrow','GROW YOUR NETWORK')
@section('heading','Referral program')
@section('content')
<style>
.referral-hero{background:linear-gradient(120deg,#111d33,#172b46);border:1px solid #29415f;border-radius:18px;padding:26px;margin-bottom:20px;color:#f4f7fb}.referral-hero p{color:#b9c7d8;max-width:650px}.referral-link{display:flex;gap:10px;margin-top:18px;max-width:760px}.referral-link input{flex:1;min-width:0;background:#0a1424;color:#fff;border:1px solid #405674;border-radius:9px;padding:12px}.referral-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}.referral-stat{background:var(--panel,#101b2b);border:1px solid var(--line,#26364a);border-radius:14px;padding:18px}.referral-stat small{display:block;color:var(--muted,#91a0b5);margin-bottom:7px}.referral-stat strong{font-size:24px}.referral-section{margin-bottom:20px}.referral-section .panel-head{display:flex;justify-content:space-between;align-items:center;padding:18px 20px}.referral-section .table-wrap{overflow:auto}.referral-status{display:inline-block;padding:5px 9px;border-radius:20px;font-size:11px;font-weight:800;text-transform:uppercase}.referral-status.paid{background:#123c2d;color:#6ce0a7}.referral-status.pending{background:#493817;color:#f4c76b}.referral-status.rejected{background:#4b2027;color:#ff9ca5}@media(max-width:700px){.referral-stats{grid-template-columns:1fr}.referral-link{flex-direction:column}.referral-link .button{width:100%}}
</style>
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
<section class="referral-hero">
<span class="eyebrow">INVITE · EARN · GROW</span><h2 style="margin:8px 0 4px">Invite people to SHELL</h2>
<p>Share your personal referral link. When someone registers through it and makes a qualifying real-money deposit, you earn a commission based on the current referral rates.</p>
@if($settings && $settings->enabled)
<label for="referral-url" style="display:block;color:#b9c7d8;font-size:12px;font-weight:700;margin-top:18px">YOUR PERSONAL REFERRAL LINK</label>
<div class="referral-link"><input id="referral-url" readonly value="{{ $referralUrl }}"><button type="button" class="button button-primary" id="copy-referral-link">Copy link</button></div><small id="referral-copy-feedback" style="display:block;color:#8ddab4;margin-top:8px" aria-live="polite"></small>
@else<p><strong>Referral invitations are temporarily disabled.</strong></p>@endif
</section>
<div class="referral-stats">
<div class="referral-stat"><small>People referred</small><strong>{{ number_format($referrals->total()) }}</strong></div>
<div class="referral-stat"><small>Pending commissions</small><strong>&#36;{{ number_format((float) $totals->pending_total, 2) }}</strong></div>
<div class="referral-stat"><small>Credited to real wallet</small><strong>&#36;{{ number_format((float) $totals->paid_total, 2) }}</strong></div>
</div>
<section class="panel referral-section"><div class="panel-head"><div><span class="eyebrow">YOUR NETWORK</span><h2>Referred accounts</h2></div><span class="count-chip">{{ $referrals->total() }} accounts</span></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>JOINED</th><th>CONFIRMED DEPOSITS</th><th>FIRST DEPOSIT</th></tr></thead><tbody>
@forelse($referrals as $referral)<tr><td><strong>{{ $referral->name }}</strong><small>{{ $referral->email }}</small></td><td>{{ $referral->created_at ? date('d M Y', strtotime($referral->created_at)) : '—' }}</td><td>{{ number_format($referral->successful_deposits) }}</td><td>{{ $referral->first_deposit_at ? date('d M Y', strtotime($referral->first_deposit_at)) : 'Not yet' }}</td></tr>
@empty<tr><td colspan="4" class="table-empty">No one has registered through your referral link yet. Share your link to start building your network.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $referrals->links() }}</div></section>
<section class="panel referral-section"><div class="panel-head"><div><span class="eyebrow">EARNINGS</span><h2>Commission history</h2></div><span class="count-chip">{{ $totals->commission_count }} records</span></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>REFERRED CLIENT</th><th>DEPOSIT</th><th>RATE</th><th>COMMISSION</th><th>TYPE</th><th>STATUS</th></tr></thead><tbody>
@forelse($commissions as $commission)<tr><td><strong>{{ $commission->referred_name }}</strong><small>{{ $commission->created_at ? date('d M Y H:i', strtotime($commission->created_at)) : '' }}</small></td><td>&#36;{{ number_format((float) $commission->deposit_amount_usd, 2) }}</td><td>{{ number_format((float) $commission->commission_percent, 2) }}%</td><td><strong>&#36;{{ number_format((float) $commission->commission_amount_usd, 2) }}</strong></td><td>{{ $commission->commission_type === 'first' ? 'First deposit' : 'Recurring' }}</td><td><span class="referral-status {{ $commission->status }}">{{ $commission->status }}</span></td></tr>
@empty<tr><td colspan="6" class="table-empty">Commissions will appear here after your referred clients make confirmed qualifying deposits.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $commissions->links() }}</div></section>
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>How commissions are paid</strong><p>Commissions are calculated only after a real-money deposit is confirmed. They remain pending until reviewed by the SHELL administration team. Approved commissions are credited to your real wallet. Reversed, rejected, demo, or unconfirmed deposits do not qualify.</p></div></div>
@endsection
@push('scripts')
<script>
document.getElementById('copy-referral-link')?.addEventListener('click', async function () {
const input=document.getElementById('referral-url');const feedback=document.getElementById('referral-copy-feedback');
try { await navigator.clipboard.writeText(input.value); feedback.textContent='Referral link copied. Share it with someone you want to invite.'; }
catch(error) { input.select(); const copied=document.execCommand('copy'); feedback.textContent=copied?'Referral link copied.':'Select and copy the link manually.'; }
});
</script>
@endpush
