<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Workspace') · {{ config('app.name', 'Shell') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/site.js'])
</head>
<body class="shell-body">
<div class="app-shell">
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">FINANCIAL TERMINAL</span></span></a>
        <div class="sidebar-label">WORKSPACE</div>
        <nav class="side-nav">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Overview</a>
                <a href="{{ route('admin.clients') }}" class="{{ request()->routeIs('admin.clients') ? 'active' : '' }}">Client accounts</a>
            @else
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Overview</a>
                <a href="{{ route('markets.index') }}" class="{{ request()->routeIs('markets.*') ? 'active' : '' }}">Markets</a>
                <a href="{{ route('trading.index') }}" class="{{ request()->routeIs('trading.*') ? 'active' : '' }}">Trading hub</a>
                <a href="{{ route('portfolio.index') }}" class="{{ request()->routeIs('portfolio.*') ? 'active' : '' }}">Portfolio</a>
                <a href="{{ route('activity.index') }}" class="{{ request()->routeIs('activity.*') ? 'active' : '' }}">Activity</a>
            @endif
        </nav>
        <div class="sidebar-bottom">
            <a href="{{ route('profile.edit') }}">Account settings</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-logout" type="submit">Sign out</button></form>
        </div>
    </aside>
    <main class="main-panel">
        <header class="topbar"><div><span class="eyebrow">@yield('eyebrow', 'ACCOUNT')</span><h1>@yield('heading', 'Overview')</h1></div><div class="topbar-right"><span class="status-pill"><i></i>{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Client account' }}</span><span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span></div></header>
        <section class="page-content">@yield('content')</section>
    </main>
</div>
<div id="toast-region" class="toast-region" aria-live="polite"></div>
@stack('modals')
</body>
</html>
