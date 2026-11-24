@extends('layouts.app')
@section('title','Trading terminal')
@section('eyebrow','FIXED-TIME OPTIONS SIMULATOR')
@section('heading','Trading terminal')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Demo trading is ready</strong><p>Practise fixed-time UP and DOWN positions with demo credits. Market charts provide visual context, while the platform validates server-captured quotes for trade opening and settlement.</p></div><span class="mode-tag">PAPER ONLY</span></div>

<div class="trading-layout">
<section class="panel trading-chart-panel">
    <div class="panel-head trading-chart-heading">
        <div><span class="eyebrow">MARKET CHART</span><h2 id="chart-symbol">{{ $symbols[0] ?? 'BTCUSDT' }}</h2></div>
        <span class="table-status verified" id="chart-health"><i class="live-dot"></i> CONNECTING</span>
    </div>
    <div class="chart-market-summary" aria-live="polite">
        <div class="chart-price-main"><strong id="chart-current-price">—</strong><span id="chart-price-change">Awaiting price</span></div>
        <div class="chart-ohlc">
            <span>O <b id="chart-open">—</b></span><span>H <b id="chart-high">—</b></span><span>L <b id="chart-low">—</b></span><span>C <b id="chart-close">—</b></span>
        </div>
    </div>
    <div class="chart-toolbar">
        <label>Market<select id="chart-market">@foreach($symbols as $symbol)<option value="{{ $symbol }}">{{ $symbol }}</option>@endforeach</select></label>
        <div class="chart-intervals" role="group" aria-label="Chart timeframe">@foreach(['1m','5m','15m','1h','4h','1d'] as $interval)<button type="button" data-interval="{{ $interval }}" class="{{ $interval==='1m'?'active':'' }}" aria-pressed="{{ $interval==='1m'?'true':'false' }}">{{ $interval }}</button>@endforeach</div>
    </div>
    <div class="market-chart-wrap">
        <canvas id="market-candles" aria-label="Interactive candlestick market chart with price and time scales"></canvas>
        <div class="chart-loading" id="chart-loading">Loading market data…</div>
    </div>
    <div class="chart-foot"><span id="chart-last-price">Price scale · USDT</span><span id="chart-updated">Waiting for latest update</span></div>
</section>

<section class="panel trade-ticket">
    <div class="panel-head"><div><span class="eyebrow">NEW POSITION</span><h2>Trade ticket</h2></div><span class="table-status verified">DEMO</span></div>
    <div class="wallet-summary"><span>AVAILABLE DEMO BALANCE</span><strong>@money($balance) {{ $currency }}</strong></div>
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

<section class="panel"><div class="panel-head"><div><span class="eyebrow">YOUR POSITIONS</span><h2>Trade history</h2></div><a class="button button-secondary" href="{{ route('activity.index') }}"><svg class="nav-icon"><use href="#i-clock"/></svg> View all trade history</a><a class="button button-secondary" href="{{ route('portfolio.index') }}"><svg class="nav-icon"><use href="#i-briefcase"/></svg> View portfolio</a></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPEN PRICE</th><th>EXPIRY</th><th>TIME LEFT</th><th>STATUS</th></tr></thead><tbody>
@forelse($trades as $trade)<tr><td><strong>{{ $trade->symbol }}</strong></td><td>{{ $trade->direction }}</td><td>@money($trade->stake) {{ $trade->wallet_currency ?? $currency }}</td><td>@decimal($trade->opening_price)</td><td>{{ $trade->expires_at }}</td><td>@if($trade->status==='open')<span class="trade-countdown" data-expiry="{{ strtotime($trade->expires_at) * 1000 }}">Calculating…</span>@else — @endif</td><td><span class="table-status {{ $trade->status==='open'?'pending':'verified' }}">{{ strtoupper($trade->status) }}</span></td></tr>@empty<tr><td colspan="7" class="table-empty">No paper trades yet. Your first position will appear here.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $trades->links() }}</div></section>
@endsection

