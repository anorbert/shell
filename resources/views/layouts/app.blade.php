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
</head>
<body class="shell-body">
<svg aria-hidden="true" class="icon-library" xmlns="http://www.w3.org/2000/svg">
    <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
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
                <a href="{{ route('admin.wallet-operations.index') }}" class="{{ request()->routeIs('admin.wallet-operations.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-wallet"/></svg><span>Wallet operations</span></a>
            @else
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard')?'active':'' }}"><svg class="nav-icon"><use href="#i-grid"/></svg><span>Overview</span></a>
                <a href="{{ route('trading.index') }}" class="{{ request()->routeIs('trading.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-clock"/></svg><span>Trade</span></a>
                <a href="{{ route('markets.index') }}" class="{{ request()->routeIs('markets.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-chart"/></svg><span>Markets</span></a>
                <a href="{{ route('portfolio.index') }}" class="{{ request()->routeIs('portfolio.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-briefcase"/></svg><span>Portfolio</span></a>
            @endif
        </nav>
    </aside>
    <main class="main-panel">
        <header class="topbar">
            <div class="topbar-title"><span class="eyebrow">@yield('eyebrow','ACCOUNT')</span><h1>@yield('heading','Overview')</h1></div>
            <nav class="topbar-nav" aria-label="Workspace shortcuts">
                @if(auth()->user()->role==='admin')
                    <a href="{{ route('admin.kyc.index') }}" class="{{ request()->routeIs('admin.kyc.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-shield"/></svg><span>KYC review</span><span class="nav-count">{{ \Illuminate\Support\Facades\DB::table('kyc_submissions')->where('status','pending')->count() }}</span></a>
                    <a href="{{ route('admin.support.index') }}" class="{{ request()->routeIs('admin.support.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-message"/></svg><span>Support inbox</span></a>
                @else
                    <a href="{{ route('convert.index') }}" class="{{ request()->routeIs('convert.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-repeat"/></svg><span>Convert</span></a>
                    <a href="{{ route('wallet-requests.index') }}" class="{{ request()->routeIs('wallet-requests.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-wallet"/></svg><span>Wallet</span></a>
                    <a href="{{ route('activity.index') }}" class="{{ request()->routeIs('activity.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-clock"/></svg><span>Activity</span></a>
                    <a href="{{ route('statement.index') }}" class="{{ request()->routeIs('statement.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-receipt"/></svg><span>Statement</span></a>
                    <a href="{{ route('support.index') }}" class="{{ request()->routeIs('support.*')?'active':'' }}"><svg class="nav-icon"><use href="#i-life"/></svg><span>Support</span></a>
                    <a class="topbar-icon-link {{ request()->routeIs('notifications.*')?'active':'' }}" href="{{ route('notifications.index') }}" aria-label="Notifications" title="Notifications"><svg class="nav-icon"><use href="#i-bell"/></svg><span class="nav-count">{{ \Illuminate\Support\Facades\DB::table('user_notifications')->where('user_id',auth()->id())->whereNull('read_at')->count() }}</span></a>
                @endif
            </nav>
            <div class="topbar-right">
                <span class="status-pill"><i></i>{{ auth()->user()->role==='admin'?'Administrator':'Client account' }}</span>
                <details class="profile-menu">
                    <summary class="profile-trigger" aria-label="Open profile menu"><span class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><span class="profile-trigger-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role==='admin'?'Administrator':'My account' }}</small></span><span class="profile-chevron">⌄</span></summary>
                    <div class="profile-dropdown">
                        <div class="profile-dropdown-head"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div>
                        <a href="{{ route('profile.edit') }}"><svg class="nav-icon"><use href="#i-user"/></svg>Profile & account details</a>
                        <a href="{{ route('password.edit') }}"><svg class="nav-icon"><use href="#i-shield"/></svg>Change password</a>
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
@stack('scripts')
</body>
</html>
