@extends('layouts.app')
@section('title','Support ticket') @section('eyebrow','ADMINISTRATION / SUPPORT') @section('heading','Ticket #'.$ticket->id)
@section('content')
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
<section class="panel">
    <div class="panel-head">
        <div><span class="eyebrow">{{ $ticket->client_name ?? 'Unknown visitor' }} · {{ $ticket->client_email ?? 'No email' }}</span><h2>{{ $ticket->subject }}</h2><small>{{ $ticket->guest_email ? 'Public website chat' : 'Registered client ticket' }} · {{ strtoupper($ticket->category) }}</small></div>
        <span class="table-status">{{ strtoupper($ticket->status) }}</span>
    </div>
    <div class="message-thread">
        @foreach($messages as $message)
            <article class="thread-message">
                <strong>{{ $message->sender_role==='admin'?'SHELL Support':($message->sender_name ?? ($ticket->guest_name ?? 'Visitor')) }}</strong>
                <p>{{ $message->message }}</p><small>{{ $message->created_at }}</small>
            </article>
        @endforeach
    </div>
    <form method="POST" action="{{ route('admin.support.reply',$ticket->id) }}" class="form-stack">
        @csrf
        <label>Reply<textarea name="message" rows="4" required maxlength="5000" placeholder="Write a helpful response..."></textarea></label>
        <label>Conversation status<select name="status"><option value="open" @selected($ticket->status==='open')>Open</option><option value="pending" @selected($ticket->status==='pending')>Pending</option><option value="closed" @selected($ticket->status==='closed')>Closed</option></select></label>
        <button class="button button-primary" type="submit">Send reply and update</button>
        @if($ticket->guest_email)<small>The visitor will receive an email containing the complete conversation after you reply.</small>@endif
    </form>
</section>
@endsection