@extends('layouts.app')
@section('title','Security') @section('eyebrow','ACCOUNT PROTECTION') @section('heading','Security')
@section('content')
<section class="panel">
    <div class="panel-head">
        <div><span class="eyebrow">ACTIVE SESSIONS</span><h2>Signed-in devices</h2></div>
    </div>
    @if(!$databaseSessions)
        <div class="empty-state"><strong>Session management unavailable</strong><p>The configured session driver is not database-backed, so Shell cannot reliably list or revoke individual sessions.</p></div>
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>DEVICE &amp; BROWSER</th><th>IP ADDRESS</th><th>LAST ACTIVITY</th><th>SESSION</th><th>ACTION</th></tr></thead>
                <tbody>
                @forelse($sessions as $session)
                    @php
                        $ua = strtolower($session->user_agent ?? '');
                        $isMobile = str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone') || str_contains($ua, 'ipad');
                        if (str_contains($ua, 'edg/')) { $browser = 'Microsoft Edge'; $browserKey = 'edge'; $browserMark = 'e'; }
                        elseif (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) { $browser = 'Opera'; $browserKey = 'opera'; $browserMark = 'o'; }
                        elseif (str_contains($ua, 'firefox/')) { $browser = 'Firefox'; $browserKey = 'firefox'; $browserMark = 'f'; }
                        elseif (str_contains($ua, 'chrome/') || str_contains($ua, 'crios/')) { $browser = 'Google Chrome'; $browserKey = 'chrome'; $browserMark = 'c'; }
                        elseif (str_contains($ua, 'safari/')) { $browser = 'Safari'; $browserKey = 'safari'; $browserMark = 's'; }
                        else { $browser = 'Unknown browser'; $browserKey = 'unknown'; $browserMark = '?'; }
                        if (str_contains($ua, 'android')) { $device = 'Android'; }
                        elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod')) { $device = 'Apple device'; }
                        elseif (str_contains($ua, 'windows')) { $device = 'Windows PC'; }
                        elseif (str_contains($ua, 'macintosh') || str_contains($ua, 'mac os')) { $device = 'Mac'; }
                        elseif (str_contains($ua, 'linux')) { $device = 'Linux device'; }
                        else { $device = 'Unknown device'; }
                    @endphp
                    <tr>
                        <td>
                            <div class="session-device">
                                <span class="session-device-icon" aria-hidden="true">
                                    @if($isMobile)
                                        <svg viewBox="0 0 24 24" fill="none"><rect x="6.5" y="2.5" width="11" height="19" rx="2.2" stroke="currentColor" stroke-width="1.8"/><path d="M10 18.5h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3.5" width="18" height="13" rx="1.8" stroke="currentColor" stroke-width="1.8"/><path d="M8 20.5h8M12 16.5v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                    @endif
                                </span>
                                <span class="session-device-copy">
                                    <strong>{{ $device }}</strong>
                                    <span class="session-browser"><span class="browser-logo browser-{{ $browserKey }}" aria-hidden="true">{{ $browserMark }}</span>{{ $browser }}</span>
                                </span>
                            </div>
                        </td>
                        <td>{{ $session->ip_address }}</td>
                        <td>{{ \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity)->toDateTimeString() }}</td>
                        <td>@if($session->id===$currentSessionId)<span class="table-status verified">CURRENT</span>@else<span class="table-status pending">OTHER</span>@endif</td>
                        <td>@if($session->id!==$currentSessionId)<button class="button button-quiet" type="button" data-revoke-session="{{ $session->id }}">Revoke</button>@else — @endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="table-empty">No database sessions found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <form id="revoke-other-sessions" class="filter-row">
            <label>Confirm password to sign out other devices<input type="password" name="password" required autocomplete="current-password"></label>
            <button class="button button-primary" type="submit">Revoke other sessions</button>
        </form>
    @endif
</section>
@endsection
@push('styles')
<style>
.session-device{display:flex;align-items:center;gap:11px;min-width:170px}
.session-device-icon{width:36px;height:36px;display:grid;place-items:center;flex:0 0 36px;border:1px solid var(--border-color,rgba(148,163,184,.2));border-radius:10px;background:rgba(148,163,184,.07);color:var(--text-secondary,#94a3b8)}
.session-device-icon svg{width:20px;height:20px}
.session-device-copy{display:flex;flex-direction:column;gap:5px}
.session-device-copy>strong{font-size:13px;font-weight:650}
.session-browser{display:flex;align-items:center;gap:6px;color:var(--text-secondary,#94a3b8);font-size:11px}
.browser-logo{width:17px;height:17px;display:inline-grid;place-items:center;border-radius:50%;font-size:11px;font-weight:800;line-height:1;text-transform:uppercase}
.browser-chrome{color:#fff;background:conic-gradient(#e94335 0 120deg,#fbbc05 120deg 225deg,#34a853 225deg 300deg,#e94335 300deg)}
.browser-firefox{color:#fff;background:linear-gradient(135deg,#ffca45,#f05a28 55%,#8b35c9)}
.browser-edge{color:#fff;background:linear-gradient(135deg,#16c7b5,#1674d1)}
.browser-opera{color:#fff;background:#e23b4b}
.browser-safari{color:#fff;background:linear-gradient(135deg,#55c8f5,#1765c4)}
.browser-unknown{color:#fff;background:#64748b}
@media(max-width:700px){.session-device{min-width:145px}.session-device-icon{width:32px;height:32px;flex-basis:32px}.session-device-copy>strong{font-size:12px}}
</style>
@endpush
@push('scripts')
<script>
const revokeForm=document.getElementById('revoke-other-sessions');
revokeForm?.addEventListener('submit',async e=>{e.preventDefault();const button=revokeForm.querySelector('button');button.disabled=true;try{const data=await window.shellAjax(@json(route('security.sessions.revoke-others')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({password:revokeForm.elements.password.value})});window.shellToast(data.message);window.location.reload();}catch(error){window.shellToast(error.message,'error');button.disabled=false;}});
document.querySelectorAll('[data-revoke-session]').forEach(button=>button.addEventListener('click',async()=>{if(!confirm('Revoke this session? This also invalidates remembered-device tokens.'))return;button.disabled=true;try{const id=encodeURIComponent(button.dataset.revokeSession);await window.shellAjax(@json(url('/security/sessions'))+'/'+id,{method:'DELETE'});window.shellToast('Session revoked.');window.location.reload();}catch(error){window.shellToast(error.message,'error');button.disabled=false;}}));
</script>
@endpush