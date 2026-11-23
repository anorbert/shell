@extends('layouts.app')
@section('title','Notifications') @section('eyebrow','ACCOUNT UPDATES') @section('heading','Notifications')
@section('content')
<section class="panel"><div class="panel-head"><div><span class="eyebrow">SYSTEM MESSAGES</span><h2>Your notifications</h2></div></div><div class="quick-links">
@forelse($notifications as $n)<article class="notification-row" data-notification="{{ $n->id }}"><div><strong>{{ $n->title }}</strong><p>{{ $n->body }}</p><small>{{ $n->created_at }}</small></div>@if(!$n->read_at)<button class="button button-quiet" type="button" data-mark-read="{{ $n->id }}">Mark read</button>@else<span class="table-status verified">READ</span>@endif</article>
@empty<div class="table-empty">No notifications yet.</div>@endforelse</div><div class="pagination-wrap">{{ $notifications->links() }}</div></section>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-mark-read]').forEach(button=>button.addEventListener('click',async()=>{button.disabled=true;try{await window.shellAjax(@json(url('/notifications'))+'/'+button.dataset.markRead+'/read',{method:'POST'});const badge=document.createElement('span');badge.className='table-status verified';badge.textContent='READ';button.replaceWith(badge);}catch(e){window.shellToast(e.message,'error');button.disabled=false;}}));
</script>
@endpush