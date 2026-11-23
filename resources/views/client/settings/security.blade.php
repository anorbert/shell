@extends('layouts.app')
@section('title','Security') @section('eyebrow','ACCOUNT PROTECTION') @section('heading','Security')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Protect your account</strong><p>Password changes and session controls are available here. Two-factor authentication is not enabled in this release; do not assume your account has 2FA protection.</p></div><span class="mode-tag">SECURITY</span></div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">ACTIVE SESSIONS</span><h2>Signed-in devices</h2></div></div>
@if(!$databaseSessions)<div class="empty-state"><strong>Session management unavailable</strong><p>The configured session driver is not database-backed, so Shell cannot reliably list or revoke individual sessions.</p></div>
@else
<div class="table-wrap"><table class="data-table"><thead><tr><th>DEVICE / BROWSER</th><th>IP ADDRESS</th><th>LAST ACTIVITY</th><th>SESSION</th><th>ACTION</th></tr></thead><tbody>
@forelse($sessions as $session)<tr><td>{{ $session->user_agent }}</td><td>{{ $session->ip_address }}</td><td>{{ \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity)->toDateTimeString() }}</td><td>@if($session->id===$currentSessionId)<span class="table-status verified">CURRENT</span>@else<span class="table-status pending">OTHER</span>@endif</td><td>@if($session->id!==$currentSessionId)<button class="button button-quiet" type="button" data-revoke-session="{{ $session->id }}">Revoke</button>@else — @endif</td></tr>
@empty<tr><td colspan="5" class="table-empty">No database sessions found.</td></tr>@endforelse
</tbody></table></div>
<form id="revoke-other-sessions" class="filter-row"><label>Confirm password to sign out other devices<input type="password" name="password" required autocomplete="current-password"></label><button class="button button-primary" type="submit">Revoke other sessions</button></form>
@endif
</section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">LOGIN HISTORY</span><h2>Recent account activity</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>EVENT</th><th>IP ADDRESS</th><th>DEVICE / BROWSER</th><th>TIME</th></tr></thead><tbody>
@forelse($loginEvents as $event)<tr><td><span class="table-status {{ $event->event_type==='login'?'verified':'pending' }}">{{ strtoupper($event->event_type) }}</span></td><td>{{ $event->ip_address }}</td><td>{{ $event->user_agent }}</td><td>{{ $event->created_at }}</td></tr>@empty<tr><td colspan="4" class="table-empty">No login history recorded yet.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
@push('scripts')
<script>
const revokeForm=document.getElementById('revoke-other-sessions');
revokeForm?.addEventListener('submit',async e=>{e.preventDefault();const button=revokeForm.querySelector('button');button.disabled=true;try{const data=await window.shellAjax(@json(route('security.sessions.revoke-others')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({password:revokeForm.elements.password.value})});window.shellToast(data.message);window.location.reload();}catch(error){window.shellToast(error.message,'error');button.disabled=false;}});
document.querySelectorAll('[data-revoke-session]').forEach(button=>button.addEventListener('click',async()=>{if(!confirm('Revoke this session? This also invalidates remembered-device tokens.'))return;button.disabled=true;try{const id=encodeURIComponent(button.dataset.revokeSession);await window.shellAjax(@json(url('/security/sessions'))+'/'+id,{method:'DELETE'});window.shellToast('Session revoked.');window.location.reload();}catch(error){window.shellToast(error.message,'error');button.disabled=false;}}));
</script>
@endpush