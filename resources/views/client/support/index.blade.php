@extends('layouts.app')
@section('title','Support')
@section('eyebrow','HELP CENTRE')
@section('heading','Support')
@section('content')
<div class="support-page">
    @if(session('status'))
        <div class="notice-banner support-notice"><span class="support-notice-icon">✓</span><div><strong>{{ session('status') }}</strong></div></div>
    @endif

    @if($errors->any())
        <div class="notice-banner support-error"><strong>Please check your request:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="content-grid support-grid">
        <section class="panel support-panel support-compose">
            <div class="panel-head support-panel-head">
                <div><span class="eyebrow">WE'RE HERE TO HELP</span><h2>Create a support request</h2><p class="support-subtitle">Tell our team what you need help with. We'll respond in your ticket.</p></div>
                <span class="support-head-icon" aria-hidden="true">✉</span>
            </div>
            <form method="POST" action="{{ route('support.store') }}" class="form-stack support-form">
                @csrf
                <label>Subject<input name="subject" maxlength="140" required value="{{ old('subject') }}" placeholder="Briefly describe the issue"></label>
                <label>Category
                    <select name="category" required>
                        <option value="account" @selected(old('category')==='account')>Account</option>
                        <option value="trading" @selected(old('category')==='trading')>Paper trading</option>
                        <option value="market_data" @selected(old('category')==='market_data')>Market data</option>
                        <option value="security" @selected(old('category')==='security')>Security</option>
                        <option value="other" @selected(old('category')==='other')>Other</option>
                    </select>
                </label>
                <label>Message<textarea name="message" rows="6" maxlength="5000" required placeholder="Explain what happened and what you expected...">{{ old('message') }}</textarea></label>
                <div class="support-form-footer"><small>Be as specific as possible so we can help faster.</small><button class="button button-primary" type="submit"><span>Send request</span><span aria-hidden="true">→</span></button></div>
            </form>
        </section>

        <section class="panel support-panel support-tickets">
            <div class="panel-head support-panel-head">
                <div><span class="eyebrow">YOUR SUPPORT ACTIVITY</span><h2>Your requests</h2><p class="support-subtitle">Track replies and the status of your requests.</p></div>
                <span class="support-count">{{ $tickets->total() }}</span>
            </div>
            <div class="quick-links support-ticket-list">
                @forelse($tickets as $t)
                    <a class="support-ticket-link" href="{{ route('support.show',$t->id) }}">
                        <span class="support-ticket-mark" aria-hidden="true">#</span>
                        <span class="support-ticket-copy"><strong>{{ $t->subject }}</strong><small>#{{ $t->id }} · {{ ucfirst(str_replace('_',' ',$t->category)) }} · {{ $t->last_message_at }}</small></span>
                        <span class="support-ticket-side"><span class="support-status support-status-{{ $t->status }}">{{ str_replace('_',' ',strtoupper($t->status)) }}</span><b aria-hidden="true">→</b></span>
                    </a>
                @empty
                    <div class="support-empty"><span aria-hidden="true">◎</span><strong>No support requests yet</strong><p>Your submitted tickets and replies will appear here.</p></div>
                @endforelse
            </div>
            @if($tickets->hasPages())<div class="pagination-wrap support-pagination">{{ $tickets->links() }}</div>@endif
        </section>
    </div>

    @if($ticket)
        <section class="panel support-panel support-conversation">
            <div class="panel-head support-panel-head">
                <div><span class="eyebrow">SUPPORT CONVERSATION · TICKET #{{ $ticket->id }}</span><h2>{{ $ticket->subject }}</h2><p class="support-subtitle">Keep all updates for this request in one conversation.</p></div>
                <span class="support-status support-status-{{ $ticket->status }}">{{ str_replace('_',' ',strtoupper($ticket->status)) }}</span>
            </div>
            <div class="message-thread support-thread">
                @forelse($messages as $message)
                    <article class="thread-message support-message {{ $message->sender_role==='admin'?'support-message-agent':'support-message-user' }}">
                        <div class="support-message-meta">
                            <span class="support-avatar {{ $message->sender_role==='admin'?'support-avatar-agent':'support-avatar-user' }}" aria-hidden="true">{{ $message->sender_role==='admin'?'S':'Y' }}</span>
                            <strong>{{ $message->sender_role==='admin'?'Shell Support':($message->sender_name??'You') }}</strong>
                            <small>{{ $message->created_at }}</small>
                        </div>
                        <p>{{ $message->message }}</p>
                    </article>
                @empty
                    <div class="support-empty"><strong>No messages yet</strong><p>Send a reply below to start the conversation.</p></div>
                @endforelse
            </div>
            @if($ticket->status!=='closed')
                <form method="POST" action="{{ route('support.reply',$ticket->id) }}" class="form-stack support-reply-form">
                    @csrf
                    <label for="support-reply">Your reply<textarea id="support-reply" name="message" rows="4" required maxlength="5000" placeholder="Write your reply...">{{ old('message') }}</textarea></label>
                    <div class="support-form-footer"><small>Replies are visible to you and the Shell Support team.</small><button class="button button-primary" type="submit">Send reply <span aria-hidden="true">→</span></button></div>
                </form>
            @else
                <div class="support-closed-note"><span aria-hidden="true">✓</span><p>This ticket is closed. Create a new request if you need further assistance.</p></div>
            @endif
        </section>
    @endif
