@extends('layouts.app')
@section('title','Activity') @section('eyebrow','ACCOUNT RECORDS') @section('heading','Activity')
@section('content')
<section class="panel"><div class="panel-head"><div><span class="eyebrow">PAPER TRADING</span><h2>Trade activity</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>DATE</th><th>PAIR</th><th>TYPE</th><th>STAKE</th><th>OPEN / CLOSE</th><th>RESULT</th></tr></thead><tbody>
@forelse($trades as $t)<tr><td>{{ $t->opened_at }}</td><td><strong>{{ $t->symbol }}</strong></td><td>{{ $t->direction }}</td><td>{{ $t->stake }} USDT</td><td>{{ $t->opening_price }} / {{ $t->closing_price??'—' }}</td><td><span class="table-status {{ $t->status==='open'?'pending':'verified' }}">{{ strtoupper($t->status) }}</span></td></tr>@empty<tr><td colspan="6" class="table-empty">No activity recorded.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $trades->links() }}</div></section>
@endsection
