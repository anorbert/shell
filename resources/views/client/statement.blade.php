@extends('layouts.app')
@section('title','Wallet statement')
@section('eyebrow','ACCOUNT RECORDS')
@section('heading','Wallet statement')
@section('content')
<style>
.statement-page .statement-flow{display:inline-flex;align-items:center;gap:7px;min-width:82px;padding:6px 10px;border:1px solid transparent;border-radius:999px;font-size:10px;font-weight:850;letter-spacing:.07em;line-height:1;white-space:nowrap}
.statement-page .statement-flow:before{content:"";width:7px;height:7px;flex:0 0 7px;border-radius:50%;background:currentColor}
.statement-page .statement-flow.is-in{color:#43d39a;background:rgba(39,190,129,.12);border-color:rgba(67,211,154,.24)}
.statement-page .statement-flow.is-out{color:#ff8585;background:rgba(235,83,83,.12);border-color:rgba(255,133,133,.24)}
.statement-page .statement-amount{display:inline-block;font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
.statement-page .statement-amount.is-in{color:#43d39a}
.statement-page .statement-amount.is-out{color:#ff8585}
.statement-page .statement-table td{vertical-align:middle}
.statement-page .statement-table tbody tr:hover{background:rgba(255,255,255,.025)}
.statement-page .statement-legend{display:flex;flex-wrap:wrap;align-items:center;gap:12px;padding:13px 18px;border-bottom:1px solid var(--line,#26364a);color:var(--muted,#91a0b5);font-size:11px}
.statement-page .statement-legend-item{display:inline-flex;align-items:center;gap:7px}
.statement-page .statement-legend-dot{width:8px;height:8px;border-radius:50%}
.statement-page .statement-legend-dot.is-in{background:#43d39a}
.statement-page .statement-legend-dot.is-out{background:#ff8585}
@media(max-width:640px){.statement-page .statement-legend{padding:12px 14px}}
</style>
<x-client-attention title="Ledger-backed account history"><p>This statement shows posted entries affecting your own demo and real wallets. Demo credits are simulation-only. Pending real deposits are not wallet credits.</p></x-client-attention>
<section class="panel statement-page">
<form method="GET" action="{{ route('statement.index') }}" class="filter-row">
<label>Wallet<select name="wallet"><option value="">All wallets</option><option value="PAPER_WALLET" @selected(request('wallet')==='PAPER_WALLET')>Demo wallet</option><option value="REAL_WALLET" @selected(request('wallet')==='REAL_WALLET')>Real wallet</option></select></label>
<label>From<input type="date" name="from" value="{{ request('from') }}"></label>
<label>To<input type="date" name="to" value="{{ request('to') }}"></label>
<button class="button button-primary" type="submit">Filter</button><a class="text-link" href="{{ route('statement.index') }}">Clear</a>
</form>
<div class="statement-legend"><span class="statement-legend-item"><span class="statement-legend-dot is-in"></span> Money in · deposits and credits</span><span class="statement-legend-item"><span class="statement-legend-dot is-out"></span> Money out · withdrawals and debits</span></div>
<div class="table-wrap"><table class="data-table statement-table"><thead><tr><th>DATE</th><th>DESCRIPTION</th><th>WALLET</th><th>MONEY FLOW</th><th>AMOUNT</th><th>REFERENCE</th></tr></thead><tbody>
@forelse($entries as $entry)<tr><td>{{ $entry->posted_at }}</td><td>{{ $entry->description }}</td><td>{{ $entry->code==='PAPER_WALLET'?'Demo':'Real' }} · {{ $entry->currency }}</td><td><span class="statement-flow {{ $entry->side==='credit'?'is-in':'is-out' }}">{{ $entry->side==='credit'?'MONEY IN':'MONEY OUT' }}</span></td><td><span class="statement-amount {{ $entry->side==='credit'?'is-in':'is-out' }}">{{ $entry->side==='credit'?'+':'−' }}@money($entry->amount)</span></td><td>{{ $entry->idempotency_key }}</td></tr>@empty<tr><td colspan="6" class="table-empty">No wallet entries match these filters.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $entries->links() }}</div>
</section>
@endsection
