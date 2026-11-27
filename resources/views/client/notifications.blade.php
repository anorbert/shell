@extends('layouts.app')

@section('title', 'Notifications')
@section('eyebrow', 'ACCOUNT')
@section('heading', 'Notifications')

@section('content')
<style>
.notification-page{width:100%;max-width:1040px;min-width:0;color:var(--text,#eef3f8)}
.notification-page .np-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:0 0 18px}
.notification-page .np-heading p{margin:4px 0 0;color:var(--muted,#91a0b5);font-size:13px;line-height:1.5}
.notification-page .np-count{flex:0 0 auto;color:var(--muted,#91a0b5);font-size:12px}
.notification-page .np-count strong{color:var(--text,#eef3f8);font-weight:750}
.notification-page .np-heading-actions{display:flex;align-items:center;gap:14px}
.notification-page .np-mark-all{padding:7px 0;border:0;background:none;color:#8bcdf1;font-size:11px;font-weight:750;white-space:nowrap;cursor:pointer}
.notification-page .np-mark-all:hover{color:#c0e7ff;text-decoration:underline}
.notification-page .np-mark-all:disabled{opacity:.55;cursor:wait}
.notification-page .np-mark-all[hidden]{display:none}
.notification-page .np-list{border-top:1px solid var(--line,#26364a)}
.notification-page .np-item{display:grid;grid-template-columns:10px minmax(0,1fr) auto;align-items:start;gap:14px;padding:11px 8px;border-bottom:1px solid var(--line,#26364a)}
.notification-page .np-item.is-unread{background:linear-gradient(90deg,rgba(105,184,230,.055),transparent 70%)}
.notification-page .np-dot{width:7px;height:7px;margin:6px 0 0 1px;border-radius:50%;background:transparent}
.notification-page .np-item.is-unread .np-dot{background:#69b8e6;box-shadow:0 0 0 3px rgba(105,184,230,.12)}
.notification-page .np-main{min-width:0}
.notification-page .np-title{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 3px;color:var(--text,#eef3f8);font-size:13px;font-weight:750;line-height:1.45}
.notification-page .np-item.is-read .np-title{font-weight:600}
.notification-page .np-new{padding:2px 6px;border-radius:4px;background:rgba(105,184,230,.12);color:#8bcdf1;font-size:9px;font-weight:800;letter-spacing:.04em}
.notification-page .np-body{margin:0;color:var(--muted,#91a0b5);font-size:12px;line-height:1.5;overflow-wrap:anywhere;white-space:pre-line}
.notification-page .np-time{display:block;margin-top:4px;color:var(--muted,#8494a8);font-size:10px}
.notification-page .np-action{display:flex;flex-direction:column;align-items:flex-end;justify-content:center;gap:8px;align-self:center}
.notification-page .np-mark{padding:6px 0;border:0;background:none;color:#8bcdf1;font-size:11px;font-weight:700;cursor:pointer}
.notification-page .np-mark:hover{color:#c0e7ff;text-decoration:underline}
.notification-page .np-mark:disabled{opacity:.55;cursor:wait}
.notification-page .np-read{color:var(--muted,#8494a8);font-size:10px}
.notification-page .np-empty{padding:54px 16px;text-align:center;border-bottom:1px solid var(--line,#26364a)}
.notification-page .np-empty strong{display:block;margin-bottom:6px;font-size:14px}
.notification-page .np-empty span{color:var(--muted,#91a0b5);font-size:12px}
.notification-page .np-pagination{padding-top:16px}
@media(max-width:600px){.notification-page .np-heading{align-items:flex-start}.notification-page .np-heading-actions{gap:10px;flex-wrap:wrap;justify-content:flex-end}.notification-page .np-item{grid-template-columns:9px minmax(0,1fr);gap:9px;padding:10px 3px}.notification-page .np-action{grid-column:2;flex-direction:row;align-items:center;justify-content:space-between;justify-self:stretch;width:100%;margin-top:2px}.notification-page .np-mark{padding:3px 0}}
</style>

<div class="notification-page">
    <div class="np-heading">
        <p>Your latest account updates and activity.</p>
        <div class="np-heading-actions">
            <span class="np-count"><strong id="np-unread-count">{{ number_format($unreadCount) }}</strong> unread</span>
            @if($unreadCount > 0)
                <button class="np-mark-all" id="np-mark-all" type="button" data-read-all-url="{{ route('notifications.read-all') }}">Mark all as read</button>
            @endif
        </div>
    </div>

    <div class="np-list" aria-label="Notifications">
        @forelse($notifications as $n)
            <article class="np-item {{ $n->read_at ? 'is-read' : 'is-unread' }}" data-notification="{{ $n->id }}">
                <span class="np-dot" aria-hidden="true"></span>
                <div class="np-main">
                    <h3 class="np-title">
                        {{ $n->title }}
                        @if(!$n->read_at)<span class="np-new">NEW</span>@endif
                    </h3>
                    <p class="np-body">{{ $n->body }}</p>
                </div>
                <div class="np-action">
                    <time class="np-time" datetime="{{ \Illuminate\Support\Carbon::parse($n->created_at)->toIso8601String() }}">{{ \Illuminate\Support\Carbon::parse($n->created_at)->format('M j, Y · g:i A') }}</time>
                    @if(!$n->read_at)
                        <button class="np-mark" type="button" data-mark-read="{{ $n->id }}" data-read-url="{{ route('notifications.read', ['notification' => $n->id]) }}">Mark as read</button>
                    @else
                        <span class="np-read">Read</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="np-empty">
                <strong>You're all caught up</strong>
                <span>New account updates will appear here.</span>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="np-pagination">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const markAllButton = document.getElementById('np-mark-all');
markAllButton?.addEventListener('click', async () => {
    if (markAllButton.disabled) return;
    markAllButton.disabled = true;
    try {
        await window.shellAjax(markAllButton.dataset.readAllUrl, { method: 'POST' });

        document.querySelectorAll('[data-notification].is-unread').forEach(row => {
            row.classList.remove('is-unread');
            row.classList.add('is-read');
            row.querySelector('.np-new')?.remove();

            const action = row.querySelector('.np-action');
            const markButton = action?.querySelector('[data-mark-read]');
            if (markButton) {
                const readState = document.createElement('span');
                readState.className = 'np-read';
                readState.textContent = 'Read';
                markButton.replaceWith(readState);
            }
        });

        const unreadCount = document.getElementById('np-unread-count');
        if (unreadCount) unreadCount.textContent = '0';
        markAllButton.remove();
    } catch (error) {
        window.shellToast?.(error.message || 'Could not mark notifications as read.', 'error');
        markAllButton.disabled = false;
    }
});

document.querySelectorAll('[data-mark-read]').forEach(button => {
    button.addEventListener('click', async () => {
        if (button.disabled) return;
        button.disabled = true;
        try {
            await window.shellAjax(button.dataset.readUrl, { method: 'POST' });

            const row = button.closest('[data-notification]');
            row?.classList.remove('is-unread');
            row?.classList.add('is-read');
            row?.querySelector('.np-new')?.remove();

            const readState = document.createElement('span');
            readState.className = 'np-read';
            readState.textContent = 'Read';
            button.replaceWith(readState);

            const unreadCount = document.querySelector('.np-count strong');
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
