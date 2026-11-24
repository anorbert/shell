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
        <label>Stake ({{ $currency }})<input id="trade-stake" name="stake" type="number" inputmode="decimal" min="0.00000001" max="{{ config('shell.paper.max_stake') }}" step="0.00000001" value="10" required aria-describedby="stake-help"><small id="stake-help" class="muted">Enter an amount above zero. Maximum stake: {{ config('shell.paper.max_stake') }} {{ $currency }}. The stake is reserved from your demo balance when the trade opens.</small></label>
        <div class="stake-presets"><button type="button" data-stake="10">$10</button><button type="button" data-stake="25">$25</button><button type="button" data-stake="50">$50</button><button type="button" data-stake="100">$100</button></div>
        <label>Expiry duration<select name="duration">@foreach($durations as $duration)<option value="{{ $duration }}">{{ $duration>=3600?($duration/3600).' hour':($duration/60).' minutes' }}</option>@endforeach</select></label>
        <div class="payout-preview"><span>Configured payout rate</span><strong>{{ $payoutPercentage }}%</strong><small>Potential total return: <b id="potential-return">—</b> {{ $currency }} including stake, if the prediction wins. A losing position loses the reserved stake; a tie returns the stake.</small></div>
        <details class="trade-process-hint"><summary>How this trade works <span>Quick guide</span></summary><ol><li>Choose the market, stake and expiry.</li><li>Select UP if you expect the price to rise, or DOWN if you expect it to fall.</li><li>We verify a fresh server price and reserve the stake from your demo wallet.</li><li>At expiry, the position settles against the next eligible server-captured quote. Your result appears in Trade history.</li></ol></details>
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

<div class="position-modal" id="position-modal" role="dialog" aria-modal="true" aria-labelledby="position-modal-title" aria-hidden="true">
    <div class="position-modal-backdrop"></div>
    <section class="position-modal-card">
        <div class="position-modal-head"><div><span class="eyebrow">LIVE DEMO POSITION</span><h2 id="position-modal-title">Trade opened</h2></div><span class="table-status pending" id="position-status">OPEN</span></div>
        <div class="position-market-line"><strong id="position-symbol">—</strong><span id="position-direction">—</span></div>
        <div class="position-live-price"><span>CURRENT MARKET PRICE</span><strong id="position-current-price">Waiting for live price…</strong><small id="position-price-move">Waiting for market update</small></div>
        <div class="position-details-grid">
            <div><span>Stake</span><strong id="position-stake">—</strong></div>
            <div><span>Opening price</span><strong id="position-opening-price">—</strong></div>
            <div><span>Expiry</span><strong id="position-expiry">—</strong></div>
            <div><span>Time remaining</span><strong id="position-countdown">—</strong></div>
        </div>
        <div class="position-result" id="position-result" aria-live="polite">Your position is active. Live movement is indicative; the final result is confirmed by server settlement.</div>
        <button type="button" class="button button-primary position-close" id="position-close" disabled>Waiting for result…</button>
    </section>
</div>

@endsection

@push('scripts')
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

