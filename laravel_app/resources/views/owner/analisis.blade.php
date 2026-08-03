@extends('layouts.app')

@section('title', 'Analisis penjualan — ' . config('app.name'))

@push('styles')
<style>
    .analytic-card { min-height: 4.5rem; }
    canvas { max-height: 320px; }
</style>
@endpush

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Analisis penjualan</h1>
            <p class="text-muted small mb-0">Periode: {{ $dariTanggal }} s/d {{ $sampaiTanggal }} ({{ $periodeHari }} hari)</p>
        </div>
        <form method="get" action="{{ route('owner.analisis') }}" class="d-flex flex-wrap align-items-center gap-2">
            <label class="small text-muted mb-0" for="periode">Jangka</label>
            <select class="form-select form-select-sm" name="periode" id="periode" onchange="this.form.submit()">
                @foreach ([7, 30, 90, 180] as $h)
                    <option value="{{ $h }}" @selected($periodeHari === $h)>{{ $h }} hari</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm analytic-card">
                <div class="card-body">
                    <div class="small text-uppercase text-muted">Hari terlaris</div>
                    <div class="fs-4 fw-semibold">{{ $hariTerlarisLabel }}</div>
                    @if ($hariTerlarisNilai > 0)
                        <div class="small text-muted">Total penjualan: Rp {{ number_format($hariTerlarisNilai, 0, ',', '.') }}</div>
                    @else
                        <div class="small text-muted">Belum ada data</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm analytic-card">
                <div class="card-body">
                    <div class="small text-uppercase text-muted">Jam ramai</div>
                    <div class="fs-4 fw-semibold">{{ $jamRamaiLabel }}</div>
                    @if ($jamRamaiJumlah > 0)
                        <div class="small text-muted">{{ $jamRamaiJumlah }} transaksi</div>
                    @else
                        <div class="small text-muted">Belum ada data</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm analytic-card">
                <div class="card-body">
                    <div class="small text-uppercase text-muted">Produk terlaris</div>
                    <div class="fs-6 fw-semibold text-truncate" title="{{ $produkTerlarisNama }}">{{ $produkTerlarisNama }}</div>
                    @if ($produkTerlarisQty > 0)
                        <div class="small text-muted">{{ number_format($produkTerlarisQty, 0, ',', '.') }} unit terjual</div>
                    @else
                        <div class="small text-muted">Belum ada data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <span class="fw-semibold">Penjualan per hari (dalam minggu)</span>
                </div>
                <div class="card-body">
                    <canvas id="chartHari" aria-label="Grafik penjualan per hari" role="img"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <span class="fw-semibold">Jumlah transaksi per jam</span>
                </div>
                <div class="card-body">
                    <canvas id="chartJam" aria-label="Grafik transaksi per jam" role="img"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <span class="fw-semibold">10 produk terlaris (qty)</span>
                </div>
                <div class="card-body">
                    <canvas id="chartProduk" aria-label="Grafik produk terlaris" role="img"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const rupiah = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v);
    const chartHari = @json($chartHari);
    const chartJam = @json($chartJam);
    const chartProduk = @json($chartProduk);

    new Chart(document.getElementById('chartHari'), {
        type: 'bar',
        data: {
            labels: chartHari.labels,
            datasets: [{
                label: 'Total penjualan',
                data: chartHari.data,
                backgroundColor: 'rgba(13, 110, 253, 0.55)',
                borderColor: 'rgb(13, 110, 253)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: (ctx) => rupiah(ctx.parsed.y ?? 0) }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => (v >= 1e6 ? (v / 1e6) + ' jt' : (v >= 1e3 ? (v / 1e3) + ' rb' : v))
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('chartJam'), {
        type: 'line',
        data: {
            labels: chartJam.labels.map((h) => String(h).padStart(2, '0') + ':00'),
            datasets: [{
                label: 'Transaksi',
                data: chartJam.data,
                borderColor: 'rgb(25, 135, 84)',
                backgroundColor: 'rgba(25, 135, 84, 0.15)',
                fill: true,
                tension: 0.2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    new Chart(document.getElementById('chartProduk'), {
        type: 'bar',
        data: {
            labels: chartProduk.labels,
            datasets: [{
                label: 'Qty terjual',
                data: chartProduk.data,
                backgroundColor: 'rgba(255, 193, 7, 0.65)',
                borderColor: 'rgb(200, 150, 0)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });
})();
</script>
@endpush