@push('scripts')
<style>
.trading-chart-panel{min-width:0;overflow:hidden}.trading-chart-heading{align-items:center}.trading-chart-heading h2{font-variant-numeric:tabular-nums;letter-spacing:.02em}
.chart-market-summary{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;padding:15px 19px 13px;border-top:1px solid #253247;border-bottom:1px solid #253247;background:#0c1523}
.chart-price-main{display:grid;gap:5px}.chart-price-main strong{font:800 clamp(22px,2.7vw,31px)/1.1 'Manrope';font-variant-numeric:tabular-nums;letter-spacing:-.035em}.chart-price-main span{font-size:11px;color:#8e9cb0}
.chart-ohlc{display:flex;gap:13px;flex-wrap:wrap;justify-content:flex-end;color:#8190a5;font-size:10px;font-variant-numeric:tabular-nums}.chart-ohlc b{color:#d9e0e9;font-weight:700;margin-left:3px}
.chart-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 17px}.chart-toolbar>label{display:flex;align-items:center;gap:9px;color:#8f9db0;font-size:11px}.chart-toolbar select{min-width:115px;border:1px solid #35445a;border-radius:6px;background:#0b1421;color:#e9edf5;padding:8px 10px;font-size:11px}
.chart-intervals{display:flex;align-items:center;gap:4px;flex-wrap:wrap}.chart-intervals button{min-width:37px;padding:7px 8px;border:1px solid transparent;border-radius:5px;background:transparent;color:#8f9db0;font-size:10px;font-weight:700;cursor:pointer}.chart-intervals button:hover,.chart-intervals button.active{border-color:#c8a96a55;background:#c8a96a18;color:#f0d99f}
.market-chart-wrap{height:390px;position:relative;min-width:0;background:#0b1421;border-top:1px solid #202d40;border-bottom:1px solid #202d40}.market-chart-wrap canvas{display:block;width:100%;height:100%;touch-action:pan-y}.chart-loading{position:absolute;inset:0;display:none;align-items:center;justify-content:center;color:#8c9bb0;font-size:12px;pointer-events:none;background:#0b1421aa}.chart-loading.visible{display:flex}
.chart-foot{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 17px;color:#7e8da2;font-size:10px;font-variant-numeric:tabular-nums}
#chart-health{display:inline-flex;align-items:center;gap:5px;white-space:nowrap}#chart-health .live-dot{width:6px;height:6px;margin:0}
@media(max-width:720px){.market-chart-wrap{height:330px}.chart-market-summary{align-items:flex-start;flex-direction:column}.chart-ohlc{justify-content:flex-start;gap:8px 12px}.chart-toolbar{align-items:flex-start;flex-direction:column}.chart-intervals{width:100%;justify-content:space-between}.chart-foot{font-size:9px}}
@media(max-width:420px){.market-chart-wrap{height:290px}.chart-ohlc{font-size:9px;gap:7px}.chart-toolbar{padding-inline:12px}.chart-intervals button{min-width:32px;padding-inline:5px}}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const canvas = document.getElementById('market-candles');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const marketSelect = document.getElementById('chart-market');
    const tradeSymbol = document.getElementById('trade-symbol');
    const loading = document.getElementById('chart-loading');
    const palette = { text:'#8b9ab0', muted:'#65758b', grid:'rgba(139,154,176,.13)', border:'#26364b', up:'#22b78b', down:'#ed6474', crosshair:'rgba(190,204,222,.55)', panel:'#0b1421' };
    let interval = '1m', candles = [], loadingRequest = false, failed = false, crosshair = null, previousClose = null;
    let plot = null;

    const number = value => Number(value);
    const formatPrice = value => {
        const n = number(value);
        if (!Number.isFinite(n)) return '—';
        const decimals = n >= 1000 ? 2 : n >= 1 ? 4 : n >= .01 ? 6 : 8;
        return new Intl.NumberFormat(undefined,{minimumFractionDigits:0,maximumFractionDigits:decimals}).format(n);
    };
    const formatAxis = value => {
        const n = number(value);
        if (!Number.isFinite(n)) return '';
        const decimals = n >= 1000 ? 2 : n >= 1 ? 4 : n >= .01 ? 6 : 8;
        return new Intl.NumberFormat(undefined,{maximumFractionDigits:decimals}).format(n);
    };
    const formatTime = timestamp => {
        const date = new Date(timestamp);
        if (interval === '1d') return date.toLocaleDateString(undefined,{day:'2-digit',month:'short'});
        if (interval === '4h' || interval === '1h') return date.toLocaleString(undefined,{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'});
        return date.toLocaleTimeString(undefined,{hour:'2-digit',minute:'2-digit'});
    };
    const setText = (id,value) => { const el=document.getElementById(id); if(el) el.textContent=value; };

    function dimensions() {
        const width = canvas.clientWidth || 700, height = canvas.clientHeight || 390;
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        if (canvas.width !== Math.round(width*ratio) || canvas.height !== Math.round(height*ratio)) {
            canvas.width = Math.round(width*ratio); canvas.height = Math.round(height*ratio);
        }
        ctx.setTransform(ratio,0,0,ratio,0,0);
        return {width,height,ratio};
    }

    function draw() {
        const {width,height} = dimensions();
        ctx.clearRect(0,0,width,height);
        ctx.fillStyle=palette.panel;ctx.fillRect(0,0,width,height);
        const margin={top:17,right:88,bottom:31,left:12};
        const chartWidth=Math.max(30,width-margin.left-margin.right);
        const chartHeight=Math.max(30,height-margin.top-margin.bottom);
        const left=margin.left,right=left+chartWidth,top=margin.top,bottom=top+chartHeight;
        plot={left,right,top,bottom,width:chartWidth,height:chartHeight};
        if (!candles.length) {
            ctx.fillStyle=palette.text;ctx.font='12px DM Sans, sans-serif';ctx.textAlign='center';
            ctx.fillText(failed?'Market data is temporarily unavailable':'Waiting for market data',width/2,height/2);
            return;
        }

        const values=candles.flatMap(c=>[number(c.high),number(c.low)]).filter(Number.isFinite);
        let min=Math.min(...values),max=Math.max(...values);
        const rawRange=max-min || Math.max(Math.abs(max)*.001,1e-8);
        const padding=rawRange*.09;min-=padding;max+=padding;
        const range=max-min;
        const y=value=>bottom-((value-min)/range)*chartHeight;
        const step=chartWidth/candles.length;
        const bodyWidth=Math.max(2,Math.min(13,step*.64));

        // Horizontal price grid and right-hand price scale.
        ctx.font='10px DM Sans, sans-serif';ctx.textAlign='left';ctx.textBaseline='middle';
        for(let i=0;i<=5;i++){
            const value=max-(range*i/5),py=top+(chartHeight*i/5);
            ctx.strokeStyle=palette.grid;ctx.lineWidth=1;ctx.beginPath();ctx.moveTo(left,py+.5);ctx.lineTo(right,py+.5);ctx.stroke();
            ctx.fillStyle=palette.text;ctx.fillText(formatAxis(value),right+10,py);
        }
        // Vertical time grid and time labels.
        const timeCount=Math.min(6,candles.length);
        ctx.textAlign='center';ctx.textBaseline='top';
        for(let i=0;i<timeCount;i++){
            const index=Math.round(i*(candles.length-1)/Math.max(1,timeCount-1));
            const x=left+index*step+step/2;
            ctx.strokeStyle=palette.grid;ctx.beginPath();ctx.moveTo(x,top);ctx.lineTo(x,bottom);ctx.stroke();
            ctx.fillStyle=palette.text;ctx.fillText(formatTime(candles[index].time),x,bottom+9);
        }
        // Candlesticks.
        candles.forEach((c,i)=>{
            const open=number(c.open),close=number(c.close),high=number(c.high),low=number(c.low);
            const x=left+i*step+step/2,up=close>=open,color=up?palette.up:palette.down;
            ctx.strokeStyle=color;ctx.lineWidth=Math.max(1,Math.min(1.5,step*.13));
            ctx.beginPath();ctx.moveTo(x,y(high));ctx.lineTo(x,y(low));ctx.stroke();
            const bodyTop=y(Math.max(open,close)),bodyBottom=y(Math.min(open,close));
            ctx.fillStyle=color;ctx.fillRect(x-bodyWidth/2,bodyTop,bodyWidth,Math.max(1.5,bodyBottom-bodyTop));
        });

        const last=candles[candles.length-1],lastPrice=number(last.close),priceY=y(lastPrice);
        // Current price guide and value marker.
        ctx.save();ctx.setLineDash([4,4]);ctx.strokeStyle=last.close>=last.open?palette.up:palette.down;ctx.lineWidth=1;
        ctx.beginPath();ctx.moveTo(left,priceY);ctx.lineTo(right,priceY);ctx.stroke();ctx.restore();
        ctx.fillStyle=last.close>=last.open?palette.up:palette.down;ctx.fillRect(right+3,priceY-10,82,20);
        ctx.fillStyle='#07131b';ctx.font='bold 10px DM Sans, sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';
        ctx.fillText(formatAxis(lastPrice),right+44,priceY);

        // Crosshair follows pointer without affecting chart data.
        if(crosshair && crosshair.x>=left && crosshair.x<=right && crosshair.y>=top && crosshair.y<=bottom){
            ctx.save();ctx.strokeStyle=palette.crosshair;ctx.setLineDash([3,4]);ctx.lineWidth=1;
            ctx.beginPath();ctx.moveTo(crosshair.x,top);ctx.lineTo(crosshair.x,bottom);ctx.moveTo(left,crosshair.y);ctx.lineTo(right,crosshair.y);ctx.stroke();ctx.restore();
            const value=max-((crosshair.y-top)/chartHeight)*range;
            ctx.fillStyle='#34465d';ctx.fillRect(right+3,crosshair.y-9,82,18);
            ctx.fillStyle='#f1f4f8';ctx.font='10px DM Sans, sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(formatAxis(value),right+44,crosshair.y);
        }
    }

    function updateSummary() {
        if(!candles.length) return;
        const first=candles[0],last=candles[candles.length-1],prior=candles.length>1?candles[candles.length-2]:null;
        ['open','high','low','close'].forEach(key=>setText('chart-'+key,formatPrice(last[key])));
        setText('chart-current-price',formatPrice(last.close));
        const change=prior?((number(last.close)-number(prior.close))/number(prior.close))*100:0;
        const changeEl=document.getElementById('chart-price-change');
        changeEl.textContent=(change>=0?'+':'')+change.toFixed(2)+'% · '+interval;
        changeEl.style.color=change>=0?palette.up:palette.down;
        setText('chart-last-price','Last price '+formatPrice(last.close)+' USDT');
    }

    async function loadCandles() {
        if(loadingRequest) return;
        loadingRequest=true;failed=false;loading.classList.add('visible');
        setText('chart-health','UPDATING');
        const symbol=marketSelect.value;
        setText('chart-symbol',symbol);
        try {
            const result=await window.shellAjax(@json(route('markets.candles'))+'?'+new URLSearchParams({symbol,interval,limit:'120'}));
            if(!Array.isArray(result.candles) || !result.candles.length) throw new Error('No candle data is available for this market.');
            candles=result.candles.map(c=>({...c,time:number(c.time)})).filter(c=>[c.time,c.open,c.high,c.low,c.close].every(v=>Number.isFinite(number(v))));
            if(!candles.length) throw new Error('The market returned invalid candle data.');
            updateSummary();draw();
            setText('chart-health','LIVE');
            setText('chart-updated','Updated '+new Date().toLocaleTimeString());
        } catch(error) {
            failed=true;
            if(!candles.length) draw();
            setText('chart-health','RECONNECTING');
            setText('chart-updated',error.message || 'Market data unavailable');
        } finally {
            loadingRequest=false;loading.classList.remove('visible');
        }
    }

    marketSelect.addEventListener('change',()=>{tradeSymbol.value=marketSelect.value;candles=[];loadCandles();});
    tradeSymbol.addEventListener('change',()=>{marketSelect.value=tradeSymbol.value;candles=[];loadCandles();});
    document.querySelectorAll('[data-interval]').forEach(button=>button.addEventListener('click',()=>{
        interval=button.dataset.interval;
        document.querySelectorAll('[data-interval]').forEach(item=>{const active=item===button;item.classList.toggle('active',active);item.setAttribute('aria-pressed',active?'true':'false');});
        candles=[];loadCandles();
    }));
    canvas.addEventListener('pointermove',event=>{
        if(!plot) return;
        const rect=canvas.getBoundingClientRect();crosshair={x:event.clientX-rect.left,y:event.clientY-rect.top};draw();
    });
    canvas.addEventListener('pointerleave',()=>{crosshair=null;draw();});
    if('ResizeObserver' in window){new ResizeObserver(draw).observe(canvas.parentElement);}else{window.addEventListener('resize',draw);}
    loadCandles();
    window.setInterval(loadCandles,30000);

    function updateTradeCountdowns(){
        const now=Date.now();
        document.querySelectorAll('[data-expiry]').forEach(element=>{
            const seconds=Math.max(0,Math.ceil((Number(element.dataset.expiry)-now)/1000));
            if(seconds===0){element.textContent='Awaiting settlement';return;}
            element.textContent=Math.floor(seconds/60)+'m '+String(seconds%60).padStart(2,'0')+'s';
        });
    }
    updateTradeCountdowns();window.setInterval(updateTradeCountdowns,1000);
    const stakeInput=document.getElementById('trade-stake');
    const payoutRate={{ (int) config('shell.paper.payout_basis_points',18000) / 10000 }};
    function updatePayout(){const stake=Math.max(0,Number(stakeInput.value)||0);document.getElementById('potential-return').textContent=new Intl.NumberFormat(undefined,{maximumFractionDigits:2}).format(stake*payoutRate);}
    stakeInput.addEventListener('input',updatePayout);updatePayout();
    document.querySelectorAll('[data-stake]').forEach(button=>button.addEventListener('click',()=>{stakeInput.value=button.dataset.stake;updatePayout();}));
    document.querySelectorAll('[data-direction]').forEach(button=>button.addEventListener('click',()=>{document.getElementById('trade-direction').value=button.dataset.direction;}));
    document.getElementById('paper-trade-form').addEventListener('submit',async event=>{
        event.preventDefault();
        const form=event.currentTarget,submitter=event.submitter;
        if(submitter?.dataset.direction)document.getElementById('trade-direction').value=submitter.dataset.direction;
        const buttons=form.querySelectorAll('button[type=submit]');buttons.forEach(button=>button.disabled=true);
        const feedback=document.getElementById('trade-feedback');feedback.textContent='Validating fresh market price and reserving demo stake…';
        try{
            const payload=Object.fromEntries(new FormData(form).entries());
            const result=await window.shellAjax(@json(route('trading.store')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            feedback.textContent=result.message;window.shellToast(result.message);window.location.reload();
        }catch(error){feedback.textContent=error.message;window.shellToast(error.message,'error');}
        finally{buttons.forEach(button=>button.disabled=false);}
    });
})();
</script>
@endpush