.position-modal{display:none;position:fixed;inset:0;z-index:10000;align-items:center;justify-content:center;padding:18px}.position-modal.is-visible{display:flex}.position-modal-backdrop{position:absolute;inset:0;background:rgba(2,7,15,.82);backdrop-filter:blur(7px)}.position-modal-card{position:relative;width:min(100%,490px);max-height:calc(100vh - 36px);overflow:auto;border:1px solid #35445a;border-radius:16px;background:#101b2b;box-shadow:0 24px 90px #0009;padding:22px;color:#e8eef7}.position-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px}.position-modal-head h2{margin:5px 0 0;font-size:21px}.position-market-line{display:flex;align-items:center;gap:10px;margin:20px 0 12px}.position-market-line strong{font-size:17px}.position-market-line span{font-size:11px;font-weight:800;border-radius:5px;padding:5px 9px;background:#173a36;color:#53d6aa}.position-market-line span.down{background:#44252d;color:#ff8996}.position-live-price{padding:16px;border-radius:10px;background:#0a1422;border:1px solid #29384d;display:grid;gap:6px}.position-live-price>span,.position-details-grid span{font-size:10px;color:#91a0b4;letter-spacing:.06em}.position-live-price strong{font-size:clamp(25px,6vw,34px);font-variant-numeric:tabular-nums}.position-live-price small{font-size:11px;color:#9aa9bc}.position-details-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:13px 0}.position-details-grid>div{display:grid;gap:6px;padding:12px;border:1px solid #29384d;border-radius:8px}.position-details-grid strong{font-size:13px;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}.position-result{padding:12px;border-radius:8px;background:#19283a;color:#c2cede;font-size:12px;line-height:1.5}.position-result.is-win{background:#12382f;color:#7de0bd}.position-result.is-loss{background:#42232c;color:#ff9aa5}.position-close{width:100%;margin-top:14px}.position-close:disabled{opacity:.55;cursor:not-allowed}@media(max-width:420px){.position-modal-card{padding:17px}.position-details-grid{gap:7px}.position-details-grid>div{padding:10px}}
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
    let plot = null, liveSocket = null, reconnectTimer = null, reconnectAttempts = 0, streamGeneration = 0;

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

    function connectLiveStream(symbol, selectedInterval, generation) {
        if (generation !== streamGeneration) return;
        const streamKey = symbol.toLowerCase() + '@kline_' + selectedInterval;
        setText('chart-health', 'CONNECTING');
        const socket = new WebSocket('wss://stream.binance.com:443/ws/' + streamKey);
        liveSocket = socket;

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
            setText('chart-health', 'RECONNECTING');
            const delay = Math.min(30000, 1000 * Math.pow(2, Math.min(reconnectAttempts, 5)));
            reconnectAttempts++;
            reconnectTimer = window.setTimeout(() => connectLiveStream(symbol, selectedInterval, generation), delay);
        };
    }

    async function loadCandles() {
        closeLiveStream();
        const generation = streamGeneration;
        const symbol = marketSelect.value;
        const selectedInterval = interval;
        loadingRequest = true;
        failed = false;
        loading.classList.add('visible');
        setText('chart-health', 'LOADING');
        setText('chart-symbol', symbol);
        try {
            const result = await window.shellAjax(@json(route('markets.candles')) + '?' + new URLSearchParams({symbol,interval:selectedInterval,limit:'200'}));
            if (generation !== streamGeneration) return;
            if (!Array.isArray(result.candles) || !result.candles.length) throw new Error('No candle data is available for this market.');
            candles = result.candles.map(c => ({...c,time:number(c.time)}))
                .filter(c => [c.time,c.open,c.high,c.low,c.close].every(v => Number.isFinite(number(v))));
            if (!candles.length) throw new Error('The market returned invalid candle data.');
            updateSummary();
            draw();
            setText('chart-health', 'CONNECTING');
            setText('chart-updated', 'Opening live market stream…');
            connectLiveStream(symbol, selectedInterval, generation);
        } catch(error) {
            if (generation !== streamGeneration) return;
            failed = true;
            if (!candles.length) draw();
            setText('chart-health', 'UNAVAILABLE');
            setText('chart-updated', error.message || 'Market data unavailable');
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
    const stakeInput=document.getElementById('trade-stake');
    const tradeForm=document.getElementById('paper-trade-form');
    const payoutRate={{ (int) config('shell.paper.payout_basis_points',18000) / 10000 }};
    function updatePayout(){const stake=Math.max(0,Number(stakeInput.value)||0);document.getElementById('potential-return').textContent=new Intl.NumberFormat(undefined,{maximumFractionDigits:2}).format(stake*payoutRate);}
    stakeInput.addEventListener('input',updatePayout);updatePayout();
    document.querySelectorAll('[data-stake]').forEach(button=>button.addEventListener('click',()=>{stakeInput.value=button.dataset.stake;updatePayout();}));
    document.querySelectorAll('[data-direction]').forEach(button=>button.addEventListener('click',()=>{document.getElementById('trade-direction').value=button.dataset.direction;}));

    const positionModal=document.getElementById('position-modal');
    let activePosition=null, positionPollTimer=null, positionClockTimer=null, latestStreamPrice=null;
    const money=(value,maximum=8)=>new Intl.NumberFormat(undefined,{minimumFractionDigits:2,maximumFractionDigits:maximum}).format(Number(value)||0);
    const priceText=value=>{const n=Number(value);return Number.isFinite(n)?new Intl.NumberFormat(undefined,{maximumFractionDigits:n>=1000?2:n>=1?4:8}).format(n):'—';};
    function setPositionText(id,value){const el=document.getElementById(id);if(el)el.textContent=value;}
    function updatePositionPrice(price){
        if(!activePosition||activePosition.status!=='open'||!Number.isFinite(Number(price)))return;
        latestStreamPrice=Number(price);
        const opening=Number(activePosition.opening_price),delta=latestStreamPrice-opening;
        const favorable=activePosition.direction==='UP'?delta>0:delta<0;
        const pct=opening?Math.abs(delta/opening*100):0;
        setPositionText('position-current-price',priceText(latestStreamPrice));
        const move=document.getElementById('position-price-move');
        move.textContent=(delta>0?'+':'')+priceText(delta)+' ('+(delta>0?'+':'')+(opening?(delta/opening*100).toFixed(4):'0.0000')+'%) · '+(delta===0?'At opening':favorable?'Currently favorable':'Currently unfavorable');
        move.style.color=delta===0?'#aab8c9':favorable?'#53d6aa':'#ff8996';
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
            const data=await window.shellAjax(@json(url('/trading/paper'))+'/'+activePosition.id,{method:'GET',headers:{'Accept':'application/json'}});
            const trade=data.trade;
            activePosition=trade;
            setPositionText('position-status',trade.status.toUpperCase());
            if(trade.status==='open'){
                setPositionText('position-result','Your position is active. Live movement is indicative; the final result is confirmed by server settlement.');
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
            window.shellToast('Trade settled: '+status);
        }catch(error){setPositionText('position-result','Connection interrupted. This position remains recorded. Reconnecting to retrieve the confirmed result…');}
    }
    async function openPositionModal(id){
        const modal=positionModal;
        modal.classList.add('is-visible');modal.setAttribute('aria-hidden','false');
        document.getElementById('position-close').disabled=true;
        setPositionText('position-result','Trade opened. Connecting to live position updates…');
        setPositionText('position-close','Waiting for result…');
        try{
            const data=await window.shellAjax(@json(url('/trading/paper'))+'/'+id,{method:'GET',headers:{'Accept':'application/json'}});
            activePosition=data.trade;latestStreamPrice=null;
            setPositionText('position-symbol',activePosition.symbol);
            setPositionText('position-direction',activePosition.direction+' · '+(activePosition.direction==='UP'?'PRICE RISE':'PRICE FALL'));
            const dir=document.getElementById('position-direction');dir.classList.toggle('down',activePosition.direction==='DOWN');
            setPositionText('position-stake',money(activePosition.stake)+' '+activePosition.currency);
            setPositionText('position-opening-price',priceText(activePosition.opening_price));
            setPositionText('position-expiry',new Date(activePosition.expires_at).toLocaleTimeString());
            setPositionText('position-current-price','Waiting for live price…');
            updatePositionClock();
            if(positionPollTimer)window.clearInterval(positionPollTimer);
            if(positionClockTimer)window.clearInterval(positionClockTimer);
            positionPollTimer=window.setInterval(pollPosition,2000);
            positionClockTimer=window.setInterval(updatePositionClock,1000);
            await pollPosition();
        }catch(error){setPositionText('position-result','Trade was accepted, but its details could not be loaded. Refresh the page and check Trade history.');}
    }
    document.getElementById('position-close').addEventListener('click',()=>{
        if(!activePosition||activePosition.status==='open')return;
        positionModal.classList.remove('is-visible');positionModal.setAttribute('aria-hidden','true');window.location.reload();
    });

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
            const result=await window.shellAjax(@json(route('trading.store')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            feedback.textContent=result.message;window.shellToast(result.message);
            if(result.trade_id){await openPositionModal(result.trade_id);}
        }catch(error){feedback.textContent=error.message;window.shellToast(error.message,'error');}
        finally{form.dataset.submitting='false';buttons.forEach(button=>button.disabled=false);}
    });
})();
</script>
@endpush
