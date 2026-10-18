<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle.' · ' : '' }}{{ config('app.name') }}</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2F6FED">
    <link rel="icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        @include('layouts.partials.sidebar')

        <div class="app-main">
            @include('layouts.partials.topbar', [
                'pageTitle' => $pageTitle ?? ($header ?? 'Dashboard'),
                'pageSubtitle' => $pageSubtitle ?? null,
            ])

            <main class="app-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div class="fab-container">
        {{ $fab ?? '' }}
    </div>

    @if (session('success') || session('error') || session('info'))
        <div id="server-flash"
             data-icon="{{ session('success') ? 'success' : (session('error') ? 'error' : 'info') }}"
             data-message="{{ session('success') ?? session('error') ?? session('info') }}"
             class="d-none"></div>
    @endif

    @stack('scripts')
</body>
</html>
