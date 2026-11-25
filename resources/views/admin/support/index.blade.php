@extends('layouts.app')
@section('title','Support inbox') @section('eyebrow','ADMINISTRATION') @section('heading','Support inbox')
@section('content')
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">CLIENT & WEBSITE SUPPORT</span><h2>Incoming conversations</h2></div></div>
    <form method="GET" class="filter-row">
        <label>Status<select name="status"><option value="">All</option>@foreach(['open','pending','closed'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select></label>
        <button class="button button-primary" type="submit">Filter</button>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>TICKET</th><th>CONTACT</th><th>TYPE</th><th>STATUS</th><th>UPDATED</th><th></th></tr></thead>
        <tbody>
        @forelse($tickets as $t)
            <tr>
                <td><strong>#{{ $t->id }} · {{ $t->subject }}</strong></td>
                <td>{{ $t->client_name ?? 'Unknown visitor' }}<small>{{ $t->client_email ?? 'No email' }}</small></td>
                <td>{{ $t->guest_email ? 'Website visitor' : 'Registered client' }}<small>{{ $t->category }}</small></td>
                <td><span class="table-status {{ $t->status==='closed'?'pending':'verified' }}">{{ strtoupper($t->status) }}</span></td>
                <td>{{ $t->last_message_at }}</td>
                <td><a class="text-link" href="{{ route('admin.support.show',$t->id) }}">Open →</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="table-empty">No support conversations found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="pagination-wrap">{{ $tickets->links() }}</div>
</section>
@endsection