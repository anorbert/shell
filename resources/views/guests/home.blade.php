@extends('layouts.guest')
@section('title', 'SHELL · Crypto Markets & Fixed-Time Trading')
@section('content')
<div class="marketing-shell shell-home" id="top">
    <header class="marketing-nav">
        <a class="brand" href="{{ route('home') }}" aria-label="SHELL home"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">MARKET & TRADING PLATFORM</span></span></a>
        <nav aria-label="Main navigation"><a href="#how-it-works">How it works</a><a href="#faq">FAQs</a></nav>
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
                        <div class="shell-chart-toolbar"><span class="chart-symbol">BTC / USDT</span><span class="chart-price">67,482.36 <i>+2.41%</i></span><span class="chart-timeframes">1m&nbsp; 5m&nbsp; <b>15m</b>&nbsp; 1H&nbsp; 4H</span></div>
                        <div class="shell-chart-grid"></div>
                        <svg viewBox="0 0 620 220" preserveAspectRatio="none" role="img" aria-label="Illustrative Bitcoin candlestick chart with volume bars">
                            <defs><linearGradient id="shellPriceShade" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#35b98b" stop-opacity=".14"/><stop offset="1" stop-color="#35b98b" stop-opacity="0"/></linearGradient></defs>
                            <path d="M0 158 C38 146 61 166 98 137 S151 145 188 111 S234 132 271 101 S315 115 352 82 S396 101 433 67 S477 87 514 52 S570 64 620 28 L620 220H0Z" fill="url(#shellPriceShade)"/>
                            <g opacity=".35" stroke="#9aabba" stroke-dasharray="3 5"><path d="M0 38H620M0 78H620M0 118H620M0 158H620M0 198H620"/></g>
                            <g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M11 145.0V166.0"/><rect x="8.0" y="150.0" width="6" height="10.0" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M26.8 162.7V182.8"/><rect x="23.8" y="169.7" width="6" height="5.1" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M42.6 142.4V162.1"/><rect x="39.6" y="151.4" width="6" height="3.0" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M58.400000000000006 127.3V151.7"/><rect x="55.4" y="138.3" width="6" height="7.4" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M74.2 138.3V154.3"/><rect x="71.2" y="143.3" width="6" height="3.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M90 110.4V145.1"/><rect x="87.0" y="117.4" width="6" height="17.7" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M105.80000000000001 100.2V115.4"/><rect x="102.8" y="109.2" width="6" height="3.0" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M121.60000000000001 94.2V130.3"/><rect x="118.6" y="105.2" width="6" height="17.1" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M137.4 123.1V140.5"/><rect x="134.4" y="128.1" width="6" height="3.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M153.20000000000002 134.3V153.4"/><rect x="150.2" y="141.3" width="6" height="6.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M169 127.7V146.3"/><rect x="166.0" y="136.7" width="6" height="3.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M184.8 102.9V130.3"/><rect x="181.8" y="113.9" width="6" height="6.4" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M200.60000000000002 97.6V119.1"/><rect x="197.6" y="102.6" width="6" height="10.5" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M216.4 81.2V107.4"/><rect x="213.4" y="88.2" width="6" height="11.1" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M232.20000000000002 66.7V101.8"/><rect x="229.2" y="75.7" width="6" height="16.1" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M248 91.0V115.2"/><rect x="245.0" y="102.0" width="6" height="7.2" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M263.8 107.3V131.4"/><rect x="260.8" y="112.3" width="6" height="11.1" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M279.6 93.9V112.7"/><rect x="276.6" y="100.9" width="6" height="3.0" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M295.40000000000003 93.1V110.6"/><rect x="292.4" y="102.1" width="6" height="3.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M311.2 95.1V115.9"/><rect x="308.2" y="106.1" width="6" height="3.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M327 67.0V96.7"/><rect x="324.0" y="72.0" width="6" height="14.7" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M342.8 47.9V67.6"/><rect x="339.8" y="54.9" width="6" height="6.7" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M358.6 48.2V81.9"/><rect x="355.6" y="57.2" width="6" height="16.7" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M374.40000000000003 66.5V96.3"/><rect x="371.4" y="77.5" width="6" height="8.9" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M390.20000000000005 85.4V104.9"/><rect x="387.2" y="90.4" width="6" height="8.6" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M406 81.1V98.6"/><rect x="403.0" y="88.1" width="6" height="3.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M421.8 64.7V84.8"/><rect x="418.8" y="73.7" width="6" height="3.0" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M437.6 50.6V77.6"/><rect x="434.6" y="61.6" width="6" height="9.9" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M453.40000000000003 42.8V60.2"/><rect x="450.4" y="47.8" width="6" height="4.3" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M469.20000000000005 21.4V56.8"/><rect x="466.2" y="28.4" width="6" height="18.4" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M485 45.1V61.2"/><rect x="482.0" y="54.1" width="6" height="3.0" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M500.8 44.0V78.4"/><rect x="497.8" y="55.0" width="6" height="15.5" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M516.6 47.6V63.8"/><rect x="513.6" y="52.6" width="6" height="3.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M532.4 56.2V72.2"/><rect x="529.4" y="63.2" width="6" height="3.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M548.2 56.0V75.9"/><rect x="545.2" y="65.0" width="6" height="3.0" rx="1"/></g><g stroke="#e46f78" fill="#e46f78" stroke-width="1.4"><path d="M564 15.9V45.9"/><rect x="561.0" y="26.9" width="6" height="9.1" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M579.8000000000001 -0.1V22.2"/><rect x="576.8" y="4.9" width="6" height="11.3" rx="1"/></g><g stroke="#35b98b" fill="#35b98b" stroke-width="1.4"><path d="M595.6 7.3V34.6"/><rect x="592.6" y="14.3" width="6" height="12.3" rx="1"/></g>
                            <path d="M0 158 C38 146 61 166 98 137 S151 145 188 111 S234 132 271 101 S315 115 352 82 S396 101 433 67 S477 87 514 52 S570 64 620 28" fill="none" stroke="#d1b477" stroke-width="1.4" opacity=".8"/>
                            <path d="M0 180H620" stroke="#7d8da1" stroke-opacity=".45" stroke-dasharray="3 4"/>
                            <rect x="8.0" y="192.0" width="6" height="21.0" fill="#e46f78" opacity=".55"/><rect x="23.8" y="180.2" width="6" height="32.8" fill="#35b98b" opacity=".55"/><rect x="39.6" y="197.9" width="6" height="15.1" fill="#35b98b" opacity=".55"/><rect x="55.4" y="181.8" width="6" height="31.2" fill="#e46f78" opacity=".55"/><rect x="71.2" y="192.1" width="6" height="20.9" fill="#35b98b" opacity=".55"/><rect x="87.0" y="184.8" width="6" height="28.2" fill="#35b98b" opacity=".55"/><rect x="102.8" y="175.2" width="6" height="37.8" fill="#e46f78" opacity=".55"/><rect x="118.6" y="189.2" width="6" height="23.8" fill="#35b98b" opacity=".55"/><rect x="134.4" y="183.4" width="6" height="29.6" fill="#35b98b" opacity=".55"/><rect x="150.2" y="194.5" width="6" height="18.5" fill="#e46f78" opacity=".55"/><rect x="166.0" y="180.9" width="6" height="32.1" fill="#35b98b" opacity=".55"/><rect x="181.8" y="200.4" width="6" height="12.6" fill="#35b98b" opacity=".55"/><rect x="197.6" y="168.0" width="6" height="45.0" fill="#e46f78" opacity=".55"/><rect x="213.4" y="201.4" width="6" height="11.6" fill="#35b98b" opacity=".55"/><rect x="229.2" y="180.7" width="6" height="32.3" fill="#35b98b" opacity=".55"/><rect x="245.0" y="195.4" width="6" height="17.6" fill="#e46f78" opacity=".55"/><rect x="260.8" y="182.9" width="6" height="30.1" fill="#35b98b" opacity=".55"/><rect x="276.6" y="189.9" width="6" height="23.1" fill="#35b98b" opacity=".55"/><rect x="292.4" y="174.5" width="6" height="38.5" fill="#e46f78" opacity=".55"/><rect x="308.2" y="185.4" width="6" height="27.6" fill="#35b98b" opacity=".55"/><rect x="324.0" y="191.3" width="6" height="21.7" fill="#35b98b" opacity=".55"/><rect x="339.8" y="182.2" width="6" height="30.8" fill="#e46f78" opacity=".55"/><rect x="355.6" y="196.9" width="6" height="16.1" fill="#35b98b" opacity=".55"/><rect x="371.4" y="180.3" width="6" height="32.7" fill="#35b98b" opacity=".55"/><rect x="387.2" y="191.0" width="6" height="22.0" fill="#e46f78" opacity=".55"/><rect x="403.0" y="180.1" width="6" height="32.9" fill="#35b98b" opacity=".55"/><rect x="418.8" y="198.8" width="6" height="14.2" fill="#35b98b" opacity=".55"/><rect x="434.6" y="181.4" width="6" height="31.6" fill="#e46f78" opacity=".55"/><rect x="450.4" y="193.0" width="6" height="20.0" fill="#35b98b" opacity=".55"/><rect x="466.2" y="184.3" width="6" height="28.7" fill="#35b98b" opacity=".55"/><rect x="482.0" y="175.9" width="6" height="37.1" fill="#e46f78" opacity=".55"/><rect x="497.8" y="188.4" width="6" height="24.6" fill="#35b98b" opacity=".55"/><rect x="513.6" y="183.9" width="6" height="29.1" fill="#35b98b" opacity=".55"/><rect x="529.4" y="193.6" width="6" height="19.4" fill="#e46f78" opacity=".55"/><rect x="545.2" y="181.2" width="6" height="31.8" fill="#35b98b" opacity=".55"/><rect x="561.0" y="199.5" width="6" height="13.5" fill="#35b98b" opacity=".55"/><rect x="576.8" y="168.0" width="6" height="45.0" fill="#e46f78" opacity=".55"/><rect x="592.6" y="202.4" width="6" height="10.6" fill="#35b98b" opacity=".55"/>
                            <rect x="574" y="17" width="46" height="17" rx="3" fill="#247f68"/><text x="597" y="29" fill="#fff" font-size="9" text-anchor="middle">67.48k</text>
                        </svg>
                        <div class="shell-chart-axis"><span>09:00</span><span>10:00</span><span>11:00</span><span>12:00</span><span>13:00</span><span>14:00</span></div>
                        <span class="chart-illustration-note">ILLUSTRATIVE MARKET DATA · NOT A LIVE QUOTE</span>
                    </div>
                    <div class="shell-chart-legend"><span><i class="legend-dot legend-cyan"></i> BTC / USDT · Candlestick</span><span>Volume · 15-minute interval</span></div>
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

    <section class="shell-risk-section" id="risk" aria-labelledby="shell-risk-title">
        <div class="shell-risk-icon" aria-hidden="true">!</div>
        <div>
            <span class="eyebrow">IMPORTANT RISK INFORMATION</span>
            <h2 id="shell-risk-title">Trading involves significant risk.</h2>
            <p>Fixed-time and binary-style options are high-risk financial products. You may lose all the money committed to a position. These products may not be suitable for every investor and may be restricted or prohibited in some jurisdictions. Do not trade with money you cannot afford to lose. Understand the product, its payout structure, fees and settlement rules, and seek independent financial and legal advice where appropriate. SHELL does not promise profits or guaranteed income.</p>
        </div>
    </section>

    <footer class="shell-home-footer" aria-label="SHELL site footer">
        <div class="shell-footer-main">
            <div class="shell-footer-brand">
                <a class="brand" href="{{ route('home') }}" aria-label="SHELL home"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">MARKET & TRADING PLATFORM</span></span></a>
                <p>A focused workspace to explore supported markets, understand fixed-time trading and review account activity. Explore the demo environment before deciding whether real-money trading is right for you.</p>
                <span class="shell-footer-note"><i aria-hidden="true"></i> Trade thoughtfully. Understand the risk.</span>
            </div>

            <nav class="shell-footer-column" aria-label="Explore SHELL">
                <h3>Explore</h3>
                <a href="#how-it-works">How it works</a>
                <a href="#faq">Frequently asked questions</a>
                <a href="#risk">Risk information</a>
            </nav>
        </div>

        <div class="shell-home-footer-bottom">
            <p>© {{ date('Y') }} SHELL. All rights reserved.</p>
            <a class="shell-footer-backtop" href="#top">Back to top <span aria-hidden="true">↑</span></a>
        </div>
    </footer>
