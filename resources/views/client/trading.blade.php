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
        <div class="chart-intervals" role="group" aria-label="Chart timeframe">@foreach(['15m','1m','5m','1h','4h','1d'] as $interval)<button type="button" data-interval="{{ $interval }}" class="{{ $interval==='15m'?'active':'' }}" aria-pressed="{{ $interval==='15m'?'true':'false' }}">{{ $interval }}</button>@endforeach</div>
    </div><div class="chart-zoom" role="group" aria-label="Chart zoom"><button type="button" id="chart-zoom-out" aria-label="Zoom out">−</button><span id="chart-zoom-level">80 candles</span><button type="button" id="chart-zoom-in" aria-label="Zoom in">+</button><button type="button" id="chart-zoom-reset">Reset</button></div>
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
        <label>Stake ({{ $currency }})<input id="trade-stake" name="stake" type="number" inputmode="decimal" min="0.00000001" max="{{ config('shell.paper.max_stake') }}" step="0.00000001" value="10" required></label>
        <div class="stake-presets"><button type="button" data-stake="10">$10</button><button type="button" data-stake="25">$25</button><button type="button" data-stake="50">$50</button><button type="button" data-stake="100">$100</button></div>
        <label>Expiry duration<select name="duration">@foreach($durations as $duration)<option value="{{ $duration }}">{{ $duration>=3600?($duration/3600).' hour':($duration/60).' minutes' }}</option>@endforeach</select></label>
        <div class="payout-preview"><span>Potential total payout</span><strong><b id="potential-return">—</b> {{ $currency }}</strong><small>Estimated amount returned if this position wins, including your stake. A loss forfeits the stake; a tie returns the stake.</small></div>
        <details class="trade-process-hint"><summary>How this trade works <span>Quick guide</span></summary><ol><li>Choose the market, stake and expiry.</li><li>Select UP if you expect the price to rise, or DOWN if you expect it to fall.</li><li>We verify a fresh server price and reserve the stake from your demo wallet.</li><li>At expiry, the position settles against the next eligible server-captured quote. Your result appears in Trade history.</li></ol></details>
        <div class="direction-buttons"><button class="direction-up" type="submit" data-direction="UP">↗ UP <small>Price rises</small></button><button class="direction-down" type="submit" data-direction="DOWN">↘ DOWN <small>Price falls</small></button></div>
        <input type="hidden" name="direction" id="trade-direction" value="UP">
        <p class="muted" id="trade-feedback" role="status" aria-live="polite"></p>
    </form>
</section>
</div>

<section class="panel"><div class="panel-head"><div><span class="eyebrow">YOUR POSITIONS</span><h2>Trade history</h2></div><a class="button button-secondary" href="{{ route('activity.index') }}"><svg class="nav-icon"><use href="#i-clock"/></svg> View all trade history</a><a class="button button-secondary" href="{{ route('portfolio.index') }}"><svg class="nav-icon"><use href="#i-briefcase"/></svg> View portfolio</a></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPEN PRICE</th><th>CLOSING PRICE</th><th>EXPIRY</th><th>TIME LEFT</th><th>RESULT</th></tr></thead><tbody>
@forelse($trades as $trade)<tr @if($trade->status==='open')data-open-trade="{{ $trade->id }}"@endif><td><strong>{{ $trade->symbol }}</strong></td><td>{{ $trade->direction }}</td><td>@money($trade->stake) {{ $trade->wallet_currency ?? $currency }}</td><td>@decimal($trade->opening_price)</td><td class="trade-closing-price">{{ $trade->closing_price===null?'—':\App\Support\NumberFormatter::decimal($trade->closing_price) }}</td><td>{{ $trade->expires_at }}</td><td>@if($trade->status==='open')<span class="trade-countdown" data-expiry="{{ strtotime($trade->expires_at) * 1000 }}">Calculating…</span>@else <span class="muted">Settled</span> @endif</td><td><span class="table-status {{ $trade->status==='open'?'pending':($trade->status==='won'?'verified':'') }}">{{ $trade->status==='won'?'WIN':($trade->status==='lost'?'LOSS':($trade->status==='void'?'TIE':'OPEN')) }}</span></td></tr>@empty<tr><td colspan="8" class="table-empty">No paper trades yet. Your first position will appear here.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $trades->links() }}</div></section>

