@extends('layouts.app')

@section('title', 'Laporan Laba Rugi — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-journal-check text-primary me-2"></i>Laporan Laba Rugi</h1>
        <form id="export-pdf-form" action="{{ route('laporan.laba.export.pdf') }}" method="POST" target="_blank" style="display:none;">
            @csrf
            @foreach(request()->query() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <input type="hidden" name="chart_image" id="pdf-chart-image">
        </form>
        <button type="button" id="btn-export-pdf" class="btn btn-danger btn-sm">
            <i class="bi bi-file-pdf"></i> Export PDF
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-filter-left text-primary me-1"></i> Filter Periode Keuangan
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.laba') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="tipe" class="form-label fw-semibold">Periode</label>
                    <select id="tipe" name="tipe" class="form-select" onchange="this.form.submit()">
                        <option value="harian" @selected($tipe === 'harian')>Harian</option>
                        <option value="bulanan" @selected($tipe === 'bulanan')>Bulanan</option>
                        <option value="tahunan" @selected($tipe === 'tahunan')>Tahunan</option>
                        <option value="rentang" @selected($tipe === 'rentang')>Rentang Tanggal</option>
                    </select>
                </div>

                @if ($tipe === 'harian')
                    <div class="col-md-4">
                        <label for="tanggal" class="form-label fw-semibold">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', $tanggal) }}" class="form-control @error('tanggal') is-invalid @enderror">
                        @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @elseif ($tipe === 'bulanan')
                    <div class="col-md-4">
                        <label for="bulan" class="form-label fw-semibold">Bulan</label>
                        <input type="month" id="bulan" name="bulan" value="{{ old('bulan', $bulan) }}" class="form-control @error('bulan') is-invalid @enderror">
                        @error('bulan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @elseif ($tipe === 'tahunan')
                    <div class="col-md-4">
                        <label for="tahun" class="form-label fw-semibold">Tahun</label>
                        <input type="number" id="tahun" name="tahun" value="{{ old('tahun', $tahun) }}" min="2000" max="2100" class="form-control @error('tahun') is-invalid @enderror">
                        @error('tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @elseif ($tipe === 'rentang')
                    <div class="col-md-3">
                        <label for="tanggal_dari" class="form-label fw-semibold">Tanggal Dari</label>
                        <input type="date" id="tanggal_dari" name="tanggal_dari" value="{{ old('tanggal_dari', $tanggal_dari) }}" class="form-control @error('tanggal_dari') is-invalid @enderror">
                        @error('tanggal_dari')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_sampai" class="form-label fw-semibold">Tanggal Sampai</label>
                        <input type="date" id="tanggal_sampai" name="tanggal_sampai" value="{{ old('tanggal_sampai', $tanggal_sampai) }}" class="form-control @error('tanggal_sampai') is-invalid @enderror">
                        @error('tanggal_sampai')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Terapkan</button>
                    <a href="{{ route('laporan.laba') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- P&L Summaries Cards -->
    <div class="row g-3 mb-4 font-monospace">
        <!-- 1. Pendapatan Penjualan -->
        <div class="col-12 col-md-4 col-lg-2-4" style="width: 20%; min-width: 210px;">
            <div class="card border-0 border-start border-4 border-primary shadow-sm h-100 bg-white">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px; font-family: sans-serif;">Pendapatan Penjualan</div>
                    <div class="fs-5 fw-bold text-primary">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                    <div class="small text-muted" style="font-size: 0.68rem; font-family: sans-serif;"><i class="bi bi-info-circle"></i> Σ Penjualan Kotor</div>
                </div>
            </div>
        </div>
        <!-- 2. HPP -->
        <div class="col-12 col-md-4 col-lg-2-4" style="width: 20%; min-width: 210px;">
            <div class="card border-0 border-start border-4 border-warning shadow-sm h-100 bg-white">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px; font-family: sans-serif;">Harga Pokok Penjualan (HPP)</div>
                    <div class="fs-5 fw-bold text-warning">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
                    <div class="small text-muted" style="font-size: 0.68rem; font-family: sans-serif;"><i class="bi bi-info-circle"></i> Σ Modal Terjual</div>
                </div>
            </div>
        </div>
        <!-- 3. Laba Kotor -->
        <div class="col-12 col-md-4 col-lg-2-4" style="width: 20%; min-width: 210px;">
            <div class="card border-0 border-start border-4 border-info shadow-sm h-100 bg-white">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px; font-family: sans-serif;">Laba Kotor</div>
                    <div class="fs-5 fw-bold text-info">Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}</div>
                    <div class="small text-muted" style="font-size: 0.68rem; font-family: sans-serif;"><i class="bi bi-calculator"></i> Pendapatan − HPP</div>
                </div>
            </div>
        </div>
        <!-- 4. Total Pengeluaran -->
        <div class="col-12 col-md-6 col-lg-2-4" style="width: 20%; min-width: 210px;">
            <div class="card border-0 border-start border-4 border-danger shadow-sm h-100 bg-white">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px; font-family: sans-serif;">Total Pengeluaran</div>
                    <div class="fs-5 fw-bold text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
                    <div class="small text-muted" style="font-size: 0.68rem; font-family: sans-serif;"><i class="bi bi-info-circle"></i> Σ Pengeluaran Operasional</div>
                </div>
            </div>
        </div>
        <!-- 5. Laba Bersih -->
        <div class="col-12 col-md-6 col-lg-2-4" style="width: 20%; min-width: 210px;">
            <div class="card border-0 border-start border-4 @if($labaBersih >= 0) border-success bg-success bg-opacity-10 @else border-danger bg-danger bg-opacity-10 @endif shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px; font-family: sans-serif;">Laba Bersih</div>
                    <div class="fs-5 fw-bold @if($labaBersih >= 0) text-success @else text-danger @endif">Rp {{ number_format($labaBersih, 0, ',', '.') }}</div>
                    <div class="small text-muted" style="font-size: 0.68rem; font-family: sans-serif;"><i class="bi bi-cash-stack"></i> Laba Kotor − Operasional</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Saldo Kas Toko Saat Ini Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 border-start border-4 border-dark shadow-sm bg-white font-monospace">
                <div class="card-body py-3">
                    <h5 class="card-title text-muted text-uppercase fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px; font-family: sans-serif;">
                        <i class="bi bi-wallet2 text-dark me-2"></i>Saldo Kas Toko Saat Ini
                    </h5>
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-3 border-end">
                            <div class="text-muted small" style="font-size: 0.7rem; font-family: sans-serif;">Saldo Awal</div>
                            <div class="fs-6 fw-bold text-secondary">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-12 col-md-2 border-end text-success">
                            <div class="text-muted small" style="font-size: 0.7rem; font-family: sans-serif;">+ Penjualan Cash</div>
                            <div class="fs-6 fw-bold">Rp {{ number_format($totalPenjualanCash, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-12 col-md-2 border-end text-danger">
                            <div class="text-muted small" style="font-size: 0.7rem; font-family: sans-serif;">- Pembelian Cash</div>
                            <div class="fs-6 fw-bold">Rp {{ number_format($totalPembelianCash, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-12 col-md-2 border-end text-danger">
                            <div class="text-muted small" style="font-size: 0.7rem; font-family: sans-serif;">- Pengeluaran Cash</div>
                            <div class="fs-6 fw-bold">Rp {{ number_format($totalPengeluaranCash, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-12 col-md-3 ps-md-4">
                            <div class="text-muted small fw-bold" style="font-size: 0.75rem; font-family: sans-serif;">Saldo Kas Saat Ini</div>
                            <div class="fs-5 fw-bold text-dark">Rp {{ number_format($saldoKasSaatIni, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Tambahan (Metrics) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 bg-light shadow-sm text-center py-2 px-3">
                <div class="text-muted small fw-semibold">Jumlah Transaksi</div>
                <div class="fs-5 fw-bold text-dark">{{ $jumlahTransaksi }} Kali</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 bg-light shadow-sm text-center py-2 px-3">
                <div class="text-muted small fw-semibold">Jumlah Pengeluaran</div>
                <div class="fs-5 fw-bold text-dark">{{ $jumlahPengeluaran }} Kali</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 bg-light shadow-sm text-center py-2 px-3">
                <div class="text-muted small fw-semibold">Margin Laba Kotor</div>
                <div class="fs-5 fw-bold text-info">{{ number_format($marginLabaKotor, 2, ',', '.') }}%</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 bg-light shadow-sm text-center py-2 px-3">
                <div class="text-muted small fw-semibold">Margin Laba Bersih</div>
                <div class="fs-5 fw-bold @if($marginLabaBersih >= 0) text-success @else text-danger @endif">{{ number_format($marginLabaBersih, 2, ',', '.') }}%</div>
            </div>
        </div>
    </div>

    <!-- Grafik Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-graph-up-arrow text-primary me-1"></i> Grafik Tren Laba Rugi
        </div>
        <div class="card-body">
            @if(empty($chartLabels))
                <p class="text-muted text-center py-3 mb-0">Tidak ada data untuk divisualisasikan dalam grafik.</p>
            @else
                <div style="max-height: 350px;">
                    <canvas id="profitTrendChart" style="max-height: 350px;"></canvas>
                </div>
            @endif
        </div>
    </div>

    <!-- Daily Summary Table Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-file-earmark-spreadsheet text-primary me-1"></i> Ringkasan Laporan Laba Rugi Harian (Daily Summary)
        </div>
        <div class="card-body p-0">
            @if ($lines->isEmpty())
                <p class="text-muted p-4 mb-0 text-center">Tidak ada catatan transaksi keuangan pada periode ini.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0 align-middle">
                        <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-3" style="width: 150px;">Tanggal</th>
                            <th class="text-end">Pendapatan Penjualan</th>
                            <th class="text-end">Harga Pokok Penjualan (HPP)</th>
                            <th class="text-end">Pembelian Barang</th>
                            <th class="text-end">Pengeluaran Operasional</th>
                            <th class="text-end" style="width: 160px;">Saldo Kas Harian</th>
                            <th class="text-end pe-3" style="width: 180px;">Laba Bersih</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($lines as $row)
                            <tr>
                                <td class="ps-3 font-monospace small text-secondary">
                                    {{ \Illuminate\Support\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                                </td>
                                <td class="text-end text-primary fw-semibold">
                                    Rp {{ number_format($row['pendapatan'], 0, ',', '.') }}
                                </td>
                                <td class="text-end text-warning fw-semibold">
                                    Rp {{ number_format($row['hpp'], 0, ',', '.') }}
                                </td>
                                <td class="text-end text-secondary">
                                    Rp {{ number_format($row['pembelian'], 0, ',', '.') }}
                                </td>
                                <td class="text-end text-danger">
                                    Rp {{ number_format($row['pengeluaran'], 0, ',', '.') }}
                                </td>
                                <td class="text-end text-dark fw-semibold">
                                    Rp {{ number_format($row['saldo_kas_harian'], 0, ',', '.') }}
                                </td>
                                <td class="text-end pe-3 fw-bold @if($row['laba_bersih'] >= 0) text-success @else text-danger @endif">
                                    Rp {{ number_format($row['laba_bersih'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="table-light border-top border-secondary fw-bold text-dark font-monospace">
                        <tr>
                            <td class="ps-3 text-end fw-bold">TOTAL:</td>
                            <td class="text-end text-primary">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                            <td class="text-end text-warning">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
                            <td class="text-end text-secondary">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                            <td class="text-end text-dark">Rp {{ number_format($saldoKasSaatIni, 0, ',', '.') }}</td>
                            <td class="text-end pe-3 @if($labaBersih >= 0) text-success @else text-danger @endif">
                                Rp {{ number_format($labaBersih, 0, ',', '.') }}
                            </td>
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
                const ctx = document.getElementById('profitTrendChart').getContext('2d');
                window.myProfitChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            {
                                label: 'Pendapatan Penjualan',
                                data: @json($chartPendapatan),
                                borderColor: 'rgb(13, 110, 253)',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                tension: 0.3,
                                pointRadius: 3
                            },
                            {
                                label: 'Harga Pokok Penjualan (HPP)',
                                data: @json($chartHpp),
                                borderColor: 'rgb(255, 193, 7)',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                tension: 0.3,
                                pointRadius: 3
                            },
                            {
                                label: 'Pengeluaran Operasional',
                                data: @json($chartPengeluaran),
                                borderColor: 'rgb(220, 53, 69)',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                tension: 0.3,
                                pointRadius: 3
                            },
                            {
                                label: 'Laba Bersih',
                                data: @json($chartLabaBersih),
                                borderColor: 'rgb(25, 135, 84)',
                                backgroundColor: 'rgba(25, 135, 84, 0.05)',
                                fill: true,
                                borderWidth: 3,
                                tension: 0.3,
                                pointRadius: 4
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
                                        return ctx.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnExportPdf = document.getElementById('btn-export-pdf');
            if (btnExportPdf) {
                btnExportPdf.addEventListener('click', function() {
                    const form = document.getElementById('export-pdf-form');
                    const imgInput = document.getElementById('pdf-chart-image');
                    
                    if (window.myProfitChart) {
                        imgInput.value = window.myProfitChart.toBase64Image();
                    }
                    
                    form.submit();
                });
            }
        });
    </script>
@endpush
