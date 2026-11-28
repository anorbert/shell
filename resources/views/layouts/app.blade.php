<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#080d18">
    <title>@yield('title','Workspace') · {{ config('app.name','Shell') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/site.js'])
    @stack('styles')
    <style>
        /* Wallet switcher layout is kept with the shell so stale compiled CSS cannot put the menu in the header flow. */
        .topbar-right{display:flex;align-items:center;justify-content:flex-end;gap:12px;min-width:0}
        .topbar-wallet-menu{position:relative;display:block;flex:0 0 auto;z-index:300}
        .topbar-wallet-menu>.topbar-wallet-balance{display:flex;flex-direction:column;align-items:stretch;justify-content:center;min-width:164px;min-height:44px;box-sizing:border-box;padding:6px 11px;border:1px solid #34445a;border-radius:9px;background:#111c2b;gap:3px;list-style:none;cursor:pointer}
        .topbar-wallet-menu>.topbar-wallet-balance::-webkit-details-marker{display:none}
        .topbar-wallet-menu>.topbar-wallet-balance::marker{content:""}
        .topbar-wallet-menu[open]>.topbar-wallet-balance,.topbar-wallet-menu>.topbar-wallet-balance:hover{background:#172337;border-color:#806b43}
        .topbar-wallet-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;color:#91a1b6;font-size:9px;font-weight:700;letter-spacing:.08em;line-height:1.2}
        .topbar-wallet-meta i{width:6px;height:6px;flex:0 0 6px;border-radius:50%;background:#65c99c;box-shadow:0 0 0 3px #65c99c18}
        .topbar-wallet-balance>strong{color:#f0d99f;font-size:13px;font-weight:700;font-variant-numeric:tabular-nums;white-space:nowrap;line-height:1.2}
        .topbar-wallet-balance>strong small{color:#91a1b6;font-size:9px;font-weight:600;margin-left:2px}
        .wallet-switch-chevron{font-size:13px;line-height:9px;color:#9eacbf}
        .topbar-wallet-menu[open] .wallet-switch-chevron{transform:rotate(180deg)}
        .wallet-switch-dropdown{display:block;position:absolute!important;top:calc(100% + 10px)!important;right:0!important;left:auto!important;width:310px;max-width:calc(100vw - 24px);box-sizing:border-box;padding:10px;background:#111c2b;border:1px solid #35445a;border-radius:10px;box-shadow:0 18px 55px #000b;z-index:400}
        .topbar-wallet-menu:not([open])>.wallet-switch-dropdown{display:none!important}
        .wallet-switch-heading{display:grid;gap:5px;padding:11px 11px 13px;margin:0 0 5px;border-bottom:1px solid #29384d}
        .wallet-switch-heading>span{color:#e6c681;font-size:9px;font-weight:800;letter-spacing:.13em}
        .wallet-switch-heading>small{color:#8795a9;font-size:10px;line-height:1.45}
        .wallet-switch-dropdown form{display:block;margin:0}
        .wallet-switch-option{display:flex;width:100%;align-items:center;gap:9px;padding:10px 8px;border:1px solid transparent;border-radius:7px;background:transparent;color:#e9edf5;text-align:left;cursor:pointer}
        .wallet-switch-option:hover,.wallet-switch-option.is-active{background:#1a2638;border-color:#34445a}
        .wallet-switch-icon{width:31px;height:31px;display:grid;place-items:center;flex:0 0 31px;border-radius:8px;background:#243246;color:#aab8ca}
        .wallet-switch-icon.real{background:#30291d;color:#e2c98e}
        .wallet-switch-icon .nav-icon{width:16px;height:16px}
        .wallet-switch-copy{display:grid;gap:3px;min-width:0;flex:1}
        .wallet-switch-copy strong,.wallet-switch-amount strong{font-size:11px;font-weight:700}
        .wallet-switch-copy small{color:#8291a6;font-size:9px;white-space:nowrap}
        .wallet-switch-amount{display:grid;gap:3px;text-align:right}
        .wallet-switch-amount strong{color:#f0d99f;font-variant-numeric:tabular-nums;white-space:nowrap}
        .wallet-switch-amount small{color:#8493a8;font-size:8px}
        .wallet-switch-check{color:#70c9a0;font-size:13px;font-weight:800}
        .wallet-switch-manage{display:flex;justify-content:space-between;align-items:center;margin-top:6px;padding:11px 9px 4px;border-top:1px solid #29384d;color:#d7bd86;font-size:10px;font-weight:700;text-decoration:none}
        @media(max-width:720px){.topbar-wallet-menu>.topbar-wallet-balance{min-width:112px;min-height:40px;padding:5px 8px}.wallet-switch-dropdown{top:calc(100% + 8px)!important;right:0!important;width:min(290px,calc(100vw - 20px))}}
        @media(max-width:380px){.topbar-wallet-menu>.topbar-wallet-balance{min-width:102px;padding:5px 6px}.wallet-switch-dropdown{right:-4px!important;width:min(280px,calc(100vw - 16px))}}
    </style>
</head>
<body class="shell-body">
<svg aria-hidden="true" class="icon-library" xmlns="http://www.w3.org/2000/svg">
    <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
    <symbol id="i-gift" viewBox="0 0 24 24"><rect x="3" y="9" width="18" height="12" rx="2"/><path d="M12 9v12M3 13h18M12 9H7.5a2.5 2.5 0 1 1 2.5-2.5V9Zm0 0h4.5A2.5 2.5 0 1 0 14 6.5V9Z"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
    <symbol id="i-chart" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m7 14 4-4 4 3 6-7"/></symbol>
    <symbol id="i-wallet" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 8h18M16 14h2"/><path d="M6 5V3h12"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/></symbol>
    <symbol id="i-message" viewBox="0 0 24 24"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z"/></symbol>
    <symbol id="i-repeat" viewBox="0 0 24 24"><path d="m17 2 4 4-4 4"/><path d="M3 11V9a3 3 0 0 1 3-3h15M7 22l-4-4 4-4"/><path d="M21 13v2a3 3 0 0 1-3 3H3"/></symbol>
    <symbol id="i-briefcase" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/></symbol>
    <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M12 3v18m-7-7 7 7 7-7M5 7l7-4 7 4"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
    <symbol id="i-receipt" viewBox="0 0 24 24"><path d="M4 3h16v18l-4-2-4 2-4-2-4 2V3Z"/><path d="M8 8h8M8 12h8M8 16h4"/></symbol>
    <symbol id="i-settings" viewBox="0 0 24 24"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.5-.9l-1.7.6-1.4-2.4 1.4-1.1a8 8 0 0 1 0-1.8l-1.4-1.1 1.4-2.4 1.7.6a8 8 0 0 1 1.5-.9l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.6 1.4 2.4-1.4 1.1a8 8 0 0 1 0 1.8Z"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></symbol>
    <symbol id="i-life" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3m.1 4h.01"/></symbol>
    <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></symbol>
</svg>
<div class="app-shell">
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">FINANCIAL TERMINAL</span></span></a>
        <div class="sidebar-label">MAIN MENU</div>
        <nav class="side-nav" aria-label="Primary navigation">
            @if(auth()->user()->role==='admin')
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard')?'active':'' }}"><svg class="nav-icon"><use href="#i-grid"/></svg><span>Overview</span></a>
                <a href="{{ route('admin.clients') }}" class="{{ request()->routeIs('admin.clients*')?'active':'' }}"><svg class="nav-icon"><use href="#i-users"/></svg><span>Client accounts</span></a>
                <a href="{{ route('admin.operations') }}" class="{{ request()->routeIs('admin.operations')?'active':'' }}"><svg class="nav-icon"><use href="#i-chart"/></svg><span>Trading operations</span></a>
                <a href="{{ route('admin.referrals.index') }}" class="{{ request()->routeIs('admin.referrals.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-gift"/></svg><span>Referral program</span></a>
                <a href="{{ route('admin.wallet-operations.index') }}" class="{{ request()->routeIs('admin.wallet-operations.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-wallet"/></svg><span>Wallet operations</span></a>
            @else
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard')?'active':'' }}"><svg class="nav-icon"><use href="#i-grid"/></svg><span>Home</span></a>
                <a href="{{ route('trading.index') }}" class="{{ request()->routeIs('trading.*','activity.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-clock"/></svg><span>Trade</span></a>
                <a href="{{ route('wallet-requests.index') }}" class="{{ request()->routeIs('wallet-requests.*','statement.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-wallet"/></svg><span>Wallet</span></a>
                <a href="{{ route('referrals.index') }}" class="{{ request()->routeIs('referrals.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-gift"/></svg><span>Referral</span></a>
                <a href="{{ route('markets.index') }}" class="{{ request()->routeIs('markets.*','convert.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-chart"/></svg><span>Markets</span></a>
            @endif
        </nav>
    </aside>
    <main class="main-panel">
        <header class="topbar">
            <div class="topbar-title"><span class="eyebrow">@yield('eyebrow','ACCOUNT')</span><h1>@yield('heading','Overview')</h1></div>
            <nav class="topbar-nav" aria-label="Workspace shortcuts">
                @if(auth()->user()->role==='admin')
                    <a href="{{ route('admin.kyc.index') }}" aria-label="KYC review" title="KYC review" class="{{ request()->routeIs('admin.kyc.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-shield"/></svg><span>KYC review</span><span class="nav-count">{{ \Illuminate\Support\Facades\DB::table('kyc_submissions')->where('status','pending')->count() }}</span></a>
                    <a href="{{ route('admin.support.index') }}" aria-label="Support inbox" title="Support inbox" class="{{ request()->routeIs('admin.support.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-message"/></svg><span>Support inbox</span></a>
                @else
                    <a href="{{ route('support.index') }}" aria-label="Support" title="Support" class="{{ request()->routeIs('support.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-life"/></svg><span>Support</span></a>
                    <a class="topbar-icon-link {{ request()->routeIs('notifications.*')?'active':'' }}" href="{{ route('notifications.index') }}" aria-label="Notifications" title="Notifications"><svg class="nav-icon"><use href="#i-bell"/></svg><span class="nav-count">{{ Illuminate\Support\Facades\DB::table('user_notifications')->where('user_id',auth()->id())->whereNull('read_at')->count() }}</span></a>
                @endif
            </nav>
            <div class="topbar-right">
                @if(auth()->user()->role==='client')
                    @php
                        $activeWalletType = session('active_wallet', 'demo') === 'real' ? 'real' : 'demo';
                        $activeWalletCurrency = config('shell.paper.currency', 'USD');
                        $activeWalletLedger = app(\App\Services\LedgerService::class);
                        $activeWalletId = $activeWalletType === 'real'
                            ? $activeWalletLedger->realWallet(auth()->user(), $activeWalletCurrency)
                            : $activeWalletLedger->demoWallet(auth()->user(), $activeWalletCurrency);
                        $activeWalletBalance = \App\Support\Decimal::fromUnits($activeWalletLedger->balanceUnits($activeWalletId));
                    @endphp
                    @php
                        $demoWalletId = $activeWalletLedger->demoWallet(auth()->user(), $activeWalletCurrency);
                        $realWalletId = $activeWalletLedger->realWallet(auth()->user(), $activeWalletCurrency);
                        $demoWalletBalance = \App\Support\Decimal::fromUnits($activeWalletLedger->balanceUnits($demoWalletId));
                        $realWalletBalance = \App\Support\Decimal::fromUnits($activeWalletLedger->balanceUnits($realWalletId));
                    @endphp
                    <details class="topbar-wallet-menu">
                        <summary class="topbar-wallet-balance" aria-label="Current {{ strtoupper($activeWalletType) }} account balance; open account switcher">
                            <span class="topbar-wallet-meta"><i></i>{{ strtoupper($activeWalletType) }} ACCOUNT <span class="wallet-switch-chevron">⌄</span></span>
                            <strong>@money($activeWalletBalance) <small>{{ $activeWalletCurrency }}</small></strong>
                        </summary>
                        <div class="wallet-switch-dropdown">
                            <div class="wallet-switch-heading">
                                <span>TRADING ACCOUNTS</span>
                                <small>Select an account to trade with</small>
                            </div>
                            @foreach ([
                                'demo' => ['label' => 'Demo account', 'balance' => $demoWalletBalance, 'description' => 'Practice with virtual funds'],
                                'real' => ['label' => 'Real account', 'balance' => $realWalletBalance, 'description' => 'Trade with your available funds'],
                            ] as $walletKey => $walletOption)
                                <form method="POST" action="{{ route('wallet.account.switch') }}">
                                    @csrf
                                    <input type="hidden" name="account" value="{{ $walletKey }}">
                                    <button type="submit" class="wallet-switch-option {{ $activeWalletType === $walletKey ? 'is-active' : '' }}">
                                        <span class="wallet-switch-icon {{ $walletKey }}"><svg class="nav-icon"><use href="#i-wallet"/></svg></span>
                                        <span class="wallet-switch-copy"><strong>{{ $walletOption['label'] }}</strong><small>{{ $walletOption['description'] }}</small></span>
                                        <span class="wallet-switch-amount"><strong>@money($walletOption['balance'])</strong><small>{{ $activeWalletCurrency }}</small></span>
                                        @if ($activeWalletType === $walletKey)<span class="wallet-switch-check">✓</span>@endif
                                    </button>
                                </form>
                            @endforeach
                            <a class="wallet-switch-manage" href="{{ route('wallet-requests.index', ['account' => $activeWalletType]) }}">Manage wallet <span>↗</span></a>
                        </div>
                    </details>
                @endif
                @if(auth()->user()->role==='admin')
                    <span class="status-pill"><i></i>Administrator</span>
                @endif
                <details class="profile-menu">
                    <summary class="profile-trigger" aria-label="Open profile menu"><span class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><span class="profile-trigger-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role==='admin'?'Administrator':'My account' }}</small></span><span class="profile-chevron">⌄</span></summary>
                    <div class="profile-dropdown">
                        <div class="profile-dropdown-head"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div>
                        <a href="{{ route('profile.edit') }}"><svg class="nav-icon"><use href="#i-user"/></svg>Profile & account details</a>
                        @if(auth()->user()->role==='client')
                            <a href="{{ route('kyc.index') }}" class="{{ request()->routeIs('kyc.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-shield"/></svg>Identity verification (KYC)</a>
                        @endif
                        @if(auth()->user()->role==='client')
                            <a href="{{ route('security.index') }}" class="{{ request()->routeIs('security.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-settings"/></svg>Security & sessions</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><svg class="nav-icon"><use href="#i-arrow"/></svg>Sign out</button></form>
                    </div>
                </details>
            </div>
        </header>
        <section class="page-content">@yield('content')</section>
    </main>
</div>
<div id="toast-region" class="toast-region" aria-live="polite"></div>
@stack('modals')
<script>
document.addEventListener('click', function (event) {
    const opener = event.target.closest('[data-attention-open]');
    if (opener) {
        const dialog = document.getElementById(opener.dataset.attentionOpen);
        if (dialog && typeof dialog.showModal === 'function') dialog.showModal();
        return;
    }

    const closer = event.target.closest('[data-attention-close]');
    if (closer) {
        const dialog = closer.closest('dialog');
        if (dialog) dialog.close();
        return;
    }

    if (event.target instanceof HTMLDialogElement && event.target.classList.contains('client-attention-dialog')) {
        event.target.close();
    }
});
</script>
@stack('scripts')
</body>
</html>