<div class="position-modal" id="position-modal" role="dialog" aria-modal="true" aria-labelledby="position-modal-title" aria-hidden="true">
    <div class="position-modal-backdrop"></div>
    <section class="position-modal-card">
        <div class="position-modal-head"><div><span class="eyebrow">LIVE DEMO POSITION</span><h2 id="position-modal-title">Trade opened</h2></div><span class="table-status pending" id="position-status">OPEN</span></div>
        <div class="position-market-line"><strong id="position-symbol">—</strong><span id="position-direction">—</span></div>
        <div class="position-live-price"><span>CURRENT MARKET PRICE</span><strong id="position-current-price">Connecting to live market…</strong><small id="position-price-move">Loading your position details</small></div>
        <div class="position-details-grid">
            <div><span>Stake</span><strong id="position-stake">—</strong></div>
            <div><span>Opening price</span><strong id="position-opening-price">—</strong></div>
            <div><span>Expiry</span><strong id="position-expiry">—</strong></div>
            <div><span>Time remaining</span><strong id="position-countdown">—</strong></div>
            <div class="position-live-outcome"><span>LIVE INDICATIVE RESULT</span><strong id="position-live-outcome">Waiting for market price…</strong><small id="position-potential-payout">Potential payout: —</small></div>
        </div>
        <div class="position-result" id="position-result" aria-live="polite">Your position is being loaded. The displayed live result is indicative until the server confirms settlement.</div>
        <button type="button" class="button button-primary position-close" id="position-close" disabled>Waiting for result…</button>
    </section>
</div>

@endsection

