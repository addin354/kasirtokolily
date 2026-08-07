<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'POS Kasir') }} — Lily Sembako</title>

    <!-- Favicon / Logo Tab Browser -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}?v=2">
    <link rel="shortcut icon" type="image/png" href="{{ asset('logo.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}?v=2">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-4 px-3">
        <div class="w-100" style="max-width: 420px;">
            <div class="text-center mb-4 d-flex flex-column align-items-center justify-content-center">
                <img
                    src="{{ asset('logo.png') }}"
                    alt="Logo Lily Sembako"
                    class="rounded-circle shadow-sm mx-auto d-block mb-3"
                    width="100"
                    height="100"
                    style="object-fit: cover; display: block;"
                >
                <h1 class="h3 fw-semibold text-dark mb-1 text-center">Lily Sembako</h1>
                <p class="text-muted small mb-0 text-center">{{ config('app.name', 'POS Kasir') }}</p>
            </div>

            <div class="card shadow border-0">
                <div class="card-body p-4 p-md-5">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
