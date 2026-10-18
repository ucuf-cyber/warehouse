@props(['code', 'title', 'message', 'icon' => 'bi-exclamation-octagon'])

<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} — {{ $title }} · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="app-card text-center p-5" style="max-width: 460px;">
            <div class="mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                      style="width:72px;height:72px;background:rgba(239,68,68,.1);">
                    <i class="bi {{ $icon }} text-danger" style="font-size:2rem;"></i>
                </span>
            </div>
            <div class="fw-bold text-secondary-app mb-1">Error {{ $code }}</div>
            <h4 class="fw-bold mb-2">{{ $title }}</h4>
            <p class="text-secondary-app mb-4">{{ $message }}</p>
            <a href="{{ url('/') }}" class="btn btn-primary rounded-3 px-4">
                <i class="bi bi-house-door me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>
