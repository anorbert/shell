@extends('layouts.app')
@section('title','Markets') @section('eyebrow','MARKET DATA') @section('heading','Spot markets')
@section('content')
<div class="page-action-row">
    <a href="{{ route('convert.index') }}" class="button button-secondary"><svg class="nav-icon"><use href="#i-repeat"/></svg> Convert assets</a>
</div>
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Provider-sourced quotes only</strong><p>Binance Spot quotes older than {{ config('shell.market.max_age_seconds') }} seconds are hidden and cannot open paper trades.</p></div><span class="mode-tag">SPOT DATA</span></div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">USDT PAIRS</span><h2>Available quotes</h2></div><button class="button button-quiet" id="refresh-markets" type="button">Refresh view</button></div>
<form id="market-filters" class="filter-row"><label>Search pair <input name="search" placeholder="Search BTC, ETH..."></label><label>Sort <select name="sort"><option value="symbol">Symbol</option><option value="price_desc">Price: high to low</option><option value="price_asc">Price: low to high</option></select></label><label class="inline-check"><input type="checkbox" name="favorites" value="1"> Favorites only</label></form>
<div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>PRICE (USDT)</th><th>PROVIDER</th><th>CAPTURED</th><th>WATCHLIST</th></tr></thead><tbody id="market-rows">
@forelse($quotes as $q)<tr><td><strong>{{ $q->symbol }}</strong></td><td>@decimal($q->price)</td><td>{{ $q->provider }}</td><td>{{ $q->captured_at }}</td><td><button class="button button-quiet favorite-toggle" data-symbol="{{ $q->symbol }}" type="button">{{ in_array($q->symbol,$favorites,true)?'★ Saved':'☆ Save' }}</button></td></tr>
@empty<tr><td colspan="5" class="table-empty">No fresh quotes. Provider refresh must run successfully.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
@push('scripts')
<script>
const marketRows=document.getElementById('market-rows'),marketFilters=document.getElementById('market-filters');
async function loadMarkets(){const params=new URLSearchParams(new FormData(marketFilters));try{const data=await window.shellAjax(@json(route('markets.data'))+'?'+params.toString());marketRows.replaceChildren();if(!data.quotes.length){const row=document.createElement('tr'),cell=document.createElement('td');cell.colSpan=5;cell.className='table-empty';cell.textContent='No fresh quotes match your filters.';row.append(cell);marketRows.append(row);return;}data.quotes.forEach(q=>{const row=document.createElement('tr');[q.symbol,q.price,q.provider,q.captured_at].forEach((v,i)=>{const cell=document.createElement('td');cell.textContent=i===1?new Intl.NumberFormat(undefined,{maximumFractionDigits:8}).format(Number(v)):v;if(i===0){const strong=document.createElement('strong');strong.textContent=v;cell.replaceChildren(strong);}row.append(cell);});const cell=document.createElement('td'),button=document.createElement('button');button.type='button';button.className='button button-quiet favorite-toggle';button.dataset.symbol=q.symbol;button.textContent=q.favorite?'★ Saved':'☆ Save';cell.append(button);row.append(cell);marketRows.append(row);});}catch(e){window.shellToast(e.message,'error');}}
marketFilters.addEventListener('input',loadMarkets);document.getElementById('refresh-markets').addEventListener('click',loadMarkets);
marketRows.addEventListener('click',async e=>{const button=e.target.closest('.favorite-toggle');if(!button)return;button.disabled=true;try{await window.shellAjax(@json(url('/markets'))+'/'+encodeURIComponent(button.dataset.symbol)+'/favorite',{method:'POST'});await loadMarkets();window.shellToast('Watchlist updated.');}catch(err){window.shellToast(err.message,'error');button.disabled=false;}});
</script>
@endpush