@php
    $navMobile = $navMobile ?? false;
    $navSidebar = $navSidebar ?? false;
    $unreadReportsCount = \App\Models\Report::where('status', \App\Models\Report::STATUS_TERKIRIM)->count();
    $lowStockCount = \App\Models\Product::where('is_active', true)->whereColumn('stok', '<=', 'stok_minimum')->count();
@endphp
@auth
    @if (auth()->user()->isOwner())
        {{-- ===== MENU UTAMA ===== --}}
        <li class="nav-item mt-2">
            <span class="nav-link disabled fw-semibold text-uppercase px-0 py-1 {{ $navSidebar ? 'text-muted' : 'text-white-50' }}" style="font-size: 0.7rem; letter-spacing: 0.05em;">MENU UTAMA</span>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('owner.stok') ? 'active' : '' }} d-flex justify-content-between align-items-center" href="{{ route('owner.stok') }}">
                <span><i class="bi bi-boxes me-2"></i> Monitoring Stok</span>
                @if($lowStockCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill" title="{{ $lowStockCount }} produk perlu restok">{{ $lowStockCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('products.*', 'categories.*', 'satuans.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                <i class="bi bi-box-seam me-2"></i> Produk
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                <i class="bi bi-people me-2"></i> Supplier
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('stok-masuk.*') ? 'active' : '' }}" href="{{ route('stok-masuk.index') }}">
                <i class="bi bi-archive me-2"></i> Inventory
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('pembelian.*') ? 'active' : '' }}" href="{{ route('pembelian.index') }}">
                <i class="bi bi-cart4 me-2"></i> Pembelian Barang
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}" href="{{ route('pengeluaran.index') }}">
                <i class="bi bi-cash-stack me-2"></i> Pengeluaran
            </a>
        </li>

        {{-- ===== LAPORAN ===== --}}
        <li class="nav-item mt-3">
            <span class="nav-link disabled fw-semibold text-uppercase px-0 py-1 {{ $navSidebar ? 'text-muted' : 'text-white-50' }}" style="font-size: 0.7rem; letter-spacing: 0.05em;">LAPORAN</span>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }} d-flex justify-content-between align-items-center" href="{{ route('reports.index') }}">
                <span><i class="bi bi-journal-text me-2"></i> Laporan Kasir</span>
                @if($unreadReportsCount > 0)
                    <span class="badge bg-danger rounded-pill">{{ $unreadReportsCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('laporan.penjualan') || request()->routeIs('laporan.export.pdf') ? 'active' : '' }}" href="{{ route('laporan.penjualan') }}">
                <i class="bi bi-bar-chart-line me-2"></i> Laporan Penjualan
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('laporan.laba') || request()->routeIs('laporan.laba.export.pdf') ? 'active' : '' }}" href="{{ route('laporan.laba') }}">
                <i class="bi bi-graph-up me-2"></i> Laporan Laba Rugi
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('laporan.retur') ? 'active' : '' }}" href="{{ route('laporan.retur') }}">
                <i class="bi bi-arrow-counterclockwise me-2"></i> Laporan Retur
            </a>
        </li>
        <li class="nav-item">
            @php
                $isOwnerReportActive = request()->routeIs('owner.reports');
                $collapseOwnerReportId = 'collapseLaporanProduk_owner_' . ($navSidebar ? 'sb' : ($navMobile ? 'mb' : 'gen'));
            @endphp
            <a class="nav-link {{ $isOwnerReportActive ? 'active' : '' }} d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" 
               href="#{{ $collapseOwnerReportId }}" 
               role="button" 
               aria-expanded="{{ $isOwnerReportActive ? 'true' : 'false' }}" 
               aria-controls="{{ $collapseOwnerReportId }}">
                <span><i class="bi bi-box-seam me-2"></i> Laporan Produk</span>
                <div class="d-flex align-items-center gap-1">
                    @if($lowStockCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill" title="{{ $lowStockCount }} produk perlu restok">{{ $lowStockCount }}</span>
                    @endif
                    <i class="bi bi-chevron-down small"></i>
                </div>
            </a>
            <div class="collapse {{ $isOwnerReportActive ? 'show' : '' }} ps-2" id="{{ $collapseOwnerReportId }}">
                <ul class="nav flex-column gap-1 pt-1 border-start border-light border-opacity-25 ms-2 ps-2">
                    <li class="nav-item">
                        <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link py-1 px-2 small {{ request()->routeIs('owner.reports') && request('report_type', 'terlaris') === 'terlaris' ? 'fw-bold text-primary active' : '' }}" href="{{ route('owner.reports', ['report_type' => 'terlaris']) }}">
                            <i class="bi bi-trophy me-2"></i> 10 Produk Terlaris
                        </a>
                    </li>
                    <li class="nav-item">
                        <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link py-1 px-2 small {{ request()->routeIs('owner.reports') && request('report_type') === 'persediaan' ? 'fw-bold text-primary active' : '' }}" href="{{ route('owner.reports', ['report_type' => 'persediaan']) }}">
                            <i class="bi bi-boxes me-2"></i> Persediaan Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link py-1 px-2 small d-flex justify-content-between align-items-center {{ request()->routeIs('owner.reports') && request('report_type') === 'restok' ? 'fw-bold text-primary active' : '' }}" href="{{ route('owner.reports', ['report_type' => 'restok']) }}">
                            <span><i class="bi bi-exclamation-triangle me-2"></i> Produk Perlu Restok</span>
                            @if($lowStockCount > 0)
                                <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.7rem;">{{ $lowStockCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link py-1 px-2 small {{ request()->routeIs('owner.reports') && request('report_type') === 'produk' ? 'fw-bold text-primary active' : '' }}" href="{{ route('owner.reports', ['report_type' => 'produk']) }}">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i> Daftar Semua Produk
                        </a>
                    </li>
                </ul>
            </div>
        </li>


    @elseif (auth()->user()->isAdmin())
        {{-- ===== MASTER DATA ===== --}}
        <li class="nav-item mt-2">
            <span class="nav-link disabled fw-semibold text-uppercase px-0 py-1 {{ $navSidebar ? 'text-muted' : 'text-white-50' }}" style="font-size: 0.7rem; letter-spacing: 0.05em;">MASTER DATA</span>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('products.*', 'categories.*', 'satuans.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                <i class="bi bi-box-seam me-2"></i> Produk
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                <i class="bi bi-people me-2"></i> Supplier
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}" href="{{ route('pelanggan.index') }}">
                <i class="bi bi-person-vcard me-2"></i> Pelanggan
            </a>
        </li>

        {{-- ===== STOK & TRANSAKSI ===== --}}
        <li class="nav-item mt-3">
            <span class="nav-link disabled fw-semibold text-uppercase px-0 py-1 {{ $navSidebar ? 'text-muted' : 'text-white-50' }}" style="font-size: 0.7rem; letter-spacing: 0.05em;">STOK & TRANSAKSI</span>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('stok-masuk.*') ? 'active' : '' }}" href="{{ route('stok-masuk.index') }}">
                <i class="bi bi-archive me-2"></i> Inventory
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('pembelian.*') ? 'active' : '' }}" href="{{ route('pembelian.index') }}">
                <i class="bi bi-cart4 me-2"></i> Pembelian Barang
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}" href="{{ route('pengeluaran.index') }}">
                <i class="bi bi-cash-stack me-2"></i> Pengeluaran
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('owner.stok') ? 'active' : '' }} d-flex justify-content-between align-items-center" href="{{ route('owner.stok') }}">
                <span><i class="bi bi-boxes me-2"></i> Monitoring Stok</span>
                @if($lowStockCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill" title="{{ $lowStockCount }} produk perlu restok">{{ $lowStockCount }}</span>
                @endif
            </a>
        </li>

        {{-- ===== LAPORAN ===== --}}
        <li class="nav-item mt-3">
            <span class="nav-link disabled fw-semibold text-uppercase px-0 py-1 {{ $navSidebar ? 'text-muted' : 'text-white-50' }}" style="font-size: 0.7rem; letter-spacing: 0.05em;">LAPORAN</span>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('laporan.penjualan') || request()->routeIs('laporan.export.pdf') ? 'active' : '' }}" href="{{ route('laporan.penjualan') }}">
                <i class="bi bi-bar-chart-line me-2"></i> Laporan Penjualan
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }} d-flex justify-content-between align-items-center" href="{{ route('reports.index') }}">
                <span><i class="bi bi-journal-text me-2"></i> Laporan Kasir</span>
                @if($unreadReportsCount > 0)
                    <span class="badge bg-danger rounded-pill">{{ $unreadReportsCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('laporan.laba') || request()->routeIs('laporan.laba.export.pdf') ? 'active' : '' }}" href="{{ route('laporan.laba') }}">
                <i class="bi bi-graph-up me-2"></i> Laporan Laba Rugi
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('laporan.retur') ? 'active' : '' }}" href="{{ route('laporan.retur') }}">
                <i class="bi bi-arrow-counterclockwise me-2"></i> Laporan Retur
            </a>
        </li>
        <li class="nav-item">
            @php
                $isAdminReportActive = request()->routeIs('owner.reports');
                $collapseAdminReportId = 'collapseLaporanProduk_admin_' . ($navSidebar ? 'sb' : ($navMobile ? 'mb' : 'gen'));
            @endphp
            <a class="nav-link {{ $isAdminReportActive ? 'active' : '' }} d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" 
               href="#{{ $collapseAdminReportId }}" 
               role="button" 
               aria-expanded="{{ $isAdminReportActive ? 'true' : 'false' }}" 
               aria-controls="{{ $collapseAdminReportId }}">
                <span><i class="bi bi-box-seam me-2"></i> Laporan Produk</span>
                <div class="d-flex align-items-center gap-1">
                    @if($lowStockCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill" title="{{ $lowStockCount }} produk perlu restok">{{ $lowStockCount }}</span>
                    @endif
                    <i class="bi bi-chevron-down small"></i>
                </div>
            </a>
            <div class="collapse {{ $isAdminReportActive ? 'show' : '' }} ps-2" id="{{ $collapseAdminReportId }}">
                <ul class="nav flex-column gap-1 pt-1 border-start border-light border-opacity-25 ms-2 ps-2">
                    <li class="nav-item">
                        <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link py-1 px-2 small {{ request()->routeIs('owner.reports') && request('report_type', 'terlaris') === 'terlaris' ? 'fw-bold text-primary active' : '' }}" href="{{ route('owner.reports', ['report_type' => 'terlaris']) }}">
                            <i class="bi bi-trophy me-2"></i> 10 Produk Terlaris
                        </a>
                    </li>
                    <li class="nav-item">
                        <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link py-1 px-2 small {{ request()->routeIs('owner.reports') && request('report_type') === 'persediaan' ? 'fw-bold text-primary active' : '' }}" href="{{ route('owner.reports', ['report_type' => 'persediaan']) }}">
                            <i class="bi bi-boxes me-2"></i> Persediaan Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link py-1 px-2 small d-flex justify-content-between align-items-center {{ request()->routeIs('owner.reports') && request('report_type') === 'restok' ? 'fw-bold text-primary active' : '' }}" href="{{ route('owner.reports', ['report_type' => 'restok']) }}">
                            <span><i class="bi bi-exclamation-triangle me-2"></i> Produk Perlu Restok</span>
                            @if($lowStockCount > 0)
                                <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.7rem;">{{ $lowStockCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link py-1 px-2 small {{ request()->routeIs('owner.reports') && request('report_type') === 'produk' ? 'fw-bold text-primary active' : '' }}" href="{{ route('owner.reports', ['report_type' => 'produk']) }}">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i> Daftar Semua Produk
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- ===== PENGATURAN ===== --}}
        <li class="nav-item mt-3">
            <span class="nav-link disabled fw-semibold text-uppercase px-0 py-1 {{ $navSidebar ? 'text-muted' : 'text-white-50' }}" style="font-size: 0.7rem; letter-spacing: 0.05em;">PENGATURAN</span>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <i class="bi bi-shield-lock me-2"></i> Manajemen User
            </a>
        </li>


    @elseif (auth()->user()->isKasir())
        <!-- MENU UTAMA -->
        <li class="nav-item mt-2">
            <span class="nav-link disabled fw-semibold text-uppercase px-0 py-1 {{ $navSidebar ? 'text-muted' : 'text-white-50' }}" style="font-size: 0.7rem; letter-spacing: 0.05em;">MENU UTAMA</span>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('kasir.*') ? 'active' : '' }}" href="{{ route('kasir.index') }}">
                <i class="bi bi-cart me-2"></i> Penjualan
            </a>
        </li>
        <!-- LAPORAN -->
        <li class="nav-item mt-3">
            <span class="nav-link disabled fw-semibold text-uppercase px-0 py-1 {{ $navSidebar ? 'text-muted' : 'text-white-50' }}" style="font-size: 0.7rem; letter-spacing: 0.05em;">LAPORAN</span>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="bi bi-journal-text me-2"></i> Laporan ke Owner
            </a>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('laporan.retur') ? 'active' : '' }}" href="{{ route('laporan.retur') }}">
                <i class="bi bi-arrow-counterclockwise me-2"></i> Laporan Retur
            </a>
        </li>

    @elseif (auth()->user()->isPelanggan())
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link {{ request()->routeIs('katalog.*') ? 'active' : '' }}" href="{{ route('katalog.index') }}">Katalog produk</a>
        </li>
    @endif

    @if (!auth()->user()->isKasir() && !auth()->user()->isOwner() && !auth()->user()->isAdmin())
        <li class="nav-item">
            <span class="nav-link small {{ $navMobile ? 'text-white-50' : '' }} {{ $navSidebar ? 'text-muted px-0' : 'py-lg-2 text-white-50' }}">{{ auth()->user()->name }} · {{ auth()->user()->roleLabel() }}</span>
        </li>
        <li class="nav-item">
            <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link" href="{{ route('profile.edit') }}">Profil</a>
        </li>
        <li class="nav-item @if($navMobile) w-100 @endif">
            <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                @csrf
                <button type="submit" class="btn {{ $navSidebar ? 'btn-outline-secondary w-100 mt-1' : 'btn-outline-light' }} {{ $navMobile ? 'w-100 btn-lg-touch' : ($navSidebar ? '' : 'btn-sm ms-lg-2') }}">Keluar</button>
            </form>
        </li>
    @endif
@else
    <li class="nav-item">
        <a @if($navMobile) data-bs-dismiss="offcanvas" @endif class="nav-link" href="{{ route('login') }}">Masuk</a>
    </li>
@endauth
