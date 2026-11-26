@extends('layouts.app')
@section('title', 'Trading terminal')
@section('eyebrow', 'FIXED-TIME OPTIONS SIMULATOR')
@section('heading', 'Trading terminal')
@section('content')
    <x-client-attention :title="$selectedAccount === 'real' ? 'Real-money trading' : 'Demo trading account'" :tag="$selectedAccount === 'real' ? 'REAL ACCOUNT' : 'PRACTICE ACCOUNT'">
        @if ($selectedAccount === 'real')
            @if ($hasConfirmedDeposit)
            <p>Your deposit has been verified and credited. You can use your available real-wallet balance to place real positions.</p>
        @else
            <p>Your real account is ready for funding. Make your first deposit to qualify for real-money trading. Once your payment is verified and credited, you can use your available balance to place positions.</p>
        @endif
        @else
            <p>Practise fixed-time UP and DOWN positions with demo credits. Demo trading is independent of deposits and KYC; positions settle against server-captured market quotes.</p>
        @endif
    </x-client-attention>


    @if ($selectedAccount === 'real' && ! $hasConfirmedDeposit)
        <details class="real-activation real-activation-collapsible">
            <summary class="real-activation-summary">
                <div class="real-activation-top">
                    <span class="real-activation-icon" aria-hidden="true">↗</span>
                    <span class="real-activation-kicker">YOUR REAL ACCOUNT</span>
                    <span class="real-activation-status">READY FOR YOUR FIRST DEPOSIT</span>
                    <span class="real-activation-toggle" aria-hidden="true"></span>
                </div>
                <h2 id="real-activation-title">Start your trading journey with a deposit</h2>
                <span class="real-activation-hint">View funding steps and details</span>
            </summary>
            <div class="real-activation-details">
                <p class="real-activation-copy">Your real account is ready for funding. Make your first deposit to begin setting up your real-money trading account. Once your payment has been verified and credited, you can use your available balance to open positions.</p>
                <div class="real-activation-steps">
                    <div><span>01</span><strong>Fund your account</strong><small>Choose an available payment method and deposit into your real wallet.</small></div>
                    <div><span>02</span><strong>Get your payment confirmed</strong><small>Our operations team reviews your transaction and credits your wallet after verification.</small></div>
                    <div><span>03</span><strong>Begin trading</strong><small>After your deposit is confirmed, use your available real balance to place your first position.</small></div>
                </div>
                <div class="real-activation-footer">
                    <a class="button button-primary" href="{{ route('wallet-requests.index', ['account' => 'real']) }}">Make your first deposit <span aria-hidden="true">→</span></a>
                    <span class="real-activation-note">Only deposit through the payment methods shown in your wallet center.</span>
                </div>
            </div>
        </details>
    @elseif ($selectedAccount === 'real' && ! $hasConfirmedDeposit)
        <section class="real-activation" aria-labelledby="real-activation-title">
            <div class="real-activation-top">
                <span class="real-activation-icon" aria-hidden="true">↗</span>
                <span class="real-activation-kicker">YOUR REAL ACCOUNT</span>
                <span class="real-activation-status">READY FOR FUNDING</span>
            </div>
            <h2 id="real-activation-title">Fund your account. Get ready to trade.</h2>
            <p class="real-activation-copy">You are one verified deposit away from qualifying for real-money trading. Add funds to your real wallet, then start trading once your payment has been confirmed and credited.</p>
            <div class="real-activation-steps">
                <div><span>01</span><strong>Make a deposit</strong><small>Choose an available payment method and follow its instructions.</small></div>
                <div><span>02</span><strong>Payment is verified</strong><small>Our operations team verifies the transaction and credits your real wallet.</small></div>
                <div><span>03</span><strong>Start real trading</strong><small>Once credited, use your available real balance to open positions.</small></div>
            </div>
            <div class="real-activation-footer">
                <a class="button button-primary" href="{{ route('wallet-requests.index', ['account' => 'real']) }}">Deposit to real wallet <span aria-hidden="true">→</span></a>
            </div>
        </section>
    @endif

    <div class="trading-layout">
        <section class="panel trading-chart-panel">
            <div class="panel-head trading-chart-heading">
                <div><span class="eyebrow">MARKET CHART</span>
                    <h2 id="chart-symbol">{{ $symbols[0] ?? 'BTCUSDT' }}</h2>
                </div>
                <span class="table-status verified" id="chart-health"><i class="live-dot"></i> CONNECTING</span>
            </div>
            <div class="chart-market-summary" aria-live="polite">
                <div class="chart-price-main"><strong id="chart-current-price">—</strong><span
                        id="chart-price-change">Awaiting price</span></div>
                <div class="chart-ohlc">
                    <span>O <b id="chart-open">—</b></span><span>H <b id="chart-high">—</b></span><span>L <b
                            id="chart-low">—</b></span><span>C <b id="chart-close">—</b></span>
                </div>
            </div>
            <div class="chart-toolbar">
                <label>Market<select id="chart-market">
                        @foreach ($symbols as $symbol)
                            <option value="{{ $symbol }}" @selected($symbol === $selectedSymbol)>{{ $symbol }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="chart-intervals" role="group" aria-label="Chart timeframe">
                    @foreach (['15m', '1m', '5m', '1h', '4h', '1d'] as $interval)
                        <button type="button" data-interval="{{ $interval }}"
                            class="{{ $interval === '15m' ? 'active' : '' }}"
                            aria-pressed="{{ $interval === '15m' ? 'true' : 'false' }}">{{ $interval }}</button>
                    @endforeach
                </div>
            </div>
            <div class="chart-zoom" role="group" aria-label="Chart zoom"><button type="button" id="chart-zoom-out"
                    aria-label="Zoom out">−</button><span id="chart-zoom-level">80 candles</span><button type="button"
                    id="chart-zoom-in" aria-label="Zoom in">+</button><button type="button"
                    id="chart-zoom-reset">Reset</button></div>
            <div class="market-chart-wrap">
                <canvas id="market-candles"
                    aria-label="Interactive candlestick market chart with price and time scales"></canvas>
                <div class="chart-loading" id="chart-loading">Loading market data…</div>
            </div>
            <div class="chart-foot"><span id="chart-last-price">Price scale · USDT</span><span id="chart-updated">Waiting
                    for latest update</span></div>
        </section>

        <section class="panel trade-ticket">
            <div class="panel-head trade-ticket-head">
                <div><span class="eyebrow">NEW POSITION</span>
                    <h2>Trade ticket</h2>
                </div>
                <nav class="account-switch" aria-label="Choose trading account">
                    <a class="account-switch-option {{ $selectedAccount === 'demo' ? 'is-active' : '' }}"
                        href="{{ route('trading.index', ['account' => 'demo']) }}"
                        {{ $selectedAccount === 'demo' ? 'aria-current=page' : '' }}>
                        <span class="account-switch-dot account-switch-demo-dot" aria-hidden="true"></span>
                        <span>Demo</span>
                        <span class="account-switch-check" aria-hidden="true">✓</span>
                    </a>
                    <a class="account-switch-option account-switch-real {{ $selectedAccount === 'real' ? 'is-active' : '' }}"
                        href="{{ route('trading.index', ['account' => 'real']) }}"
                        {{ $selectedAccount === 'real' ? 'aria-current=page' : '' }}>
                        <span class="account-switch-dot account-switch-real-dot" aria-hidden="true"></span>
                        <span>Real</span>
                        <span class="account-switch-check" aria-hidden="true">✓</span>
                    </a>
                </nav>
            </div>
            <div class="wallet-summary"><span>AVAILABLE {{ strtoupper($selectedAccount) }} BALANCE</span><strong id="wallet-balance" aria-live="polite">@decimal($balance)
                    {{ $currency }}</strong></div>
            <form id="paper-trade-form" class="form-stack">
                <input type="hidden" name="account_type" value="{{ $selectedAccount }}">
                <input type="hidden" name="request_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                <label>Market pair<select name="symbol" id="trade-symbol" required>
                        @foreach ($symbols as $symbol)
                            <option value="{{ $symbol }}" @selected($symbol === $selectedSymbol)>{{ $symbol }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Stake ({{ $currency }})<input id="trade-stake" name="stake" type="number" inputmode="decimal"
                        min="0.00000001" max="{{ config('shell.paper.max_stake') }}" step="0.00000001" value="10"
                        required></label>
                <div class="stake-presets"><button type="button" data-stake="10">$10</button><button type="button"
                        data-stake="25">$25</button><button type="button" data-stake="50">$50</button><button
                        type="button" data-stake="100">$100</button></div>
                <label>Expiry duration<select name="duration">
                        @foreach ($durations as $duration)
                            <option value="{{ $duration }}">
                                {{ $duration >= 3600 ? $duration / 3600 . ' hour' : $duration / 60 . ' minutes' }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="payout-preview"><span>Potential total return</span><strong><b id="potential-return">—</b>
                        {{ $currency }}</strong><small>Estimated amount returned if this position wins, including your
                        stake. A loss forfeits the stake; a tie returns the stake.</small></div>
                <details class="trade-process-hint">
                    <summary>How this trade works <span>Quick guide</span></summary>
                    <ol>
                        <li>Choose the market, stake and expiry.</li>
                        <li>Select UP if you expect the price to rise, or DOWN if you expect it to fall.</li>
                        <li>We verify a fresh server price and reserve the stake from your selected account wallet.</li>
                        <li>At expiry, the position settles against the next eligible server-captured quote. Your result
                            appears in Trade history.</li>
                    </ol>
                </details>
                <div class="direction-buttons"><button class="direction-up" type="submit" data-direction="UP" {{ $selectedAccount === 'real' && ! $hasConfirmedDeposit ? 'disabled' : '' }}>↗ UP
                        <small>Price rises</small></button><button class="direction-down" type="submit"
                        data-direction="DOWN" {{ $selectedAccount === 'real' && ! $hasConfirmedDeposit ? 'disabled' : '' }}>↘ DOWN <small>Price falls</small></button></div>
                <input type="hidden" name="direction" id="trade-direction" value="UP">
                <p class="muted" id="trade-feedback" role="status" aria-live="polite"></p>
            </form>
        </section>
    </div>

    <section class="panel">
        <div class="panel-head">
            <div><span class="eyebrow">YOUR POSITIONS</span>
                <h2>Trade history</h2>
            </div><a class="button button-secondary" href="{{ route('activity.index', ['account' => $selectedAccount]) }}"><svg class="nav-icon">
                    <use href="#i-clock" />
                </svg> View all trade history</a><a class="button button-secondary"
                href="{{ route('portfolio.index') }}"><svg class="nav-icon">
                    <use href="#i-briefcase" />
                </svg> View portfolio</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>PAIR</th>
                        <th>DIRECTION</th>
                        <th>STAKE</th>
                        <th>OPEN PRICE</th>
                        <th>CLOSING PRICE</th>
                        <th>EXPIRY</th>
                        <th>TIME LEFT</th>
                        <th>RESULT</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trades as $trade)
                        <tr class="trade-history-row {{ $trade->status === 'won' ? 'trade-row-win' : ($trade->status === 'lost' ? 'trade-row-loss' : '') }}" data-trade-id="{{ $trade->id }}" @if ($trade->status === 'open') data-open-trade="{{ $trade->id }}" @endif tabindex="0" role="button" aria-label="View details for {{ $trade->symbol }} {{ strtolower($trade->direction) }} trade">
                            <td><strong>{{ $trade->symbol }}</strong></td>
                            <td>{{ $trade->direction }}</td>
                            <td>@money($trade->stake) {{ $trade->wallet_currency ?? $currency }}</td>
                            <td>@decimal($trade->opening_price)</td>
                            <td class="trade-closing-price">
                                {{ $trade->closing_price === null ? '—' : \App\Support\NumberFormatter::decimal($trade->closing_price) }}
                            </td>
                            <td>{{ $trade->expires_at }}</td>
                            <td>
                                @if ($trade->status === 'open')
                                    <span class="trade-countdown"
                                        data-expiry="{{ strtotime($trade->expires_at) * 1000 }}">Calculating…</span>
                                @else
                                    <span class="muted">Settled</span>
                                @endif
                            </td>
                            <td><span
                                    class="table-status trade-result-badge {{ $trade->status === 'open' ? 'pending' : ($trade->status === 'won' ? 'verified trade-result-win' : ($trade->status === 'lost' ? 'rejected trade-result-loss' : 'pending')) }}">{{ $trade->status === 'won' ? 'WIN' : ($trade->status === 'lost' ? 'LOSS' : ($trade->status === 'void' ? 'TIE' : 'OPEN')) }}</span>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="8" class="table-empty">No trades yet. Your first position will appear
                                here.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="history-detail-modal" id="history-detail-modal" role="dialog" aria-modal="true" aria-labelledby="history-detail-title" aria-hidden="true">
        <div class="history-detail-backdrop" data-close-history-detail></div>
        <section class="history-detail-card" id="history-detail-card">
            <button type="button" class="history-detail-close" data-close-history-detail aria-label="Close trade details">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
            <div class="history-detail-topline">
                <span class="history-detail-brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 17 9 7l3 6 3-6 5 10M5 20h14"/></svg></span>
                <div><span class="history-detail-kicker">TRADE HISTORY · {{ strtoupper($selectedAccount) }} ACCOUNT</span><h2 id="history-detail-title">Position details</h2></div>
                <span id="history-detail-status" class="history-detail-status is-pending">LOADING</span>
            </div>
            <div class="history-result-hero is-pending" id="history-result-hero">
                <div class="history-result-emblem" id="history-result-emblem" aria-hidden="true">
                    <svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="20"/><path d="m15 24 6 6 13-14"/></svg>
                </div>
                <div class="history-result-copy"><span id="history-result-kicker">POSITION STATUS</span><strong id="history-result-title">Loading trade result</strong><small id="history-result-subtitle">Confirming the recorded outcome…</small></div>
                <div class="history-result-net"><span>NET RESULT</span><strong id="history-detail-profit">—</strong></div>
            </div>
            <div class="history-detail-market">
                <div class="history-market-symbol"><span class="history-coin-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2v20m5-16H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span><div><strong id="history-detail-symbol">—</strong><small id="history-detail-account">DEMO</small></div></div>
                <span class="history-direction-chip" id="history-detail-direction">—</span>
            </div>
            <div class="history-detail-section-title"><span>POSITION SUMMARY</span><span id="history-detail-id">TRADE #—</span></div>
            <div class="history-detail-grid">
                <div class="history-detail-item"><span><svg viewBox="0 0 24 24"><path d="M12 2v20m5-16H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Stake</span><strong id="history-detail-stake">—</strong></div>
                <div class="history-detail-item"><span><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Duration</span><strong id="history-detail-duration">—</strong></div>
                <div class="history-detail-item"><span><svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h16M7 15l4-4 3 2 5-6"/></svg>Opening price</span><strong id="history-detail-opening">—</strong></div>
                <div class="history-detail-item"><span><svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h16m-13-4 4-4 3 2 5-6"/></svg>Closing price</span><strong id="history-detail-closing">—</strong></div>
            </div>
            <div class="history-detail-section-title history-timeline-title"><span>TRADE TIMELINE</span><span>RECORDED TIMES</span></div>
            <div class="history-trade-timeline">
                <div><i class="history-timeline-dot is-start"></i><span>Opened at</span><strong id="history-detail-opened">—</strong></div>
                <div><i class="history-timeline-dot is-expiry"></i><span>Expiry time</span><strong id="history-detail-expiry">—</strong></div>
                <div><i class="history-timeline-dot is-settled"></i><span>Settled at</span><strong id="history-detail-settled">—</strong></div>
            </div>
            <div class="history-payout-row"><div><span class="history-payout-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 7h18v13H3zM3 10h18M7 4h10M12 13v4m-2-2h4"/></svg></span><div><strong>Total payout</strong><small>Stake returned plus profit, if won</small></div></div><strong id="history-detail-payout">—</strong></div>
            <p class="history-detail-note" id="history-detail-note">Final position details as recorded by the trading system.</p>
        </section>
    </div>

    <div class="position-modal" id="position-modal" role="dialog" aria-modal="true"
        aria-labelledby="position-modal-title" aria-hidden="true">
        <div class="position-modal-backdrop"></div>
        <section class="position-modal-card" id="position-modal-card">
            <button type="button" class="position-modal-x" id="position-modal-x" aria-label="Close position result" disabled>×</button>
            <div class="position-modal-head">
                <div><span class="eyebrow">TRADE DETAILS · {{ strtoupper($selectedAccount) }}</span>
                    <h2 id="position-modal-title">Trade opened</h2>
                </div><span class="table-status pending" id="position-status">OPEN</span>
            </div>
            <div class="position-market-line"><strong id="position-symbol">—</strong><span
                    id="position-direction">—</span></div>
            <div class="position-result-hero" id="position-result-hero" hidden><div class="position-result-icon" id="position-result-icon" aria-hidden="true">✓</div><div><span id="position-result-kicker">POSITION SETTLED</span><strong id="position-result-title">Result confirmed</strong></div></div>
            <div class="position-live-strip">
                <div class="position-live-price">
                    <div class="position-live-price-heading"><span id="position-price-label">LIVE MARKET PRICE</span><span class="position-feed-badge" id="position-feed-badge">Reconnecting…</span></div>
                    <strong id="position-current-price">Connecting to live market…</strong>
                    <small id="position-price-move">Loading your position details</small>
                </div>
                <div class="position-live-outcome">
                    <span>LIVE INDICATIVE RESULT</span>
                    <strong id="position-live-outcome">Waiting for market price…</strong>
                </div>
            </div>
            <div class="position-details-grid" aria-label="Position details">
                <div class="position-detail-item"><span class="position-detail-label"><svg class="position-detail-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span>Stake</span></span><strong id="position-stake">—</strong></div>
                <div class="position-detail-item"><span class="position-detail-label"><svg class="position-detail-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 12h18M12 3v18M7 7l-4 5 4 5M17 7l4 5-4 5"/></svg><span>Opening price</span></span><strong id="position-opening-price">—</strong></div>
                <div class="position-detail-item"><span class="position-detail-label"><svg class="position-detail-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/></svg><span>Expiry</span></span><strong id="position-expiry">—</strong></div>
                <div class="position-detail-item"><span class="position-detail-label"><svg class="position-detail-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><span>Time remaining</span></span><strong id="position-countdown">—</strong></div>
                <div class="position-detail-item"><span class="position-detail-label"><svg class="position-detail-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 19V5m0 14h16M7 15l4-4 3 2 5-6"/></svg><span>Confirmed closing price</span></span><strong id="position-closing-price">—</strong></div>
                <div class="position-detail-item"><span class="position-detail-label"><svg class="position-detail-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2v20m5-16H9a3 3 0 0 0 0 6h6a3 3 0 0 1 0 6H6"/></svg><span>Total payout</span></span><strong id="position-final-payout">—</strong></div>
            </div>
            <div class="position-result" id="position-result" aria-live="polite"></div>
            <button type="button" class="button button-primary position-close" id="position-close" disabled>Waiting for
                result…</button>
        </section>
    </div>

@endsection

@push('styles')
    <style>
        .real-activation {
            position: relative;
            overflow: hidden;
            margin: 18px 0 22px;
            padding: clamp(20px, 3vw, 30px);
            border: 1px solid rgba(201, 169, 106, .32);
            border-radius: 14px;
            background: linear-gradient(125deg, rgba(24, 39, 59, .98), rgba(11, 20, 34, .98));
            box-shadow: 0 14px 34px rgba(0, 0, 0, .16)
        }
        .real-activation:before {
            content: "";
            position: absolute;
            top: -90px;
            right: -55px;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201, 169, 106, .13), transparent 68%);
            pointer-events: none
        }
        .real-activation-top {
            position: relative;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 17px
        }
        .real-activation-icon {
            display: inline-grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border: 1px solid rgba(201, 169, 106, .45);
            border-radius: 9px;
            color: #e4c27e;
            background: rgba(201, 169, 106, .1);
            font-weight: 900
        }
        .real-activation-kicker {
            color: #d9c18d;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .13em
        }
        .real-activation-status {
            margin-left: auto;
            padding: 6px 9px;
            border: 1px solid rgba(201, 169, 106, .32);
            border-radius: 999px;
            color: #e7c985;
            background: rgba(201, 169, 106, .09);
            font-size: 9px;
            font-weight: 850;
            letter-spacing: .07em
        }
        .real-activation h2 {
            position: relative;
            margin: 0 0 9px;
            color: #f2f5fa;
            font-size: clamp(21px, 2.5vw, 28px);
            letter-spacing: -.035em
        }
        .real-activation-copy {
            position: relative;
            max-width: 760px;
            margin: 0;
            color: #aebbd0;
            font-size: 13px;
            line-height: 1.75
        }
        .real-activation-steps {
            position: relative;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin: 22px 0
        }
        .real-activation-steps>div {
            display: grid;
            align-content: start;
            gap: 7px;
            min-width: 0;
            padding: 14px;
            border: 1px solid rgba(160, 178, 202, .14);
            border-radius: 10px;
            background: rgba(5, 12, 23, .28)
        }
        .real-activation-steps>div>span {
            color: #d5b777;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .1em
        }
        .real-activation-steps strong {
            color: #edf2f8;
            font-size: 12px
        }
        .real-activation-steps small {
            color: #93a3b9;
            font-size: 11px;
            line-height: 1.55
        }
        .real-activation-footer {
            position: relative;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 13px
        }
        .real-activation-footer .button {
            min-height: 43px;
            padding-inline: 18px
        }
        .real-activation-note {
            color: #aebbd0;
            font-size: 11px
        }
        .real-activation-disclaimer {
            position: relative;
            margin: 16px 0 0;
            color: #8292a8;
            font-size: 10px;
            line-height: 1.6
        }
        .real-activation-funded {
            border-color: rgba(70, 190, 143, .3)
        }
        .real-activation-funded .real-activation-icon,
        .real-activation-funded .real-activation-status {
            color: #6be0ad;
            border-color: rgba(70, 190, 143, .35);
            background: rgba(70, 190, 143, .09)
        }
        .real-activation-paused {
            border-color: rgba(223, 164, 91, .34)
        }
        @media (max-width: 760px) {
            .real-activation { padding: 19px 16px; margin: 14px 0 18px }
            .real-activation-steps { grid-template-columns: 1fr; gap: 8px; margin: 17px 0 }
            .real-activation-steps>div { grid-template-columns: 30px minmax(0, 1fr); column-gap: 8px; padding: 11px }
            .real-activation-steps>div>span { grid-row: span 2; padding-top: 2px }
            .real-activation-steps small { grid-column: 2 }
            .real-activation-status { margin-left: 0 }
        }

        .trade-ticket-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px
        }
        .account-switch {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            flex: 0 0 auto;
            margin: 0;
            padding: 4px;
            border: 1px solid rgba(145, 164, 190, .16);
            border-radius: 11px;
            background: rgba(8, 17, 31, .72)
        }
        .account-switch-option {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-width: 76px;
            min-height: 34px;
            padding: 7px 10px;
            border: 1px solid transparent;
            border-radius: 8px;
            color: #9eacc0;
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
            transition: background .18s ease, border-color .18s ease, color .18s ease
        }
        .account-switch-option:hover {
            color: #f4f7fb;
            background: rgba(255,255,255,.045)
        }
        .account-switch-option.is-active {
            color: #f7f9fc;
            border-color: rgba(201, 169, 106, .42);
            background: linear-gradient(120deg, rgba(201,169,106,.17), rgba(201,169,106,.07));
            box-shadow: 0 2px 8px rgba(0,0,0,.14)
        }
        .account-switch-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7da9db;
            box-shadow: 0 0 0 3px rgba(125,169,219,.11)
        }
        .account-switch-real-dot {
            background: #d8b66f;
            box-shadow: 0 0 0 3px rgba(216,182,111,.11)
        }
        .account-switch-check { display: none; font-size: 10px; color: #e4c783 }
        .account-switch-option.is-active .account-switch-check { display: inline }
        .account-switch-option:focus-visible {
            outline: 2px solid #d9bd7e;
            outline-offset: 2px
        }
        @media (max-width: 520px) {
            .trade-ticket-head { align-items: flex-start; flex-wrap: wrap }
            .account-switch { width: 100% }
            .account-switch-option { flex: 1; min-width: 0 }
        }

        .trading-chart-panel {
            min-width: 0;
            overflow: hidden
        }

        .position-feed-badge {
            display: inline-flex;
            align-items: center;
            margin-left: 8px;
            padding: 3px 8px;
            border: 1px solid rgba(150, 170, 195, .28);
            border-radius: 999px;
            color: #b9c8d9;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            vertical-align: middle;
        }
        .position-feed-badge.is-live { color: #53d6aa; border-color: rgba(83,214,170,.4); }
        .position-feed-badge.is-server { color: #e6bd70; border-color: rgba(230,189,112,.4); }
        .position-feed-badge.is-reconnecting { color: #ffadad; border-color: rgba(255,137,150,.4); }

        .history-detail-modal{position:fixed;inset:0;z-index:1200;display:none;align-items:center;justify-content:center;padding:20px}
        .history-detail-modal.is-visible{display:flex}
        .history-detail-backdrop{position:absolute;inset:0;background:rgba(2,8,19,.84);backdrop-filter:blur(9px)}
        .history-detail-card{position:relative;z-index:1;width:min(690px,100%);max-height:min(91vh,900px);overflow-y:auto;padding:30px;border:1px solid rgba(148,170,199,.18);border-radius:24px;background:radial-gradient(ellipse at 8% 0%,rgba(44,91,130,.2),transparent 42%),linear-gradient(155deg,#101f38 0%,#0a1427 72%);color:#edf4fc;box-shadow:0 30px 100px rgba(0,0,0,.62);scrollbar-width:thin;scrollbar-color:#344761 transparent}
        .history-detail-close{position:absolute;top:18px;right:18px;display:grid;place-items:center;width:36px;height:36px;border:1px solid rgba(164,183,207,.18);border-radius:11px;background:rgba(255,255,255,.045);color:#aebdd0;cursor:pointer;transition:.2s}
        .history-detail-close:hover{background:rgba(255,255,255,.1);color:#fff}
        .history-detail-close svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round}
        .history-detail-topline{display:flex;align-items:center;gap:12px;padding-right:42px}
        .history-detail-brand-mark{display:grid;place-items:center;flex:0 0 42px;height:42px;border:1px solid rgba(212,168,67,.25);border-radius:13px;background:rgba(212,168,67,.09);color:#d9b65f}
        .history-detail-brand-mark svg{width:23px;height:23px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
        .history-detail-kicker{display:block;color:#879bb5;font-size:9px;font-weight:850;letter-spacing:.14em}
        .history-detail-topline h2{margin:4px 0 0;font-size:21px;letter-spacing:-.025em}
        .history-detail-status{margin-left:auto;padding:7px 11px;border:1px solid rgba(155,174,199,.2);border-radius:8px;color:#c4d1e1;font-size:9px;font-weight:900;letter-spacing:.1em}
        .history-detail-status.is-win{color:#5be0ae;border-color:rgba(66,211,154,.32);background:rgba(39,171,119,.1)}
        .history-detail-status.is-loss{color:#ff8793;border-color:rgba(255,103,119,.3);background:rgba(216,67,83,.1)}
        .history-detail-status.is-tie{color:#e7c77f;border-color:rgba(215,180,99,.3);background:rgba(191,148,56,.1)}
        .history-detail-status.is-pending{color:#c4d1e1}
        .history-result-hero{display:flex;align-items:center;gap:16px;margin:25px 0 18px;padding:20px;border:1px solid rgba(150,171,197,.15);border-radius:17px;background:rgba(255,255,255,.035)}
        .history-result-emblem{display:grid;place-items:center;flex:0 0 62px;height:62px;border:1px solid rgba(154,177,205,.22);border-radius:50%;background:rgba(150,170,198,.08);color:#b8c8dc}
        .history-result-emblem svg{width:38px;height:38px;fill:none;stroke:currentColor;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
        .history-result-copy{display:flex;flex:1;min-width:0;flex-direction:column;gap:4px}
        .history-result-copy span,.history-result-net span{color:#8fa2bb;font-size:9px;font-weight:850;letter-spacing:.12em}
        .history-result-copy strong{font-size:21px;letter-spacing:-.025em}
        .history-result-copy small{color:#91a4bd;font-size:11px}
        .history-result-net{display:flex;flex-direction:column;align-items:flex-end;gap:5px;text-align:right}
        .history-result-net strong{font-size:18px;font-weight:850;font-variant-numeric:tabular-nums}
        .history-result-hero.is-win{border-color:rgba(66,211,154,.26);background:linear-gradient(110deg,rgba(27,143,99,.18),rgba(19,72,61,.09))}
        .history-result-hero.is-win .history-result-emblem{border-color:rgba(76,224,164,.42);background:radial-gradient(circle,rgba(62,212,151,.22),rgba(29,126,91,.08));color:#58e0a9;box-shadow:0 0 28px rgba(50,205,142,.12)}
        .history-result-hero.is-win .history-result-emblem svg{filter:drop-shadow(0 0 5px rgba(75,226,164,.3))}
        .history-result-hero.is-win .history-result-copy strong,.history-result-hero.is-win .history-result-net strong{color:#5be0ae}
        .history-result-hero.is-loss{border-color:rgba(255,103,119,.27);background:linear-gradient(110deg,rgba(157,48,64,.2),rgba(83,28,42,.1))}
        .history-result-hero.is-loss .history-result-emblem{border-color:rgba(255,119,133,.42);background:rgba(218,64,83,.13);color:#ff8290}
        .history-result-hero.is-loss .history-result-emblem svg{transform:rotate(180deg)}
        .history-result-hero.is-loss .history-result-copy strong,.history-result-hero.is-loss .history-result-net strong{color:#ff8996}
        .history-result-hero.is-tie{border-color:rgba(215,180,99,.25);background:linear-gradient(110deg,rgba(157,119,45,.16),rgba(83,67,35,.08))}
        .history-result-hero.is-tie .history-result-emblem{border-color:rgba(215,180,99,.4);background:rgba(191,148,56,.1);color:#e7c77f}
        .history-detail-market{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 23px;padding:14px 16px;border:1px solid rgba(151,174,202,.12);border-radius:14px;background:rgba(5,13,27,.45)}
        .history-market-symbol{display:flex;align-items:center;gap:11px}
        .history-coin-icon{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:rgba(212,168,67,.11);color:#e2bd66}
        .history-coin-icon svg{width:21px;height:21px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round}
        .history-market-symbol div{display:flex;flex-direction:column;gap:3px}
        .history-market-symbol strong{font-size:16px;letter-spacing:.015em}
        .history-market-symbol small{color:#8297b1;font-size:9px;font-weight:800;letter-spacing:.1em}
        .history-direction-chip{padding:7px 11px;border:1px solid rgba(58,203,145,.25);border-radius:8px;background:rgba(40,166,119,.09);color:#5bdcab;font-size:10px;font-weight:900;letter-spacing:.07em}
        .history-direction-chip.is-down{border-color:rgba(255,117,132,.25);background:rgba(206,63,80,.09);color:#ff8996}
        .history-detail-section-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 10px;color:#a7b7ca;font-size:9px;font-weight:900;letter-spacing:.12em}
        .history-detail-section-title span+span{color:#70849f;font-size:8px}
        .history-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
        .history-detail-item{min-width:0;padding:13px 14px;border:1px solid rgba(151,174,202,.12);border-radius:12px;background:rgba(255,255,255,.025)}
        .history-detail-item>span{display:flex;align-items:center;gap:7px;margin-bottom:9px;color:#91a4bd;font-size:10px}
        .history-detail-item svg{width:14px;height:14px;fill:none;stroke:#8298b4;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
        .history-detail-item strong{display:block;overflow-wrap:anywhere;color:#e8eff8;font-size:14px;font-weight:750;font-variant-numeric:tabular-nums}
        .history-timeline-title{margin-top:23px}
        .history-trade-timeline{position:relative;display:grid;gap:0;padding:2px 0}
        .history-trade-timeline:before{position:absolute;top:15px;bottom:18px;left:5px;width:1px;background:rgba(135,157,184,.23);content:""}
        .history-trade-timeline>div{position:relative;display:grid;grid-template-columns:15px 105px minmax(0,1fr);align-items:center;gap:10px;min-height:34px}
        .history-timeline-dot{z-index:1;width:9px;height:9px;border:2px solid #8ca1bc;border-radius:50%;background:#101e34}
        .history-timeline-dot.is-start{border-color:#55d9a7}.history-timeline-dot.is-expiry{border-color:#d8b45e}.history-timeline-dot.is-settled{border-color:#7e9fca}
        .history-trade-timeline span{color:#91a4bd;font-size:10px}.history-trade-timeline strong{overflow-wrap:anywhere;color:#dce6f2;font-size:11px;font-weight:650;font-variant-numeric:tabular-nums}
        .history-payout-row{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:20px;padding:15px;border:1px solid rgba(212,168,67,.2);border-radius:13px;background:linear-gradient(100deg,rgba(212,168,67,.075),rgba(255,255,255,.02))}
        .history-payout-row>div{display:flex;align-items:center;gap:11px}.history-payout-icon{display:grid;place-items:center;width:36px;height:36px;border-radius:11px;background:rgba(212,168,67,.12);color:#dfbd70}
        .history-payout-icon svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
        .history-payout-row>div div{display:flex;flex-direction:column;gap:3px}.history-payout-row>div strong{font-size:12px}.history-payout-row small{color:#8297b1;font-size:9px}.history-payout-row>strong{color:#e7c77f;font-size:16px;font-weight:850;font-variant-numeric:tabular-nums}
        .history-detail-note{margin:16px 0 0;color:#8297b1;font-size:10px;line-height:1.55}
        @media(max-width:560px){.history-detail-modal{padding:9px}.history-detail-card{padding:21px 16px;border-radius:18px}.history-detail-topline{gap:9px;padding-right:34px}.history-detail-brand-mark{flex-basis:36px;height:36px}.history-detail-topline h2{font-size:18px}.history-detail-status{padding:6px 8px;font-size:8px}.history-result-hero{gap:10px;padding:14px}.history-result-emblem{flex-basis:48px;height:48px}.history-result-emblem svg{width:30px;height:30px}.history-result-copy strong{font-size:16px}.history-result-copy small{font-size:9px}.history-result-net strong{font-size:14px}.history-detail-grid{gap:7px}.history-detail-item{padding:11px}.history-detail-item strong{font-size:12px}}
        @media(max-width:380px){.history-result-hero{flex-wrap:wrap}.history-result-net{margin-left:58px;align-items:flex-start;text-align:left}.history-trade-timeline>div{grid-template-columns:15px 86px minmax(0,1fr)}}

        .data-table tbody tr.trade-history-row {
            cursor: pointer;
            transition: background-color .15s ease
        }

        .data-table tbody tr.trade-history-row:focus-visible {
            outline: 2px solid var(--accent, #d4a843);
            outline-offset: -2px
        }

        .data-table tbody tr.trade-row-win {
            background: rgba(35, 170, 116, .075)
        }

        .data-table tbody tr.trade-row-loss {
            background: rgba(220, 75, 88, .075)
        }

        .data-table tbody tr.trade-row-win:hover {
            background: rgba(35, 170, 116, .13)
        }

        .data-table tbody tr.trade-row-loss:hover {
            background: rgba(220, 75, 88, .13)
        }

        .trade-result-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 58px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .07em
        }

        .trade-result-badge.trade-result-win {
            color: #55e0ad;
            background: rgba(35, 170, 116, .16);
            border: 1px solid rgba(85, 224, 173, .35)
        }

        .trade-result-badge.trade-result-loss {
            color: #ff8996;
            background: rgba(220, 75, 88, .16);
            border: 1px solid rgba(255, 137, 150, .35)
        }

        .data-table tbody tr.trade-history-row:hover {
            background: rgba(255, 255, 255, .055)
        }

        .trading-chart-heading {
            align-items: center
        }

        .trading-chart-heading h2 {
            font-variant-numeric: tabular-nums;
            letter-spacing: .02em
        }

        .chart-market-summary {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            padding: 15px 19px 13px;
            border-top: 1px solid #253247;
            border-bottom: 1px solid #253247;
            background: #0c1523
        }

        .chart-price-main {
            display: grid;
            gap: 5px
        }

        .chart-price-main strong {
            font: 800 clamp(22px, 2.7vw, 31px)/1.1 'Manrope';
            font-variant-numeric: tabular-nums;
            letter-spacing: -.035em
        }

        .chart-price-main span {
            font-size: 11px;
            color: #8e9cb0
        }

        .chart-ohlc {
            display: flex;
            gap: 13px;
            flex-wrap: wrap;
            justify-content: flex-end;
            color: #8190a5;
            font-size: 10px;
            font-variant-numeric: tabular-nums
        }

        .chart-ohlc b {
            color: #d9e0e9;
            font-weight: 700;
            margin-left: 3px
        }

        .chart-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 17px
        }

        .chart-toolbar>label {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #8f9db0;
            font-size: 11px
        }

        .chart-toolbar select {
            min-width: 115px;
            border: 1px solid #35445a;
            border-radius: 6px;
            background: #0b1421;
            color: #e9edf5;
            padding: 8px 10px;
            font-size: 11px
        }

        .chart-zoom {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            padding: 0 17px 10px;
            color: #8f9db0;
            font-size: 10px
        }

        .chart-zoom button {
            border: 1px solid #35445a;
            border-radius: 5px;
            background: #0b1421;
            color: #dce4ef;
            padding: 5px 10px;
            cursor: pointer
        }

        .chart-zoom button:hover {
            border-color: #c8a96a;
            color: #f0d99f
        }

        .chart-intervals {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap
        }

        .chart-intervals button {
            min-width: 37px;
            padding: 7px 8px;
            border: 1px solid transparent;
            border-radius: 5px;
            background: transparent;
            color: #8f9db0;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer
        }

        .chart-intervals button:hover,
        .chart-intervals button.active {
            border-color: #c8a96a55;
            background: #c8a96a18;
            color: #f0d99f
        }

        .market-chart-wrap {
            height: 390px;
            position: relative;
            min-width: 0;
            background: #0b1421;
            border-top: 1px solid #202d40;
            border-bottom: 1px solid #202d40
        }

        .market-chart-wrap canvas {
            display: block;
            width: 100%;
            height: 100%;
            touch-action: pan-y
        }

        .chart-loading {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            color: #8c9bb0;
            font-size: 12px;
            pointer-events: none;
            background: #0b1421aa
        }

        .chart-loading.visible {
            display: flex
        }

        .chart-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 17px;
            color: #7e8da2;
            font-size: 10px;
            font-variant-numeric: tabular-nums
        }

        .trade-process-hint {
            padding: 12px 14px;
            border: 1px solid #29384d;
            border-radius: 8px;
            background: #0b1421;
            color: #9aa8ba;
            font-size: 11px;
            line-height: 1.55
        }

        .trade-process-hint summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            color: #e5ebf3;
            font-weight: 700;
            cursor: pointer;
            list-style: none
        }

        .trade-process-hint summary::-webkit-details-marker {
            display: none
        }

        .trade-process-hint summary:after {
            content: '＋';
            color: #d5b879
        }

        .trade-process-hint[open] summary:after {
            content: '−'
        }

        .trade-process-hint summary span {
            margin-left: auto;
            color: #8f9db0;
            font-size: 10px;
            font-weight: 500
        }

        .trade-process-hint ol {
            margin: 10px 0 0;
            padding-left: 18px
        }

        .trade-process-hint li+li {
            margin-top: 3px
        }

        #chart-health {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap
        }

        #chart-health .live-dot {
            width: 6px;
            height: 6px;
            margin: 0
        }

        @media(max-width:720px) {
            .market-chart-wrap {
                height: 330px
            }

            .chart-market-summary {
                align-items: flex-start;
                flex-direction: column
            }

            .chart-ohlc {
                justify-content: flex-start;
                gap: 8px 12px
            }

            .chart-toolbar {
                align-items: flex-start;
                flex-direction: column
            }

            .chart-intervals {
                width: 100%;
                justify-content: space-between
            }

            .chart-foot {
                font-size: 9px
            }
        }

        @media(max-width:420px) {
            .market-chart-wrap {
                height: 290px
            }

            .chart-ohlc {
                font-size: 9px;
                gap: 7px
            }

            .chart-toolbar {
                padding-inline: 12px
            }

            .chart-intervals button {
                min-width: 32px;
                padding-inline: 5px
            }
        }

        .position-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 18px
        }

        .position-modal.is-visible {
            display: flex
        }

        .position-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(2, 7, 15, .82);
            backdrop-filter: blur(7px)
        }

        .position-modal-card {
            position: relative;
            width: min(100%, 520px);
            max-height: calc(100vh - 36px);
            overflow: auto;
            border: 1px solid #35445a;
            border-radius: 16px;
            background: #101b2b;
            box-shadow: 0 24px 90px #0009;
            padding: 22px;
            color: #e8eef7
        }

        .position-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px
        }

        .position-modal-head h2 {
            margin: 5px 0 0;
            font-size: 21px
        }

        .position-market-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0 12px
        }

        .position-market-line strong {
            font-size: 17px
        }

        .position-market-line span {
            font-size: 11px;
            font-weight: 800;
            border-radius: 5px;
            padding: 5px 9px;
            background: #173a36;
            color: #53d6aa
        }

        .position-market-line span.down {
            background: #44252d;
            color: #ff8996
        }

        .position-live-strip {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
            align-items: stretch;
            gap: 10px;
            margin-bottom: 12px
        }

        .position-live-price {
            min-width: 0;
            padding: 16px;
            border-radius: 10px;
            background: #0a1422;
            border: 1px solid #29384d;
            display: grid;
            align-content: center;
            gap: 8px
        }

        .position-live-price-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px
        }

        .position-live-price-heading>span:first-child,
        .position-details-grid span {
            font-size: 10px;
            color: #91a0b4;
            letter-spacing: .06em
        }

        .position-live-price strong {
            font-size: clamp(25px, 6vw, 34px);
            font-variant-numeric: tabular-nums
        }

        .position-live-price strong {
            transition: color .15s ease, text-shadow .15s ease
        }

        .position-live-price small {
            font-size: 11px;
            color: #9aa9bc
        }

        .position-live-outcome {
            min-width: 0;
            display: grid;
            align-content: center;
            justify-items: end;
            text-align: right;
            gap: 7px;
            padding: 14px 16px;
            border: 1px solid #34445a;
            border-radius: 10px;
            background: #0b1625
        }

        .position-live-outcome>span {
            font-size: 10px;
            letter-spacing: .09em;
            color: #91a0b4;
            font-weight: 800
        }

        .position-live-outcome strong {
            font-size: 25px;
            font-weight: 850;
            font-variant-numeric: tabular-nums;
            color: #dce5f2;
            transition: color .15s ease
        }

        .position-live-outcome small {
            font-size: 11px;
            color: #9aa9bc
        }

        .position-live-outcome.is-win {
            border-color: #238e7055;
            background: #0d241f
        }

        .position-live-outcome.is-win strong {
            color: #53d6aa
        }

        .position-live-outcome.is-loss {
            border-color: #b34f5b66;
            background: #28171d
        }

        .position-live-outcome.is-loss strong {
            color: #ff8996
        }

        .position-market-line {
            font-variant-numeric: tabular-nums
        }

        .position-modal-card {
            max-height: calc(100vh - 32px);
            overflow: auto
        }

        @media(max-width:520px) {
            .position-modal-card {
                width: calc(100vw - 24px);
                padding: 16px
            }

            .position-details-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .position-live-strip {
                grid-template-columns: 1fr
            }

            .position-live-outcome {
                justify-items: start;
                text-align: left
            }
        }

        .position-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
            margin: 0 0 14px
        }

        .position-detail-item {
            min-width: 0;
            display: grid;
            align-content: space-between;
            gap: 10px;
            min-height: 76px;
            padding: 12px;
            border: 1px solid #29384d;
            border-radius: 10px;
            background: linear-gradient(145deg, #101e30, #0b1625);
            transition: border-color .18s ease, background .18s ease
        }

        .position-detail-item:hover {
            border-color: #53657d;
            background: #122238
        }

        .position-detail-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-width: 0
        }

        .position-detail-label>span {
            font-size: 10px;
            line-height: 1.3;
            color: #91a0b4;
            letter-spacing: .035em
        }

        .position-detail-icon {
            width: 15px;
            height: 15px;
            flex: 0 0 15px;
            fill: none;
            stroke: #c8a96a;
            stroke-width: 1.7;
            stroke-linecap: round;
            stroke-linejoin: round
        }

        .position-detail-item strong {
            display: block;
            min-width: 0;
            color: #edf3fb;
            font-size: 13px;
            line-height: 1.35;
            font-weight: 750;
            font-variant-numeric: tabular-nums;
            overflow-wrap: anywhere
        }

        .position-result:empty {
            display: none
        }

        .position-result {
            padding: 12px;
            border-radius: 8px;
            background: #19283a;
            color: #c2cede;
            font-size: 12px;
            line-height: 1.5
        }

        .position-result.is-win {
            background: #12382f;
            color: #7de0bd
        }

        .position-result.is-loss {
            background: #42232c;
            color: #ff9aa5
        }

        .position-result.is-tie {
            background: #3b3220;
            color: #efd9a5
        }

        .position-close {
            width: 100%;
            margin-top: 14px
        }

        .position-close:disabled {
            opacity: .55;
            cursor: not-allowed
        }

        .position-modal-x {
            position: absolute;
            right: 14px;
            top: 14px;
            width: 34px;
            height: 34px;
            border: 1px solid #35445a;
            border-radius: 50%;
            background: #0b1421;
            color: #b9c6d7;
            font-size: 20px;
            cursor: pointer;
            z-index: 2
        }

        .position-modal-x:disabled {
            opacity: .35;
            cursor: wait
        }

        .position-modal-card {
            animation: positionModalIn .22s ease-out
        }

        @keyframes positionModalIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(.98)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .position-result-hero {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 16px 0 12px;
            padding: 16px;
            border: 1px solid #35445a;
            border-radius: 12px;
            background: linear-gradient(135deg, #17243a, #0d1726)
        }

        .position-result-hero[hidden] {
            display: none
        }

        .position-result-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            border: 1px solid #4f667f;
            background: #20334d;
            color: #dce7f4;
            font-size: 25px;
            font-weight: 900
        }

        .position-result-hero>div:last-child {
            display: grid;
            gap: 4px
        }

        .position-result-hero span {
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .12em;
            color: #9dafc4
        }

        .position-result-hero strong {
            font-size: 20px
        }

        .position-modal-card.is-win .position-result-hero {
            border-color: #238e7055;
            background: linear-gradient(135deg, #12382f, #0d1e21)
        }

        .position-modal-card.is-win .position-result-icon {
            border-color: #53d6aa;
            background: #1b5b49;
            color: #aaf0d4
        }

        .position-modal-card.is-loss .position-result-hero {
            border-color: #b34f5b66;
            background: linear-gradient(135deg, #42232c, #241923)
        }

        .position-modal-card.is-loss .position-result-icon {
            border-color: #ff8996;
            background: #66303a;
            color: #ffc1c7
        }

        .position-modal-card.is-tie .position-result-hero {
            border-color: #c8a96a66;
            background: linear-gradient(135deg, #3b3220, #211d16)
        }

        .position-modal-card.is-tie .position-result-icon {
            border-color: #d5b879;
            background: #5c4b2b;
            color: #efd9a5
        }

        @media(max-width:420px) {
            .position-modal-card {
                padding: 17px
            }

            .position-details-grid {
                gap: 7px
            }

            .position-details-grid>div {
                padding: 10px
            }
        }
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
            const palette = {
                text: '#8b9ab0',
                muted: '#65758b',
                grid: 'rgba(139,154,176,.13)',
                border: '#26364b',
                up: '#22b78b',
                down: '#ed6474',
                crosshair: 'rgba(190,204,222,.55)',
                panel: '#0b1421'
            };
            let interval = '15m',
                candles = [],
                loadingRequest = false,
                failed = false,
                crosshair = null,
                visibleCandles = 80;
            let plot = null,
                liveSocket = null,
                reconnectTimer = null,
                retryTimer = null,
                reconnectAttempts = 0,
                streamGeneration = 0,
                tickerFallbackBusy = false;

            const requestJson = async (url, options = {}) => {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                const timeoutMs = options.timeoutMs || 8000;
                const retries = Math.max(0, Math.min(3, Number(options.retries || 0)));
                const { timeoutMs: ignoredTimeout, retries: ignoredRetries, ...fetchOptions } = options;

                for (let attempt = 0; ; attempt++) {
                    const controller = new AbortController();
                    const timeout = window.setTimeout(() => controller.abort(), timeoutMs);
                    try {
                        const response = await fetch(url, {
                            ...fetchOptions,
                            signal: controller.signal,
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                                ...(options.headers || {})
                            }
                        });

                        if (response.status === 401 || response.status === 419) {
                            notify('Your session has expired. Please sign in again.', 'error');
                            window.location.assign('/login');
                            const authError = new Error('Your session has expired. Sign in again to continue.');
                            authError.status = response.status;
                            throw authError;
                        }

                        const contentType = response.headers.get('content-type') || '';
                        if (!contentType.includes('application/json')) {
                            if (response.redirected || response.status === 200) {
                                window.location.assign('/login');
                                const authError = new Error('Your session has expired. Sign in again to continue.');
                                authError.status = response.status || 401;
                                throw authError;
                            }
                        }

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            const error = new Error(payload.message || 'The request could not be completed.');
                            error.status = response.status;
                            if (response.status >= 500 && attempt < retries) {
                                await new Promise(resolve => window.setTimeout(resolve,
                                    Math.min(4000, 300 * Math.pow(2, attempt)) + Math.random() * 200));
                                continue;
                            }
                            throw error;
                        }
                        return payload;
                    } catch (error) {
                        const retryable = error.name === 'AbortError' || !Number.isInteger(error.status);
                        if (retryable && attempt < retries) {
                            await new Promise(resolve => window.setTimeout(resolve,
                                Math.min(4000, 300 * Math.pow(2, attempt)) + Math.random() * 200));
                            continue;
                        }
                        if (error.name === 'AbortError') {
                            throw new Error('The server is taking too long to respond. Please retry using the same request.');
                        }
                        throw error;
                    } finally {
                        window.clearTimeout(timeout);
                    }
                }
            };
            const notify = (message, type = 'success') => {
                if (typeof window.shellToast === 'function') {
                    window.shellToast(message, type);
                    return;
                }
                const region = document.getElementById('toast-region');
                if (region) {
                    const item = document.createElement('div');
                    item.className = 'toast toast-' + type;
                    item.textContent = message;
                    region.appendChild(item);
                    window.setTimeout(() => item.remove(), 4200);
                }
            };

            const number = value => Number(value);
            const formatPrice = value => {
                const n = number(value);
                if (!Number.isFinite(n)) return '—';
                const decimals = n >= 1000 ? 2 : n >= 1 ? 4 : n >= .01 ? 6 : 8;
                return new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: decimals
                }).format(n);
            };
            const formatAxis = value => {
                const n = number(value);
                if (!Number.isFinite(n)) return '';
                const decimals = n >= 1000 ? 2 : n >= 1 ? 4 : n >= .01 ? 6 : 8;
                return new Intl.NumberFormat(undefined, {
                    maximumFractionDigits: decimals
                }).format(n);
            };
            const formatTime = timestamp => {
                const date = new Date(timestamp);
                if (interval === '1d') return date.toLocaleDateString(undefined, {
                    day: '2-digit',
                    month: 'short'
                });
                if (interval === '4h' || interval === '1h') return date.toLocaleString(undefined, {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                return date.toLocaleTimeString(undefined, {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            };
            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            };

            function dimensions() {
                const width = canvas.clientWidth || 700,
                    height = canvas.clientHeight || 390;
                const ratio = Math.min(window.devicePixelRatio || 1, 2);
                if (canvas.width !== Math.round(width * ratio) || canvas.height !== Math.round(height * ratio)) {
                    canvas.width = Math.round(width * ratio);
                    canvas.height = Math.round(height * ratio);
                }
                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                return {
                    width,
                    height,
                    ratio
                };
            }

            function draw() {
                const {
                    width,
                    height
                } = dimensions();
                ctx.clearRect(0, 0, width, height);
                ctx.fillStyle = palette.panel;
                ctx.fillRect(0, 0, width, height);
                const margin = {
                    top: 17,
                    right: 88,
                    bottom: 31,
                    left: 12
                };
                const chartWidth = Math.max(30, width - margin.left - margin.right);
                const chartHeight = Math.max(30, height - margin.top - margin.bottom);
                const left = margin.left,
                    right = left + chartWidth,
                    top = margin.top,
                    bottom = top + chartHeight;
                plot = {
                    left,
                    right,
                    top,
                    bottom,
                    width: chartWidth,
                    height: chartHeight
                };
                if (!candles.length) {
                    ctx.fillStyle = palette.text;
                    ctx.font = '12px DM Sans, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText(failed ? 'Market data is temporarily unavailable' : 'Waiting for market data', width /
                        2, height / 2);
                    return;
                }

                const visible = candles.slice(-visibleCandles);
                const values = visible.flatMap(c => [number(c.high), number(c.low)]).filter(Number.isFinite);
                let min = Math.min(...values),
                    max = Math.max(...values);
                const rawRange = max - min || Math.max(Math.abs(max) * .001, 1e-8);
                const padding = rawRange * .09;
                min -= padding;
                max += padding;
                const range = max - min;
                const y = value => bottom - ((value - min) / range) * chartHeight;
                const step = chartWidth / visible.length;
                const bodyWidth = Math.max(2, Math.min(13, step * .64));

                // Horizontal price grid and right-hand price scale.
                ctx.font = '10px DM Sans, sans-serif';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                for (let i = 0; i <= 5; i++) {
                    const value = max - (range * i / 5),
                        py = top + (chartHeight * i / 5);
                    ctx.strokeStyle = palette.grid;
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(left, py + .5);
                    ctx.lineTo(right, py + .5);
                    ctx.stroke();
                    ctx.fillStyle = palette.text;
                    ctx.fillText(formatAxis(value), right + 10, py);
                }
                // Vertical time grid and time labels.
                const timeCount = Math.min(6, visible.length);
                ctx.textAlign = 'center';
                ctx.textBaseline = 'top';
                for (let i = 0; i < timeCount; i++) {
                    const index = Math.round(i * (visible.length - 1) / Math.max(1, timeCount - 1));
                    const x = left + index * step + step / 2;
                    ctx.strokeStyle = palette.grid;
                    ctx.beginPath();
                    ctx.moveTo(x, top);
                    ctx.lineTo(x, bottom);
                    ctx.stroke();
                    ctx.fillStyle = palette.text;
                    ctx.fillText(formatTime(visible[index].time), x, bottom + 9);
                }
                // Candlesticks.
                visible.forEach((c, i) => {
                    const open = number(c.open),
                        close = number(c.close),
                        high = number(c.high),
                        low = number(c.low);
                    const x = left + i * step + step / 2,
                        up = close >= open,
                        color = up ? palette.up : palette.down;
                    ctx.strokeStyle = color;
                    ctx.lineWidth = Math.max(1, Math.min(1.5, step * .13));
                    ctx.beginPath();
                    ctx.moveTo(x, y(high));
                    ctx.lineTo(x, y(low));
                    ctx.stroke();
                    const bodyTop = y(Math.max(open, close)),
                        bodyBottom = y(Math.min(open, close));
                    ctx.fillStyle = color;
                    ctx.fillRect(x - bodyWidth / 2, bodyTop, bodyWidth, Math.max(1.5, bodyBottom - bodyTop));
                });

                const last = candles[candles.length - 1],
                    lastPrice = number(last.close),
                    priceY = y(lastPrice);
                // Current price guide and value marker.
                ctx.save();
                ctx.setLineDash([4, 4]);
                ctx.strokeStyle = last.close >= last.open ? palette.up : palette.down;
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(left, priceY);
                ctx.lineTo(right, priceY);
                ctx.stroke();
                ctx.restore();
                ctx.fillStyle = last.close >= last.open ? palette.up : palette.down;
                ctx.fillRect(right + 3, priceY - 10, 82, 20);
                ctx.fillStyle = '#07131b';
                ctx.font = 'bold 10px DM Sans, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(formatAxis(lastPrice), right + 44, priceY);

                // Crosshair follows pointer without affecting chart data.
                if (crosshair && crosshair.x >= left && crosshair.x <= right && crosshair.y >= top && crosshair.y <=
                    bottom) {
                    ctx.save();
                    ctx.strokeStyle = palette.crosshair;
                    ctx.setLineDash([3, 4]);
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(crosshair.x, top);
                    ctx.lineTo(crosshair.x, bottom);
                    ctx.moveTo(left, crosshair.y);
                    ctx.lineTo(right, crosshair.y);
                    ctx.stroke();
                    ctx.restore();
                    const value = max - ((crosshair.y - top) / chartHeight) * range;
                    ctx.fillStyle = '#34465d';
                    ctx.fillRect(right + 3, crosshair.y - 9, 82, 18);
                    ctx.fillStyle = '#f1f4f8';
                    ctx.font = '10px DM Sans, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(formatAxis(value), right + 44, crosshair.y);
                }
            }

            function updateSummary() {
                if (!candles.length) return;
                const first = candles[0],
                    last = candles[candles.length - 1],
                    prior = candles.length > 1 ? candles[candles.length - 2] : null;
                ['open', 'high', 'low', 'close'].forEach(key => setText('chart-' + key, formatPrice(last[key])));
                setText('chart-current-price', formatPrice(last.close));
                const change = prior ? ((number(last.close) - number(prior.close)) / number(prior.close)) * 100 : 0;
                const changeEl = document.getElementById('chart-price-change');
                changeEl.textContent = (change >= 0 ? '+' : '') + change.toFixed(2) + '% · ' + interval;
                changeEl.style.color = change >= 0 ? palette.up : palette.down;
                setText('chart-last-price', 'Last price ' + formatPrice(last.close) + ' USDT');
            }

            function closeLiveStream() {
                streamGeneration++;
                reconnectAttempts = 0;
                if (reconnectTimer) {
                    window.clearTimeout(reconnectTimer);
                    reconnectTimer = null;
                }
                if (retryTimer) {
                    window.clearTimeout(retryTimer);
                    retryTimer = null;
                }
                if (liveSocket) {
                    liveSocket.onopen = liveSocket.onmessage = liveSocket.onerror = liveSocket.onclose = null;
                    try {
                        liveSocket.close();
                    } catch (_) {}
                    liveSocket = null;
                }
            }

            function applyLiveCandle(kline) {
                const candle = {
                    time: number(kline.t),
                    open: String(kline.o),
                    high: String(kline.h),
                    low: String(kline.l),
                    close: String(kline.c),
                    volume: String(kline.v)
                };
                if (![candle.time, candle.open, candle.high, candle.low, candle.close].every(v => Number.isFinite(
                        number(v)))) return;
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
                window.dispatchEvent(new CustomEvent('shell:market-price', {
                    detail: {
                        symbol: marketSelect.value,
                        price: candle.close
                    }
                }));
                setText('chart-health', 'LIVE');
                setText('chart-updated', 'Live · ' + new Date().toLocaleTimeString());
            }

            function applyLiveTrade(trade) {
                const price = Number(trade.p);
                const timestamp = Number(trade.T || Date.now());
                if (!Number.isFinite(price) || price <= 0 || !Number.isFinite(timestamp)) return;

                const intervalMs = ({
                    '1m': 60000, '5m': 300000, '15m': 900000,
                    '1h': 3600000, '4h': 14400000, '1d': 86400000
                })[interval] || 60000;
                const bucket = Math.floor(timestamp / intervalMs) * intervalMs;
                const value = String(trade.p);
                const last = candles[candles.length - 1];

                if (!last || bucket > last.time) {
                    candles.push({
                        time: bucket, open: value, high: value, low: value,
                        close: value, volume: String(trade.q || '0')
                    });
                } else if (bucket === last.time) {
                    last.high = String(Math.max(Number(last.high), price));
                    last.low = String(Math.min(Number(last.low), price));
                    last.close = value;
                    last.volume = String((Number(last.volume) || 0) + (Number(trade.q) || 0));
                } else {
                    return;
                }

                if (candles.length > 200) candles.shift();
                failed = false;
                updateSummary();
                draw();
                window.dispatchEvent(new CustomEvent('shell:market-price', {
                    detail: { symbol: marketSelect.value, price: value, source: 'websocket' }
                }));
                setText('chart-health', 'LIVE');
                setText('chart-updated', 'Live tick · ' + new Date(timestamp).toLocaleTimeString());
            }

            function connectLiveStream(symbol, selectedInterval, generation, hostIndex = 0) {
                if (generation !== streamGeneration) return;
                // Trade stream delivers every executed market trade. Kline streams
                // can update only once every 1–2 seconds for larger intervals.
                const streamKey = symbol.toLowerCase() + '@trade';
                const hosts = [
                    'wss://stream.binance.com:443/ws/',
                    'wss://data-stream.binance.vision:443/ws/'
                ];
                setText('chart-health', 'CONNECTING');
                let socket;
                let connectTimeout = null;
                try {
                    socket = new WebSocket(hosts[hostIndex] + streamKey);
                    liveSocket = socket;
                    connectTimeout = window.setTimeout(() => {
                        if (generation !== streamGeneration || socket.readyState === WebSocket.OPEN || socket.readyState === WebSocket.CLOSED) return;
                        socket.onopen = socket.onmessage = socket.onerror = socket.onclose = null;
                        try { socket.close(); } catch (_) {}
                        if (liveSocket === socket) liveSocket = null;
                        if (hostIndex + 1 < hosts.length) connectLiveStream(symbol, selectedInterval, generation, hostIndex + 1);
                        else scheduleStreamReconnect(symbol, selectedInterval, generation);
                    }, 4000);
                } catch (_) {
                    if (hostIndex + 1 < hosts.length) {
                        connectLiveStream(symbol, selectedInterval, generation, hostIndex + 1);
                    } else {
                        scheduleStreamReconnect(symbol, selectedInterval, generation);
                    }
                    return;
                }

                socket.onopen = () => {
                    if (generation !== streamGeneration) {
                        socket.close();
                        return;
                    }
                    if (connectTimeout) { window.clearTimeout(connectTimeout); connectTimeout = null; }
                    reconnectAttempts = 0;
                    setText('chart-health', 'LIVE');
                    setText('chart-updated', 'Live stream connected');
                };
                socket.onmessage = event => {
                    if (generation !== streamGeneration) return;
                    try {
                        const message = JSON.parse(event.data);
                        if (message.e === 'trade' && message.p !== undefined) applyLiveTrade(message);
                    } catch (_) {
                        // Ignore malformed stream messages; the next valid candle update will recover.
                    }
                };
                socket.onerror = () => {
                    if (generation === streamGeneration) setText('chart-health', 'RECONNECTING');
                };
                socket.onclose = () => {
                    if (connectTimeout) { window.clearTimeout(connectTimeout); connectTimeout = null; }
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
                const delay = Math.min(30000, 1000 * Math.pow(2, Math.min(reconnectAttempts, 5)) + Math.random() * 750);
                reconnectAttempts++;
                reconnectTimer = window.setTimeout(() => connectLiveStream(symbol, selectedInterval, generation, 0),
                    delay);
            }

            async function fetchPublicCandles(symbol, selectedInterval) {
                const query = new URLSearchParams({
                    symbol,
                    interval: selectedInterval,
                    limit: '200'
                });
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
                            response = await fetch(endpoint, {
                                headers: {
                                    'Accept': 'application/json'
                                },
                                cache: 'no-store',
                                signal: controller.signal
                            });
                        } finally {
                            window.clearTimeout(timeout);
                        }
                        if (!response.ok) throw new Error('Public market feed returned HTTP ' + response.status);
                        const rows = await response.json();
                        if (!Array.isArray(rows) || !rows.length) throw new Error(
                            'Public market feed returned no candles.');
                        const parsed = rows.filter(row => Array.isArray(row) && row.length >= 6).map(row => ({
                            time: Number(row[0]),
                            open: String(row[1]),
                            high: String(row[2]),
                            low: String(row[3]),
                            close: String(row[4]),
                            volume: String(row[5])
                        })).filter(c => [c.time, c.open, c.high, c.low, c.close].every(v => Number.isFinite(
                            Number(v))));
                        if (parsed.length) return parsed;
                        throw new Error('Public market feed returned invalid candle data.');
                    } catch (error) {
                        lastError = error;
                    }
                }
                throw lastError || new Error('No public market feed is available.');
            }

            // The public trade WebSocket is the live tick source. The HTTP ticker
            // helper is retained only as an unused compatibility fallback; it is not
            // polled on a timer and never authorizes a trade or settlement.
            async function pollPublicTicker() {
                if (tickerFallbackBusy || !marketSelect.value) return;
                const symbol = marketSelect.value;
                tickerFallbackBusy = true;
                const query = new URLSearchParams({ symbol });
                const endpoints = [
                    'https://data-api.binance.vision/api/v3/ticker/price?' + query,
                    'https://api.binance.com/api/v3/ticker/price?' + query
                ];
                try {
                    let ticker = null;
                    for (const endpoint of endpoints) {
                        const controller = new AbortController();
                        const timeout = window.setTimeout(() => controller.abort(), 3500);
                        try {
                            const response = await fetch(endpoint, {
                                headers: { 'Accept': 'application/json' },
                                cache: 'no-store',
                                signal: controller.signal
                            });
                            if (!response.ok) continue;
                            const data = await response.json();
                            if (data?.symbol === symbol && Number.isFinite(Number(data.price)) && Number(data.price) > 0) {
                                ticker = data;
                                break;
                            }
                        } catch (_) {
                            // Try the alternate public endpoint.
                        } finally {
                            window.clearTimeout(timeout);
                        }
                    }

                    if (!ticker) {
                        if (marketSelect.value === symbol) {
                            setText('chart-health', 'RECONNECTING');
                            if (activePosition && activePosition.status === 'open' && activePosition.symbol === symbol) {
                                setText('position-price-move', 'Live price feed interrupted · reconnecting automatically. Your saved position remains active.');
                            }
                        }
                        return;
                    }
                    if (marketSelect.value !== symbol) return;
                    const price = String(ticker.price);
                    const last = candles[candles.length - 1];
                    if (last) {
                        last.close = price;
                        last.high = String(Math.max(Number(last.high), Number(price)));
                        last.low = String(Math.min(Number(last.low), Number(price)));
                        updateSummary();
                        draw();
                    } else {
                        setText('chart-current-price', formatPrice(price));
                    }
                    window.dispatchEvent(new CustomEvent('shell:market-price', {
                        detail: { symbol, price }
                    }));
                    setText('chart-health', 'LIVE');
                    setText('chart-updated', 'Live price · ' + new Date().toLocaleTimeString());
                } finally {
                    tickerFallbackBusy = false;
                }
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
                        const result = await requestJson(@json(route('markets.candles')) + '?' + new URLSearchParams({
                            symbol,
                            interval: selectedInterval,
                            limit: '200'
                        }));
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
                    candles = candleRows.map(c => ({
                            ...c,
                            time: number(c.time)
                        }))
                        .filter(c => [c.time, c.open, c.high, c.low, c.close].every(v => Number.isFinite(number(
                            v))));
                    if (!candles.length) throw new Error('The market returned invalid candle data.');
                    updateSummary();
                    draw();
                    const initialCandle = candles[candles.length - 1];
                    if (initialCandle) window.dispatchEvent(new CustomEvent('shell:market-price', {
                        detail: {
                            symbol,
                            price: initialCandle.close
                        }
                    }));
                    setText('chart-health', 'CONNECTING');
                    setText('chart-updated', 'Opening live market stream…');
                    connectLiveStream(symbol, selectedInterval, generation);
                } catch (error) {
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

            function updateZoomLabel() {
                setText('chart-zoom-level', visibleCandles + ' candles');
            }
            document.getElementById('chart-zoom-in').addEventListener('click', () => {
                visibleCandles = Math.max(20, visibleCandles - 10);
                updateZoomLabel();
                draw();
            });
            document.getElementById('chart-zoom-out').addEventListener('click', () => {
                visibleCandles = Math.min(200, visibleCandles + 10);
                updateZoomLabel();
                draw();
            });
            document.getElementById('chart-zoom-reset').addEventListener('click', () => {
                visibleCandles = 80;
                updateZoomLabel();
                draw();
            });
            updateZoomLabel();

            marketSelect.addEventListener('change', () => {
                tradeSymbol.value = marketSelect.value;
                candles = [];
                loadCandles();
            });
            tradeSymbol.addEventListener('change', () => {
                marketSelect.value = tradeSymbol.value;
                candles = [];
                loadCandles();
            });
            document.querySelectorAll('[data-interval]').forEach(button => button.addEventListener('click', () => {
                interval = button.dataset.interval;
                document.querySelectorAll('[data-interval]').forEach(item => {
                    const active = item === button;
                    item.classList.toggle('active', active);
                    item.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                candles = [];
                loadCandles();
            }));
            canvas.addEventListener('pointermove', event => {
                if (!plot) return;
                const rect = canvas.getBoundingClientRect();
                crosshair = {
                    x: event.clientX - rect.left,
                    y: event.clientY - rect.top
                };
                draw();
            });
            canvas.addEventListener('pointerleave', () => {
                crosshair = null;
                draw();
            });
            if ('ResizeObserver' in window) {
                new ResizeObserver(draw).observe(canvas.parentElement);
            } else {
                window.addEventListener('resize', draw);
            }
            draw(); // Paint the chart frame immediately, before the first network response.
            loadCandles();
            // The market WebSocket is the primary live price source. Do not
            // add a periodic ticker fetch that makes the terminal feel delayed.
            window.addEventListener('beforeunload', closeLiveStream);

            function updateTradeCountdowns() {
                const now = Date.now();
                document.querySelectorAll('[data-expiry]').forEach(element => {
                    const seconds = Math.max(0, Math.ceil((Number(element.dataset.expiry) - now) / 1000));
                    const expiresAt = Number(element.dataset.expiry);
                    if (!element.dataset.expiryTriggerScheduled) {
                        element.dataset.expiryTriggerScheduled = 'true';
                        // Schedule against the exact server-provided expiry timestamp.
                        // The countdown's one-second repaint is display-only.
                        window.setTimeout(() => {
                            element.textContent = 'Awaiting settlement';
                            if (!element.dataset.settlementRequested) {
                                element.dataset.settlementRequested = 'true';
                                settleExpiredTrades();
                            }
                        }, Math.max(0, expiresAt - Date.now()));
                    }
                    if (seconds === 0) {
                        element.textContent = 'Awaiting settlement';
                        return;
                    }
                    element.textContent = Math.floor(seconds / 60) + 'm ' + String(seconds % 60).padStart(2,
                        '0') + 's';
                });
            }
            updateTradeCountdowns();
            window.setInterval(updateTradeCountdowns, 1000);

            // Reconcile visible open rows with the authoritative server settlement state.
            // A row is never marked won/lost from the browser's live chart price.
            async function reconcileOpenTrades() {
                const rows = [...document.querySelectorAll('[data-open-trade]')];
                for (const row of rows) {
                    try {
                        const id = row.dataset.openTrade;
                        const response = await requestJson(@json(url('/trading/paper')) + '/' + id, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const trade = response.trade;
                        if (trade.status !== 'open') {
                            if (activePosition && Number(activePosition.id) === Number(trade.id)) {
                                await pollPosition();
                                return;
                            }
                            if (row.cells[4]) row.cells[4].textContent = priceText(trade.closing_price);
                            if (row.cells[6]) row.cells[6].textContent = 'Settled';
                            if (row.cells[7]) {
                                const status = String(trade.status).toUpperCase();
                                row.cells[7].textContent = status === 'WON' ? 'WIN' : status === 'LOST' ? 'LOSS' : 'TIE';
                                row.cells[7].className = 'table-status trade-result-badge ' + (status === 'WON' ? 'verified trade-result-win' : status === 'LOST' ? 'rejected trade-result-loss' : 'pending');
                                row.classList.toggle('trade-row-win', status === 'WON');
                                row.classList.toggle('trade-row-loss', status === 'LOST');
                            }
                            row.removeAttribute('data-open-trade');
                            // Do not reload the terminal underneath another live position.
                            if (activePosition && activePosition.status === 'open') return;
                            // The server has already committed settlement. Update
                            // the row in place; do not reload and interrupt the
                            // live chart or any other open position.
                        }
                    } catch (error) {
                        // Keep the row visibly pending; the next cycle retries without fabricating a result.
                    }
                }
            }
            // Rows are reconciled from the settlement response and expiry event.
            // No periodic read loop is needed while the live market stream is connected.
            const stakeInput = document.getElementById('trade-stake');
            const tradeForm = document.getElementById('paper-trade-form');
            const payoutRate = {{ (int) config('shell.paper.payout_basis_points', 18000) / 10000 }};

            function updatePayout() {
                const stake = Math.max(0, Number(stakeInput.value) || 0);
                document.getElementById('potential-return').textContent = new Intl.NumberFormat(undefined, {
                    maximumFractionDigits: 2
                }).format(stake * payoutRate);
            }
            stakeInput.addEventListener('input', updatePayout);
            updatePayout();
            document.querySelectorAll('[data-stake]').forEach(button => button.addEventListener('click', () => {
                stakeInput.value = button.dataset.stake;
                updatePayout();
            }));
            document.querySelectorAll('[data-direction]').forEach(button => button.addEventListener('click', () => {
                document.getElementById('trade-direction').value = button.dataset.direction;
            }));

            const positionModal = document.getElementById('position-modal');
            let activePosition = null,
                positionPollTimer = null,
                positionClockTimer = null,
                positionRetryTimer = null,
                positionSettlementRetryTimer = null,
                positionAutoCloseTimer = null,
                latestStreamPrice = null,
                positionPricePollTimer = null,
                positionPricePollFailures = 0,
                lastPositionWebSocketTickAt = 0,
                hasPositionPrice = false,
                positionPricePollBusy = false;
            const positionPriceCache = new Map();
            const money = (value, maximum = 8) => new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: maximum
            }).format(Number(value) || 0);
            const priceText = value => {
                const n = Number(value);
                return Number.isFinite(n) ? new Intl.NumberFormat(undefined, {
                    maximumFractionDigits: n >= 1000 ? 2 : n >= 1 ? 4 : 8
                }).format(n) : '—';
            };

            function upsertTradeRow(trade) {
                const body = document.querySelector('.data-table tbody');
                if (!body || !trade?.id) return;
                let row = [...body.querySelectorAll('[data-open-trade]')]
                    .find(item => Number(item.dataset.openTrade) === Number(trade.id));
                if (row) return;

                body.querySelector('.table-empty')?.closest('tr')?.remove();
                row = document.createElement('tr');
                row.className = 'trade-history-row';
                row.setAttribute('data-trade-id', String(trade.id));
                row.setAttribute('data-open-trade', String(trade.id));
                row.tabIndex = 0;
                row.setAttribute('role', 'button');
                row.setAttribute('aria-label', 'View details for ' + trade.symbol + ' ' + trade.direction.toLowerCase() + ' trade');
                const values = [
                    trade.symbol,
                    trade.direction,
                    money(trade.stake) + ' ' + trade.currency,
                    priceText(trade.opening_price),
                    '—',
                    new Date(trade.expires_at).toLocaleString()
                ];
                values.forEach(value => {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.appendChild(cell);
                });

                const countdownCell = document.createElement('td');
                const countdown = document.createElement('span');
                countdown.className = 'trade-countdown';
                countdown.dataset.expiry = String(new Date(trade.expires_at).getTime());
                countdown.textContent = 'Calculating…';
                countdownCell.appendChild(countdown);
                row.appendChild(countdownCell);

                const resultCell = document.createElement('td');
                const status = document.createElement('span');
                status.className = 'table-status pending';
                status.textContent = 'OPEN';
                resultCell.appendChild(status);
                row.appendChild(resultCell);
                body.prepend(row);
                while (body.querySelectorAll('tr').length > 10) {
                    body.querySelector('tr:last-child')?.remove();
                }
                updateTradeCountdowns();
            }

            function setPositionText(id, value) {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            }

            function updatePositionPrice(marketPrice, source = 'websocket', receivedAt = Date.now()) {
                if (!activePosition || activePosition.status !== 'open' || positionSettlementInFlight
                    || new Date(activePosition.expires_at).getTime() <= Date.now()
                    || !Number.isFinite(Number(marketPrice))) return;
                latestStreamPrice = Number(marketPrice);
                hasPositionPrice = true;
                const feedBadge = document.getElementById('position-feed-badge');
                if (feedBadge) {
                    feedBadge.textContent = source === 'websocket' ? 'Live feed' : 'Server price (1s)';
                    setPositionText('position-price-label', source === 'websocket' ? 'LIVE MARKET PRICE' : 'SERVER MARKET PRICE · INDICATIVE');
                    feedBadge.className = 'position-feed-badge ' + (source === 'websocket' ? 'is-live' : 'is-server');
                }
                if (source === 'websocket') lastPositionWebSocketTickAt = receivedAt;
                if (activePosition?.id !== undefined) positionPriceCache.set(String(activePosition.id), {
                    price: String(marketPrice), source, receivedAt
                });
                const opening = Number(activePosition.opening_price),
                    delta = latestStreamPrice - opening;
                const favorable = activePosition.direction === 'UP' ? delta > 0 : delta < 0;
                const stake = Number(activePosition.stake) || 0;
                const totalPayout = stake * payoutRate;
                const indicative = favorable ? totalPayout - stake : (delta === 0 ? 0 : -stake);
                const priceElement = document.getElementById('position-current-price');
                priceElement.textContent = priceText(latestStreamPrice);
                priceElement.style.color = delta === 0 ? '#dce5f2' : favorable ? '#53d6aa' : '#ff8996';
                priceElement.style.textShadow = delta === 0 ? 'none' : favorable ? '0 0 22px #22b78b44' :
                    '0 0 22px #ed647444';
                const move = document.getElementById('position-price-move');
                move.textContent = (delta > 0 ? '+' : '') + priceText(delta) + '  (' + (delta > 0 ? '+' : '') + (
                    opening ? (delta / opening * 100).toFixed(4) : '0.0000') + '%)  ·  ' + (delta === 0 ?
                    'AT OPEN' : favorable ? 'PRICE FAVOURS YOUR ' + activePosition.direction :
                    'PRICE MOVING AGAINST ' + activePosition.direction);
                move.style.color = delta === 0 ? '#aab8c9' : favorable ? '#53d6aa' : '#ff8996';
                move.style.fontWeight = '800';
                const outcome = document.getElementById('position-live-outcome');
                const outcomeWrap = outcome.closest('.position-live-outcome');
                outcome.textContent = (indicative > 0 ? '+' : indicative < 0 ? '−' : '') +
                    money(Math.abs(indicative)) + ' ' + activePosition.currency;
                outcomeWrap.classList.toggle('is-win', favorable);
                outcomeWrap.classList.toggle('is-loss', delta !== 0 && !favorable);

            }
            window.addEventListener('shell:market-price', event => {
                if (activePosition && event.detail?.symbol === activePosition.symbol && event.detail?.source === 'websocket') updatePositionPrice(event.detail.price, 'websocket');
            });

            function stopPositionPricePolling() {
                if (positionPricePollTimer) {
                    window.clearTimeout(positionPricePollTimer);
                    positionPricePollTimer = null;
                }
                positionPricePollBusy = false;
            }

            function schedulePositionPricePoll(delay = 1000) {
                if (positionPricePollTimer || !positionModal.classList.contains('is-visible')
                    || !activePosition || activePosition.status !== 'open') return;
                positionPricePollTimer = window.setTimeout(async () => {
                    positionPricePollTimer = null;
                    if (!positionModal.classList.contains('is-visible') || !activePosition
                        || activePosition.status !== 'open') return;

                    // Prefer a recent browser tick. Once it is older than three
                    // seconds, use the authenticated server-captured quote.
                    if (Date.now() - lastPositionWebSocketTickAt <= 3000) {
                        setPositionText('position-feed-badge', 'Live feed');
                        const badge = document.getElementById('position-feed-badge');
                        if (badge) badge.className = 'position-feed-badge is-live';
                        schedulePositionPricePoll(1000);
                        return;
                    }

                    if (positionPricePollBusy) {
                        schedulePositionPricePoll(500);
                        return;
                    }
                    positionPricePollBusy = true;
                    try {
                        const response = await requestJson(@json(route('markets.price')) + '?' + new URLSearchParams({
                            symbol: activePosition.symbol
                        }), { method: 'GET', retries: 0, timeoutMs: 5000 });
                        if (response.symbol === activePosition.symbol && response.price !== undefined
                            && Date.now() - lastPositionWebSocketTickAt > 3000) {
                            updatePositionPrice(response.price, 'server');
                            positionPricePollFailures = 0;
                        } else {
                            throw new Error('Server returned a mismatched market symbol.');
                        }
                    } catch (_) {
                        positionPricePollFailures = Math.min(positionPricePollFailures + 1, 5);
                        const badge = document.getElementById('position-feed-badge');
                        if (badge) {
                            badge.textContent = 'Reconnecting…';
                            badge.className = 'position-feed-badge is-reconnecting';
                        }
                    } finally {
                        positionPricePollBusy = false;
                        const backoff = Math.min(8000, 1000 * Math.pow(2, positionPricePollFailures));
                        schedulePositionPricePoll(backoff);
                    }
                }, delay);
            }

            let positionSettlementInFlight = false;

            function scheduleSettlementRetry(delay) {
                if (positionSettlementRetryTimer) return;
                positionSettlementRetryTimer = window.setTimeout(() => {
                    positionSettlementRetryTimer = null;
                    beginImmediateSettlement();
                }, delay);
            }

            function showSettledResult(trade) {
                stopPositionPricePolling();
                if (positionPollTimer) { window.clearInterval(positionPollTimer); window.clearTimeout(positionPollTimer); positionPollTimer = null; }
                if (positionClockTimer) { window.clearInterval(positionClockTimer); positionClockTimer = null; }
                if (positionSettlementRetryTimer) { window.clearTimeout(positionSettlementRetryTimer); positionSettlementRetryTimer = null; }
                activePosition = trade;
                positionSettlementInFlight = false;
                const status = trade.status.toUpperCase();
                const card = document.getElementById('position-modal-card');
                const hero = document.getElementById('position-result-hero');
                const icon = document.getElementById('position-result-icon');
                const result = document.getElementById('position-result');
                card.classList.remove('is-win', 'is-loss', 'is-tie');
                card.classList.add(status === 'WON' ? 'is-win' : status === 'LOST' ? 'is-loss' : 'is-tie');
                hero.hidden = false;
                const statusBadge = document.getElementById('position-status');
                statusBadge.textContent = status === 'WON' ? 'YOU WON' : status === 'LOST' ? 'YOU LOST' : 'TIE';
                statusBadge.className = 'table-status ' + (status === 'WON' ? 'verified' : status === 'LOST' ? 'rejected' : 'pending');
                setPositionText('position-modal-title', status === 'WON' ? 'Position won' : status === 'LOST' ? 'Position lost' : 'Position tied');
                setPositionText('position-price-label', 'CONFIRMED CLOSING PRICE');
                setPositionText('position-current-price', priceText(trade.closing_price));
                setPositionText('position-price-move', 'Server-verified expiry quote · ' + (trade.settled_at ? new Date(trade.settled_at).toLocaleTimeString() : 'settled'));
                setPositionText('position-countdown', 'Settled');
                setPositionText('position-closing-price', priceText(trade.closing_price));
                setPositionText('position-final-payout', money(trade.payout_amount ?? (status === 'WON' ? Number(trade.stake) * payoutRate : status === 'TIE' ? Number(trade.stake) : 0)) + ' ' + trade.currency);
                const settledNet = Number(trade.profit_amount ?? 0);
                setPositionText('position-live-outcome', (settledNet > 0 ? '+' : settledNet < 0 ? '−' : '') +
                    money(Math.abs(settledNet)) + ' ' + trade.currency);
                if (trade.wallet_balance !== undefined && trade.wallet_balance !== null) {
                    setText('wallet-balance', money(trade.wallet_balance) + ' ' + trade.currency);
                }
                const historyRow = document.querySelector('[data-open-trade="' + trade.id + '"]');
                if (historyRow) {
                    if (historyRow.cells[4]) historyRow.cells[4].textContent = priceText(trade.closing_price);
                    if (historyRow.cells[6]) historyRow.cells[6].textContent = 'Settled';
                    if (historyRow.cells[7]) {
                        historyRow.cells[7].textContent = status === 'WON' ? 'WIN' : status === 'LOST' ? 'LOSS' : 'TIE';
                        historyRow.cells[7].className = 'table-status trade-result-badge ' + (status === 'WON' ? 'verified trade-result-win' : status === 'LOST' ? 'rejected trade-result-loss' : 'pending');
                        historyRow.classList.toggle('trade-row-win', status === 'WON');
                        historyRow.classList.toggle('trade-row-loss', status === 'LOST');
                    }
                    historyRow.removeAttribute('data-open-trade');
                }
                if (status === 'WON') {
                    const payout = Number(trade.payout_amount ?? ((Number(trade.stake) || 0) * payoutRate));
                    icon.textContent = '↗';
                    setPositionText('position-result-kicker', 'SUCCESSFUL PREDICTION');
                    setPositionText('position-result-title', 'You won ' + money(trade.profit_amount ?? (payout - Number(trade.stake))) + ' ' + trade.currency);
                    result.textContent = 'WIN — The closing price confirmed your ' + trade.direction + ' prediction. The payout of ' + money(payout) + ' ' + trade.currency + ' has been credited to your selected account wallet.';
                } else if (status === 'LOST') {
                    icon.textContent = '↘';
                    setPositionText('position-result-kicker', 'POSITION CLOSED');
                    setPositionText('position-result-title', 'Stake result: loss');
                    result.textContent = 'LOSS — The closing price moved against your ' + trade.direction + ' prediction. The ' + money(trade.stake) + ' ' + trade.currency + ' stake was settled.';
                } else {
                    icon.textContent = '=';
                    setPositionText('position-result-kicker', 'PRICE MATCHED');
                    setPositionText('position-result-title', 'Stake returned');
                    result.textContent = 'TIE — The opening and closing prices matched. Your full stake of ' + money(trade.stake) + ' ' + trade.currency + ' has been returned.';
                }
                result.className = 'position-result ' + (status === 'WON' ? 'is-win' : status === 'LOST' ? 'is-loss' : 'is-tie');
                setPositionText('position-close', 'Continue trading');
                document.getElementById('position-close').disabled = false;
                document.getElementById('position-modal-x').disabled = false;
                if (positionAutoCloseTimer) window.clearTimeout(positionAutoCloseTimer);
                setPositionText('position-close', 'Continue trading');
                positionAutoCloseTimer = window.setTimeout(closeSettledPosition, 4500);
                notify(status === 'WON' ? 'Position won — payout confirmed.' : status === 'LOST' ? 'Position settled — stake lost.' : 'Position tied — stake returned.', status === 'LOST' ? 'error' : 'success');
            }

            async function beginImmediateSettlement() {
                if (!activePosition || activePosition.status !== 'open' || positionSettlementInFlight || positionSettlementRetryTimer) return;
                positionSettlementInFlight = true;
                setPositionText('position-modal-title', 'Settling position');
                setPositionText('position-status', 'SETTLING');
                setPositionText('position-live-outcome', 'Confirming the closing price…');
                setPositionText('position-result', 'Trade expired. Settlement is being confirmed securely with the latest server-captured market quote…');
                setPositionText('position-close', 'Settling securely…');
                try {
                    const data = await requestJson(@json(url('/trading/paper')) + '/' + activePosition.id + '/settle', { method: 'POST', timeoutMs: 40000, retries: 2 });
                    if (data.trade?.status && data.trade.status !== 'open') {
                        data.trade.wallet_balance = data.wallet_balance;
                        showSettledResult(data.trade);
                        return;
                    }
                    positionSettlementInFlight = false;
                    setPositionText('position-result', 'Expiry reached. Waiting for the first verified server quote at or after the expiry time. We are retrying automatically.');
                    scheduleSettlementRetry(1200);
                } catch (error) {
                    positionSettlementInFlight = false;
                    setPositionText('position-result', 'Settlement is retrying automatically. ' + (error.message || 'The verified quote is not ready yet.'));
                    scheduleSettlementRetry(1500);
                }
            }

            function updatePositionClock() {
                if (!activePosition) return;
                const left = Math.max(0, Math.ceil((new Date(activePosition.expires_at).getTime() - Date.now()) /
                    1000));
                setPositionText('position-countdown', left > 0 ? Math.floor(left / 60) + 'm ' + String(left % 60)
                    .padStart(2, '0') + 's' : 'Expiry reached · settling');
                if (left === 0 && activePosition.status === 'open') {
                    // Never trap the client in an expired-position dialog while a quote is pending.
                    const closeButton = document.getElementById('position-close');
                    closeButton.disabled = false;
                    closeButton.textContent = 'Close and continue trading';
                    document.getElementById('position-modal-x').disabled = false;
                    beginImmediateSettlement();
                }
            }
            async function pollPosition() {
                if (!activePosition || activePosition.status !== 'open') return;
                try {
                    const data = await requestJson(@json(url('/trading/paper')) + '/' + activePosition.id, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    });
                    const trade = data.trade;
                    if (trade.status === 'open') {
                        activePosition = trade;
                        setPositionText('position-status', 'OPEN');
                        if (new Date(trade.expires_at).getTime() <= Date.now()) beginImmediateSettlement();
                        return;
                    }

                    // A scheduler or another request may have settled it first.
                    // Ask the same idempotent endpoint for the final payout and balance.
                    const settlement = await requestJson(@json(url('/trading/paper')) + '/' + trade.id + '/settle', {
                        method: 'POST',
                        timeoutMs: 40000,
                        retries: 2
                    });
                    const settledTrade = settlement.trade || trade;
                    settledTrade.wallet_balance = settlement.wallet_balance;
                    showSettledResult(settledTrade);
                } catch (error) {
                    setPositionText('position-result',
                        'Connection interrupted. Your position remains recorded. Reconnecting automatically for the confirmed result…'
                    );
                }
            }
            async function openPositionModal(id, initialTrade = null) {
                const modal = positionModal;
                modal.classList.add('is-visible');
                modal.setAttribute('aria-hidden', 'false');
                // The modal is a monitor, not a lock screen. Hiding it never
                // cancels or pauses the persisted position or server settlement.
                document.getElementById('position-close').disabled = false;
                document.getElementById('position-modal-x').disabled = false;
                stopPositionPricePolling();
                setPositionText('position-result', '');
                setPositionText('position-close', 'Hide modal · trade continues');
                try {
                    const data = initialTrade ? { trade: initialTrade } : await requestJson(@json(url('/trading/paper')) + '/' + id, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (positionRetryTimer) {
                        window.clearTimeout(positionRetryTimer);
                        positionRetryTimer = null;
                    }
                    activePosition = data.trade;
                    const cachedPositionPrice = positionPriceCache.get(String(activePosition.id));
                    positionSettlementInFlight = false;
                    if (positionSettlementRetryTimer) { window.clearTimeout(positionSettlementRetryTimer); positionSettlementRetryTimer = null; }
                    const resultHero = document.getElementById('position-result-hero');
                    resultHero.hidden = true;
                    document.getElementById('position-modal-card').classList.remove('is-win', 'is-loss', 'is-tie');
                    document.getElementById('position-modal-title').textContent = 'Trade opened';
                    document.getElementById('position-status').textContent = 'OPEN';
                    document.getElementById('position-modal-x').disabled = true;
                    latestStreamPrice = null;
                    lastPositionWebSocketTickAt = 0;
                    hasPositionPrice = false;
                    positionPricePollFailures = 0;
                    const feedBadge = document.getElementById('position-feed-badge');
                    if (feedBadge) { feedBadge.textContent = 'Reconnecting…'; feedBadge.className = 'position-feed-badge is-reconnecting'; }
                    if (cachedPositionPrice) {
                        updatePositionPrice(cachedPositionPrice.price, cachedPositionPrice.source, cachedPositionPrice.receivedAt);
                    }
                    setPositionText('position-symbol', activePosition.symbol);
                    setPositionText('position-direction', activePosition.direction + ' · ' + (activePosition
                        .direction === 'UP' ? 'PRICE RISE' : 'PRICE FALL'));
                    const dir = document.getElementById('position-direction');
                    dir.classList.toggle('down', activePosition.direction === 'DOWN');
                    setPositionText('position-stake', money(activePosition.stake) + ' ' + activePosition.currency);
                    setPositionText('position-opening-price', priceText(activePosition.opening_price));
                    setPositionText('position-live-outcome', '—');
                    setPositionText('position-expiry', new Date(activePosition.expires_at).toLocaleTimeString());
                    if (!hasPositionPrice) setPositionText('position-current-price', 'Waiting for live price…');
                    setPositionText('position-price-label', 'LIVE MARKET PRICE');
                    setPositionText('position-closing-price', '—');
                    setPositionText('position-final-payout', 'Pending');

                    // The scheduler may have settled this position while the page
                    // was loading. Hydrate the same modal with its final payout.
                    if (activePosition.status !== 'open') {
                        const settlement = await requestJson(@json(url('/trading/paper')) + '/' + activePosition.id + '/settle', {
                            method: 'POST',
                            timeoutMs: 40000, retries: 2
                        });
                        const settledTrade = settlement.trade || activePosition;
                        settledTrade.wallet_balance = settlement.wallet_balance;
                        showSettledResult(settledTrade);
                        return;
                    }

                    updatePositionClock();
                    schedulePositionPricePoll(0);
                    if (positionPollTimer) window.clearInterval(positionPollTimer);
                    if (positionClockTimer) window.clearInterval(positionClockTimer);
                    // The live market stream updates indicative prices. Expiry itself
                    // triggers an immediate server settlement request; no 2-second poll.
                    positionClockTimer = window.setInterval(updatePositionClock, 100);
                    await pollPosition();
                } catch (error) {
                    setPositionText('position-result', (error.message || 'Connection error') +
                        ' Your trade remains recorded. We will retry automatically.');
                    if (!hasPositionPrice) setPositionText('position-current-price', 'Waiting for live price…');
                    setPositionText('position-live-outcome', 'Waiting for a verified server response…');
                    setPositionText('position-close', 'Retrying position…');
                    document.getElementById('position-close').disabled = true;
                    if (positionRetryTimer) window.clearTimeout(positionRetryTimer);
                    positionRetryTimer = window.setTimeout(() => {
                        if (positionModal.classList.contains('is-visible')) openPositionModal(id);
                    }, 5000);
                }
            }
            function closeSettledPosition() {
                stopPositionPricePolling();
                if (positionAutoCloseTimer) { window.clearTimeout(positionAutoCloseTimer); positionAutoCloseTimer = null; }
                positionModal.classList.remove('is-visible');
                positionModal.setAttribute('aria-hidden', 'true');

                // An open trade is safely persisted. Hiding the modal stops only
                // its one-second price polling; server-side settlement continues.
                if (activePosition && activePosition.status === 'open') {
                    if (new Date(activePosition.expires_at).getTime() <= Date.now()) {
                        settleExpiredTrades();
                    }
                    return;
                }

                if (positionPollTimer) { window.clearInterval(positionPollTimer); window.clearTimeout(positionPollTimer); positionPollTimer = null; }
                if (positionClockTimer) { window.clearInterval(positionClockTimer); window.clearTimeout(positionClockTimer); positionClockTimer = null; }
                if (positionSettlementRetryTimer) { window.clearTimeout(positionSettlementRetryTimer); positionSettlementRetryTimer = null; }
                activePosition = null;
                positionSettlementInFlight = false;
                // The settled trade and wallet are already reconciled in place.
            }
            document.getElementById('position-close').addEventListener('click', closeSettledPosition);
            document.getElementById('position-modal-x').addEventListener('click', closeSettledPosition);

            // Every history row opens its persisted trade details in the same modal.
            // This includes settled positions; the server remains authoritative for the result.
            const tradeHistoryBody = document.querySelector('.data-table tbody');
            const historyDetailModal = document.getElementById('history-detail-modal');
            let historyDetailRequest = 0;
            const historyDetailFields = {
                id: 'history-detail-id', account_type: 'history-detail-account',
                symbol: 'history-detail-symbol', direction: 'history-detail-direction',
                stake: 'history-detail-stake', duration_seconds: 'history-detail-duration',
                opening_price: 'history-detail-opening', closing_price: 'history-detail-closing',
                opened_at: 'history-detail-opened', expires_at: 'history-detail-expiry',
                settled_at: 'history-detail-settled'
            };
            const detailPrice = value => value === null || value === undefined ? '—' : priceText(value);
            const detailDate = value => value ? new Date(value).toLocaleString() : '—';
            function closeHistoryDetails() {
                historyDetailModal.classList.remove('is-visible');
                historyDetailModal.setAttribute('aria-hidden', 'true');
            }
            historyDetailModal.querySelectorAll('[data-close-history-detail]').forEach(el => el.addEventListener('click', closeHistoryDetails));
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && historyDetailModal.classList.contains('is-visible')) closeHistoryDetails();
            });
            async function openHistoryTrade(row) {
                if (!row) return;
                const requestId = ++historyDetailRequest;
                historyDetailModal.classList.add('is-visible');
                historyDetailModal.setAttribute('aria-hidden', 'false');
                document.getElementById('history-detail-title').textContent = 'Position details';
                const loadingBadge = document.getElementById('history-detail-status');
                loadingBadge.textContent = 'LOADING';
                loadingBadge.className = 'history-detail-status is-pending';
                document.getElementById('history-result-hero').className = 'history-result-hero is-pending';
                document.getElementById('history-result-kicker').textContent = 'POSITION STATUS';
                document.getElementById('history-result-title').textContent = 'Loading trade result';
                document.getElementById('history-result-subtitle').textContent = 'Confirming the recorded outcome…';
                document.getElementById('history-detail-note').textContent = 'Loading the recorded trade details…';
                try {
                    const response = await requestJson(@json(url('/trading/paper')) + '/' + Number(row.dataset.tradeId), {
                        method: 'GET', headers: { 'Accept': 'application/json' }
                    });
                    if (requestId !== historyDetailRequest) return;
                    const trade = response.trade;
                    Object.entries(historyDetailFields).forEach(([key, elementId]) => {
                        const value = trade[key];
                        const formatted = key === 'stake' ? money(value) + ' ' + trade.currency
                            : key === 'duration_seconds' ? (Number(value) >= 3600 ? (Number(value) / 3600) + ' hour(s)' : (Number(value) / 60) + ' minute(s)')
                            : key === 'opening_price' || key === 'closing_price' ? detailPrice(value)
                            : key === 'opened_at' || key === 'expires_at' || key === 'settled_at' ? detailDate(value)
                            : key === 'account_type' ? String(value || 'demo').toUpperCase()
                            : value ?? '—';
                        document.getElementById(elementId).textContent = formatted;
                    });
                    const normalizedStatus = String(trade.status || 'open').toLowerCase();
                    const status = normalizedStatus.toUpperCase();
                    const won = normalizedStatus === 'won';
                    const lost = normalizedStatus === 'lost';
                    const tied = normalizedStatus === 'void' || normalizedStatus === 'tie';
                    const badge = document.getElementById('history-detail-status');
                    badge.textContent = won ? 'WON' : lost ? 'LOST' : tied ? 'TIE' : 'OPEN';
                    badge.className = 'history-detail-status ' + (won ? 'is-win' : lost ? 'is-loss' : tied ? 'is-tie' : 'is-pending');
                    const hero = document.getElementById('history-result-hero');
                    hero.className = 'history-result-hero ' + (won ? 'is-win' : lost ? 'is-loss' : tied ? 'is-tie' : 'is-pending');
                    const resultIcon = document.getElementById('history-result-emblem');
                    resultIcon.innerHTML = won
                        ? '<svg viewBox="0 0 48 48"><path d="M15 7h18v9a9 9 0 0 1-18 0V7Z"/><path d="M15 11H8v4a8 8 0 0 0 8 8M33 11h7v4a8 8 0 0 1-8 8M24 25v9m-8 7h16m-13-7h10l3 7H16l3-7Z"/><path d="m20 15 3 3 6-7"/></svg>'
                        : lost
                            ? '<svg viewBox="0 0 48 48"><path d="M24 5 40 11v11c0 10-7 17-16 21C15 39 8 32 8 22V11l16-6Z"/><path d="m17 18 14 14m0-14L17 32"/></svg>'
                            : tied
                                ? '<svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="19"/><path d="M15 20h18M15 28h18"/></svg>'
                                : '<svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="19"/><path d="M24 13v12l8 5"/></svg>';
                    document.getElementById('history-result-kicker').textContent = won ? 'SUCCESSFUL POSITION' : lost ? 'POSITION CLOSED' : tied ? 'STAKE RETURNED' : 'POSITION STATUS';
                    document.getElementById('history-result-title').textContent = won ? 'You won this trade' : lost ? 'This trade was lost' : tied ? 'Trade ended in a tie' : 'Position is still open';
                    const payout = won ? Number(trade.stake) * Number(trade.payout_basis_points || @json((int) config('shell.paper.payout_basis_points', 18000))) / 10000
                        : tied ? Number(trade.stake) : 0;
                    const profit = won ? payout - Number(trade.stake) : lost ? -Number(trade.stake) : 0;
                    document.getElementById('history-result-subtitle').textContent = won
                        ? 'Your position settled in the predicted direction.'
                        : lost ? 'The market settled against your selected direction.'
                        : tied ? 'Your original stake was returned to your wallet.'
                        : 'The expiry and settlement process is still in progress.';
                    document.getElementById('history-detail-payout').textContent = normalizedStatus === 'open' ? 'Pending' : money(payout) + ' ' + trade.currency;
                    const profitEl = document.getElementById('history-detail-profit');
                    profitEl.textContent = normalizedStatus === 'open' ? 'Pending' : (profit > 0 ? '+' : profit < 0 ? '−' : '') + money(Math.abs(profit)) + ' ' + trade.currency;
                    profitEl.style.color = won ? '#5be0ae' : lost ? '#ff8996' : tied ? '#e7c77f' : '#c4d1e1';
                    const directionEl = document.getElementById('history-detail-direction');
                    directionEl.textContent = (trade.direction || '—') + (trade.direction === 'UP' ? '  ↗' : trade.direction === 'DOWN' ? '  ↘' : '');
                    directionEl.classList.toggle('is-down', trade.direction === 'DOWN');
                    document.getElementById('history-detail-id').textContent = 'TRADE #' + trade.id;
                    document.getElementById('history-detail-title').textContent = 'Position details';
                    document.getElementById('history-detail-note').textContent = normalizedStatus === 'open'
                        ? 'This position is still open. Expiry and settlement continue independently. This details window will remain open until you close it.'
                        : 'Final position details as recorded by the trading system. This window will remain open until you close it.';
                } catch (error) {
                    if (requestId !== historyDetailRequest) return;
                    const unavailableBadge = document.getElementById('history-detail-status');
                    unavailableBadge.textContent = 'UNAVAILABLE';
                    unavailableBadge.className = 'history-detail-status is-loss';
                    document.getElementById('history-result-hero').className = 'history-result-hero is-pending';
                    document.getElementById('history-result-kicker').textContent = 'DETAILS UNAVAILABLE';
                    document.getElementById('history-result-title').textContent = 'Could not load this trade';
                    document.getElementById('history-result-subtitle').textContent = 'Please close this window and try again.';
                    document.getElementById('history-detail-note').textContent = error.message || 'Unable to load this trade. Please try again.';
                }
            }
            tradeHistoryBody?.addEventListener('click', event => {
                openHistoryTrade(event.target.closest('tr[data-trade-id]'));
            });
            tradeHistoryBody?.addEventListener('keydown', event => {
                if (event.key !== 'Enter' && event.key !== ' ') return;
                const row = event.target.closest('tr[data-trade-id]');
                if (!row) return;
                event.preventDefault();
                openHistoryTrade(row);
            });

            // Resume an active trade, or reopen the latest expired position directly
            // in the modal so the client sees its confirmed result without a page reload.
            const resumableTradeId = @json($activeTradeId);
            if (resumableTradeId) openPositionModal(resumableTradeId);

            // Expired positions are reconciled in the background as a batch.
            // They must never reopen or pin the active-position modal.
            let expiredSettlementInFlight = false;
            let expiredSettlementTimer = null;
            async function settleExpiredTrades() {
                if (expiredSettlementInFlight) return;
                expiredSettlementInFlight = true;
                try {
                    const response = await requestJson(@json(route('trading.settle-expired', ['account' => $selectedAccount])), {
                        method: 'POST',
                        timeoutMs: 40000, retries: 2
                    });
                    if (response.wallet_balance !== undefined && response.wallet_balance !== null) {
                        setText('wallet-balance', money(response.wallet_balance) + ' {{ $currency }}');
                    }
                    // Reconcile even when this request settled zero rows: the
                    // scheduler may already have committed the result just before us.
                    if (activePosition && activePosition.status === 'open') {
                        await pollPosition();
                    } else {
                        await reconcileOpenTrades();
                    }
                    if (response.has_due) scheduleExpiredSettlementRetry();
                } catch (_) {
                    scheduleExpiredSettlementRetry();
                } finally {
                    expiredSettlementInFlight = false;
                }
            }
            function scheduleExpiredSettlementRetry() {
                if (expiredSettlementTimer) return;
                expiredSettlementTimer = window.setTimeout(() => {
                    expiredSettlementTimer = null;
                    settleExpiredTrades();
                }, 1000);
            }
            if (@json((bool) ($hasExpiredOpenTrades ?? false))) settleExpiredTrades();
            // Every open row's countdown triggers one settlement request at expiry.
            // The server scheduler is the independent fallback when this browser is offline.

            tradeForm.addEventListener('submit', async event => {
                event.preventDefault();
                const form = event.currentTarget,
                    submitter = event.submitter;
                if (form.dataset.submitting === 'true') return;
                const stake = Number(stakeInput.value),
                    maxStake = Number(stakeInput.max);
                if (!Number.isFinite(stake) || stake <= 0) {
                    document.getElementById('trade-feedback').textContent =
                        'Enter a valid stake greater than zero.';
                    stakeInput.focus();
                    return;
                }
                if (stake > maxStake) {
                    document.getElementById('trade-feedback').textContent =
                        'Your stake is above the maximum allowed amount of ' + stakeInput.max +
                        ' {{ $currency }}.';
                    stakeInput.focus();
                    return;
                }
                if (submitter?.dataset.direction) document.getElementById('trade-direction').value =
                    submitter.dataset.direction;
                form.dataset.submitting = 'true';
                const buttons = form.querySelectorAll('button[type=submit]');
                buttons.forEach(button => button.disabled = true);
                const feedback = document.getElementById('trade-feedback');
                feedback.textContent = 'Validating fresh market price and reserving stake…';
                try {
                    const payload = Object.fromEntries(new FormData(form).entries());
                    const result = await requestJson(@json(route('trading.store')), {
                        method: 'POST',
                        timeoutMs: 18000,
                        retries: 2,
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    feedback.textContent = result.message;
                    notify(result.message);
                    // A new confirmed submission needs a new idempotency key.
                    // Keep the old key only when the request failed, so retries
                    // can safely recover a trade whose response was interrupted.
                    const requestKeyInput = form.querySelector('[name="request_key"]');
                    if (requestKeyInput) {
                        requestKeyInput.value = window.crypto?.randomUUID
                            ? window.crypto.randomUUID()
                            : 'trade-' + Date.now() + '-' + Math.random().toString(36).slice(2, 12);
                    }
                    if (result.wallet_balance !== undefined && result.wallet_balance !== null) {
                        setText('wallet-balance', money(result.wallet_balance) + ' {{ $currency }}');
                    }
                    if (result.trade_id) {
                        if (result.trade) upsertTradeRow(result.trade);
                        await openPositionModal(result.trade_id, result.trade || null);
                    }
                } catch (error) {
                    feedback.textContent = error.message;
                    notify(error.message, 'error');
                } finally {
                    form.dataset.submitting = 'false';
                    buttons.forEach(button => button.disabled = false);
                }
            });
        })();
    </script>
@endpush
