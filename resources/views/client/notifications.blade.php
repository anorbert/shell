@extends('layouts.app')

@section('title', 'Notifications')
@section('eyebrow', 'YOUR ACTIVITY')
@section('heading', 'Notifications')

@section('content')
<style>
.notification-center{--nc-blue:#69b8e6;--nc-blue-soft:rgba(83,164,215,.13);--nc-purple:#b39aff;--nc-green:#69d5a5;--nc-muted:var(--muted,#91a0b5);width:100%;min-width:0;color:var(--text,#eef3f8)}
.notification-center .nc-hero{position:relative;display:flex;align-items:center;justify-content:space-between;gap:22px;overflow:hidden;margin:0 0 20px;padding:25px 27px;border:1px solid rgba(105,184,230,.22);border-radius:18px;background:radial-gradient(ellipse at 85% 10%,rgba(89,157,211,.22),transparent 42%),linear-gradient(125deg,rgba(22,43,66,.98),rgba(13,25,41,.98));box-shadow:0 12px 32px rgba(0,0,0,.13)}
.notification-center .nc-hero-copy{position:relative;z-index:1;min-width:0}
.notification-center .nc-kicker{display:flex;align-items:center;gap:8px;margin-bottom:9px;color:#8bcaf0;font-size:10px;font-weight:900;letter-spacing:.13em}
.notification-center .nc-kicker svg{width:15px;height:15px}
.notification-center .nc-hero h2{margin:0 0 7px;color:#f3f7fb;font-size:clamp(20px,3vw,27px);font-weight:850;letter-spacing:-.035em}
.notification-center .nc-hero p{max-width:570px;margin:0;color:#aabbd0;font-size:12px;line-height:1.7}
.notification-center .nc-hero-art{display:grid;place-items:center;flex:0 0 76px;width:76px;height:76px;border:1px solid rgba(137,202,240,.25);border-radius:22px;background:rgba(105,184,230,.12);color:#91d4fa;box-shadow:inset 0 0 24px rgba(105,184,230,.08)}
.notification-center .nc-hero-art svg{width:37px;height:37px}
.notification-center .nc-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:0 0 20px}
.notification-center .nc-stat{display:flex;align-items:center;gap:13px;min-width:0;padding:16px 18px;border:1px solid var(--line,#26364a);border-radius:14px;background:var(--panel,#101b2b)}
.notification-center .nc-stat-icon{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:12px}
.notification-center .nc-stat-icon svg{width:19px;height:19px}
.notification-center .nc-stat-icon.total{background:rgba(105,184,230,.13);color:#83c9f0}
.notification-center .nc-stat-icon.unread{background:rgba(179,154,255,.14);color:#c0adff}
.notification-center .nc-stat-copy{display:grid;gap:3px;min-width:0}
.notification-center .nc-stat-copy small{color:var(--nc-muted);font-size:9px;font-weight:850;letter-spacing:.1em}
.notification-center .nc-stat-copy strong{font-size:23px;line-height:1.1;font-variant-numeric:tabular-nums}
.notification-center .nc-panel{overflow:hidden;border:1px solid var(--line,#26364a);border-radius:16px;background:var(--panel,#101b2b);box-shadow:0 10px 28px rgba(0,0,0,.09)}
.notification-center .nc-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 21px;border-bottom:1px solid var(--line,#26364a)}
.notification-center .nc-panel-title{display:flex;align-items:center;gap:11px;min-width:0}
.notification-center .nc-panel-title-icon{display:grid;place-items:center;flex:0 0 36px;width:36px;height:36px;border-radius:11px;background:var(--nc-blue-soft);color:#82c9f0}
.notification-center .nc-panel-title-icon svg{width:18px;height:18px}
.notification-center .nc-panel-title-copy{min-width:0}
.notification-center .nc-panel-title-copy small{display:block;margin-bottom:3px;color:var(--nc-muted);font-size:9px;font-weight:900;letter-spacing:.12em}
.notification-center .nc-panel-title-copy h3{margin:0;font-size:15px;font-weight:850}
.notification-center .nc-live-count{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid rgba(105,184,230,.2);border-radius:999px;background:rgba(105,184,230,.08);color:#9bd8fa;font-size:9px;font-weight:850;white-space:nowrap}
.notification-center .nc-live-count:before{content:"";width:6px;height:6px;border-radius:50%;background:#70c7f3}
.notification-center .nc-list{display:grid}
.notification-center .nc-item{position:relative;display:grid;grid-template-columns:42px minmax(0,1fr) auto;align-items:start;gap:14px;padding:18px 21px;border-bottom:1px solid rgba(125,151,179,.11);transition:background .18s}
.notification-center .nc-item:last-child{border-bottom:0}
.notification-center .nc-item:hover{background:rgba(105,184,230,.035)}
.notification-center .nc-item.is-unread{background:linear-gradient(90deg,rgba(105,184,230,.075),transparent 65%)}
.notification-center .nc-item.is-unread:before{position:absolute;top:0;bottom:0;left:0;width:3px;background:#69b8e6;content:""}
.notification-center .nc-item-icon{display:grid;place-items:center;width:42px;height:42px;border:1px solid rgba(105,184,230,.16);border-radius:13px;background:rgba(105,184,230,.1);color:#8bcdf1}
.notification-center .nc-item-icon svg{width:19px;height:19px}
.notification-center .nc-item.is-read .nc-item-icon{border-color:rgba(125,151,179,.14);background:rgba(125,151,179,.08);color:#8497ad}
.notification-center .nc-item-main{min-width:0;padding-top:1px}
.notification-center .nc-item-top{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:5px}
.notification-center .nc-item-top strong{color:#edf3f8;font-size:13px;font-weight:800;line-height:1.45;overflow-wrap:anywhere}
.notification-center .nc-unread-tag{padding:3px 7px;border-radius:999px;background:rgba(105,184,230,.13);color:#8dd1f6;font-size:8px;font-weight:900;letter-spacing:.07em}
.notification-center .nc-item-main p{margin:0;color:#a9b8c9;font-size:12px;line-height:1.7;overflow-wrap:anywhere;white-space:pre-line}
.notification-center .nc-item-time{display:flex;align-items:center;gap:6px;margin-top:9px;color:#8092a8;font-size:10px}
.notification-center .nc-item-time svg{width:12px;height:12px}
.notification-center .nc-item-action{display:flex;align-items:center;justify-content:flex-end;min-width:82px;padding-top:2px}
.notification-center .nc-read-button{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:34px;padding:0 10px;border:1px solid rgba(105,184,230,.24);border-radius:9px;background:rgba(105,184,230,.08);color:#9bd8fa;font-size:10px;font-weight:800;cursor:pointer;transition:background .18s,border-color .18s}
.notification-center .nc-read-button:hover{border-color:rgba(105,184,230,.55);background:rgba(105,184,230,.15)}
.notification-center .nc-read-button:disabled{opacity:.55;cursor:wait}
.notification-center .nc-read-button svg{width:13px;height:13px}
.notification-center .nc-read-state{display:inline-flex;align-items:center;gap:5px;color:#76d8ac;font-size:9px;font-weight:900;letter-spacing:.06em;white-space:nowrap}
.notification-center .nc-read-state svg{width:14px;height:14px}
.notification-center .nc-empty{display:grid;justify-items:center;padding:54px 20px;text-align:center}
.notification-center .nc-empty-icon{display:grid;place-items:center;width:64px;height:64px;margin-bottom:15px;border:1px solid rgba(105,184,230,.2);border-radius:20px;background:rgba(105,184,230,.09);color:#8bcdf1}
.notification-center .nc-empty-icon svg{width:29px;height:29px}
.notification-center .nc-empty h3{margin:0 0 6px;font-size:15px}
.notification-center .nc-empty p{max-width:360px;margin:0;color:var(--nc-muted);font-size:12px;line-height:1.7}
.notification-center .nc-pagination{padding:14px 18px;border-top:1px solid var(--line,#26364a)}
.notification-center .nc-item[hidden]{display:none}
@media(max-width:640px){.notification-center .nc-hero{padding:20px}.notification-center .nc-hero-art{flex-basis:58px;width:58px;height:58px;border-radius:17px}.notification-center .nc-hero-art svg{width:29px;height:29px}.notification-center .nc-stats{gap:9px}.notification-center .nc-stat{gap:9px;padding:13px}.notification-center .nc-stat-icon{flex-basis:34px;width:34px;height:34px}.notification-center .nc-stat-copy strong{font-size:20px}.notification-center .nc-panel-head{padding:15px}.notification-center .nc-item{grid-template-columns:36px minmax(0,1fr);gap:11px;padding:15px}.notification-center .nc-item-icon{width:36px;height:36px;border-radius:11px}.notification-center .nc-item-action{grid-column:2;justify-content:flex-start;min-width:0;padding:0}.notification-center .nc-read-button{min-height:32px}}
@media(max-width:390px){.notification-center .nc-hero-art{display:none}.notification-center .nc-stats{grid-template-columns:1fr}.notification-center .nc-live-count{font-size:8px}}
</style>

<div class="notification-center">
    <section class="nc-hero">
        <div class="nc-hero-copy">
            <div class="nc-kicker">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                NOTIFICATION CENTER
            </div>
            <h2>Stay on top of what matters.</h2>
            <p>Important account updates, activity alerts and platform messages—all in one place. Unread notifications are highlighted so you can spot them quickly.</p>
        </div>
        <div class="nc-hero-art" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/><path d="M19 3l1-1M5 3 4 2"/></svg>
        </div>
    </section>

    <div class="nc-stats" aria-label="Notification summary">
        <article class="nc-stat">
            <span class="nc-stat-icon total" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="M7 8h10M7 12h7M7 16h4"/></svg></span>
            <span class="nc-stat-copy"><small>TOTAL NOTIFICATIONS</small><strong id="nc-total-count">{{ number_format($totalCount) }}</strong></span>
        </article>
        <article class="nc-stat">
            <span class="nc-stat-icon unread" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg></span>
            <span class="nc-stat-copy"><small>UNREAD MESSAGES</small><strong id="nc-unread-count">{{ number_format($unreadCount) }}</strong></span>
        </article>
    </div>

    <section class="nc-panel" aria-label="Your notifications">
        <header class="nc-panel-head">
            <div class="nc-panel-title">
                <span class="nc-panel-title-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg></span>
                <span class="nc-panel-title-copy"><small>ACCOUNT ACTIVITY</small><h3>Recent notifications</h3></span>
            </div>
            <span class="nc-live-count">{{ $notifications->total() }} INBOX ITEMS</span>
        </header>

        @forelse($notifications as $n)
            <article class="nc-item {{ $n->read_at ? 'is-read' : 'is-unread' }}" data-notification="{{ $n->id }}">
                <span class="nc-item-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                </span>
                <div class="nc-item-main">
                    <div class="nc-item-top">
                        <strong>{{ $n->title }}</strong>
                        @if(!$n->read_at)<span class="nc-unread-tag">NEW</span>@endif
                    </div>
                    <p>{{ $n->body }}</p>
                    <span class="nc-item-time">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        {{ \Illuminate\Support\Carbon::parse($n->created_at)->format('M j, Y · g:i A') }}
                    </span>
                </div>
                <div class="nc-item-action">
                    @if(!$n->read_at)
                        <button class="nc-read-button" type="button" data-mark-read="{{ $n->id }}" data-read-url="{{ route('notifications.read', ['notification' => $n->id]) }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                            Mark as read
                        </button>
                    @else
                        <span class="nc-read-state"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>READ</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="nc-empty">
                <span class="nc-empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg></span>
                <h3>You're all caught up</h3>
                <p>There are no notifications to show right now. New account updates and important messages will appear here.</p>
            </div>
        @endforelse

        @if($notifications->hasPages())
            <div class="nc-pagination">{{ $notifications->links() }}</div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-mark-read]').forEach(button => {
    button.addEventListener('click', async () => {
        if (button.disabled) return;
        button.disabled = true;
        try {
            await window.shellAjax(button.dataset.readUrl, { method: 'POST' });

            const row = button.closest('[data-notification]');
            row?.classList.remove('is-unread');
            row?.classList.add('is-read');

            const tag = row?.querySelector('.nc-unread-tag');
            tag?.remove();

            const readState = document.createElement('span');
            readState.className = 'nc-read-state';
            readState.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>READ';
            button.replaceWith(readState);

            const unreadCount = document.getElementById('nc-unread-count');
            if (unreadCount) {
                const nextCount = Math.max(0, Number(unreadCount.textContent.replace(/,/g, '')) - 1);
                unreadCount.textContent = nextCount.toLocaleString();
            }
        } catch (error) {
            window.shellToast?.(error.message || 'Could not update this notification.', 'error');
            button.disabled = false;
        }
    });
});
</script>
@endpush
