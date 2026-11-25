@extends('layouts.app')
@section('title', 'Dashboard')
@section('eyebrow', 'TRADING OVERVIEW')
@section('heading', 'Welcome back, ' . explode(' ', $user->name)[0])

@push('styles')
<style>
    .dash-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:22px;padding:22px 24px;border:1px solid var(--line,#263247);border-radius:18px;background:linear-gradient(115deg,rgba(32,111,164,.17),rgba(12,21,37,.92) 62%);}.dash-hero-copy{min-width:0}.dash-hero h2{margin:0 0 6px;font-size:clamp(19px,2.5vw,25px);letter-spacing:-.04em}.dash-hero p{margin:0;color:var(--muted,#91a0b5);font-size:13px;line-height:1.6}.dash-actions{display:flex;flex-wrap:wrap;gap:9px;flex-shrink:0}.dash-actions .button{white-space:nowrap}.dash-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:14px}.dash-metric{min-width:0;padding:17px;border:1px solid var(--line,#263247);border-radius:15px;background:var(--panel,#101a2b)}.dash-metric-top{display:flex;align-items:center;justify-content:space-between;gap:8px;color:var(--muted,#91a0b5);font-size:10px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}.dash-metric-icon{display:grid;place-items:center;width:32px;height:32px;border-radius:10px;background:rgba(56,137,190,.13);color:#72b9e6}.dash-metric-icon svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}.dash-metric strong{display:block;margin-top:12px;font-family:Manrope,sans-serif;font-size:clamp(21px,2.3vw,28px);font-weight:800;letter-spacing:-.045em;overflow-wrap:anywhere}.dash-metric small{display:block;margin-top:5px;color:var(--muted,#91a0b5);font-size:11px;line-height:1.45}.dash-metric .dash-positive{color:#56c99a}.dash-metric .dash-neutral{color:#d4b36a}.dash-secondary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:20px}.dash-secondary-item{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:13px 15px;border:1px solid var(--line,#263247);border-radius:13px;background:rgba(16,26,43,.72)}.dash-secondary-item span{color:var(--muted,#91a0b5);font-size:12px}.dash-secondary-item strong{font-size:17px}.dash-layout{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(280px,.85fr);gap:16px;align-items:start}.dash-panel{min-width:0;margin-bottom:16px}.dash-panel .panel-head{gap:12px}.dash-panel .panel-head h2{margin:3px 0 0}.dash-chart{display:flex;align-items:stretch;gap:10px;height:160px;padding:18px 4px 0}.dash-chart-day{display:flex;flex:1;min-width:0;flex-direction:column;align-items:center;justify-content:flex-end;gap:8px}.dash-chart-track{display:flex;align-items:flex-end;justify-content:center;width:min(100%,34px);height:116px;border-radius:7px 7px 3px 3px;background:rgba(255,255,255,.035);overflow:hidden}.dash-chart-bar{display:block;width:100%;min-height:3px;border-radius:7px 7px 2px 2px;background:linear-gradient(180deg,#5eb8e8,#246b9d)}.dash-chart-day small{color:var(--muted,#91a0b5);font-size:10px}.dash-chart-legend{display:flex;align-items:center;gap:7px;margin-top:12px;color:var(--muted,#91a0b5);font-size:11px}.dash-chart-legend i{width:8px;height:8px;border-radius:3px;background:#3989bb}.dash-quick-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.dash-quick-link{display:flex;align-items:center;gap:11px;padding:13px;border:1px solid var(--line,#263247);border-radius:12px;color:inherit;text-decoration:none;transition:border-color .18s,transform .18s}.dash-quick-link:hover{border-color:#3989bb;transform:translateY(-1px)}.dash-quick-icon{display:grid;place-items:center;flex:0 0 34px;height:34px;border-radius:10px;background:rgba(56,137,190,.13);color:#72b9e6}.dash-quick-icon svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}.dash-quick-link strong{display:block;font-size:12px}.dash-quick-link small{display:block;margin-top:3px;color:var(--muted,#91a0b5);font-size:10px;line-height:1.4}.dash-quick-arrow{margin-left:auto;color:#80bfe4}.dash-status{display:inline-flex;align-items:center;gap:6px;padding:5px 8px;border-radius:20px;background:rgba(140,153,174,.1);color:#b5c0cf;font-size:9px;font-weight:800;letter-spacing:.05em;white-space:nowrap}.dash-status:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}.dash-status.won{background:rgba(57,178,127,.12);color:#65d2a3}.dash-status.lost{background:rgba(219,91,103,.12);color:#f08b96}.dash-status.open{background:rgba(212,179,106,.12);color:#d9bd7e}.dash-empty{padding:24px 14px;text-align:center;color:var(--muted,#91a0b5);font-size:12px}.dash-pending-list{display:grid;gap:9px}.dash-pending-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 12px;border:1px solid var(--line,#263247);border-radius:11px}.dash-pending-row strong{display:block;font-size:12px}.dash-pending-row small{display:block;margin-top:3px;color:var(--muted,#91a0b5);font-size:10px}.dash-market-note{margin-top:10px;color:var(--muted,#91a0b5);font-size:10px;line-height:1.5}.dash-table .data-table{min-width:640px}.dash-table .data-table td,.dash-table .data-table th{white-space:nowrap}.dash-table .data-table td:first-child strong{font-size:12px}.dash-link{color:#80bfe4;font-size:11px;font-weight:700;text-decoration:none}.dash-link:hover{text-decoration:underline}
    @media(max-width:1050px){.dash-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.dash-layout{grid-template-columns:1fr}}
    @media(max-width:650px){.dash-hero{align-items:flex-start;flex-direction:column;padding:18px}.dash-actions{width:100%}.dash-actions .button{flex:1;justify-content:center}.dash-secondary{grid-template-columns:1fr}.dash-quick-grid{grid-template-columns:1fr}.dash-metric{padding:14px}.dash-metric strong{font-size:23px}}

    .dash-market-subtitle{margin:5px 0 0;color:var(--muted,#91a0b5);font-size:11px;line-height:1.5}.dash-market-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px;padding:0 16px 14px}.dash-market-card{min-width:0;padding:13px;border:1px solid var(--line,#263247);border-radius:13px;background:rgba(8,17,31,.55)}.dash-market-card-top,.dash-market-card-bottom{display:flex;align-items:center;justify-content:space-between;gap:7px}.dash-market-card-top strong{font-size:12px}.dash-trend-badge{padding:4px 7px;border-radius:20px;font-size:8px;font-weight:900;letter-spacing:.06em;white-space:nowrap}.dash-trend-badge.up{color:#62d6a6;background:rgba(52,190,132,.13)}.dash-trend-badge.down{color:#ff8d99;background:rgba(225,82,99,.13)}.dash-trend-badge.hot{color:#ffd27a;background:rgba(238,166,57,.16)}.dash-trend-badge.neutral{color:#aab7c8;background:rgba(150,165,185,.1)}.dash-market-card-price{margin-top:12px;font-size:clamp(16px,1.7vw,21px);font-weight:850;font-variant-numeric:tabular-nums;letter-spacing:-.04em;overflow-wrap:anywhere}.dash-market-card-change{margin-top:3px;font-size:10px;font-weight:750;font-variant-numeric:tabular-nums}.dash-market-card-change.up{color:#62d6a6}.dash-market-card-change.down{color:#ff8d99}.dash-market-card-change.neutral{color:var(--muted,#91a0b5)}.dash-sparkline{display:block;width:100%;height:62px;margin:7px 0 4px}.dash-market-card-bottom{font-size:9px;color:var(--muted,#91a0b5)}.dash-trade-link{color:#80bfe4;text-decoration:none;font-size:10px;font-weight:800;white-space:nowrap}.dash-trade-link:hover{text-decoration:underline}.dash-market-error{margin:7px 0 0;color:#ff9aa5;font-size:10px}@media(max-width:900px){.dash-market-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:420px){.dash-market-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
    <section class="dash-hero">
        <div class="dash-hero-copy">
            <h2>Your trading command center</h2>
            <p>Monitor your demo performance, check wallet activity and get straight to the next action. Demo and real funds remain separate.</p>
        </div>
        <div class="dash-actions">
            <a href="{{ route('trading.index') }}" class="button button-primary button-large"><svg class="nav-icon"><use href="#i-chart"/></svg> Open trading</a>
            <a href="{{ route('wallet-requests.index', ['account' => 'real']) }}#deposit-form" class="button button-quiet"> <svg class="nav-icon"><use href="#i-wallet"/></svg> Deposit funds</a>
        </div>
    </section>

    <div class="dash-metrics">
        <article class="dash-metric">
            <div class="dash-metric-top"><span>Demo balance</span><span class="dash-metric-icon"><svg><use href="#i-wallet"/></svg></span></div>
            <strong>@money($demoBalance)</strong><small>Simulation credits · {{ $walletCurrency }}</small>
        </article>
        <article class="dash-metric">
            <div class="dash-metric-top"><span>Real balance</span><span class="dash-metric-icon"><svg><use href="#i-shield"/></svg></span></div>
            <strong>@money($realBalance)</strong><small>Real wallet · {{ $walletCurrency }}</small>
        </article>
        <article class="dash-metric">
            <div class="dash-metric-top"><span>Open positions</span><span class="dash-metric-icon"><svg><use href="#i-clock"/></svg></span></div>
            <strong>{{ number_format((int) ($tradeStats->open ?? 0)) }}</strong><small>Awaiting settlement or expiry processing</small>
        </article>
        <article class="dash-metric">
            <div class="dash-metric-top"><span>Win rate</span><span class="dash-metric-icon"><svg><use href="#i-chart"/></svg></span></div>
            <strong>{{ $winRate === null ? '—' : number_format($winRate, 1) . '%' }}</strong><small>{{ number_format((int) ($tradeStats->won ?? 0)) }} wins from {{ number_format(($tradeStats->won ?? 0) + ($tradeStats->lost ?? 0)) }} resolved wins/losses · ties excluded</small>
        </article>
    </div>

    <div class="dash-secondary">
        <div class="dash-secondary-item"><span>Trades in last 7 days</span><strong>{{ number_format($dailyActivity->sum('total')) }}</strong></div>
        <div class="dash-secondary-item"><span>Pending deposits</span><strong class="{{ $pendingDeposits > 0 ? 'dash-neutral' : '' }}">{{ $pendingDeposits }}</strong></div>
        <div class="dash-secondary-item"><span>Pending withdrawals</span><strong class="{{ $pendingWithdrawals > 0 ? 'dash-neutral' : '' }}">{{ $pendingWithdrawals }}</strong></div>
    </div>

    <section class="panel dash-panel dash-live-markets" aria-labelledby="dash-live-markets-title">
        <div class="panel-head">
            <div><span class="eyebrow">LIVE MARKET MOMENTUM · 1 MIN CANDLES</span><h2 id="dash-live-markets-title">Markets in motion</h2><p class="dash-market-subtitle">See which pairs are rising, falling or moving strongly before opening the trading terminal.</p></div>
            <a href="{{ route('markets.index') }}" class="dash-link">All markets ↗</a>
        </div>
        <div class="dash-market-grid" id="dash-market-grid">
            @foreach($dashboardSymbols as $symbol)
                <article class="dash-market-card" data-symbol="{{ $symbol }}">
                    <div class="dash-market-card-top"><strong>{{ $symbol }}</strong><span class="dash-trend-badge neutral" data-trend>LOADING</span></div>
                    <div class="dash-market-card-price" data-price>—</div>
                    <div class="dash-market-card-change neutral" data-change>Fetching recent candles…</div>
                    <canvas class="dash-sparkline" width="320" height="88" aria-label="{{ $symbol }} recent price trend"></canvas>
                    <div class="dash-market-card-bottom"><span data-updated>Waiting for market data</span><a href="{{ route('trading.index', ['symbol' => $symbol]) }}" class="dash-trade-link">Trade pair ↗</a></div>
                    <p class="dash-market-error" data-error hidden>Market chart temporarily unavailable.</p>
                </article>
            @endforeach
        </div>
        <p class="dash-market-note">Momentum is calculated from the latest 30 one-minute candle closes. “ON FIRE” means a rise of at least 0.5% over that short window; it is not a prediction or a trading signal. Prices can reverse quickly.</p>
    </section>

    <div class="dash-layout">
        <div>
            <section class="panel dash-panel">
                <div class="panel-head">
                    <div><span class="eyebrow">LAST 7 DAYS</span><h2>Trading activity</h2></div>
                    <a href="{{ route('activity.index') }}" class="dash-link">Full history ↗</a>
                </div>
                @if($dailyActivity->sum('total') > 0)
                    <div class="dash-chart" role="img" aria-label="Number of trades opened on each of the last seven days">
                        @foreach($dailyActivity as $day)
                            <div class="dash-chart-day" title="{{ $day['date'] }}: {{ $day['total'] }} trades, {{ $day['wins'] }} wins">
                                <div class="dash-chart-track"><span class="dash-chart-bar" style="height: {{ max(4, ($day['total'] / $dailyMax) * 100) }}%"></span></div>
                                <small>{{ $day['label'] }}</small>
                            </div>
                        @endforeach
                    </div>
                    <div class="dash-chart-legend"><i></i> Positions opened per day</div>
                @else
                    <div class="dash-empty">No trades in the last seven days. Open a demo position to start building your activity history.</div>
                @endif
            </section>

            <section class="panel dash-panel">
                <div class="panel-head">
                    <div><span class="eyebrow">YOUR ACCOUNT</span><h2>Recent trades</h2></div>
                    <a href="{{ route('activity.index') }}" class="dash-link">View all ↗</a>
                </div>
                <div class="table-wrap dash-table">
                    <table class="data-table">
                        <thead><tr><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPEN PRICE</th><th>CLOSE PRICE</th><th>STATUS</th></tr></thead>
                        <tbody>
                        @forelse($recentTrades as $trade)
                            <tr>
                                <td><strong>{{ $trade->symbol }}</strong><small style="display:block;color:var(--muted);font-size:10px">{{ \Illuminate\Support\Carbon::parse($trade->opened_at)->format('M j, H:i') }}</small></td>
                                <td>{{ $trade->direction }}</td>
                                <td>@money($trade->stake) {{ $trade->wallet_currency ?? $walletCurrency }}</td>
                                <td>@decimal($trade->opening_price)</td>
                                <td>{{ $trade->closing_price === null ? '—' : \App\Support\NumberFormatter::decimal($trade->closing_price) }}</td>
                                <td><span class="dash-status {{ $trade->status === 'won' ? 'won' : ($trade->status === 'lost' ? 'lost' : ($trade->status === 'open' ? 'open' : '')) }}">{{ $trade->status === 'void' ? 'VOID / REFUNDED' : strtoupper($trade->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="table-empty">No trades yet. Your completed and open positions will appear here.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div>
            <section class="panel dash-panel">
                <div class="panel-head"><div><span class="eyebrow">QUICK ACCESS</span><h2>What would you like to do?</h2></div></div>
                <div class="dash-quick-grid">
                    <a class="dash-quick-link" href="{{ route('trading.index') }}"><span class="dash-quick-icon"><svg><use href="#i-chart"/></svg></span><span><strong>Trade demo</strong><small>Open a position</small></span><b class="dash-quick-arrow">↗</b></a>
                    <a class="dash-quick-link" href="{{ route('wallet-requests.index', ['account' => 'real']) }}#deposit-form"><span class="dash-quick-icon"><svg><use href="#i-wallet"/></svg></span><span><strong>Deposit funds</strong><small>Fund real wallet</small></span><b class="dash-quick-arrow">↗</b></a>
                    <a class="dash-quick-link" href="{{ route('portfolio.index') }}"><span class="dash-quick-icon"><svg><use href="#i-briefcase"/></svg></span><span><strong>My wallets</strong><small>Review balances</small></span><b class="dash-quick-arrow">↗</b></a>
                    <a class="dash-quick-link" href="{{ route('markets.index') }}"><span class="dash-quick-icon"><svg><use href="#i-chart"/></svg></span><span><strong>Explore markets</strong><small>Check available pairs</small></span><b class="dash-quick-arrow">↗</b></a>
                    <a class="dash-quick-link" href="{{ route('convert.index') }}"><span class="dash-quick-icon"><svg><use href="#i-repeat"/></svg></span><span><strong>Convert assets</strong><small>Demo wallet conversions</small></span><b class="dash-quick-arrow">↗</b></a>
                    <a class="dash-quick-link" href="{{ route('activity.index') }}"><span class="dash-quick-icon"><svg><use href="#i-receipt"/></svg></span><span><strong>Trade history</strong><small>Review past results</small></span><b class="dash-quick-arrow">↗</b></a>
                </div>
            </section>

            <section class="panel dash-panel">
                <div class="panel-head"><div><span class="eyebrow">WALLET OPERATIONS</span><h2>Request status</h2></div><a href="{{ route('wallet-requests.index') }}" class="dash-link">Open wallet ↗</a></div>
                <div class="dash-pending-list">
                    <div class="dash-pending-row"><span><strong>Deposit requests</strong><small>Awaiting verification</small></span><span class="dash-status {{ $pendingDeposits > 0 ? 'open' : '' }}">{{ $pendingDeposits }} pending</span></div>
                    <div class="dash-pending-row"><span><strong>Withdrawal requests</strong><small>Awaiting processing</small></span><span class="dash-status {{ $pendingWithdrawals > 0 ? 'open' : '' }}">{{ $pendingWithdrawals }} pending</span></div>
                </div>
                <p class="dash-market-note">Real-wallet requests are not credited or paid until the required checks and administrator processing are complete.</p>
            </section>

            <section class="panel dash-panel">
                <div class="panel-head"><div><span class="eyebrow">MARKET DATA</span><h2>Market availability</h2></div><span class="dash-status {{ $marketConnected ? 'won' : 'open' }}">{{ $marketConnected ? 'AVAILABLE' : 'LIMITED' }}</span></div>
                <div class="dash-pending-row"><span><strong>{{ $quotes->count() }} fresh pairs</strong><small>{{ $marketConnected ? 'Server market quotes available' : 'No fresh provider quotes currently available' }}</small></span><a href="{{ route('markets.index') }}" class="dash-link">Markets ↗</a></div>
                @if($marketConnected)
                    <div class="table-wrap" style="margin-top:10px">
                        <table class="data-table">
                            <thead><tr><th>PAIR</th><th>LAST PRICE</th></tr></thead>
                            <tbody>
                                @foreach($quotes->take(5) as $quote)
                                    <tr><td><strong>{{ $quote->symbol }}</strong></td><td>@decimal($quote->price)</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <p class="dash-market-note">Prices shown here are recent server-captured quotes, not a guarantee of execution price. Confirm the live quote on the trading screen before opening a position.</p>
            </section>
        </div>
    </div>
@endsection
@push('scripts')
<script>
(() => {
    const grid = document.getElementById('dash-market-grid');
    if (!grid) return;
    const endpoint = @json($candlesUrl);
    const formatPrice = value => new Intl.NumberFormat(undefined, {maximumFractionDigits: 8, minimumFractionDigits: 2}).format(value);
    function draw(canvas, values, trend) {
        const ctx = canvas.getContext('2d'); if (!ctx || values.length < 2) return;
        const w = canvas.width, h = canvas.height, pad = 7;
        const low = Math.min(...values), high = Math.max(...values), range = high - low || Math.max(Math.abs(high) * .0001, .00000001);
        ctx.clearRect(0, 0, w, h); ctx.beginPath();
        values.forEach((v, i) => { const x = pad + i * (w - pad * 2) / (values.length - 1); const y = h - pad - ((v - low) / range) * (h - pad * 2); i ? ctx.lineTo(x, y) : ctx.moveTo(x, y); });
        ctx.lineWidth = 3; ctx.lineJoin = 'round'; ctx.lineCap = 'round'; ctx.strokeStyle = trend === 'down' ? '#ff7888' : '#51d6a2'; ctx.stroke();
        const last = values[values.length - 1], x = w - pad, y = h - pad - ((last - low) / range) * (h - pad * 2);
        ctx.beginPath(); ctx.arc(x, y, 4, 0, Math.PI * 2); ctx.fillStyle = ctx.strokeStyle; ctx.fill();
    }
    async function loadCard(card) {
        const symbol = card.dataset.symbol;
        const params = new URLSearchParams({symbol, interval:'1m', limit:'30'});
        try {
            const response = await fetch(endpoint + '?' + params, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
            const data = await response.json(); if (!response.ok) throw new Error(data.message || 'Chart unavailable');
            const candles = Array.isArray(data.candles) ? data.candles : [];
            const values = candles.map(c => Number(c.close)).filter(Number.isFinite);
            if (values.length < 2) throw new Error('Not enough recent candles');
            const first = values[0], last = values[values.length - 1], change = first ? ((last - first) / first) * 100 : 0;
            const trend = change >= .5 ? 'hot' : (change > 0 ? 'up' : (change < 0 ? 'down' : 'neutral'));
            const badge = card.querySelector('[data-trend]'), changeEl = card.querySelector('[data-change]');
            badge.className = 'dash-trend-badge ' + trend; badge.textContent = trend === 'hot' ? '🔥 ON FIRE' : (trend === 'up' ? 'RISING' : (trend === 'down' ? 'FALLING' : 'FLAT'));
            card.querySelector('[data-price]').textContent = formatPrice(last) + ' USDT';
            changeEl.className = 'dash-market-card-change ' + (change > 0 ? 'up' : (change < 0 ? 'down' : 'neutral'));
            changeEl.textContent = (change > 0 ? '+' : '') + change.toFixed(3) + '% · last 30 minutes';
            draw(card.querySelector('canvas'), values, change < 0 ? 'down' : 'up');
            card.querySelector('[data-updated]').textContent = 'Updated ' + new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', second:'2-digit'});
            card.querySelector('[data-error]').hidden = true;
        } catch (error) {
            card.querySelector('[data-error]').hidden = false;
            card.querySelector('[data-error]').textContent = error.message || 'Market chart temporarily unavailable.';
            card.querySelector('[data-trend]').textContent = 'NO FEED';
            card.querySelector('[data-trend]').className = 'dash-trend-badge neutral';
        }
    }
    const cards = [...grid.querySelectorAll('[data-symbol]')];
    const refresh = () => cards.forEach(loadCard);
    refresh(); window.setInterval(refresh, 30000);
})();
</script>
@endpush

