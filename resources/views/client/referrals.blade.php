@extends('layouts.app')
@section('title','Referral program')
@section('eyebrow','GROW YOUR NETWORK')
@section('heading','Referral program')
@section('content')
<style>
.ref-page{--ref-line:var(--line,#26364a);--ref-muted:var(--muted,#91a0b5);color:var(--text,#e8eef7)}
.ref-page *{box-sizing:border-box}
.ref-hero{position:relative;overflow:hidden;isolation:isolate;padding:clamp(22px,4vw,34px);border:1px solid #294665;border-radius:20px;background:radial-gradient(circle at 90% 12%,rgba(42,125,181,.28),transparent 34%),linear-gradient(125deg,#101d32 0%,#172d49 58%,#123b50 100%);color:#f7fbff;margin-bottom:20px}
.ref-hero:after{content:"";position:absolute;z-index:-1;width:220px;height:220px;border:1px solid rgba(255,255,255,.08);border-radius:50%;right:-70px;bottom:-135px;box-shadow:0 0 0 24px rgba(255,255,255,.025),0 0 0 50px rgba(255,255,255,.02)}
.ref-hero-top{display:flex;align-items:flex-start;justify-content:space-between;gap:20px}
.ref-kicker{display:inline-flex;align-items:center;gap:8px;color:#9fd8f5;font-size:11px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}
.ref-hero h2{font-size:clamp(22px,3vw,30px);line-height:1.2;margin:12px 0 8px;color:#fff}
.ref-hero p{color:#c0d0e1;max-width:680px;line-height:1.65;margin:0}
.ref-hero-mark{width:54px;height:54px;flex:0 0 54px;display:grid;place-items:center;border-radius:16px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.14);color:#f6d88e}
.ref-hero-mark svg{width:27px;height:27px}
.ref-rate-row{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}
.ref-rate{display:flex;align-items:center;gap:9px;padding:10px 13px;border:1px solid rgba(255,255,255,.13);background:rgba(5,15,29,.24);border-radius:12px;color:#dbe8f5;font-size:12px}
.ref-rate svg{width:17px;height:17px;color:#9fd8f5;flex:none}
.ref-rate strong{color:#fff;font-size:14px}
.ref-link-label{display:block;color:#b9cbdc;font-size:11px;font-weight:800;letter-spacing:.1em;margin:22px 0 8px}
.ref-link-box{display:flex;gap:10px;max-width:760px}
.ref-link-box input{flex:1;min-width:0;background:rgba(5,14,27,.72);color:#fff;border:1px solid #49617d;border-radius:11px;padding:13px 14px;font-size:13px;outline:none}
.ref-link-box input:focus{border-color:#80c9ef;box-shadow:0 0 0 3px rgba(128,201,239,.12)}
.ref-copy{display:inline-flex;align-items:center;justify-content:center;gap:8px;white-space:nowrap;border:0;border-radius:11px;padding:0 18px;background:#d5a947;color:#172235;font-weight:800;cursor:pointer;transition:filter .2s,transform .2s}
.ref-copy:hover{filter:brightness(1.08);transform:translateY(-1px)}
.ref-copy svg{width:17px;height:17px}
.ref-copy-feedback{display:block;min-height:18px;color:#9de2bb;font-size:12px;margin-top:7px}
.ref-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}
.ref-stat{display:flex;align-items:center;gap:14px;min-width:0;padding:18px;background:var(--panel,#101b2b);border:1px solid var(--ref-line);border-radius:15px}
.ref-stat-icon{width:43px;height:43px;flex:0 0 43px;border-radius:13px;display:grid;place-items:center;background:rgba(72,145,195,.13);color:#73bce8}
.ref-stat-icon.green{background:rgba(47,174,119,.13);color:#61d7a2}
.ref-stat-icon.gold{background:rgba(210,166,67,.13);color:#e7bf68}
.ref-stat-icon svg{width:21px;height:21px}
.ref-stat small{display:block;color:var(--ref-muted);font-size:11px;font-weight:700;letter-spacing:.04em;margin-bottom:5px}
.ref-stat strong{display:block;font-size:clamp(18px,2.3vw,24px);line-height:1.2;overflow-wrap:anywhere;font-variant-numeric:tabular-nums}
.ref-panel{margin-bottom:20px;overflow:hidden}
.ref-panel-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:19px 21px;border-bottom:1px solid var(--ref-line)}
.ref-panel-title{display:flex;align-items:center;gap:11px;min-width:0}
.ref-panel-icon{width:37px;height:37px;flex:none;display:grid;place-items:center;border-radius:11px;background:rgba(72,145,195,.12);color:#75bde8}
.ref-panel-icon svg{width:19px;height:19px}
.ref-panel-head .eyebrow{display:block;margin-bottom:3px}
.ref-panel-head h2{font-size:17px;margin:0}
.ref-count{white-space:nowrap;padding:6px 10px;border:1px solid var(--ref-line);border-radius:20px;color:var(--ref-muted);font-size:11px;font-weight:700}
.ref-table-wrap{overflow-x:auto}
.ref-table{width:100%;min-width:620px}
.ref-table td,.ref-table th{padding:13px 17px}
.ref-table td strong{display:block}
.ref-table td small{display:block;color:var(--ref-muted);font-size:11px;margin-top:3px}
.ref-table .money{font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
.ref-type{display:inline-flex;align-items:center;gap:6px;color:var(--ref-muted);font-size:12px}
.ref-type svg{width:15px;height:15px}
.ref-status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.ref-status:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
.ref-status.paid{background:rgba(42,165,111,.14);color:#64d6a1}
.ref-status.pending{background:rgba(210,160,53,.14);color:#e8c16b}
.ref-status.rejected{background:rgba(220,75,90,.14);color:#ff9ca5}
.ref-empty{padding:30px 18px!important;text-align:center;color:var(--ref-muted);line-height:1.6}
.ref-empty svg{display:block;width:29px;height:29px;margin:0 auto 9px;color:#7292b2}
.ref-note{display:flex;align-items:flex-start;gap:12px;padding:17px 19px;border:1px solid rgba(85,151,195,.25);border-radius:14px;background:rgba(47,112,155,.08);margin-bottom:18px}
.ref-note-icon{width:34px;height:34px;flex:none;display:grid;place-items:center;border-radius:10px;background:rgba(80,157,207,.14);color:#83c9ef}
.ref-note-icon svg{width:18px;height:18px}
.ref-note strong{display:block;margin:1px 0 4px;font-size:13px}
.ref-note p{margin:0;color:var(--ref-muted);font-size:12px;line-height:1.65}
.ref-disabled{margin-top:18px;padding:12px 14px;border-radius:11px;background:rgba(210,160,53,.12);color:#f0ce82;font-size:13px}
@media(max-width:760px){.ref-stats{grid-template-columns:1fr}.ref-stat{padding:15px}.ref-hero-top{gap:12px}.ref-hero-mark{width:45px;height:45px;flex-basis:45px}.ref-panel-head{padding:16px}.ref-table td,.ref-table th{padding:12px}}
@media(max-width:520px){.ref-link-box{flex-direction:column}.ref-link-box .ref-copy{min-height:45px;width:100%}.ref-hero{padding:21px}.ref-rate-row{display:grid;grid-template-columns:1fr}.ref-rate{width:100%}.ref-panel-head h2{font-size:15px}.ref-count{font-size:10px}}
</style>
<div class="ref-page">
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif

<section class="ref-hero">
    <div class="ref-hero-top">
        <div>
            <span class="ref-kicker"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m19 8 2 2-2 2m-4-2h6"/></svg> Invite · Earn · Grow</span>
            <h2>Build your network. Earn rewards.</h2>
            <p>Invite people to SHELL using your personal link. When someone joins through your invitation and makes a qualifying real-money deposit, you can earn a commission at the rates shown below.</p>
        </div>
        <div class="ref-hero-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="9" width="18" height="12" rx="2"/><path d="M12 9v12M3 13h18M12 9H7.5a2.5 2.5 0 1 1 2.5-2.5V9Zm0 0h4.5A2.5 2.5 0 1 0 14 6.5V9Z"/></svg></div>
    </div>
    @if($settings && $settings->enabled)
        <div class="ref-rate-row">
            <div class="ref-rate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20m5-15H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span>First deposit <strong>{{ number_format((float) $settings->first_deposit_percent, 2) }}%</strong></span></div>
            <div class="ref-rate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9A7 7 0 0 1 18 6l2 6M4 12l2 6a7 7 0 0 0 12.4-3"/></svg><span>Recurring deposits <strong>{{ number_format((float) $settings->recurring_deposit_percent, 2) }}%</strong></span></div>
            <div class="ref-rate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><span>Minimum deposit <strong>${{ number_format((float) $settings->minimum_deposit_usd, 2) }}</strong></span></div>
        </div>
        <label for="referral-url" class="ref-link-label">YOUR PERSONAL REFERRAL LINK</label>
        <div class="ref-link-box"><input id="referral-url" readonly value="{{ $referralUrl }}" aria-label="Your personal referral link"><button type="button" class="ref-copy" id="copy-referral-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg>Copy referral link</button></div>
        <small id="referral-copy-feedback" class="ref-copy-feedback" aria-live="polite"></small>
    @else
        <div class="ref-disabled">Referral invitations are temporarily disabled. Your existing referral records remain available below.</div>
    @endif
</section>

<div class="ref-stats">
    <article class="ref-stat"><span class="ref-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6m3-3h-6"/></svg></span><div><small>PEOPLE YOU REFERRED</small><strong>{{ number_format($referrals->total()) }}</strong></div></article>
    <article class="ref-stat"><span class="ref-stat-icon gold"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><small>PENDING COMMISSIONS</small><strong>${{ number_format((float) $totals->pending_total, 2) }}</strong></div></article>
    <article class="ref-stat"><span class="ref-stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18v13H3zM3 7l2-4h14l2 4"/><path d="m8 14 2.5 2.5L16 11"/></svg></span><div><small>PAID TO YOUR REAL WALLET</small><strong>${{ number_format((float) $totals->paid_total, 2) }}</strong></div></article>
</div>

<section class="panel ref-panel">
    <div class="ref-panel-head">
        <div class="ref-panel-title"><span class="ref-panel-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6m3-3h-6"/></svg></span><div><span class="eyebrow">YOUR NETWORK</span><h2>Referred accounts</h2></div></div>
        <span class="ref-count">{{ $referrals->total() }} accounts</span>
    </div>
    <div class="ref-table-wrap"><table class="data-table ref-table"><thead><tr><th>CLIENT</th><th>JOINED</th><th>CONFIRMED DEPOSITS</th><th>FIRST DEPOSIT</th></tr></thead><tbody>
    @forelse($referrals as $referral)
        <tr><td><strong>{{ $referral->name }}</strong><small>{{ $referral->email }}</small></td><td>{{ $referral->created_at ? date('d M Y', strtotime($referral->created_at)) : '—' }}</td><td>{{ number_format($referral->successful_deposits) }}</td><td>{{ $referral->first_deposit_at ? date('d M Y', strtotime($referral->first_deposit_at)) : 'Not yet' }}</td></tr>
    @empty
        <tr><td colspan="4" class="ref-empty"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="8" r="4"/><path d="M2 21v-2a7 7 0 0 1 14 0v2m3-13v6m3-3h-6"/></svg><strong>No referrals yet</strong><br>Share your personal link to invite your first member to SHELL.</td></tr>
    @endforelse
    </tbody></table></div>
    @if($referrals->hasPages())<div class="pagination-wrap">{{ $referrals->links() }}</div>@endif
</section>

<section class="panel ref-panel">
    <div class="ref-panel-head">
        <div class="ref-panel-title"><span class="ref-panel-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 3h16v18l-4-2-4 2-4-2-4 2V3Z"/><path d="M8 8h8M8 12h8M8 16h4"/></svg></span><div><span class="eyebrow">YOUR EARNINGS</span><h2>Commission history</h2></div></div>
        <span class="ref-count">{{ $totals->commission_count }} records</span>
    </div>
    <div class="ref-table-wrap"><table class="data-table ref-table"><thead><tr><th>REFERRED CLIENT</th><th>DEPOSIT</th><th>RATE</th><th>COMMISSION</th><th>TYPE</th><th>STATUS</th></tr></thead><tbody>
    @forelse($commissions as $commission)
        <tr><td><strong>{{ $commission->referred_name }}</strong><small>{{ $commission->created_at ? date('d M Y H:i', strtotime($commission->created_at)) : '' }}</small></td><td class="money">${{ number_format((float) $commission->deposit_amount_usd, 2) }}</td><td>{{ number_format((float) $commission->commission_percent, 2) }}%</td><td class="money">${{ number_format((float) $commission->commission_amount_usd, 2) }}</td><td><span class="ref-type">@if($commission->commission_type === 'first')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 2 2.5 6.5L21 11l-6.5 2.5L12 20l-2.5-6.5L3 11l6.5-2.5L12 2Z"/></svg>First deposit @else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9A7 7 0 0 1 18 6l2 6M4 12l2 6a7 7 0 0 0 12.4-3"/></svg>Recurring @endif</span></td><td><span class="ref-status {{ $commission->status }}">{{ $commission->status }}</span></td></tr>
    @empty
        <tr><td colspan="6" class="ref-empty"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 3h16v18l-4-2-4 2-4-2-4 2V3Z"/><path d="M8 8h8M8 12h8"/></svg><strong>No commissions recorded</strong><br>Qualifying commissions will appear here after referred members make confirmed real-money deposits.</td></tr>
    @endforelse
    </tbody></table></div>
    @if($commissions->hasPages())<div class="pagination-wrap">{{ $commissions->links() }}</div>@endif
</section>

<div class="ref-note"><span class="ref-note-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg></span><div><strong>How referral commissions work</strong><p>Commissions are calculated only after a referred member's real-money deposit is confirmed. They remain pending until reviewed by the SHELL administration team. Approved commissions are credited to your real wallet. Demo, reversed, rejected, or unconfirmed deposits do not qualify.</p></div></div>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('copy-referral-link')?.addEventListener('click', async function () {
    const input = document.getElementById('referral-url');
    const feedback = document.getElementById('referral-copy-feedback');
    if (!input || !feedback) return;
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(input.value);
        } else {
            input.focus();
            input.select();
            input.setSelectionRange(0, input.value.length);
            if (!document.execCommand('copy')) throw new Error('Copy unavailable');
        }
        feedback.textContent = 'Referral link copied. Share it with someone you want to invite.';
    } catch (error) {
        input.focus();
        input.select();
        feedback.textContent = 'Your link is selected. Copy it using Ctrl+C.';
    }
});
</script>
@endpush
