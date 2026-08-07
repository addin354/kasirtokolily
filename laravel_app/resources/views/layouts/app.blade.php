<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'POS Kasir'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/app-mobile.css') }}?v=2" rel="stylesheet">
    <style>
        .navbar-dark .navbar-nav .nav-link.active { font-weight: 600; }
    </style>
    @stack('styles')
</head>
<body class="bg-light min-vh-100">
<header class="navbar navbar-dark bg-primary shadow-sm sticky-top app-navbar">
    <div class="container-fluid px-2 px-sm-3 app-navbar-bar">
        <div class="d-flex align-items-center w-100">
            <button
                class="navbar-toggler d-lg-none border-0 rounded-2 p-2 me-1 app-hamburger"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#appMobileNav"
                aria-controls="appMobileNav"
                aria-label="Buka menu"
            >
                <span class="navbar-toggler-icon" aria-hidden="true"></span>
            </button>
            <a class="navbar-brand d-flex align-items-center fw-bold me-0 text-truncate" style="max-width: min(22rem, 75vw);" href="{{ auth()->check() ? route(auth()->user()->defaultDashboardRoute()) : url('/') }}">
                <img src="{{ asset('logo.png') }}" alt="Logo Toko Lily Sembako" width="42" height="42" class="me-2 rounded-circle flex-shrink-0 bg-white p-1 shadow-sm" style="object-fit: cover;" loading="lazy" decoding="async">
                <span class="text-truncate fs-5">Toko Lily Sembako</span>
            </a>
        </div>
    </div>
</header>

<div class="collapse d-lg-none bg-primary border-top border-white border-opacity-25 shadow-sm" id="appMobileNav">
    <div class="px-2 px-sm-3 py-2">
        <div class="small text-uppercase text-white-50 fw-semibold mb-2">Menu</div>
        <ul class="navbar-nav flex-column gap-1 w-100 app-offcanvas-nav">
            @include('layouts.partials.nav-items', ['navMobile' => false, 'navSidebar' => false])
        </ul>
    </div>
</div>

<div class="container-fluid">
    <div class="row g-0">
        @auth
            <aside class="d-none d-lg-flex col-lg-3 col-xl-2 bg-white border-end min-vh-100">
                <div class="w-100 p-3">
                    <div class="small text-uppercase text-muted fw-semibold mb-2">Navigasi</div>
                    <ul class="navbar-nav app-sidebar-nav">
                        @include('layouts.partials.nav-items', ['navMobile' => false, 'navSidebar' => true])
                    </ul>
                </div>
            </aside>
        @endauth

        <div class="@auth col-12 col-lg-9 col-xl-10 @else col-12 @endauth d-flex flex-column min-vh-100">
            <main class="{{ $wrapperClass ?? 'container-fluid py-3 py-md-4 px-2 px-sm-3 flex-grow-1' }}">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="py-3 mt-auto bg-white border-top text-center text-muted small">
                <div class="container-fluid">
                    &copy; {{ now()->year }} {{ config('app.name', 'POS Kasir') }}
                </div>
            </footer>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('#appMobileNav .nav-link').forEach((el) => {
        el.addEventListener('click', () => {
            const menu = document.getElementById('appMobileNav');
            if (!menu || !menu.classList.contains('show')) return;
            bootstrap.Collapse.getOrCreateInstance(menu).hide();
        });
    });
</script>
@stack('scripts')
</body>
</html>