</div>

    <button type="button" class="shell-chat-launcher" id="shellChatLauncher" aria-controls="shellChatPanel" aria-expanded="false">
        <span class="shell-chat-launch-icon" aria-hidden="true">✉</span><span><strong>Need help?</strong><small>Chat with our team</small></span><span class="shell-chat-launch-arrow">↗</span>
    </button>
    <section class="shell-chat-panel" id="shellChatPanel" aria-label="SHELL support chat" hidden>
        <header class="shell-chat-header"><div class="shell-chat-agent"><span class="shell-chat-avatar">S</span><div><strong>SHELL Support</strong><small><i></i> Send us a message</small></div></div><button type="button" class="shell-chat-close" id="shellChatClose" aria-label="Close chat">×</button></header>
        <div class="shell-chat-intro" id="shellChatIntro"><strong>How can we help?</strong><p>Start a conversation with our team. Continue chatting here and receive conversation copies by email.</p></div>
        <div class="shell-chat-thread" id="shellChatThread" aria-live="polite" hidden></div>
        <form class="shell-chat-form" id="shellChatStartForm">
            <label>Your name<input name="name" autocomplete="name" maxlength="100" required placeholder="Full name"></label>
            <label>Email address<input name="email" type="email" autocomplete="email" maxlength="254" required placeholder="you@example.com"></label>
            <label>Help category<select name="category" required><option value="account">Account</option><option value="trading">Trading</option><option value="market_data">Market data</option><option value="security">Security</option><option value="other">Other</option></select></label>
            <label>Your message<textarea name="message" rows="3" maxlength="5000" required placeholder="Tell us how we can help..."></textarea></label>
            <input name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="shell-chat-honeypot">
            <p class="shell-chat-error" id="shellChatStartError" role="alert"></p>
            <button type="submit" class="button button-primary shell-chat-submit">Start conversation →</button>
            <small class="shell-chat-privacy">Your details are used to respond to this support request.</small>
        </form>
        <form class="shell-chat-reply" id="shellChatReplyForm" hidden><textarea name="message" rows="2" maxlength="5000" required placeholder="Write your reply..." aria-label="Your reply"></textarea><button type="submit" aria-label="Send reply">➤</button></form>
        <p class="shell-chat-footnote">Your conversation is saved in SHELL Support.</p>
    </section>
    <style>
    .shell-chat-launcher{position:fixed;z-index:80;right:28px;bottom:26px;display:flex;align-items:center;gap:12px;padding:12px 17px;border:1px solid rgba(203,169,99,.42);border-radius:16px;background:#101d32;color:#f7f4ed;box-shadow:0 14px 42px #0005;cursor:pointer;text-align:left}.shell-chat-launcher strong,.shell-chat-launcher small{display:block}.shell-chat-launcher strong{font-size:13px}.shell-chat-launcher small{font-size:11px;color:#aab6c5;margin-top:2px}.shell-chat-launch-icon{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:#c7a45e22;color:#d7b978;font-size:18px}.shell-chat-launch-arrow{color:#d7b978}.shell-chat-panel{position:fixed;z-index:81;right:28px;bottom:94px;width:min(390px,calc(100vw - 28px));height:min(610px,calc(100dvh - 125px));min-height:420px;display:flex;flex-direction:column;overflow:hidden;border:1px solid #c7a45e55;border-radius:19px;background:#0d192b;color:#f4f5f7;box-shadow:0 24px 75px #0008}.shell-chat-panel[hidden],.shell-chat-thread[hidden],.shell-chat-form[hidden],.shell-chat-reply[hidden]{display:none}.shell-chat-header{display:flex;justify-content:space-between;align-items:center;padding:17px 18px;background:#14243a;border-bottom:1px solid #ffffff14}.shell-chat-agent{display:flex;align-items:center;gap:11px}.shell-chat-avatar{display:grid;place-items:center;width:39px;height:39px;border-radius:12px;background:#c7a45e;color:#142033;font-weight:800}.shell-chat-agent strong,.shell-chat-agent small{display:block}.shell-chat-agent strong{font-size:14px}.shell-chat-agent small{margin-top:4px;color:#aeb9c7;font-size:11px}.shell-chat-agent small i{display:inline-block;width:7px;height:7px;border-radius:50%;background:#64c69c;margin-right:5px}.shell-chat-close{border:0;background:transparent;color:#dce3ec;font-size:27px;cursor:pointer}.shell-chat-intro{padding:18px 19px 10px}.shell-chat-intro strong{font-size:18px}.shell-chat-intro p{font-size:12px;line-height:1.6;color:#aeb9c7;margin:7px 0 0}.shell-chat-form{display:grid;gap:10px;padding:8px 19px 16px;overflow-y:auto}.shell-chat-form label{display:grid;gap:5px;font-size:11px;color:#c5cfdb}.shell-chat-form input,.shell-chat-form select,.shell-chat-form textarea,.shell-chat-reply textarea{width:100%;box-sizing:border-box;border:1px solid #b4c2d333;border-radius:9px;background:#101f33;color:#f4f5f7;padding:10px 11px;font:inherit;font-size:12px;outline:none}.shell-chat-form input:focus,.shell-chat-form select:focus,.shell-chat-form textarea:focus,.shell-chat-reply textarea:focus{border-color:#c7a45e}.shell-chat-form textarea,.shell-chat-reply textarea{resize:vertical}.shell-chat-form select option{background:#101f33}.shell-chat-submit{width:100%;justify-content:center;min-height:42px}.shell-chat-privacy,.shell-chat-footnote{font-size:10px;color:#8998aa;line-height:1.5}.shell-chat-privacy{text-align:center}.shell-chat-honeypot{position:absolute!important;left:-10000px!important;width:1px!important;height:1px!important;opacity:0!important}.shell-chat-error{color:#ff9b9b;font-size:11px;margin:0}.shell-chat-thread{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px}.shell-chat-message{max-width:88%;padding:10px 12px;border-radius:12px;background:#172941;align-self:flex-start}.shell-chat-message.is-you{align-self:flex-end;background:#263a50}.shell-chat-message strong{display:block;font-size:10px;color:#d7b978;margin-bottom:5px}.shell-chat-message p{font-size:12px;line-height:1.55;margin:0;white-space:pre-wrap;overflow-wrap:anywhere}.shell-chat-message time{display:block;font-size:9px;color:#91a0b2;margin-top:6px}.shell-chat-reply{display:flex;gap:8px;padding:12px 14px;border-top:1px solid #ffffff14}.shell-chat-reply textarea{min-height:42px;max-height:100px}.shell-chat-reply button{width:44px;flex:0 0 44px;border:0;border-radius:10px;background:#c7a45e;color:#152238;font-size:17px;cursor:pointer}.shell-chat-footnote{margin:0;padding:0 15px 12px;text-align:center}@media(max-width:600px){.shell-chat-launcher{right:14px;bottom:14px;padding:10px 13px}.shell-chat-panel{right:8px;bottom:78px;width:calc(100vw - 16px);height:min(630px,calc(100dvh - 95px));min-height:390px}}
    </style>
    @push('scripts')
    <script>
    (() => {
        const launcher=document.getElementById('shellChatLauncher'),panel=document.getElementById('shellChatPanel');
        if(!launcher||!panel)return;
        const close=document.getElementById('shellChatClose'),intro=document.getElementById('shellChatIntro'),startForm=document.getElementById('shellChatStartForm'),replyForm=document.getElementById('shellChatReplyForm'),thread=document.getElementById('shellChatThread'),errorBox=document.getElementById('shellChatStartError'),footnote=panel.querySelector('.shell-chat-footnote');
        const startUrl=@json(route('guest.support.start')),messagesUrl=@json(route('guest.support.messages',['ticket'=>'__ID__'])),replyUrl=@json(route('guest.support.reply',['ticket'=>'__ID__']));
        const key='shellGuestSupportTicket';let ticket=null,lastId=0,pollTimer=null;
        const url=(base,id)=>base.replace('__ID__',encodeURIComponent(id));
        const headers=()=>({'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||''});
        const errorMessage=data=>data?.message||Object.values(data?.errors||{}).flat()[0]||'Unable to send. Please try again.';
        const show=()=>{panel.hidden=false;launcher.setAttribute('aria-expanded','true')},hide=()=>{panel.hidden=true;launcher.setAttribute('aria-expanded','false')};
        const appendMessage=m=>{if(!m||Number(m.id)<=lastId)return;lastId=Number(m.id);const el=document.createElement('article');el.className='shell-chat-message'+(m.role==='guest'?' is-you':'');const who=document.createElement('strong');who.textContent=m.sender||'SHELL Support';const body=document.createElement('p');body.textContent=m.message||'';const time=document.createElement('time');const d=new Date(String(m.created_at||'').replace(' ','T'));time.textContent=Number.isNaN(d.getTime())?'':d.toLocaleString();el.append(who,body,time);thread.append(el);thread.scrollTop=thread.scrollHeight};
        const render=items=>(items||[]).forEach(appendMessage);
        const stop=()=>{if(pollTimer)clearInterval(pollTimer);pollTimer=null};
        const activate=(id,items)=>{ticket=id;sessionStorage.setItem(key,String(id));intro.hidden=true;startForm.hidden=true;thread.hidden=false;replyForm.hidden=false;thread.replaceChildren();lastId=0;render(items);stop();pollTimer=setInterval(poll,5000)};
        async function poll(){if(!ticket)return;try{const r=await fetch(url(messagesUrl,ticket),{headers:headers(),credentials:'same-origin'});if(r.status===404){stop();sessionStorage.removeItem(key);ticket=null;return}if(!r.ok)return;const d=await r.json();render(d.messages);if(d.status==='closed'){replyForm.hidden=true;footnote.textContent='This conversation is closed. Start a new chat if you need more help.'}}catch(_){}}
        async function restore(){const saved=sessionStorage.getItem(key);if(!saved)return;try{const r=await fetch(url(messagesUrl,saved),{headers:headers(),credentials:'same-origin'});if(!r.ok){sessionStorage.removeItem(key);return}const d=await r.json();activate(saved,d.messages);if(d.status==='closed')replyForm.hidden=true}catch(_){}}
        launcher.addEventListener('click',()=>{show();restore()});close.addEventListener('click',hide);document.getElementById('shellFooterOpenChat')?.addEventListener('click',()=>{show();restore();launcher.focus()});
        startForm.addEventListener('submit',async e=>{e.preventDefault();errorBox.textContent='';const button=startForm.querySelector('button[type="submit"]');button.disabled=true;button.textContent='Sending...';try{const r=await fetch(startUrl,{method:'POST',headers:{...headers(),'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify(Object.fromEntries(new FormData(startForm)))});const d=await r.json();if(!r.ok)throw new Error(errorMessage(d));activate(d.ticket_id,d.messages);footnote.textContent='Conversation copies are emailed to you as messages are exchanged.'}catch(err){errorBox.textContent=err.message}finally{button.disabled=false;button.textContent='Start conversation →'}});
        replyForm.addEventListener('submit',async e=>{e.preventDefault();const field=replyForm.elements.message,msg=field.value.trim();if(!msg||!ticket)return;const button=replyForm.querySelector('button');button.disabled=true;try{const r=await fetch(url(replyUrl,ticket),{method:'POST',headers:{...headers(),'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify({message:msg})});const d=await r.json();if(!r.ok)throw new Error(errorMessage(d));field.value='';render(d.messages)}catch(err){window.alert(err.message)}finally{button.disabled=false}});
        document.addEventListener('visibilitychange',()=>{if(!document.hidden&&ticket)poll()});
    })();
    </script>
    @endpush

@endsection
