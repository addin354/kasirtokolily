@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Dashboard</h1>
            <p class="text-muted small mb-0">Ringkasan per <strong>{{ now()->format('d/m/Y') }}</strong></p>
        </div>
    </div>

    <!-- Main Statistics Row -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-secondary shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Total produk</div>
                    <div class="fs-3 fw-bold">{{ number_format($totalProduk, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Transaksi hari ini</div>
                    <div class="fs-3 fw-bold">{{ number_format($totalTransaksiHariIni, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Pendapatan hari ini</div>
                    <div class="fs-3 fw-bold text-success">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-info shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Laba hari ini</div>
                    <div class="fs-3 fw-bold text-info">Rp {{ number_format($labaHariIni, 0, ',', '.') }}</div>
                    <div class="small text-muted">(harga jual − harga beli) × qty</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Status Row (Preventive) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-success shadow-sm h-100 border-start border-4">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Produk Aman</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($countAman, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-success fs-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-warning shadow-sm h-100 border-start border-4">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Produk Perlu Restok</div>
                        <div class="fs-4 fw-bold text-warning">{{ number_format($countRestok, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-warning fs-3">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Penjualan per hari (30 hari terakhir)</div>
        <div class="card-body">
            <div style="max-height: 380px;">
                <canvas id="chartPenjualan"></canvas>
            </div>
        </div>
    </div>

    <!-- Notification & Warning Panel -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning-subtle text-warning-emphasis d-flex justify-content-between align-items-center py-3 border-0">
            <span class="fw-semibold">
                Daftar Notifikasi Stok
                <span class="badge bg-warning text-warning-emphasis ms-2">{{ $countRestok }}</span>
            </span>
            <a href="{{ route('owner.stok.restok') }}" class="btn btn-warning btn-sm">Lihat Semua Barang Perlu Restok</a>
        </div>
        <div class="card-body">
            <div class="vstack gap-2">
                @forelse($restokProducts as $p)
                    <div class="p-3 rounded border border-warning-subtle bg-warning-subtle bg-opacity-25 text-warning-emphasis small d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>{{ $p->nama }}</strong> tinggal {{ (int) $p->stok }} {{ $p->satuanModel?->nama ?? $p->satuan ?? 'pcs' }} (Minimum {{ (int) $p->stok_minimum }})
                        </div>
                        <span class="badge bg-warning text-warning-emphasis">Perlu Restok</span>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-check-circle text-success me-1"></i>
                        Semua produk dalam kondisi aman.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartPenjualan');
    if (!ctx || typeof Chart === 'undefined') return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($chartValues),
                borderColor: 'rgb(13, 110, 253)',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return new Intl.NumberFormat('id-ID').format(value);
                        },
                    },
                },
            },
            plugins: {
                legend: { display: true },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const v = ctx.raw;
                            return ' Rp ' + new Intl.NumberFormat('id-ID').format(v);
                        },
                    },
                },
            },
        },
    });
});
</script>
@endpush
