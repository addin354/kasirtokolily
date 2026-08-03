@extends('layouts.app')

@section('title', 'Laporan Rekap Penjualan — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Laporan Rekap Penjualan</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('laporan.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank" rel="noopener">
                <i class="bi bi-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('laporan.export.excel', request()->query()) }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-filter-left text-primary me-1"></i> Filter Periode Penjualan
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.penjualan') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="tanggal_dari" class="form-label fw-semibold">Dari Tanggal</label>
                    <input type="date" id="tanggal_dari" name="tanggal_dari" value="{{ old('tanggal_dari', $tanggal_dari) }}" class="form-control @error('tanggal_dari') is-invalid @enderror">
                    @error('tanggal_dari')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="tanggal_sampai" class="form-label fw-semibold">Sampai Tanggal</label>
                    <input type="date" id="tanggal_sampai" name="tanggal_sampai" value="{{ old('tanggal_sampai', $tanggal_sampai) }}" class="form-control @error('tanggal_sampai') is-invalid @enderror">
                    @error('tanggal_sampai')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="metode_pembayaran" class="form-label fw-semibold">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-select">
                        <option value="">-- Semua Metode --</option>
                        <option value="Cash" @selected(request('metode_pembayaran') === 'Cash')>Cash</option>
                        <option value="Transfer Bank" @selected(request('metode_pembayaran') === 'Transfer Bank')>Transfer Bank</option>
                        <option value="QRIS" @selected(request('metode_pembayaran') === 'QRIS')>QRIS</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-circle"></i> Terapkan</button>
                    <a href="{{ route('laporan.penjualan') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
            <p class="small text-muted mb-0 mt-2">Kosongkan kedua tanggal untuk menampilkan rekapitulasi semua waktu.</p>
        </div>
    </div>

    <!-- Ringkasan Cards (5 Cards) -->
    <div class="row g-3 mb-4 font-monospace">
        <!-- Total Transaksi -->
        <div class="col-12 col-sm-6 col-lg bg-white" style="min-width: 200px;">
            <div class="card border-0 border-start border-4 border-info shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; font-family: sans-serif;">Total Transaksi</div>
                    <div class="fs-5 fw-bold text-info">{{ number_format($totalTransaksi, 0, ',', '.') }} Kali</div>
                </div>
            </div>
        </div>
        <!-- Total Barang Terjual -->
        <div class="col-12 col-sm-6 col-lg bg-white" style="min-width: 200px;">
            <div class="card border-0 border-start border-4 border-secondary shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; font-family: sans-serif;">Total Barang Terjual</div>
                    <div class="fs-5 fw-bold text-secondary">{{ number_format($totalBarangTerjual, 0, ',', '.') }} Unit</div>
                </div>
            </div>
        </div>
        <!-- Total Pendapatan (Omzet) -->
        <div class="col-12 col-sm-6 col-lg bg-white" style="min-width: 200px;">
            <div class="card border-0 border-start border-4 border-primary shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; font-family: sans-serif;">Total Pendapatan (Omzet)</div>
                    <div class="fs-5 fw-bold text-primary">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <!-- Total HPP -->
        <div class="col-12 col-sm-6 col-lg bg-white" style="min-width: 200px;">
            <div class="card border-0 border-start border-4 border-warning shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; font-family: sans-serif;">Total HPP</div>
                    <div class="fs-5 fw-bold text-warning">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <!-- Total Laba Kotor -->
        <div class="col-12 col-sm-6 col-lg bg-white" style="min-width: 200px;">
            <div class="card border-0 border-start border-4 border-success shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; font-family: sans-serif;">Total Laba Kotor</div>
                    <div class="fs-5 fw-bold text-success">Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Tren Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-graph-up-arrow text-primary me-1"></i> Grafik Tren Omzet &amp; Jumlah Transaksi
        </div>
        <div class="card-body">
            @if(empty($chartLabels))
                <p class="text-muted text-center py-3 mb-0">Tidak ada data untuk divisualisasikan dalam grafik.</p>
            @else
                <div style="max-height: 350px;">
                    <canvas id="salesTrendChart" style="max-height: 350px;"></canvas>
                </div>
            @endif
        </div>
    </div>

    <!-- Rekap Penjualan Table Section -->
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-table text-primary me-1"></i> Rekap Penjualan Harian
        </div>
        <div class="card-body p-0">
            @if ($lines->isEmpty())
                <p class="text-muted p-4 mb-0 text-center">Tidak ada catatan transaksi penjualan pada periode ini.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0 align-middle">
                        <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-3" style="width: 150px;">Tanggal</th>
                            <th class="text-center" style="width: 150px;">Jumlah Transaksi</th>
                            <th class="text-center" style="width: 150px;">Barang Terjual</th>
                            <th class="text-end">Omzet / Pendapatan</th>
                            <th class="text-end">HPP</th>
                            <th class="text-end">Laba Kotor</th>
                            <th class="text-center pe-3" style="width: 100px;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($lines as $row)
                            <tr>
                                <td class="ps-3 font-monospace small text-secondary">
                                    {{ \Illuminate\Support\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                                </td>
                                <td class="text-center fw-semibold text-dark">{{ $row['jumlah_transaksi'] }}</td>
                                <td class="text-center text-dark">{{ $row['barang_terjual'] }} unit</td>
                                <td class="text-end text-primary fw-semibold">
                                    Rp {{ number_format($row['omzet'], 0, ',', '.') }}
                                </td>
                                <td class="text-end text-warning">
                                    Rp {{ number_format($row['hpp'], 0, ',', '.') }}
                                </td>
                                <td class="text-end text-success fw-bold">
                                    Rp {{ number_format($row['laba_kotor'], 0, ',', '.') }}
                                </td>
                                <td class="text-center pe-3">
                                    <a href="{{ route('laporan.penjualan.detail', ['tanggal' => $row['tanggal']]) }}" class="btn btn-outline-primary btn-xs py-1 px-2 fw-semibold" style="font-size: 0.72rem;">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="table-light border-top border-secondary fw-bold text-dark font-monospace">
                        <tr>
                            <td class="ps-3 text-end fw-bold">TOTAL:</td>
                            <td class="text-center">{{ number_format($totalTransaksi, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($totalBarangTerjual, 0, ',', '.') }}</td>
                            <td class="text-end text-primary">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                            <td class="text-end text-warning">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $lines->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@if(!empty($chartLabels))
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('salesTrendChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            {
                                type: 'line',
                                label: 'Jumlah Transaksi',
                                data: @json($chartCount),
                                borderColor: 'rgb(220, 53, 69)',
                                backgroundColor: 'transparent',
                                borderWidth: 2.5,
                                tension: 0.2,
                                yAxisID: 'y1',
                                pointRadius: 3
                            },
                            {
                                label: 'Omzet Penjualan',
                                data: @json($chartOmzet),
                                backgroundColor: 'rgba(13, 110, 253, 0.75)',
                                borderColor: 'rgb(13, 110, 253)',
                                borderWidth: 1,
                                yAxisID: 'y'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { boxWidth: 15, font: { size: 11, weight: '600' } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        if (ctx.datasetIndex === 0) {
                                            return ctx.dataset.label + ': ' + ctx.raw + ' Kali';
                                        }
                                        return ctx.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Omzet (Rp)'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                grid: {
                                    drawOnChartArea: false // prevent grid lines overlapping
                                },
                                title: {
                                    display: true,
                                    text: 'Jumlah Transaksi'
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
@endif
