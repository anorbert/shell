@extends('layouts.app')
@section('title','Paper trading') @section('eyebrow','SIMULATION ENGINE') @section('heading','Paper trading hub')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Simulation only — no real money</strong><p>Credits are non-withdrawable and have no cash value. Outcomes are not investment returns.</p></div><span class="mode-tag">PAPER ONLY</span></div>
<div class="content-grid"><section class="panel"><div class="panel-head"><div><span class="eyebrow">NEW POSITION</span><h2>Open a paper trade</h2></div></div>
<form id="paper-trade-form" class="form-stack">\n<input type="hidden" name="request_key" value="{{ \\Illuminate\\Support\\Str::uuid() }}">
<label>Market pair<select name="symbol" required>@foreach($symbols as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach</select></label>
<label>Direction<select name="direction"><option value="UP">UP — price rises</option><option value="DOWN">DOWN — price falls</option></select></label>
<label>Paper stake (USDT)<input name="stake" type="number" min="0.00000001" max="{{ config('shell.paper.max_stake') }}" step="0.00000001" required></label>
<label>Duration<select name="duration">@foreach($durations as $s)<option value="{{ $s }}">{{ $s>=3600?($s/3600).' hour':($s/60).' minutes' }}</option>@endforeach</select></label>
<p class="muted">Available paper balance: <strong>{{ $balance }} USDT</strong>. Settlement waits for a fresh post-expiry quote.</p><button class="button button-primary button-full" type="submit">Open paper trade</button>
</form></section><section class="panel"><div class="panel-head"><div><span class="eyebrow">PROCESS</span><h2>How it works</h2></div></div><div class="quick-links"><div><strong>1. Quote snapshot</strong><small>Opening price is saved from provider data.</small></div><div><strong>2. Stake reserved</strong><small>Paper credits move to ledger escrow.</small></div><div><strong>3. Time-based settlement</strong><small>Fresh post-expiry quote determines outcome.</small></div><div><strong>4. Immutable record</strong><small>Every movement uses balanced ledger entries.</small></div></div></section></div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">YOUR POSITIONS</span><h2>Trade history</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPEN PRICE</th><th>EXPIRY</th><th>STATUS</th></tr></thead><tbody>
@forelse($trades as $t)<tr><td><strong>{{ $t->symbol }}</strong></td><td>{{ $t->direction }}</td><td>{{ $t->stake }} USDT</td><td>{{ $t->opening_price }}</td><td>{{ $t->expires_at }}</td><td><span class="table-status {{ $t->status==='open'?'pending':'verified' }}">{{ strtoupper($t->status) }}</span></td></tr>@empty<tr><td colspan="6" class="table-empty">No paper trades yet.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $trades->links() }}</div></section>
@endsection
@push('scripts')
<script>
document.getElementById('paper-trade-form')?.addEventListener('submit',async e=>{e.preventDefault();const f=e.currentTarget,b=f.querySelector('button[type=submit]');b.disabled=true;try{const payload=Object.fromEntries(new FormData(f).entries());await window.shellAjax(@json(route('trading.store')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});window.shellToast('Paper trade opened.');window.location.reload();}catch(err){window.shellToast(err.message,'error');}finally{b.disabled=false;}});
</script>
@endpush
