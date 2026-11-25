@extends('layouts.guest')
@section('title', 'SHELL · Crypto Markets & Fixed-Time Trading')
@section('content')
<div class="marketing-shell shell-home">
    <header class="marketing-nav">
        <a class="brand" href="{{ route('home') }}" aria-label="SHELL home"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">MARKET & TRADING PLATFORM</span></span></a>
        <nav aria-label="Main navigation"><a href="#how-it-works">How it works</a><a href="#platform">Platform</a><a href="#faq">FAQs</a></nav>
        <div class="nav-actions"><a class="button button-quiet" href="{{ route('login') }}">Sign in</a><a class="button button-primary" href="{{ route('register') }}">Get started <span>↗</span></a></div>
    </header>

    <main>
        <section class="shell-hero">
            <div class="shell-hero-copy">
                <span class="eyebrow"><i class="live-dot"></i> YOUR MARKETS. YOUR NEXT MOVE.</span>
                <h1>Explore crypto markets.<br><em>Trade with intention.</em></h1>
                <p class="shell-hero-lead">Discover market movements, analyse price charts and experience fixed-time UP or DOWN trading in one focused platform. Start by exploring the demo environment before deciding whether real-money trading is appropriate for you.</p>
                <div class="hero-actions"><a class="button button-primary button-large" href="{{ route('register') }}">Create your free account <span>↗</span></a><a class="button button-quiet button-large" href="#how-it-works">See how SHELL works</a></div>
            </div>
            <div class="shell-hero-art">
                <div class="shell-terminal">
                    <div class="shell-terminal-top"><div><span class="eyebrow">SHELL / MARKET TERMINAL</span><strong>One view. Market movement.</strong></div><span class="terminal-live-label"><i></i> MARKET DATA</span></div>
                    <div class="shell-terminal-pair"><div><span class="coin-symbol">₿</span><div><strong>Crypto markets</strong><small>Spot market references</small></div></div></div>
                    <div class="shell-chart">
                        <div class="shell-chart-grid"></div>
                        <svg viewBox="0 0 620 220" preserveAspectRatio="none" role="img" aria-label="Illustrative crypto price chart">
                            <defs><linearGradient id="shellChartLine" x1="0" y1="0" x2="620" y2="0"><stop stop-color="#65c6d8"/><stop offset="1" stop-color="#b8a16a"/></linearGradient><linearGradient id="shellChartFill" x1="0" y1="0" x2="0" y2="220"><stop stop-color="#65c6d8" stop-opacity=".22"/><stop offset="1" stop-color="#65c6d8" stop-opacity="0"/></linearGradient></defs>
                            <path d="M0 160 C28 150 42 166 67 137 S110 148 136 121 S175 139 204 105 S244 126 270 95 S309 111 337 81 S374 103 401 65 S442 89 468 59 S508 79 536 45 S581 57 620 25 L620 220 L0 220Z" fill="url(#shellChartFill)"/>
                            <path d="M0 160 C28 150 42 166 67 137 S110 148 136 121 S175 139 204 105 S244 126 270 95 S309 111 337 81 S374 103 401 65 S442 89 468 59 S508 79 536 45 S581 57 620 25" fill="none" stroke="url(#shellChartLine)" stroke-width="3" stroke-linecap="round"/>
                            <circle cx="620" cy="25" r="5" fill="#b8a16a"/>
                        </svg>
                        <span class="chart-illustration-note">ILLUSTRATIVE CHART · NOT A LIVE QUOTE</span>
                    </div>
                    <div class="shell-chart-legend"><span><i class="legend-dot legend-cyan"></i> Price analysis</span><span>Chart intervals</span></div>
                    <div class="shell-direction-preview"><div><span>↗</span><strong>UP</strong><small>Price moves higher</small></div><div><span>↘</span><strong>DOWN</strong><small>Price moves lower</small></div></div>
                    <div class="shell-terminal-bottom"><span>Fixed-time trading interface</span><span>Demo & real account modes</span></div>
                </div>
                <div class="shell-float-note"><b>01</b><span>ANALYSE THE MARKET</span></div>
            </div>
        </section>

        <section class="shell-how-section" id="how-it-works">
            <div class="shell-section-heading"><span class="eyebrow">GET STARTED IN THREE STEPS</span><h2>A clear path from market discovery<br><em>to trade review.</em></h2><p>Understand the workflow before risking capital. You stay in control of whether and when you place a position.</p></div>
            <div class="shell-steps-grid">
                <article><span class="shell-step-number">01</span><div class="shell-step-icon">⌕</div><h3>Create your account</h3><p>Register to access your personal workspace, explore supported markets and use the demo account to learn the trading flow.</p><span class="shell-step-foot">ACCOUNT ACCESS</span></article>
                <article><span class="shell-step-number">02</span><div class="shell-step-icon">⌁</div><h3>Analyse and choose</h3><p>Review price charts using the available chart intervals. Select a supported market, choose UP or DOWN, set your stake and select an expiry.</p><span class="shell-step-foot">MARKET & POSITION</span></article>
                <article><span class="shell-step-number">03</span><div class="shell-step-icon">↗</div><h3>Track every outcome</h3><p>Follow open positions through expiry, then review the recorded result, account balance and activity history.</p><span class="shell-step-foot">SETTLEMENT & HISTORY</span></article>
            </div>
        </section>

        <section class="shell-platform-section" id="platform">
            <div class="shell-platform-intro"><span class="eyebrow">MADE FOR INFORMED DECISIONS</span><h2>Your trading tools,<br><em>in one workspace.</em></h2><p>SHELL brings market discovery, fixed-time trading and account records together without dressing up simulated data as real performance.</p><a class="shell-text-link" href="{{ route('register') }}">Explore SHELL <span>→</span></a></div>
            <div class="shell-feature-list">
                <article><span class="shell-feature-icon">⌁</span><div><h3>Charts and market watchlists</h3><p>Explore supported crypto markets, view candlestick data and save instruments to your personal watchlist.</p></div><b>01</b></article>
                <article><span class="shell-feature-icon">◷</span><div><h3>Flexible chart and expiry intervals</h3><p>Choose from the chart intervals and fixed-time expiry options available on the platform.</p></div><b>02</b></article>
                <article><span class="shell-feature-icon">⇄</span><div><h3>Separate demo and real accounts</h3><p>Demo credits are for practice and cannot be withdrawn. Real-wallet funds are maintained separately from demo balances.</p></div><b>03</b></article>
                <article><span class="shell-feature-icon">▤</span><div><h3>Portfolio and transaction records</h3><p>Review positions, account activity and wallet statements to understand what happened to your funds and trades.</p></div><b>04</b></article>
                <article><span class="shell-feature-icon">◎</span><div><h3>Account controls and support</h3><p>Manage account security, identity verification and support requests from your authenticated workspace.</p></div><b>05</b></article>
            </div>
        </section>

        <section class="shell-account-section">
            <div><span class="eyebrow">TWO DISTINCT ACCOUNT ENVIRONMENTS</span><h2>Practise first.<br><em>Know the difference.</em></h2><p>Demo trading and real-money trading are not interchangeable. Understand what each account does before making a funding decision.</p></div>
            <div class="shell-account-cards">
                <article class="shell-demo-card"><span class="shell-account-tag">PRACTICE ENVIRONMENT</span><h3>Demo account</h3><p>Explore the trading interface with simulation credits. Test how positions open, expire and settle without treating demo results as real earnings.</p><ul><li>Practice the UP / DOWN workflow</li><li>Explore chart intervals and expiry times</li><li>Review simulated trade outcomes</li></ul><a href="{{ route('register') }}">Start with demo access <span>→</span></a></article>
                <article class="shell-real-card"><span class="shell-account-tag">REAL-MONEY ENVIRONMENT</span><h3>Real account</h3><p>Uses a separate real-money wallet. Deposits, identity checks, withdrawals and trading access are subject to platform controls and applicable requirements.</p><ul><li>Real funds are separate from demo credits</li><li>Deposit transactions require verification</li><li>Withdrawals are subject to account checks</li></ul><a href="#risk">Understand the risks <span>→</span></a></article>
            </div>
        </section>

        <section class="shell-faq-section" id="faq">
            <div class="shell-section-heading"><span class="eyebrow">GOOD QUESTIONS. CLEAR ANSWERS.</span><h2>Before you get started.</h2></div>
            <div class="shell-faq-grid">
                <article><h3>What can I trade on SHELL?</h3><p>SHELL provides a cryptocurrency-focused trading workspace. Sign in to review the instruments and market information available to your account.</p></article>
                <article><h3>Can I practise before using real money?</h3><p>Yes. The demo environment uses simulation credits so you can learn the interface and trade lifecycle. Demo credits and demo profits are not withdrawable.</p></article>
                <article><h3>How does a fixed-time trade work?</h3><p>You select a supported market, choose UP or DOWN, enter a stake and select an expiry duration. The position is evaluated at expiry using the platform's server-side market data and settlement rules.</p></article>
                <article><h3>Are returns guaranteed?</h3><p>No. A trade can lose the full stake. Market prices can move unexpectedly, and a past or demo result does not predict future performance.</p></article>
                <article><h3>How are deposits and withdrawals handled?</h3><p>Available wallet methods and their instructions are shown in your account. Deposit transaction details are reviewed before funds are credited. Withdrawals are subject to identity and operational checks.</p></article>
                <article><h3>Can I use SHELL from any country?</h3><p>Availability of binary-style and fixed-time trading products differs by jurisdiction. Check local laws and obtain independent advice before registering or funding an account.</p></article>
            </div>
        </section>

        <section class="shell-final-cta"><div><span class="eyebrow">TAKE THE FIRST STEP</span><h2>Get to know the platform<br><em>before you commit capital.</em></h2><p>Create an account, explore the markets and understand the tools available to you.</p></div><a class="button button-primary button-large" href="{{ route('register') }}">Create your SHELL account <span>↗</span></a></section>

    </main>

    <footer class="shell-home-footer">
        <section class="shell-risk-section" id="risk" aria-labelledby="shell-risk-title">
            <div class="shell-risk-icon">!</div>
            <div>
                <span class="eyebrow">IMPORTANT RISK INFORMATION</span>
                <h2 id="shell-risk-title">Trading involves significant risk.</h2>
                <p>Fixed-time and binary-style options are high-risk financial products. You may lose all the money committed to a position. These products may not be suitable for every investor and may be restricted or prohibited in some jurisdictions. Do not trade with money you cannot afford to lose. Understand the product, its payout structure, fees and settlement rules, and seek independent financial and legal advice where appropriate. SHELL does not promise profits or guaranteed income.</p>
            </div>
        </section>
        <div class="shell-home-footer-bottom">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">MARKET & TRADING PLATFORM</span></span></a>
            <p>© {{ date('Y') }} SHELL. All rights reserved.</p>
            <div><a href="{{ route('login') }}">Sign in</a><a href="{{ route('register') }}">Create account</a></div>
        </div>
    </footer>
</div>
@endsection
