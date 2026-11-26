@extends('layouts.app')
@section('title','Support conversation')
@section('eyebrow','ADMINISTRATION / CUSTOMER CARE')
@section('heading','Conversation #'.$ticket->id)
@section('content')
<style>
.support-workspace{display:grid;grid-template-columns:minmax(0,1fr) 290px;gap:18px;align-items:start}
.support-chat,.support-side{border:1px solid var(--border,#26354b);border-radius:16px;background:var(--surface,#101b2e);overflow:hidden}
.support-chat-head,.support-side-head{padding:18px 20px;border-bottom:1px solid var(--border,#26354b);display:flex;align-items:center;justify-content:space-between;gap:14px}
.support-chat-head h2{margin:4px 0;font-size:20px}.support-chat-head p{margin:0;color:var(--muted,#94a3b8);font-size:13px}
.support-thread{padding:22px;display:flex;flex-direction:column;gap:16px;max-height:56vh;min-height:340px;overflow:auto;background:linear-gradient(180deg,rgba(255,255,255,.012),transparent)}
.support-bubble{max-width:min(78%,680px);padding:13px 15px;border-radius:14px;background:rgba(148,163,184,.10);border:1px solid rgba(148,163,184,.13);align-self:flex-start}
.support-bubble.is-admin{align-self:flex-end;background:rgba(35,111,178,.16);border-color:rgba(59,130,246,.22)}
.support-bubble.is-note{align-self:stretch;max-width:100%;background:rgba(217,168,67,.10);border:1px dashed rgba(217,168,67,.55)}
.support-bubble p{margin:7px 0 5px;white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.6}
.support-bubble small,.support-meta{color:var(--muted,#94a3b8);font-size:11px}
.support-composer{padding:18px 20px;border-top:1px solid var(--border,#26354b)}
.support-tabs{display:flex;gap:8px;margin-bottom:12px}.support-tabs button{border:1px solid var(--border,#334155);background:transparent;color:inherit;border-radius:9px;padding:8px 13px;cursor:pointer}.support-tabs button.active{background:rgba(59,130,246,.15);border-color:#3b82f6}
.support-composer textarea{width:100%;min-height:116px;resize:vertical;border:1px solid var(--border,#334155);border-radius:12px;background:rgba(2,6,23,.28);color:inherit;padding:13px;font:inherit;line-height:1.5}
.support-compose-foot{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:12px;flex-wrap:wrap}
.support-side-section{padding:16px 18px;border-bottom:1px solid var(--border,#26354b)}.support-side-section:last-child{border-bottom:0}
.support-side-section label{display:block;font-size:11px;color:var(--muted,#94a3b8);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}
.support-side-section strong{overflow-wrap:anywhere}.support-state{display:inline-flex;padding:5px 9px;border-radius:99px;font-size:11px;font-weight:700;background:rgba(59,130,246,.13);color:#7db8f5}
@media(max-width:900px){.support-workspace{grid-template-columns:1fr}.support-thread{max-height:60vh}.support-bubble{max-width:90%}}
</style>
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
<div class="support-workspace">
    <section class="support-chat">
        <header class="support-chat-head">
            <div><span class="eyebrow">TICKET #{{ $ticket->id }} · {{ strtoupper($ticket->category) }}</span><h2>{{ $ticket->subject }}</h2><p>{{ $ticket->client_name ?? 'Unknown visitor' }} · {{ $ticket->client_email ?? 'No email provided' }}</p></div>
            <span class="support-state">{{ strtoupper($ticket->status) }}</span>
        </header>
        <div class="support-thread" id="support-thread" aria-live="polite">
            @forelse($messages as $message)
                @php($isNote = $message->sender_role === 'note')
                @php($isAdmin = $message->sender_role === 'admin')
                <article class="support-bubble {{ $isNote ? 'is-note' : ($isAdmin ? 'is-admin' : '') }}">
                    <div class="support-meta"><strong>{{ $isNote ? 'INTERNAL TEAM NOTE' : ($isAdmin ? 'SHELL SUPPORT' : ($message->sender_name ?? $ticket->client_name ?? 'Customer')) }}</strong> · {{ \Illuminate\Support\Carbon::parse($message->created_at)->format('M j, Y · H:i') }}</div>
                    <p>{{ $message->message }}</p>
                </article>
            @empty
                <p class="muted">No messages in this conversation yet.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('admin.support.reply',$ticket->id) }}" class="support-composer" id="support-reply-form">
            @csrf
            <input type="hidden" name="message_type" id="support-message-type" value="reply">
            <div class="support-tabs" role="tablist" aria-label="Message type">
                <button type="button" class="active" data-compose="reply" aria-pressed="true">Reply to customer</button>
                <button type="button" data-compose="note" aria-pressed="false">＋ Internal note</button>
            </div>
            <textarea name="message" id="support-message" maxlength="5000" required placeholder="Write a clear, helpful response…">{{ old('message') }}</textarea>
            @error('message')<small class="text-danger">{{ $message }}</small>@enderror
            <div class="support-compose-foot">
                <small class="muted"><span id="support-char-count">0</span>/5,000 · Enter a message; Shift+Enter for a new line.</small>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                    <label style="font-size:12px;color:var(--muted,#94a3b8)">After sending
                        <select name="status" id="support-next-status" style="margin-left:6px">
                            <option value="open" @selected($ticket->status==='open')>Keep open</option>
                            <option value="pending" @selected($ticket->status==='pending')>Pending</option>
                            <option value="closed" @selected($ticket->status==='closed')>Resolve conversation</option>
                        </select>
                    </label>
                    <button class="button button-primary" type="submit" id="support-send">Send reply</button>
                </div>
            </div>
            @if($ticket->guest_email)<small class="muted" style="display:block;margin-top:10px">Customer replies also trigger an email transcript. Internal notes are never emailed or shown to customers.</small>@endif
        </form>
    </section>
    <aside class="support-side">
        <div class="support-side-head"><strong>Conversation details</strong><a class="text-link" href="{{ route('admin.support.index') }}">Inbox</a></div>
        <div class="support-side-section"><label>Customer</label><strong>{{ $ticket->client_name ?? 'Unknown visitor' }}</strong><p class="muted" style="margin:5px 0 0;font-size:12px">{{ $ticket->client_email ?? 'No email' }}</p></div>
        <div class="support-side-section"><label>Channel</label><strong>{{ $ticket->guest_email ? 'Website visitor' : 'Registered SHELL client' }}</strong></div>
        <div class="support-side-section"><label>Category</label><strong>{{ ucwords(str_replace('_',' ',$ticket->category)) }}</strong></div>
        <div class="support-side-section"><label>Current status</label><strong>{{ ucfirst($ticket->status) }}</strong></div>
        <div class="support-side-section"><label>Last activity</label><strong>{{ $ticket->last_message_at ? \Illuminate\Support\Carbon::parse($ticket->last_message_at)->format('M j, Y · H:i') : '—' }}</strong></div>
        <div class="support-side-section"><label>Ticket created</label><strong>{{ \Illuminate\Support\Carbon::parse($ticket->created_at)->format('M j, Y · H:i') }}</strong></div>
    </aside>
</div>
<script>
(() => {
 const form=document.getElementById('support-reply-form'); if(!form)return;
 const textarea=document.getElementById('support-message'), type=document.getElementById('support-message-type');
 const count=document.getElementById('support-char-count'), send=document.getElementById('support-send');
 const status=document.getElementById('support-next-status');
 const tabs=[...form.querySelectorAll('[data-compose]')];
 const update=()=>count.textContent=textarea.value.length;
 textarea.addEventListener('input',update); update();
 tabs.forEach(tab=>tab.addEventListener('click',()=>{
   const note=tab.dataset.compose==='note'; type.value=note?'note':'reply';
   tabs.forEach(item=>{const active=item===tab;item.classList.toggle('active',active);item.setAttribute('aria-pressed',String(active));});
   textarea.placeholder=note?'Add a private note for your support team…':'Write a clear, helpful response…';
   send.textContent=note?'Save internal note':'Send reply';
   status.disabled=note;
   status.closest('label').style.opacity=note?'.45':'1';
 }));
 textarea.addEventListener('keydown',event=>{
   if(event.key==='Enter'&&!event.shiftKey&&!event.isComposing){event.preventDefault();if(textarea.value.trim())form.requestSubmit();}
 });
 form.addEventListener('submit',()=>{send.disabled=true;send.textContent=type.value==='note'?'Saving…':'Sending…';});
 const thread=document.getElementById('support-thread');thread.scrollTop=thread.scrollHeight;
})();
</script>
@endsection