</div>
@endsection

@push('styles')
<style>
.support-page{display:grid;gap:18px;min-width:0}
.support-grid{align-items:stretch;gap:18px}
.support-panel{min-width:0;padding:0;overflow:hidden}
.support-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px 17px;border-bottom:1px solid rgba(148,163,184,.13)}
.support-panel-head h2{margin:5px 0 0;font-size:17px;letter-spacing:-.025em}
.support-subtitle{margin:7px 0 0;color:#8f9db0;font-size:12px;line-height:1.55}
.support-head-icon{width:38px;height:38px;display:grid;place-items:center;flex:0 0 38px;border:1px solid rgba(207,171,100,.24);border-radius:11px;background:rgba(207,171,100,.09);color:#e2c58b;font-size:18px}
.support-form,.support-reply-form{padding:20px 22px 22px;gap:15px}
.support-form label,.support-reply-form label{display:grid;gap:7px;color:#c7d0dd;font-size:12px;font-weight:650}
.support-form input,.support-form select,.support-form textarea,.support-reply-form textarea{width:100%;box-sizing:border-box;border:1px solid #2a394d;border-radius:9px;background:#0b1422;color:#edf2f8;padding:11px 12px;font:inherit;font-size:13px;line-height:1.5;outline:none;transition:border-color .18s,box-shadow .18s,background .18s}
.support-form input,.support-form select{min-height:42px}
.support-form textarea,.support-reply-form textarea{min-height:118px;resize:vertical}
.support-form input::placeholder,.support-form textarea::placeholder,.support-reply-form textarea::placeholder{color:#65758b}
.support-form input:focus,.support-form select:focus,.support-form textarea:focus,.support-reply-form textarea:focus{border-color:#b99250;box-shadow:0 0 0 3px rgba(185,146,80,.12);background:#0d1929}
.support-form select option{background:#0b1422;color:#edf2f8}
.support-form-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
.support-form-footer>small{color:#77879c;font-size:11px;line-height:1.5}
.support-form-footer .button{display:inline-flex;align-items:center;justify-content:center;gap:12px;min-height:40px;padding:0 16px}
.support-count{min-width:31px;height:31px;padding:0 8px;display:grid;place-items:center;border:1px solid rgba(207,171,100,.2);border-radius:9px;background:rgba(207,171,100,.08);color:#e3c88f;font-size:12px;font-weight:750}
.support-ticket-list{padding:5px 14px}
.support-ticket-link{display:flex;align-items:center;gap:11px;padding:13px 8px;border-bottom:1px solid rgba(148,163,184,.1);text-decoration:none;transition:background .16s}
.support-ticket-link:last-child{border-bottom:0}
.support-ticket-link:hover{background:rgba(148,163,184,.055)}
.support-ticket-mark{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:9px;background:#17263a;color:#b6c4d6;font-size:13px;font-weight:750}
.support-ticket-copy{display:grid;gap:5px;min-width:0;flex:1}
.support-ticket-copy strong{overflow:hidden;color:#e6ebf3;font-size:12px;font-weight:650;text-overflow:ellipsis;white-space:nowrap}
.support-ticket-copy small{color:#8291a6;font-size:10px;line-height:1.4}
.support-ticket-side{display:flex;align-items:center;gap:10px}
.support-ticket-side>b{color:#9ba9bb;font-size:16px}
.support-status{display:inline-flex;align-items:center;justify-content:center;padding:5px 8px;border:1px solid rgba(120,190,157,.2);border-radius:6px;background:rgba(55,155,111,.1);color:#8ed5b2;font-size:9px;font-weight:800;letter-spacing:.06em;white-space:nowrap}
.support-status-closed{border-color:rgba(148,163,184,.2);background:rgba(148,163,184,.08);color:#aab5c4}
.support-status-pending{border-color:rgba(220,169,83,.22);background:rgba(220,169,83,.1);color:#e6c27e}
.support-status-open{border-color:rgba(90,165,220,.25);background:rgba(60,140,205,.12);color:#8dc6ed}
.support-empty{display:grid;justify-items:center;gap:7px;padding:30px 15px;text-align:center}
.support-empty>span{color:#b99a60;font-size:25px}
.support-empty strong{color:#dce4ef;font-size:12px}
.support-empty p{margin:0;color:#8291a6;font-size:11px}
.support-pagination{padding:12px 18px;border-top:1px solid rgba(148,163,184,.1)}
.support-conversation{margin-top:0}
.support-thread{display:grid;gap:13px;padding:20px 22px;background:#080f1b}
.support-message{max-width:min(780px,94%);padding:13px 15px;border:1px solid;border-radius:12px;box-shadow:0 5px 18px rgba(0,0,0,.12)}
.support-message-agent{justify-self:start;border-color:#293d55;background:#142338}
.support-message-user{justify-self:end;border-color:#665333;background:#2b251b}
.support-message-meta{display:flex;align-items:center;gap:8px;margin-bottom:9px}
.support-message-meta strong{color:#e8edf5;font-size:11px;font-weight:750}
.support-message-meta small{margin-left:auto;color:#9aa8ba;font-size:10px}
.support-avatar{width:25px;height:25px;display:grid;place-items:center;flex:0 0 25px;border-radius:8px;font-size:10px;font-weight:800}
.support-avatar-agent{background:#203e5d;color:#9bcaf0}
.support-avatar-user{background:#59462a;color:#f0d39a}
.support-message>p{margin:0;color:#e1e8f1;font-size:13px;line-height:1.7;white-space:pre-wrap;overflow-wrap:anywhere}
.support-reply-form{border-top:1px solid rgba(148,163,184,.13)}
.support-closed-note{display:flex;align-items:center;gap:10px;padding:15px 22px;border-top:1px solid rgba(148,163,184,.12);background:rgba(148,163,184,.045)}
.support-closed-note>span{color:#8ed5b2;font-weight:800}
.support-closed-note p{margin:0;color:#aab7c8;font-size:12px}
.support-notice{display:flex;align-items:center;gap:10px}
.support-notice-icon{width:25px;height:25px;display:grid;place-items:center;border-radius:50%;background:rgba(70,170,125,.14);color:#83d2a9}
.support-error{border-color:rgba(225,100,100,.3)}
.support-error ul{margin:7px 0 0;padding-left:18px;color:#f0a4a4;font-size:12px}
@media(max-width:900px){.support-grid{grid-template-columns:1fr}}
@media(max-width:600px){.support-page{gap:13px}.support-panel-head{padding:16px 15px 14px}.support-panel-head h2{font-size:15px}.support-form,.support-reply-form{padding:16px 15px 18px;gap:13px}.support-ticket-list{padding:4px 9px}.support-ticket-link{gap:8px;padding:12px 5px}.support-ticket-mark{width:30px;height:30px;flex-basis:30px}.support-ticket-copy strong{font-size:11px}.support-ticket-copy small{font-size:9px}.support-ticket-side{gap:6px}.support-status{padding:4px 6px;font-size:8px}.support-thread{padding:14px 12px;gap:10px}.support-message{max-width:98%;padding:11px 12px}.support-message-meta small{font-size:9px}.support-message>p{font-size:12px}.support-form-footer{align-items:stretch}.support-form-footer .button{width:100%}}
</style>
@endpush