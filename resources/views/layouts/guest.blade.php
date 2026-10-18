<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2F6FED">
    <link rel="icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="d-flex align-items-center justify-content-center p-3" style="min-height: 100vh; background: var(--bg-surface-muted);">
        <div style="width: 100%; max-width: 420px;">
            <div class="text-center mb-4">
                <span class="brand-mark d-inline-flex align-items-center justify-content-center mb-2" style="width:56px;height:56px;font-size:1.5rem;">B</span>
                <h5 class="fw-bold mb-0">Business Monitoring System</h5>
                <small class="text-secondary-app">One Dashboard for Inventory, Marketplace Sales &amp; Financial Monitoring</small>
            </div>

            <div class="app-card">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