@push('styles')
<style>
.trading-chart-panel{min-width:0;overflow:hidden}.trading-chart-heading{align-items:center}.trading-chart-heading h2{font-variant-numeric:tabular-nums;letter-spacing:.02em}
.chart-market-summary{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;padding:15px 19px 13px;border-top:1px solid #253247;border-bottom:1px solid #253247;background:#0c1523}
.chart-price-main{display:grid;gap:5px}.chart-price-main strong{font:800 clamp(22px,2.7vw,31px)/1.1 'Manrope';font-variant-numeric:tabular-nums;letter-spacing:-.035em}.chart-price-main span{font-size:11px;color:#8e9cb0}
.chart-ohlc{display:flex;gap:13px;flex-wrap:wrap;justify-content:flex-end;color:#8190a5;font-size:10px;font-variant-numeric:tabular-nums}.chart-ohlc b{color:#d9e0e9;font-weight:700;margin-left:3px}
.chart-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 17px}.chart-toolbar>label{display:flex;align-items:center;gap:9px;color:#8f9db0;font-size:11px}.chart-toolbar select{min-width:115px;border:1px solid #35445a;border-radius:6px;background:#0b1421;color:#e9edf5;padding:8px 10px;font-size:11px}
.chart-zoom{display:flex;align-items:center;justify-content:flex-end;gap:6px;padding:0 17px 10px;color:#8f9db0;font-size:10px}.chart-zoom button{border:1px solid #35445a;border-radius:5px;background:#0b1421;color:#dce4ef;padding:5px 10px;cursor:pointer}.chart-zoom button:hover{border-color:#c8a96a;color:#f0d99f}.chart-intervals{display:flex;align-items:center;gap:4px;flex-wrap:wrap}.chart-intervals button{min-width:37px;padding:7px 8px;border:1px solid transparent;border-radius:5px;background:transparent;color:#8f9db0;font-size:10px;font-weight:700;cursor:pointer}.chart-intervals button:hover,.chart-intervals button.active{border-color:#c8a96a55;background:#c8a96a18;color:#f0d99f}
.market-chart-wrap{height:390px;position:relative;min-width:0;background:#0b1421;border-top:1px solid #202d40;border-bottom:1px solid #202d40}.market-chart-wrap canvas{display:block;width:100%;height:100%;touch-action:pan-y}.chart-loading{position:absolute;inset:0;display:none;align-items:center;justify-content:center;color:#8c9bb0;font-size:12px;pointer-events:none;background:#0b1421aa}.chart-loading.visible{display:flex}
.chart-foot{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 17px;color:#7e8da2;font-size:10px;font-variant-numeric:tabular-nums}
.trade-process-hint{padding:12px 14px;border:1px solid #29384d;border-radius:8px;background:#0b1421;color:#9aa8ba;font-size:11px;line-height:1.55}.trade-process-hint summary{display:flex;justify-content:space-between;align-items:center;gap:12px;color:#e5ebf3;font-weight:700;cursor:pointer;list-style:none}.trade-process-hint summary::-webkit-details-marker{display:none}.trade-process-hint summary:after{content:'＋';color:#d5b879}.trade-process-hint[open] summary:after{content:'−'}.trade-process-hint summary span{margin-left:auto;color:#8f9db0;font-size:10px;font-weight:500}.trade-process-hint ol{margin:10px 0 0;padding-left:18px}.trade-process-hint li+li{margin-top:3px}#stake-help{display:block;line-height:1.45;margin-top:5px}
#chart-health{display:inline-flex;align-items:center;gap:5px;white-space:nowrap}#chart-health .live-dot{width:6px;height:6px;margin:0}
@media(max-width:720px){.market-chart-wrap{height:330px}.chart-market-summary{align-items:flex-start;flex-direction:column}.chart-ohlc{justify-content:flex-start;gap:8px 12px}.chart-toolbar{align-items:flex-start;flex-direction:column}.chart-intervals{width:100%;justify-content:space-between}.chart-foot{font-size:9px}}
@media(max-width:420px){.market-chart-wrap{height:290px}.chart-ohlc{font-size:9px;gap:7px}.chart-toolbar{padding-inline:12px}.chart-intervals button{min-width:32px;padding-inline:5px}}

.position-modal{display:none;position:fixed;inset:0;z-index:10000;align-items:center;justify-content:center;padding:18px}.position-modal.is-visible{display:flex}.position-modal-backdrop{position:absolute;inset:0;background:rgba(2,7,15,.82);backdrop-filter:blur(7px)}.position-modal-card{position:relative;width:min(100%,490px);max-height:calc(100vh - 36px);overflow:auto;border:1px solid #35445a;border-radius:16px;background:#101b2b;box-shadow:0 24px 90px #0009;padding:22px;color:#e8eef7}.position-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px}.position-modal-head h2{margin:5px 0 0;font-size:21px}.position-market-line{display:flex;align-items:center;gap:10px;margin:20px 0 12px}.position-market-line strong{font-size:17px}.position-market-line span{font-size:11px;font-weight:800;border-radius:5px;padding:5px 9px;background:#173a36;color:#53d6aa}.position-market-line span.down{background:#44252d;color:#ff8996}.position-live-price{padding:16px;border-radius:10px;background:#0a1422;border:1px solid #29384d;display:grid;gap:6px}.position-live-price>span,.position-details-grid span{font-size:10px;color:#91a0b4;letter-spacing:.06em}.position-live-price strong{font-size:clamp(25px,6vw,34px);font-variant-numeric:tabular-nums}.position-live-price strong{transition:color .15s ease,text-shadow .15s ease}.position-live-price small{font-size:11px;color:#9aa9bc}.position-live-outcome{grid-column:1/-1;display:grid;gap:6px;padding:14px 16px;border:1px solid #34445a;border-radius:10px;background:#0b1625}.position-live-outcome>span{font-size:10px;letter-spacing:.09em;color:#91a0b4;font-weight:800}.position-live-outcome strong{font-size:25px;font-weight:850;font-variant-numeric:tabular-nums;color:#dce5f2;transition:color .15s ease}.position-live-outcome small{font-size:11px;color:#9aa9bc}.position-live-outcome.is-win{border-color:#238e7055;background:#0d241f}.position-live-outcome.is-win strong{color:#53d6aa}.position-live-outcome.is-loss{border-color:#b34f5b66;background:#28171d}.position-live-outcome.is-loss strong{color:#ff8996}.position-market-line{font-variant-numeric:tabular-nums}.position-modal-card{max-height:calc(100vh - 32px);overflow:auto}@media(max-width:520px){.position-modal-card{width:calc(100vw - 24px);padding:16px}.position-details-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.position-live-outcome{grid-column:1/-1}}.position-details-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:13px 0}.position-details-grid>div{display:grid;gap:6px;padding:12px;border:1px solid #29384d;border-radius:8px}.position-details-grid strong{font-size:13px;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}.position-result{padding:12px;border-radius:8px;background:#19283a;color:#c2cede;font-size:12px;line-height:1.5}.position-result.is-win{background:#12382f;color:#7de0bd}.position-result.is-loss{background:#42232c;color:#ff9aa5}.position-close{width:100%;margin-top:14px}.position-close:disabled{opacity:.55;cursor:not-allowed}@media(max-width:420px){.position-modal-card{padding:17px}.position-details-grid{gap:7px}.position-details-grid>div{padding:10px}}
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
    let interval = '15m', candles = [], loadingRequest = false, failed = false, crosshair = null, visibleCandles = 80;
    let plot = null, liveSocket = null, reconnectTimer = null, retryTimer = null, reconnectAttempts = 0, streamGeneration = 0;

    const requestJson = async (url, options = {}) => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 8000);
        try {
            const response = await fetch(url, { ...options, signal:controller.signal, headers: {
                Accept:'application/json','X-Requested-With':'XMLHttpRequest',
                ...(csrf?{'X-CSRF-TOKEN':csrf}:{}), ...(options.headers||{})
            }});
            const payload = await response.json().catch(()=>({}));
            if(!response.ok) throw new Error(payload.message || 'The request could not be completed.');
            return payload;
        } catch(error) {
            if(error.name === 'AbortError') throw new Error('The server is taking too long to respond. Retrying automatically…');
            throw error;
        } finally {
            window.clearTimeout(timeout);
        }
    };
    const notify = (message,type='success') => {
        if(typeof window.shellToast==='function'){window.shellToast(message,type);return;}
        const region=document.getElementById('toast-region');
        if(region){const item=document.createElement('div');item.className='toast toast-'+type;item.textContent=message;region.appendChild(item);window.setTimeout(()=>item.remove(),4200);}
    };

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

        const visible = candles.slice(-visibleCandles); 
        const values=visible.flatMap(c=>[number(c.high),number(c.low)]).filter(Number.isFinite);
        let min=Math.min(...values),max=Math.max(...values);
        const rawRange=max-min || Math.max(Math.abs(max)*.001,1e-8);
        const padding=rawRange*.09;min-=padding;max+=padding;
        const range=max-min;
        const y=value=>bottom-((value-min)/range)*chartHeight;
        const step=chartWidth/visible.length;
        const bodyWidth=Math.max(2,Math.min(13,step*.64));

        // Horizontal price grid and right-hand price scale.
        ctx.font='10px DM Sans, sans-serif';ctx.textAlign='left';ctx.textBaseline='middle';
        for(let i=0;i<=5;i++){
            const value=max-(range*i/5),py=top+(chartHeight*i/5);
            ctx.strokeStyle=palette.grid;ctx.lineWidth=1;ctx.beginPath();ctx.moveTo(left,py+.5);ctx.lineTo(right,py+.5);ctx.stroke();
            ctx.fillStyle=palette.text;ctx.fillText(formatAxis(value),right+10,py);
        }
        // Vertical time grid and time labels.
        const timeCount=Math.min(6,visible.length);
        ctx.textAlign='center';ctx.textBaseline='top';
        for(let i=0;i<timeCount;i++){
            const index=Math.round(i*(visible.length-1)/Math.max(1,timeCount-1));
            const x=left+index*step+step/2;
            ctx.strokeStyle=palette.grid;ctx.beginPath();ctx.moveTo(x,top);ctx.lineTo(x,bottom);ctx.stroke();
            ctx.fillStyle=palette.text;ctx.fillText(formatTime(visible[index].time),x,bottom+9);
        }
        // Candlesticks.
        visible.forEach((c,i)=>{
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

    function closeLiveStream() {
        streamGeneration++;
        if (reconnectTimer) { window.clearTimeout(reconnectTimer); reconnectTimer = null; }
        if (retryTimer) { window.clearTimeout(retryTimer); retryTimer = null; }
        if (liveSocket) {
            liveSocket.onopen = liveSocket.onmessage = liveSocket.onerror = liveSocket.onclose = null;
            try { liveSocket.close(); } catch (_) {}
            liveSocket = null;
        }
    }

    function applyLiveCandle(kline) {
        const candle = {
            time: number(kline.t), open: String(kline.o), high: String(kline.h),
            low: String(kline.l), close: String(kline.c), volume: String(kline.v)
        };
        if (![candle.time,candle.open,candle.high,candle.low,candle.close].every(v => Number.isFinite(number(v)))) return;
        const last = candles[candles.length - 1];
        if (last && candle.time < last.time) return;
        if (last && candle.time === last.time) candles[candles.length - 1] = candle;
        else {
            candles.push(candle);
            if (candles.length > 200) candles.shift();
        }
        failed = false;
        updateSummary();
        draw();
        window.dispatchEvent(new CustomEvent('shell:market-price',{detail:{symbol:marketSelect.value,price:candle.close}}));
        setText('chart-health', 'LIVE');
        setText('chart-updated', 'Live · ' + new Date().toLocaleTimeString());
    }

    function connectLiveStream(symbol, selectedInterval, generation, hostIndex = 0) {
        if (generation !== streamGeneration) return;
        const streamKey = symbol.toLowerCase() + '@kline_' + selectedInterval;
        const hosts = [
            'wss://stream.binance.com:443/ws/',
            'wss://data-stream.binance.vision:443/ws/'
        ];
        setText('chart-health', 'CONNECTING');
        let socket;
        try {
            socket = new WebSocket(hosts[hostIndex] + streamKey);
            liveSocket = socket;
        } catch (_) {
            if (hostIndex + 1 < hosts.length) {
                connectLiveStream(symbol, selectedInterval, generation, hostIndex + 1);
            } else {
                scheduleStreamReconnect(symbol, selectedInterval, generation);
            }
            return;
        }

        socket.onopen = () => {
            if (generation !== streamGeneration) { socket.close(); return; }
            reconnectAttempts = 0;
            setText('chart-health', 'LIVE');
            setText('chart-updated', 'Live stream connected');
        };
        socket.onmessage = event => {
            if (generation !== streamGeneration) return;
            try {
                const message = JSON.parse(event.data);
                if (message.k) applyLiveCandle(message.k);
            } catch (_) {
                // Ignore malformed stream messages; the next valid candle update will recover.
            }
        };
        socket.onerror = () => {
            if (generation === streamGeneration) setText('chart-health', 'RECONNECTING');
        };
        socket.onclose = () => {
            if (generation !== streamGeneration) return;
            if (hostIndex + 1 < hosts.length) {
                connectLiveStream(symbol, selectedInterval, generation, hostIndex + 1);
                return;
            }
            scheduleStreamReconnect(symbol, selectedInterval, generation);
        };
    }

    function scheduleStreamReconnect(symbol, selectedInterval, generation) {
        if (generation !== streamGeneration) return;
        setText('chart-health', 'RECONNECTING');
        const delay = Math.min(30000, 1000 * Math.pow(2, Math.min(reconnectAttempts, 5)));
        reconnectAttempts++;
        reconnectTimer = window.setTimeout(() => connectLiveStream(symbol, selectedInterval, generation, 0), delay);
    }

    async function fetchPublicCandles(symbol, selectedInterval) {
        const query = new URLSearchParams({symbol, interval:selectedInterval, limit:'200'});
        const endpoints = [
            'https://data-api.binance.vision/api/v3/klines?' + query,
            'https://api.binance.com/api/v3/klines?' + query
        ];
        let lastError = null;
        for (const endpoint of endpoints) {
            try {
                const controller = new AbortController();
                const timeout = window.setTimeout(() => controller.abort(), 6000);
                let response;
                try {
                    response = await fetch(endpoint, {headers:{'Accept':'application/json'}, cache:'no-store', signal:controller.signal});
                } finally {
                    window.clearTimeout(timeout);
                }
                if (!response.ok) throw new Error('Public market feed returned HTTP ' + response.status);
                const rows = await response.json();
                if (!Array.isArray(rows) || !rows.length) throw new Error('Public market feed returned no candles.');
                const parsed = rows.filter(row => Array.isArray(row) && row.length >= 6).map(row => ({
                    time:Number(row[0]), open:String(row[1]), high:String(row[2]),
                    low:String(row[3]), close:String(row[4]), volume:String(row[5])
                })).filter(c => [c.time,c.open,c.high,c.low,c.close].every(v => Number.isFinite(Number(v))));
                if (parsed.length) return parsed;
                throw new Error('Public market feed returned invalid candle data.');
            } catch (error) {
                lastError = error;
            }
        }
        throw lastError || new Error('No public market feed is available.');
    }

    async function loadCandles() {
        closeLiveStream();
        const generation = streamGeneration;
        const symbol = marketSelect.value;
        const selectedInterval = interval;
        loadingRequest = true;
        failed = false;
        loading.textContent = 'Loading market data…';
        loading.classList.add('visible');
        setText('chart-health', 'LOADING');
        setText('chart-symbol', symbol);
        try {
            let candleRows = null;
            try {
                const result = await requestJson(@json(route('markets.candles')) + '?' + new URLSearchParams({symbol,interval:selectedInterval,limit:'200'}));
                if (Array.isArray(result.candles) && result.candles.length) candleRows = result.candles;
            } catch (_) {
                // Fall through to the public read-only feed if the Laravel market provider fails.
            }
            if (generation !== streamGeneration) return;
            // A redirected HTML response or an empty JSON response must also trigger fallback.
            if (!Array.isArray(candleRows) || !candleRows.length) {
                candleRows = await fetchPublicCandles(symbol, selectedInterval);
            }
            if (generation !== streamGeneration) return;
            candles = candleRows.map(c => ({...c,time:number(c.time)}))
                .filter(c => [c.time,c.open,c.high,c.low,c.close].every(v => Number.isFinite(number(v))));
            if (!candles.length) throw new Error('The market returned invalid candle data.');
            updateSummary();
            draw();
            const initialCandle=candles[candles.length-1];
            if(initialCandle)window.dispatchEvent(new CustomEvent('shell:market-price',{detail:{symbol,price:initialCandle.close}}));
            setText('chart-health', 'CONNECTING');
            setText('chart-updated', 'Opening live market stream…');
            connectLiveStream(symbol, selectedInterval, generation);
        } catch(error) {
            if (generation !== streamGeneration) return;
            failed = true;
            if (!candles.length) draw();
            setText('chart-health', 'RETRYING');
            setText('chart-updated', error.message || 'Market data unavailable');
            loading.textContent = 'Market feed unavailable · reconnecting automatically…';
            // Keep the chart from remaining permanently blank after a temporary provider outage.
            retryTimer = window.setTimeout(() => {
                if (generation === streamGeneration) loadCandles();
            }, 5000);
        } finally {
            if (generation === streamGeneration) {
                loadingRequest = false;
                loading.classList.remove('visible');
            }
        }
    }

    function updateZoomLabel(){setText('chart-zoom-level',visibleCandles+' candles');} 
    document.getElementById('chart-zoom-in').addEventListener('click',()=>{visibleCandles=Math.max(20,visibleCandles-10);updateZoomLabel();draw();}); 
    document.getElementById('chart-zoom-out').addEventListener('click',()=>{visibleCandles=Math.min(200,visibleCandles+10);updateZoomLabel();draw();}); 
    document.getElementById('chart-zoom-reset').addEventListener('click',()=>{visibleCandles=80;updateZoomLabel();draw();}); 
    updateZoomLabel(); 
 
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
    draw(); // Paint the chart frame immediately, before the first network response.
    loadCandles();
    window.addEventListener('beforeunload', closeLiveStream);

    function updateTradeCountdowns(){
        const now=Date.now();
        document.querySelectorAll('[data-expiry]').forEach(element=>{
            const seconds=Math.max(0,Math.ceil((Number(element.dataset.expiry)-now)/1000));
            if(seconds===0){element.textContent='Awaiting settlement';return;}
            element.textContent=Math.floor(seconds/60)+'m '+String(seconds%60).padStart(2,'0')+'s';
        });
    }
    updateTradeCountdowns();window.setInterval(updateTradeCountdowns,1000);

    // Reconcile visible open rows with the authoritative server settlement state.
    // A row is never marked won/lost from the browser's live chart price.
    let settlementRefreshPending=false;
    async function reconcileOpenTrades(){
        const rows=[...document.querySelectorAll('[data-open-trade]')];
        for(const row of rows){
            if(settlementRefreshPending) return;
            try{
                const id=row.dataset.openTrade;
                const response=await requestJson(@json(url('/trading/paper'))+'/'+id,{method:'GET',headers:{'Accept':'application/json'}});
                const trade=response.trade;
                if(trade.status!=='open'){
                    settlementRefreshPending=true;
                    window.location.reload(); // refreshes closing price, result and wallet balance from server
                    return;
                }
            }catch(error){
                // Keep the row visibly pending; the next cycle retries without fabricating a result.
            }
        }
    }
    if(document.querySelector('[data-open-trade]')) window.setInterval(reconcileOpenTrades,5000);
    const stakeInput=document.getElementById('trade-stake');
    const tradeForm=document.getElementById('paper-trade-form');
    const payoutRate={{ (int) config('shell.paper.payout_basis_points',18000) / 10000 }};
    function updatePayout(){const stake=Math.max(0,Number(stakeInput.value)||0);document.getElementById('potential-return').textContent=new Intl.NumberFormat(undefined,{maximumFractionDigits:2}).format(stake*payoutRate);}
    stakeInput.addEventListener('input',updatePayout);updatePayout();
    document.querySelectorAll('[data-stake]').forEach(button=>button.addEventListener('click',()=>{stakeInput.value=button.dataset.stake;updatePayout();}));
    document.querySelectorAll('[data-direction]').forEach(button=>button.addEventListener('click',()=>{document.getElementById('trade-direction').value=button.dataset.direction;}));

    const positionModal=document.getElementById('position-modal');
    let activePosition=null, positionPollTimer=null, positionClockTimer=null, positionRetryTimer=null, latestStreamPrice=null;
    const money=(value,maximum=8)=>new Intl.NumberFormat(undefined,{minimumFractionDigits:2,maximumFractionDigits:maximum}).format(Number(value)||0);
    const priceText=value=>{const n=Number(value);return Number.isFinite(n)?new Intl.NumberFormat(undefined,{maximumFractionDigits:n>=1000?2:n>=1?4:8}).format(n):'—';};
    function setPositionText(id,value){const el=document.getElementById(id);if(el)el.textContent=value;}
    function updatePositionPrice(marketPrice){
        if(!activePosition||activePosition.status!=='open'||!Number.isFinite(Number(marketPrice)))return;
        latestStreamPrice=Number(marketPrice);
        const opening=Number(activePosition.opening_price),delta=latestStreamPrice-opening;
        const favorable=activePosition.direction==='UP'?delta>0:delta<0;
        const stake=Number(activePosition.stake)||0;
        const totalPayout=stake*payoutRate;
        const indicative=favorable?totalPayout-stake:(delta===0?0:-stake);
        const priceElement=document.getElementById('position-current-price');
        priceElement.textContent=priceText(latestStreamPrice);
        priceElement.style.color=delta===0?'#dce5f2':favorable?'#53d6aa':'#ff8996';
        priceElement.style.textShadow=delta===0?'none':favorable?'0 0 22px #22b78b44':'0 0 22px #ed647444';
        const move=document.getElementById('position-price-move');
        move.textContent=(delta>0?'+':'')+priceText(delta)+'  ('+(delta>0?'+':'')+(opening?(delta/opening*100).toFixed(4):'0.0000')+'%)  ·  '+(delta===0?'AT OPEN':favorable?'PRICE FAVOURS YOUR '+activePosition.direction:'PRICE MOVING AGAINST '+activePosition.direction);
        move.style.color=delta===0?'#aab8c9':favorable?'#53d6aa':'#ff8996';
        move.style.fontWeight='800';
        const outcome=document.getElementById('position-live-outcome');
        const outcomeWrap=outcome.closest('.position-live-outcome');
        outcome.textContent=(indicative>0?'+':'')+money(indicative)+' '+activePosition.currency+(delta===0?' · AT OPEN':favorable?' · INDICATIVE PROFIT':' · INDICATIVE LOSS');
        outcomeWrap.classList.toggle('is-win',favorable);
        outcomeWrap.classList.toggle('is-loss',delta!==0&&!favorable);
        setPositionText('position-potential-payout','Potential total payout if settled as a win: '+money(totalPayout)+' '+activePosition.currency);
    }
    window.addEventListener('shell:market-price',event=>{if(activePosition&&event.detail?.symbol===activePosition.symbol)updatePositionPrice(event.detail.price);});
    function updatePositionClock(){
        if(!activePosition)return;
        const left=Math.max(0,Math.ceil((new Date(activePosition.expires_at).getTime()-Date.now())/1000));
        setPositionText('position-countdown',left>0?Math.floor(left/60)+'m '+String(left%60).padStart(2,'0')+'s':'Expiry reached · settling');
    }
    async function pollPosition(){
        if(!activePosition)return;
        try{
            const data=await requestJson(@json(url('/trading/paper'))+'/'+activePosition.id,{method:'GET',headers:{'Accept':'application/json'}});
            const trade=data.trade;
            activePosition=trade;
            setPositionText('position-status',trade.status.toUpperCase());
            if(trade.status==='open'){
                return;
            }
            if(positionPollTimer){window.clearInterval(positionPollTimer);positionPollTimer=null;}
            if(positionClockTimer){window.clearInterval(positionClockTimer);positionClockTimer=null;}
            setPositionText('position-current-price',priceText(trade.closing_price));
            setPositionText('position-price-move','Confirmed settlement price');
            const result=document.getElementById('position-result');
            const status=trade.status.toUpperCase();
            result.textContent=status==='WON'?'WIN — Your UP/DOWN prediction was correct. Demo payout has been settled to your demo wallet.':status==='LOST'?'LOSS — The market closed against your prediction. The demo stake was lost.':'TIE — The closing price matched the opening price. Your demo stake was returned.';
            result.classList.toggle('is-win',status==='WON');result.classList.toggle('is-loss',status==='LOST');
            setPositionText('position-countdown','Settled');
            setPositionText('position-close','Close result');
            document.getElementById('position-close').disabled=false;
            notify('Trade settled: '+status);
        }catch(error){setPositionText('position-result','Connection interrupted. This position remains recorded. Reconnecting to retrieve the confirmed result…');}
    }
    async function openPositionModal(id){
        const modal=positionModal;
        modal.classList.add('is-visible');modal.setAttribute('aria-hidden','false');
        document.getElementById('position-close').disabled=true;
        setPositionText('position-result','LIVE POSITION · Waiting for the first market update…');
        setPositionText('position-close','Waiting for result…');
        try{
            const data=await requestJson(@json(url('/trading/paper'))+'/'+id,{method:'GET',headers:{'Accept':'application/json'}});
            if(positionRetryTimer){window.clearTimeout(positionRetryTimer);positionRetryTimer=null;}
            activePosition=data.trade;latestStreamPrice=null;
            setPositionText('position-symbol',activePosition.symbol);
            setPositionText('position-direction',activePosition.direction+' · '+(activePosition.direction==='UP'?'PRICE RISE':'PRICE FALL'));
            const dir=document.getElementById('position-direction');dir.classList.toggle('down',activePosition.direction==='DOWN');
            setPositionText('position-stake',money(activePosition.stake)+' '+activePosition.currency);
            setPositionText('position-opening-price',priceText(activePosition.opening_price));
            setPositionText('position-live-outcome','Waiting for live quote…');
            setPositionText('position-potential-payout','Potential total payout if settled as a win: '+money(Number(activePosition.stake)*payoutRate)+' '+activePosition.currency);
            // Reuse the latest chart candle immediately; do not wait for the next websocket tick.
            if(marketSelect.value===activePosition.symbol&&candles.length){
                updatePositionPrice(candles[candles.length-1].close);
            }
            setPositionText('position-expiry',new Date(activePosition.expires_at).toLocaleTimeString());
            setPositionText('position-current-price','Waiting for live price…');
            updatePositionClock();
            if(positionPollTimer)window.clearInterval(positionPollTimer);
            if(positionClockTimer)window.clearInterval(positionClockTimer);
            positionPollTimer=window.setInterval(pollPosition,2000);
            positionClockTimer=window.setInterval(updatePositionClock,1000);
            await pollPosition();
        }catch(error){
            setPositionText('position-result',(error.message||'Connection error')+' Your trade remains recorded. We will retry automatically.');
            setPositionText('position-current-price','Live price temporarily unavailable');
            setPositionText('position-live-outcome','Waiting for a verified server response…');
            setPositionText('position-close','Retrying position…');
            document.getElementById('position-close').disabled=true;
            if(positionRetryTimer) window.clearTimeout(positionRetryTimer);
            positionRetryTimer=window.setTimeout(()=>{
                if(positionModal.classList.contains('is-visible')) openPositionModal(id);
            },5000);
        }
    }
    document.getElementById('position-close').addEventListener('click',()=>{
        if(!activePosition||activePosition.status==='open')return;
        positionModal.classList.remove('is-visible');positionModal.setAttribute('aria-hidden','true');window.location.reload();
    });

    // Reopen the latest still-open position automatically after refresh/navigation.
    const resumableTradeId=@json($activeTradeId);
    if(resumableTradeId){openPositionModal(resumableTradeId);}

    // Expired positions are not resumed as live trades. Ask the server to settle
    // them in the background, then refresh the authoritative history/wallet.
    const hasExpiredOpenTrades=@json((bool) ($hasExpiredOpenTrades ?? false));
    if(hasExpiredOpenTrades){
        requestJson(@json(route('trading.settle-expired')),{method:'POST'})
            .then(result=>{
                if(Number(result.settled_count)>0) window.location.reload();
                else {
                    const feedback=document.getElementById('trade-feedback');
                    if(feedback) feedback.textContent='An expired position is awaiting a verified post-expiry market price. It will be retried automatically; no result has been guessed.';
                }
            })
            .catch(()=>{
                const feedback=document.getElementById('trade-feedback');
                if(feedback) feedback.textContent='An expired position is awaiting settlement. The page is available; settlement will retry automatically.';
            });
    }

    tradeForm.addEventListener('submit',async event=>{
        event.preventDefault();
        const form=event.currentTarget,submitter=event.submitter;
        if(form.dataset.submitting==='true')return;
        const stake=Number(stakeInput.value),maxStake=Number(stakeInput.max);
        if(!Number.isFinite(stake)||stake<=0){document.getElementById('trade-feedback').textContent='Enter a valid stake greater than zero.';stakeInput.focus();return;}
        if(stake>maxStake){document.getElementById('trade-feedback').textContent='Your stake is above the maximum allowed amount of '+stakeInput.max+' {{ $currency }}.';stakeInput.focus();return;}
        if(submitter?.dataset.direction)document.getElementById('trade-direction').value=submitter.dataset.direction;
        form.dataset.submitting='true';
        const buttons=form.querySelectorAll('button[type=submit]');buttons.forEach(button=>button.disabled=true);
        const feedback=document.getElementById('trade-feedback');feedback.textContent='Validating fresh market price and reserving demo stake…';
        try{
            const payload=Object.fromEntries(new FormData(form).entries());
            const result=await requestJson(@json(route('trading.store')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            feedback.textContent=result.message;notify(result.message);
            if(result.trade_id){await openPositionModal(result.trade_id);}
        }catch(error){feedback.textContent=error.message;notify(error.message,'error');}
        finally{form.dataset.submitting='false';buttons.forEach(button=>button.disabled=false);}
    });
})();
</script>
@endpush
