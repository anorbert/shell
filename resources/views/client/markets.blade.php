@extends('layouts.app')
@section('title','Markets') @section('eyebrow','MARKET DATA') @section('heading','Spot markets')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Provider-sourced quotes only</strong><p>Binance Spot quotes older than {{ config('shell.market.max_age_seconds') }} seconds are hidden and cannot open paper trades.</p></div><span class="mode-tag">SPOT DATA</span></div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">USDT PAIRS</span><h2>Available quotes</h2></div><button class="button button-quiet" id="refresh-markets" type="button">Refresh view</button></div><div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>PRICE (USDT)</th><th>PROVIDER</th><th>CAPTURED</th><th>STATUS</th></tr></thead><tbody id="market-rows">
@forelse($quotes as $q)<tr><td><strong>{{ $q->symbol }}</strong></td><td>{{ $q->price }}</td><td>{{ $q->provider }}</td><td>{{ $q->captured_at }}</td><td><span class="table-status verified">FRESH</span></td></tr>@empty<tr><td colspan="5" class="table-empty">No fresh quotes. Provider refresh must run successfully.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
@push('scripts')
<script>
document.getElementById('refresh-markets')?.addEventListener('click',async()=>{const b=document.getElementById('refresh-markets');b.disabled=true;try{const d=await window.shellAjax(@json(route('markets.data')));const rows=document.getElementById('market-rows');rows.innerHTML=d.quotes.length?d.quotes.map(q=>'<tr><td><strong>'+q.symbol+'</strong></td><td>'+q.price+'</td><td>'+q.provider+'</td><td>'+q.captured_at+'</td><td><span class="table-status verified">FRESH</span></td></tr>').join(''):'<tr><td colspan="5" class="table-empty">No fresh quotes available.</td></tr>';window.shellToast('Market view updated.');}catch(e){window.shellToast(e.message,'error');}finally{b.disabled=false;}});
</script>
@endpush
