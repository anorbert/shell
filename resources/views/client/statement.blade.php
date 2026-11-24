@extends('layouts.app')
@section('title','Wallet statement')
@section('eyebrow','ACCOUNT RECORDS')
@section('heading','Wallet statement')
@section('content')
<x-client-attention title="Ledger-backed account history"><p>This statement shows posted entries affecting your own demo and real wallets. Demo credits are simulation-only. Pending real deposits are not wallet credits.</p></x-client-attention>
<section class="panel">
<form method="GET" action="{{ route('statement.index') }}" class="filter-row">
<label>Wallet<select name="wallet"><option value="">All wallets</option><option value="PAPER_WALLET" @selected(request('wallet')==='PAPER_WALLET')>Demo wallet</option><option value="REAL_WALLET" @selected(request('wallet')==='REAL_WALLET')>Real wallet</option></select></label>
<label>From<input type="date" name="from" value="{{ request('from') }}"></label>
<label>To<input type="date" name="to" value="{{ request('to') }}"></label>
<button class="button button-primary" type="submit">Filter</button><a class="text-link" href="{{ route('statement.index') }}">Clear</a>
</form>
<div class="table-wrap"><table class="data-table"><thead><tr><th>DATE</th><th>DESCRIPTION</th><th>WALLET</th><th>ENTRY</th><th>AMOUNT</th><th>REFERENCE</th></tr></thead><tbody>
@forelse($entries as $entry)<tr><td>{{ $entry->posted_at }}</td><td>{{ $entry->description }}</td><td>{{ $entry->code==='PAPER_WALLET'?'Demo':'Real' }} · {{ $entry->currency }}</td><td><span class="table-status {{ $entry->side==='credit'?'verified':'pending' }}">{{ strtoupper($entry->side) }}</span></td><td>@money($entry->amount)</td><td>{{ $entry->idempotency_key }}</td></tr>@empty<tr><td colspan="6" class="table-empty">No wallet entries match these filters.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $entries->links() }}</div>
</section>
@endsection
