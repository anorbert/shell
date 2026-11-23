<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shell') }}</title>
    @vite(['resources/css/app.css', 'resources/js/site.js'])
</head>
<body class="shell-body">
    @yield('content')
    <div id="toast-region" class="toast-region" aria-live="polite"></div>
</body>
</html>
