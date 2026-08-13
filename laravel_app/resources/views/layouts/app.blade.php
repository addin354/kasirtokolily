@php
    $userRole = auth()->check() ? auth()->user()->role : 'guest';
    $roleThemeClass = match($userRole) {
        'owner' => 'role-theme-owner',
        'admin' => 'role-theme-admin',
        'kasir' => 'role-theme-kasir',
        'pelanggan' => 'role-theme-pelanggan',
        default => 'role-theme-admin',
    };
    
    $roleBadgeIcon = match($userRole) {
        'owner' => 'bi-crown-fill',
        'admin' => 'bi-shield-lock-fill',
        'kasir' => 'bi-cart-check-fill',
        'pelanggan' => 'bi-person-fill',
        default => 'bi-person-badge-fill',
    };
    
    $roleLabel = auth()->check() ? auth()->user()->roleLabel() : 'Tamu';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'POS Kasir'))</title>

    <!-- Favicon / Logo Tab Browser -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}?v=2">
    <link rel="shortcut icon" type="image/png" href="{{ asset('logo.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}?v=2">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/app-mobile.css') }}?v=5" rel="stylesheet">
    <style>
        .navbar-dark .navbar-nav .nav-link.active { font-weight: 600; }

        /* ==========================================================================
           BULLETPROOF ROLE-BASED THEME HEADERS (OWNER, ADMIN, KASIR)
           ========================================================================== */

        /* 1. OWNER THEME: Royal Indigo / Deep Purple (Luxury Governance) */
        .role-theme-owner .app-header-role,
        header.app-header-role.role-theme-owner,
        header.app-navbar.role-theme-owner {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%) !important;
            background-color: #312e81 !important;
            border-bottom: 2.5px solid #4338ca !important;
            box-shadow: 0 4px 14px rgba(30, 27, 75, 0.25) !important;
        }

        body.role-theme-owner {
            --role-theme-primary: #312e81;
            --role-theme-subtle: #e0e7ff;
            --role-theme-text: #3730a3;
        }

        body.role-theme-owner .btn-primary {
            background-color: #4338ca !important;
            border-color: #3730a3 !important;
        }
        body.role-theme-owner .btn-primary:hover {
            background-color: #3730a3 !important;
        }

        .role-theme-owner #appMobileNav {
            background-color: #1e1b4b !important;
        }

        /* 2. ADMIN THEME: Corporate Ocean Blue (Management Control) */
        .role-theme-admin .app-header-role,
        header.app-header-role.role-theme-admin,
        header.app-navbar.role-theme-admin {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important;
            background-color: #0284c7 !important;
            border-bottom: 2.5px solid #38bdf8 !important;
            box-shadow: 0 4px 14px rgba(3, 105, 161, 0.25) !important;
        }

        body.role-theme-admin {
            --role-theme-primary: #0284c7;
            --role-theme-subtle: #e0f2fe;
            --role-theme-text: #0369a1;
        }

        body.role-theme-admin .btn-primary {
            background-color: #0284c7 !important;
            border-color: #0369a1 !important;
        }
        body.role-theme-admin .btn-primary:hover {
            background-color: #0369a1 !important;
        }

        .role-theme-admin #appMobileNav {
            background-color: #0369a1 !important;
        }

        /* 3. KASIR THEME: Energetic Emerald Green (POS Focus) */
        .role-theme-kasir .app-header-role,
        header.app-header-role.role-theme-kasir,
        header.app-navbar.role-theme-kasir {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%) !important;
            background-color: #047857 !important;
            border-bottom: 2.5px solid #10b981 !important;
            box-shadow: 0 4px 14px rgba(6, 95, 70, 0.25) !important;
        }

        body.role-theme-kasir {
            --role-theme-primary: #047857;
            --role-theme-subtle: #d1fae5;
            --role-theme-text: #065f46;
        }

        body.role-theme-kasir .btn-primary {
            background-color: #059669 !important;
            border-color: #047857 !important;
        }
        body.role-theme-kasir .btn-primary:hover {
            background-color: #047857 !important;
        }

        .role-theme-kasir #appMobileNav {
            background-color: #065f46 !important;
        }

        /* Header Text & Logo forced visibility on role background */
        .app-header-role .navbar-brand,
        .app-header-role .navbar-brand span,
        .app-header-role .nav-link,
        .app-header-role .navbar-toggler-icon {
            color: #ffffff !important;
        }

        /* Role Indicator Pill Badge in Navbar Top Right */
        .role-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.85rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.22) !important;
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            backdrop-filter: blur(8px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        /* Sidebar Role Card Banner */
        .sidebar-role-card {
            background: var(--role-theme-subtle);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 0.65rem;
            padding: 0.75rem 0.85rem;
            margin-bottom: 1rem;
            color: var(--role-theme-text);
        }

        .sidebar-role-card .role-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--role-theme-primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* Sidebar Active Nav Link Highlight */
        .app-sidebar-nav .nav-link.active {
            color: var(--role-theme-primary) !important;
            font-weight: 700 !important;
            border-left: 4px solid var(--role-theme-primary);
            padding-left: 0.6rem !important;
            background-color: var(--role-theme-subtle) !important;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        /* Vibrant Greeting Banners per Role */
        body.role-theme-owner .card-greeting-role {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%) !important;
            color: #ffffff !important;
        }

        body.role-theme-admin .card-greeting-role {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important;
            color: #ffffff !important;
        }

        body.role-theme-kasir .card-greeting-role {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%) !important;
            color: #ffffff !important;
        }

        .card-greeting-role h4,
        .card-greeting-role p,
        .card-greeting-role div {
            color: #ffffff !important;
        }

        .card-greeting-role .time-box-role {
            background: rgba(255, 255, 255, 0.18) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            backdrop-filter: blur(4px);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light min-vh-100 {{ $roleThemeClass }}">
<header class="navbar navbar-dark app-navbar app-header-role {{ $roleThemeClass }} shadow-sm sticky-top">
    <div class="container-fluid px-2 px-sm-3 app-navbar-bar">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center me-auto">
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

            @auth
                <div class="d-flex align-items-center gap-2">
                    <span class="role-badge-pill" title="Role Pengguna: {{ $roleLabel }}">
                        <i class="bi {{ $roleBadgeIcon }}"></i>
                        <span>{{ strtoupper($roleLabel) }}</span>
                    </span>
                </div>
            @endauth
        </div>
    </div>
</header>

<div class="collapse d-lg-none border-top border-white border-opacity-25 shadow-sm" id="appMobileNav">
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
                    <div class="sidebar-role-card d-flex align-items-center gap-2">
                        <div class="role-avatar shadow-sm">
                            <i class="bi {{ $roleBadgeIcon }}"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-bold text-truncate" style="font-size: 0.88rem;">{{ auth()->user()->name }}</div>
                            <div class="small opacity-75 text-truncate" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600;">
                                {{ $roleLabel }}
                            </div>
                        </div>
                    </div>
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
