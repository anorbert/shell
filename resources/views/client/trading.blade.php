@extends('layouts.app')
@section('title','Paper trading')
@section('eyebrow','FIXED-TIME OPTIONS SIMULATOR')
@section('heading','Trading terminal')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Simulation only — no real money</strong><p>Credits are non-withdrawable and have no cash value. The chart is provider-sourced market data; trade opening and settlement use server-captured quotes.</p></div><span class="mode-tag">PAPER ONLY</span></div>
<div class="trading-layout">
<section class="panel trading-chart-panel">
<div class="panel-head"><div><span class="eyebrow">BINANCE SPOT · OHLCV</span><h2 id="chart-symbol">{{ $symbols[0] ?? 'BTCUSDT' }}</h2></div><span class="table-status verified" id="chart-health">LOADING</span></div>
<div class="chart-toolbar"><label>Market<select id="chart-market">@foreach($symbols as $symbol)<option value="{{ $symbol }}">{{ $symbol }}</option>@endforeach</select></label><div class="chart-intervals">@foreach(['1m','5m','15m','1h','4h','1d'] as $interval)<button type="button" data-interval="{{ $interval }}" class="{{ $interval==='1m'?'active':'' }}">{{ $interval }}</button>@endforeach</div></div>
<div class="market-chart-wrap"><canvas id="market-candles" aria-label="Candlestick market chart"></canvas></div>
<div class="chart-foot"><span id="chart-last-price">Waiting for market data</span><span id="chart-updated">Chart data is informational</span></div>
<p class="muted">Candles are fetched directly from Binance Spot for visual context. The simulator independently validates a fresh server-side quote when opening and settling each position. Chart prices are not guaranteed execution prices.</p>
</section>
<section class="panel trade-ticket">
<div class="panel-head"><div><span class="eyebrow">NEW POSITION</span><h2>Trade ticket</h2></div><span class="table-status verified">DEMO</span></div>
<div class="wallet-summary"><span>AVAILABLE DEMO BALANCE</span><strong>{{ $balance }} {{ $currency }}</strong></div>
<form id="paper-trade-form" class="form-stack">
<input type="hidden" name="request_key" value="{{ \Illuminate\Support\Str::uuid() }}">
<label>Market pair<select name="symbol" id="trade-symbol" required>@foreach($symbols as $symbol)<option value="{{ $symbol }}">{{ $symbol }}</option>@endforeach</select></label>
<label>Stake ({{ $currency }})<input id="trade-stake" name="stake" type="number" min="0.00000001" max="{{ config('shell.paper.max_stake') }}" step="0.01" value="10" required></label>
<div class="stake-presets"><button type="button" data-stake="10">$10</button><button type="button" data-stake="25">$25</button><button type="button" data-stake="50">$50</button><button type="button" data-stake="100">$100</button></div>
<label>Expiry duration<select name="duration">@foreach($durations as $duration)<option value="{{ $duration }}">{{ $duration>=3600?($duration/3600).' hour':($duration/60).' minutes' }}</option>@endforeach</select></label>
<div class="payout-preview"><span>Configured payout rate</span><strong>{{ $payoutPercentage }}%</strong><small>Potential total return: <b id="potential-return">—</b> {{ $currency }} including stake, if the prediction wins.</small></div>
<div class="direction-buttons"><button class="direction-up" type="submit" data-direction="UP">↗ UP <small>Price rises</small></button><button class="direction-down" type="submit" data-direction="DOWN">↘ DOWN <small>Price falls</small></button></div>
<input type="hidden" name="direction" id="trade-direction" value="UP">
<p class="muted" id="trade-feedback" role="status">Select a direction to preview and open a simulated position.</p>
</form>
</section>
</div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">YOUR POSITIONS</span><h2>Trade history</h2></div><a class="button button-secondary" href="{{ route('activity.index') }}"><svg class="nav-icon"><use href="#i-clock"/></svg> View all trade history</a></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPEN PRICE</th><th>EXPIRY</th><th>TIME LEFT</th><th>STATUS</th></tr></thead><tbody>
@forelse($trades as $trade)<tr><td><strong>{{ $trade->symbol }}</strong></td><td>{{ $trade->direction }}</td><td>{{ $trade->stake }} {{ $trade->wallet_currency ?? $currency }}</td><td>{{ $trade->opening_price }}</td><td>{{ $trade->expires_at }}</td><td>@if($trade->status==='open')<span class="trade-countdown" data-expiry="{{ strtotime($trade->expires_at) * 1000 }}">Calculating…</span>@else — @endif</td><td><span class="table-status {{ $trade->status==='open'?'pending':'verified' }}">{{ strtoupper($trade->status) }}</span></td></tr>@empty<tr><td colspan="7" class="table-empty">No paper trades yet. Your first position will appear here.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $trades->links() }}</div></section>
@endsection
@push('scripts')
<script>
const chartCanvas=document.getElementById('market-candles');
const chartContext=chartCanvas.getContext('2d');
const chartMarket=document.getElementById('chart-market');
const tradeSymbol=document.getElementById('trade-symbol');
let chartInterval='1m';
let chartCandles=[];let chartLoading=false;
function drawCandles(){
    const ratio=window.devicePixelRatio||1;
    const width=chartCanvas.clientWidth||700;
    const height=chartCanvas.clientHeight||320;
    chartCanvas.width=Math.floor(width*ratio);chartCanvas.height=Math.floor(height*ratio);
    chartContext.setTransform(ratio,0,0,ratio,0,0);
    chartContext.clearRect(0,0,width,height);
    chartContext.strokeStyle='rgba(145,160,181,.13)';chartContext.lineWidth=1;
    for(let i=1;i<5;i++){const y=height*i/5;chartContext.beginPath();chartContext.moveTo(0,y);chartContext.lineTo(width,y);chartContext.stroke();}
    if(!chartCandles.length){chartContext.fillStyle='#9aa8bb';chartContext.font='12px sans-serif';chartContext.fillText('Waiting for verified provider candles…',18,28);return;}
    const values=chartCandles.flatMap(c=>[Number(c.high),Number(c.low)]);
    const max=Math.max(...values),min=Math.min(...values),range=max-min||1;
    const y=value=>height-18-((value-min)/range)*(height-36);
    const step=width/chartCandles.length,body=Math.max(2,Math.min(10,step*.62));
    chartCandles.forEach((c,i)=>{
        const x=i*step+step/2,open=Number(c.open),close=Number(c.close),up=close>=open;
        chartContext.strokeStyle=up?'#42c99a':'#ef7180';chartContext.fillStyle=chartContext.strokeStyle;
        chartContext.beginPath();chartContext.moveTo(x,y(Number(c.high)));chartContext.lineTo(x,y(Number(c.low)));chartContext.stroke();
        const top=Math.min(y(open),y(close)),bottom=Math.max(y(open),y(close));
        chartContext.fillRect(x-body/2,top,body,Math.max(1,bottom-top));
    });
    const last=chartCandles[chartCandles.length-1];
    document.getElementById('chart-last-price').textContent='Last candle close: '+last.close+' USDT';
}
async function loadCandles(){
    if(chartLoading)return;
    chartLoading=true;
    const symbol=chartMarket.value;
    document.getElementById('chart-symbol').textContent=symbol;
    document.getElementById('chart-health').textContent='LOADING';
    try{
        const result=await window.shellAjax(@json(route('markets.candles'))+'?'+new URLSearchParams({symbol:symbol,interval:chartInterval,limit:'100'}));
        chartCandles=result.candles;drawCandles();
        document.getElementById('chart-health').textContent='LIVE DATA';
        document.getElementById('chart-updated').textContent='Updated '+new Date().toLocaleTimeString();
    }catch(error){
        chartCandles=[];drawCandles();document.getElementById('chart-health').textContent='UNAVAILABLE';
        document.getElementById('chart-updated').textContent=error.message;
    }finally{chartLoading=false;}
}
chartMarket.addEventListener('change',()=>{tradeSymbol.value=chartMarket.value;loadCandles();});
tradeSymbol.addEventListener('change',()=>{chartMarket.value=tradeSymbol.value;loadCandles();});
document.querySelectorAll('[data-interval]').forEach(button=>button.addEventListener('click',()=>{
    chartInterval=button.dataset.interval;
    document.querySelectorAll('[data-interval]').forEach(item=>item.classList.toggle('active',item===button));
    loadCandles();
}));
window.addEventListener('resize',drawCandles);
loadCandles();
window.setInterval(loadCandles,30000);
function updateTradeCountdowns(){
    const now=Date.now();
    document.querySelectorAll('[data-expiry]').forEach(element=>{
        const seconds=Math.max(0,Math.ceil((Number(element.dataset.expiry)-now)/1000));
        if(seconds===0){element.textContent='Awaiting settlement';return;}
        const minutes=Math.floor(seconds/60),remaining=seconds%60;
        element.textContent=minutes+'m '+String(remaining).padStart(2,'0')+'s';
    });
}
updateTradeCountdowns();window.setInterval(updateTradeCountdowns,1000);
const stakeInput=document.getElementById('trade-stake');
const payoutRate={{ (int) config('shell.paper.payout_basis_points',18000) / 10000 }};
function updatePayout(){const stake=Math.max(0,Number(stakeInput.value)||0);document.getElementById('potential-return').textContent=(stake*payoutRate).toFixed(2);}
stakeInput.addEventListener('input',updatePayout);updatePayout();
document.querySelectorAll('[data-stake]').forEach(button=>button.addEventListener('click',()=>{stakeInput.value=button.dataset.stake;updatePayout();}));
document.querySelectorAll('[data-direction]').forEach(button=>button.addEventListener('click',()=>{document.getElementById('trade-direction').value=button.dataset.direction;}));
document.getElementById('paper-trade-form').addEventListener('submit',async event=>{
    event.preventDefault();
    const form=event.currentTarget;
    const submitter=event.submitter;
    if(submitter?.dataset.direction)document.getElementById('trade-direction').value=submitter.dataset.direction;
    const buttons=form.querySelectorAll('button[type=submit]');
    buttons.forEach(button=>button.disabled=true);
    const feedback=document.getElementById('trade-feedback');
    feedback.textContent='Validating fresh market price and reserving demo stake…';
    try{
        const payload=Object.fromEntries(new FormData(form).entries());
        const result=await window.shellAjax(@json(route('trading.store')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        feedback.textContent=result.message;
        window.shellToast(result.message);window.location.reload();
    }catch(error){feedback.textContent=error.message;window.shellToast(error.message,'error');}
    finally{buttons.forEach(button=>button.disabled=false);}
});
</script>
@endpush
