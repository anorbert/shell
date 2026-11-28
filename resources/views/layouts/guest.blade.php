<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Shell'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/site.js'])
    <style>
        .button-google{background:#fff;color:#202124;border:1px solid #dadce0;min-height:46px;font-size:13px;font-weight:700;letter-spacing:0;transition:background .18s,box-shadow .18s}
        .button-google:hover{background:#f8faff;color:#202124;box-shadow:0 2px 8px #0002}
        .button-google svg{flex:0 0 18px}
        .auth-divider{display:flex;align-items:center;gap:13px;margin:21px 0 2px;color:#78869a;font-size:9px;font-weight:700;letter-spacing:.13em}
        .auth-divider:before,.auth-divider:after{content:"";height:1px;flex:1;background:#2b394d}
        .auth-card .alert{margin-top:16px}
    </style>
</head>
<body class="shell-body">
    @yield('content')
    <div id="toast-region" class="toast-region" aria-live="polite"></div>

    <script>
    (() => {
        document.querySelectorAll('[data-password-toggle]').forEach(button => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            button.addEventListener('click', () => {
                const reveal = input.type === 'password';
                input.type = reveal ? 'text' : 'password';
                button.classList.toggle('is-visible', reveal);
                button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
                button.setAttribute('aria-pressed', reveal ? 'true' : 'false');
                input.focus();
            });
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
