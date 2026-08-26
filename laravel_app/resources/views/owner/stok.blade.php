@extends('layouts.app')

@section('title', (auth()->user()->isOwner() ? 'Dashboard Owner' : 'Monitoring Stok') . ' — ' . config('app.name'))

@push('styles')
<style>
    .card-kpi {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
    }
    .card-kpi:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .kpi-icon {
        font-size: 1.8rem;
        opacity: 0.85;
    }
    .chart-container {
        position: relative;
        height: 250px;
        width: 100%;
    }
    .nav-pills-custom .nav-link {
        font-size: 0.85rem;
        font-weight: 500;
        color: #555;
        padding: 0.5rem 1rem;
        border-radius: 6px;
    }
    .nav-pills-custom .nav-link.active {
        background-color: var(--bs-primary);
        color: white;
    }
</style>
@endpush

@section('content')
    <!-- Top Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0 fw-bold">{{ auth()->user()->isOwner() ? 'Dashboard Owner' : 'Monitoring Stok' }}</h1>
            <p class="text-muted small mb-0">Analisis kinerja finansial, volume persediaan barang, dan aktivitas perubahan stok secara real-time.</p>
        </div>
        <div>
            @if(auth()->user()->isOwner())
            <span class="badge bg-primary px-3 py-2 fs-7 shadow-sm">
                <i class="bi bi-shield-check me-1"></i> Mode Owner (Hanya Monitoring)
            </span>
            @endif
        </div>
    </div>

    <!-- Warnings / Alerts Section -->
    <div class="row g-3 mb-4">
        @if ($countNegatif > 0)
            <div class="col-12 col-md-6">
                <div class="alert alert-danger d-flex align-items-center mb-0 shadow-sm border-0" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-4 me-2"></i>
                    <div>
                        <span class="fw-bold">Peringatan:</span> Terdapat <strong>{{ $countNegatif }}</strong> produk dengan stok negatif!
                    </div>
                </div>
            </div>
        @endif
        @if ($countRestok > 0)
            <div class="col-12 @if($countNegatif > 0) col-md-6 @else col-12 @endif">
                <div class="alert alert-warning border-0 shadow-sm mb-0 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold"><i class="bi bi-bell-fill text-warning me-1"></i> Notifikasi Produk Perlu Restok <span class="badge bg-warning text-dark">{{ $countRestok }}</span></span>
                        <a href="{{ route('owner.reports', ['report_type' => 'restok']) }}" class="btn btn-warning btn-sm text-dark fw-bold py-0 px-2" style="font-size: 0.8rem;">Lihat Semua Produk Perlu Restok</a>
                    </div>
                    <div style="max-height: 120px; overflow-y: auto;" class="pe-2">
                        <ul class="list-unstyled mb-0 small">
                            @foreach($restokProducts as $rp)
                                <li class="mb-1 py-1 border-bottom border-light">
                                    <a href="{{ route('pembelian.create', ['produk_id' => $rp->id]) }}" class="text-decoration-none text-dark d-block">
                                        <span class="badge bg-warning text-dark me-1"><i class="bi bi-cart-plus"></i> Restok</span>
                                        ⚠ <strong>{{ $rp->nama }}</strong> tinggal <strong>{{ (int) $rp->stok }}</strong> {{ $rp->satuanModel?->nama ?? 'pcs' }}. (Minimum stok: {{ (int) $rp->stok_minimum }}, Supplier: {{ $rp->supplierTerakhir() ?? '—' }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- KPI Analisis Grid (8 Cards) -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-4">
        
        <!-- Total Produk -->
        <div class="col">
            <div class="card card-kpi h-100 shadow-sm bg-white border-start border-primary border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Produk</div>
                        <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $totalProducts }}</h3>
                    </div>
                    <div class="kpi-icon text-primary"><i class="bi bi-box-seam"></i></div>
                </div>
            </div>
        </div>

        <!-- Total Supplier -->
        <div class="col">
            <div class="card card-kpi h-100 shadow-sm bg-white border-start border-info border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Supplier</div>
                        <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $totalSuppliers }}</h3>
                    </div>
                    <div class="kpi-icon text-info"><i class="bi bi-truck"></i></div>
                </div>
            </div>
        </div>

        <!-- Nilai Persediaan -->
        <div class="col">
            <div class="card card-kpi h-100 shadow-sm bg-white border-start border-success border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Nilai Persediaan</div>
                        <h3 class="mb-0 fw-bold mt-1 text-success">Rp {{ number_format($nilaiPersediaan, 0, ',', '.') }}</h3>
                    </div>
                    <div class="kpi-icon text-success"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>

        <!-- Produk Aman -->
        <div class="col">
            <div class="card card-kpi h-100 shadow-sm bg-white border-start border-success border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">🟢 Produk Aman</div>
                        <h3 class="mb-0 fw-bold mt-1 text-success">{{ $barangAman }}</h3>
                    </div>
                    <div class="kpi-icon text-success"><i class="bi bi-shield-check"></i></div>
                </div>
            </div>
        </div>

        <!-- Produk Perlu Restok -->
        <div class="col">
            <div class="card card-kpi h-100 shadow-sm bg-white border-start border-warning border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">🟡 Perlu Restok</div>
                        <h3 class="mb-0 fw-bold mt-1 text-warning">{{ $barangRestok }}</h3>
                    </div>
                    <div class="kpi-icon text-warning"><i class="bi bi-shield-exclamation"></i></div>
                </div>
            </div>
        </div>

        <!-- Total Penjualan -->
        <div class="col">
            <div class="card card-kpi h-100 shadow-sm bg-white border-start border-primary border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Penjualan</div>
                        <h3 class="mb-0 fw-bold mt-1 text-primary">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h3>
                    </div>
                    <div class="kpi-icon text-primary"><i class="bi bi-graph-up-arrow"></i></div>
                </div>
            </div>
        </div>

        <!-- Total Pembelian -->
        <div class="col">
            <div class="card card-kpi h-100 shadow-sm bg-white border-start border-secondary border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Pembelian</div>
                        <h3 class="mb-0 fw-bold mt-1 text-secondary">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</h3>
                    </div>
                    <div class="kpi-icon text-secondary"><i class="bi bi-cart-check"></i></div>
                </div>
            </div>
        </div>

        <!-- Total Laba -->
        <div class="col">
            <div class="card card-kpi h-100 shadow-sm bg-white border-start border-success border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Laba</div>
                        <h3 class="mb-0 fw-bold mt-1 text-success">Rp {{ number_format($totalLaba, 0, ',', '.') }}</h3>
                    </div>
                    <div class="kpi-icon text-success"><i class="bi bi-wallet2"></i></div>
                </div>
            </div>
        </div>

    </div>

    <!-- Statistik Metode Pembayaran (New Section) -->
    <div class="card shadow-sm border-0 mb-4 bg-light bg-opacity-50">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-wallet2 text-primary me-2"></i>Komposisi Transaksi Berdasarkan Metode Pembayaran
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Cash -->
                <div class="col-12 col-md-4">
                    <div class="card border border-light-subtle h-100 bg-white shadow-none">
                        <div class="card-body d-flex align-items-center justify-content-between py-3">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase"><i class="bi bi-cash text-success me-1"></i> Cash</div>
                                <h4 class="mb-0 fw-bold mt-1 text-success">Rp {{ number_format($statsCashAmount, 0, ',', '.') }}</h4>
                                <small class="text-secondary">{{ $statsCashCount }} Transaksi</small>
                            </div>
                            <div class="fs-4 text-success opacity-75"><i class="bi bi-wallet2"></i></div>
                        </div>
                    </div>
                </div>
                <!-- Transfer Bank -->
                <div class="col-12 col-md-4">
                    <div class="card border border-light-subtle h-100 bg-white shadow-none">
                        <div class="card-body d-flex align-items-center justify-content-between py-3">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase"><i class="bi bi-bank text-primary me-1"></i> Transfer Bank</div>
                                <h4 class="mb-0 fw-bold mt-1 text-primary">Rp {{ number_format($statsTransferAmount, 0, ',', '.') }}</h4>
                                <small class="text-secondary">{{ $statsTransferCount }} Transaksi</small>
                            </div>
                            <div class="fs-4 text-primary opacity-75"><i class="bi bi-credit-card"></i></div>
                        </div>
                    </div>
                </div>
                <!-- QRIS -->
                <div class="col-12 col-md-4">
                    <div class="card border border-light-subtle h-100 bg-white shadow-none">
                        <div class="card-body d-flex align-items-center justify-content-between py-3">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase"><i class="bi bi-qr-code-scan text-info me-1"></i> QRIS</div>
                                <h4 class="mb-0 fw-bold mt-1 text-info">Rp {{ number_format($statsQrisAmount, 0, ',', '.') }}</h4>
                                <small class="text-secondary">{{ $statsQrisCount }} Transaksi</small>
                            </div>
                            <div class="fs-4 text-info opacity-75"><i class="bi bi-qr-code"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Negatif Stock Alert Box -->
    @if($countNegatif > 0)
        <div class="card shadow-sm border-0 mb-4 bg-danger bg-opacity-10 text-danger border-start border-danger border-3">
            <div class="card-body">
                <h5 class="fw-bold mb-2 small text-uppercase"><i class="bi bi-exclamation-triangle-fill me-1"></i> Daftar Produk dengan Stok Negatif</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0 text-danger small">
                        <thead>
                            <tr class="border-bottom border-danger border-opacity-25">
                                <th>Barcode</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th class="text-end">Stok Sistem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($negatifProducts as $np)
                                <tr>
                                    <td><code>{{ $np->barcode }}</code></td>
                                    <td class="fw-semibold">{{ $np->nama }}</td>
                                    <td>{{ $np->category?->nama ?? '—' }}</td>
                                    <td class="text-end fw-bold">{{ (int) $np->stok }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif



    <!-- Tables Section (TABS) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white p-3 border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-bold"><i class="bi bi-table me-1"></i> Analisis Detil &amp; Aktivitas Persediaan</span>
                <ul class="nav nav-pills nav-pills-custom border-0" id="analysisTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-terlaris-btn" data-bs-toggle="pill" data-bs-target="#tab-terlaris" type="button" role="tab">Top 10 Produk Terlaris</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-tidaklaku-btn" data-bs-toggle="pill" data-bs-target="#tab-tidaklaku" type="button" role="tab">Top 10 Tidak Laku</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-restok-btn" data-bs-toggle="pill" data-bs-target="#tab-restok" type="button" role="tab">Produk Perlu Restok</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-aktivitas-btn" data-bs-toggle="pill" data-bs-target="#tab-aktivitas" type="button" role="tab">Aktivitas Terbaru</button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="analysisTabsContent">
                
                <!-- 1. Top 10 Terlaris -->
                <div class="tab-pane fade show active" id="tab-terlaris" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 60px;">No</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th class="text-end">Qty Terjual</th>
                                    <th class="text-end pe-3">Total Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $idx => $tp)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $idx + 1 }}</td>
                                        <td class="fw-semibold text-dark">{{ $tp->product?->nama ?? ('#' . $tp->produk_id) }}</td>
                                        <td>{{ $tp->product?->category?->nama ?? '—' }}</td>
                                        <td class="text-end fw-bold text-success">{{ number_format($tp->total_qty, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-dark pe-3">Rp {{ number_format($tp->total_subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat penjualan penjualan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Top 10 Tidak Laku -->
                <div class="tab-pane fade" id="tab-tidaklaku" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 60px;">No</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th class="text-end">Stok Sekarang</th>
                                    <th class="text-end pe-3">Total Terjual (Pcs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bottomProducts as $idx => $bp)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $idx + 1 }}</td>
                                        <td class="fw-semibold text-dark">{{ $bp->nama }}</td>
                                        <td>{{ $bp->category?->nama ?? '—' }}</td>
                                        <td class="text-end fw-bold text-dark">{{ (int) $bp->stok }}</td>
                                        <td class="text-end fw-bold text-muted pe-3">{{ (int) $bp->total_qty }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data penjualan produk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Produk Perlu Restok -->
                <div class="tab-pane fade" id="tab-restok" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Barcode</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th class="text-end">Min Stok</th>
                                    <th class="text-end pe-3 text-warning">Stok Sekarang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($restokTableProducts as $rp)
                                    <tr>
                                        <td class="ps-3"><code>{{ $rp->barcode }}</code></td>
                                        <td class="fw-semibold text-dark">{{ $rp->nama }}</td>
                                        <td>{{ $rp->category?->nama ?? '—' }}</td>
                                        <td class="text-end text-muted">{{ (int) $rp->stok_minimum }}</td>
                                        <td class="text-end fw-bold text-warning pe-3">{{ (int) $rp->stok }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Aman! Tidak ada produk yang memerlukan restok.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 5. Aktivitas Terbaru -->
                <div class="tab-pane fade" id="tab-aktivitas" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Waktu</th>
                                    <th>Produk</th>
                                    <th>Jenis</th>
                                    <th class="text-end text-success">Masuk</th>
                                    <th class="text-end text-danger">Keluar</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Referensi</th>
                                    <th class="pe-3">User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $act)
                                    <tr>
                                        <td class="ps-3 text-secondary">{{ $act->tanggal->format('d/m/Y H:i') }}</td>
                                        <td class="fw-semibold text-dark">{{ $act->product?->nama ?? '—' }}</td>
                                        <td>
                                            <span class="badge @if($act->jenis === 'Pembelian') bg-primary-subtle text-primary-emphasis @elseif($act->jenis === 'Penjualan') bg-success-subtle text-success-emphasis @elseif($act->jenis === 'Stok Masuk') bg-info-subtle text-info-emphasis @else bg-warning-subtle text-warning-emphasis @endif">
                                                @if($act->jenis === 'Pembelian')
                                                    Pembelian Barang
                                                @elseif($act->jenis === 'Retur')
                                                    Retur Penjualan
                                                @elseif($act->jenis === 'Penyesuaian')
                                                    Penyesuaian Stok
                                                @else
                                                    {{ $act->jenis }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-end text-success fw-bold">{{ $act->masuk > 0 ? '+' . $act->masuk : '—' }}</td>
                                        <td class="text-end text-danger fw-bold">{{ $act->keluar > 0 ? '-' . $act->keluar : '—' }}</td>
                                        <td class="text-end fw-bold text-dark">{{ $act->saldo }}</td>
                                        <td class="font-monospace text-muted">{{ $act->referensi ?? '—' }}</td>
                                        <td class="pe-3">{{ $act->user?->name ?? 'System' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Belum ada aktivitas persediaan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

<!-- Scripts removed since charts were replaced by Report Inventory Card -->
