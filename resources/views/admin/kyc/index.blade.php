@extends('layouts.app')
@section('title','KYC review queue')
@section('eyebrow','COMPLIANCE OPERATIONS')
@section('heading','KYC review queue')
@section('content')
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
<div class="content-grid">
    <article class="panel"><span class="eyebrow">AWAITING REVIEW</span><h2>{{ number_format((int)$counts->pending) }}</h2><a class="text-link" href="{{ route('admin.kyc.index',['status'=>'pending']) }}">Open pending queue →</a></article>
    <article class="panel"><span class="eyebrow">VERIFIED</span><h2>{{ number_format((int)$counts->verified) }}</h2><a class="text-link" href="{{ route('admin.kyc.index',['status'=>'verified']) }}">View approved records →</a></article>
    <article class="panel"><span class="eyebrow">REJECTED</span><h2>{{ number_format((int)$counts->rejected) }}</h2><a class="text-link" href="{{ route('admin.kyc.index',['status'=>'rejected']) }}">View rejected records →</a></article>
</div>
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">IDENTITY VERIFICATION</span><h2>Find a submission</h2></div><span class="count-chip">{{ $submissions->total() }} records</span></div>
    <form method="GET" action="{{ route('admin.kyc.index') }}" class="form-grid">
        <label>Search client, email, document number or submission ID<input name="q" value="{{ $search }}" placeholder="Name, email or KYC #"></label>
        <label>Status<select name="status"><option value="pending" {{ $status==='pending'?'selected':'' }}>Pending review</option><option value="verified" {{ $status==='verified'?'selected':'' }}>Verified</option><option value="rejected" {{ $status==='rejected'?'selected':'' }}>Rejected</option><option value="all" {{ $status==='all'?'selected':'' }}>All submissions</option></select></label>
        <button class="button button-primary" type="submit">Search records</button>
        <a class="button button-secondary" href="{{ route('admin.kyc.index') }}">Reset</a>
    </form>
</section>
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">CASE FILES</span><h2>{{ ucfirst($status==='all'?'all submissions':$status.' submissions') }}</h2></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>SUBMISSION</th><th>IDENTITY</th><th>FILES</th><th>STATUS</th><th>REVIEW</th></tr></thead><tbody>
    @forelse($submissions as $item)
        <tr>
            <td><strong>{{ $item->client_name }}</strong><small>{{ $item->client_email }}</small></td>
            <td>#{{ $item->id }}<small>Submitted {{ $item->created_at }}</small></td>
            <td>{{ strtoupper(str_replace('_',' ',$item->document_type)) }}<small>{{ $item->document_number ?: 'Document number not supplied' }}</small></td>
            <td><span class="table-status verified">ID {{ $item->document_path ? 'FILED' : 'MISSING' }}</span><small>Selfie {{ $item->selfie_path ? 'filed' : 'missing' }}</small></td>
            <td><span class="table-status {{ $item->status==='verified'?'verified':'pending' }}">{{ strtoupper($item->status) }}</span></td>
            <td><a class="button button-primary" href="{{ route('admin.kyc.show',$item->id) }}">{{ $item->status==='pending'?'Open case':'View case' }} →</a></td>
        </tr>
    @empty<tr><td colspan="6" class="table-empty">No KYC submissions match this filter.</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination-wrap">{{ $submissions->links() }}</div>
</section>
@endsection
