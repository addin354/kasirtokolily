@extends('layouts.app')

@section('title', 'Laporan Terpadu (Reports Hub) — ' . config('app.name'))

@section('content')
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Laporan Terpadu (Reports Hub)</h1>
            <p class="text-muted small mb-0">Analisis produk terlaris, ketersediaan stok, kartu log stok, dan estimasi nilai persediaan toko.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('owner.stok') }}" class="btn btn-outline-secondary btn-sm">Monitoring Stok</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light fw-semibold text-uppercase small py-3">Pilih & Filter Laporan</div>
        <div class="card-body">
            <form method="GET" action="{{ route('owner.reports') }}" class="row g-3" id="filter-form">
                <!-- Tipe Laporan -->
                <div class="col-12 col-md-4">
                    <label for="report_type" class="form-label small fw-semibold">Tipe Laporan</label>
                    <select name="report_type" id="report_type" class="form-select" onchange="this.form.submit()">
                        <option value="terlaris" @selected($type === 'terlaris')>1. Laporan 10 Produk Terlaris</option>
                        <option value="produk" @selected($type === 'produk')>2. Laporan Daftar Produk</option>
                        <option value="restok" @selected($type === 'restok')>3. Laporan Produk Perlu Restok</option>
                        <option value="persediaan" @selected($type === 'persediaan')>4. Laporan Persediaan Barang</option>
                        <option value="kartu_stok" @selected($type === 'kartu_stok')>5. Laporan Kartu Stok</option>
                    </select>
                </div>

                <!-- Input Pencarian (Only for Product / Kartu list) -->
                @if($type !== 'terlaris')
                    <div class="col-12 col-md-4">
                        <label for="q" class="form-label small fw-semibold">Cari Produk</label>
                        <input type="text" name="q" id="q" value="{{ request('q') }}" class="form-control" placeholder="Nama, kode, atau barcode...">
                    </div>
                @endif

                <!-- Tanggal Mulai -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="tanggal_dari" class="form-label small fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="tanggal_dari" id="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control">
                </div>

                <!-- Tanggal Selesai -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="tanggal_sampai" class="form-label small fw-semibold">Tanggal Selesai</label>
                    <input type="date" name="tanggal_sampai" id="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control">
                </div>

                <!-- Kategori -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="kategori_id" class="form-label small fw-semibold">Kategori</label>
                    <select name="kategori_id" id="kategori_id" class="form-select">
                        <option value="">-- Semua Kategori --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('kategori_id') == $cat->id)>
                                {{ $cat->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Supplier -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="supplier_id" class="form-label small fw-semibold">Supplier Terakhir</label>
                    <select name="supplier_id" id="supplier_id" class="form-select">
                        <option value="">-- Semua Supplier --</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}" @selected(request('supplier_id') == $sup->id)>
                                {{ $sup->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kasir (Only for Terlaris) -->
                @if($type === 'terlaris')
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="user_id" class="form-label small fw-semibold">Kasir / Petugas</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">-- Semua Kasir --</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                    {{ $u->name }} ({{ $u->roleLabel() }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Limit (Only for Terlaris) -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="limit" class="form-label small fw-semibold">Jumlah Produk</label>
                        <select name="limit" id="limit" class="form-select">
                            <option value="10" @selected(request('limit', 10) == 10)>10 Produk</option>
                            <option value="20" @selected(request('limit') == 20)>20 Produk</option>
                            <option value="50" @selected(request('limit') == 50)>50 Produk</option>
                        </select>
                    </div>
                @endif

                <!-- Status (Only for Produk / Persediaan) -->
                @if($type === 'produk' || $type === 'persediaan')
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="status" class="form-label small fw-semibold">Status Stok</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="aman" @selected(request('status') === 'aman')>Stok Aman</option>
                            <option value="restok" @selected(request('status') === 'restok')>Perlu Restok</option>
                        </select>
                    </div>
                @endif

                <!-- Action buttons -->
                <div class="col-12 d-flex gap-2 justify-content-end align-items-end mt-3 border-top pt-3">
                    <button type="submit" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                        <i class="bi bi-funnel-fill"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('owner.reports', ['report_type' => $type]) }}" class="btn btn-outline-secondary btn-sm">
                        Reset Filter
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tombol Ekspor -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="h6 mb-0 text-uppercase fw-bold text-muted">
            Preview Laporan: 
            @if($type === 'terlaris') Produk Terlaris
            @elseif($type === 'produk') Daftar Produk
            @elseif($type === 'restok') Produk Perlu Restok
            @elseif($type === 'persediaan') Persediaan Barang
            @elseif($type === 'kartu_stok') Kartu Stok
            @endif
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('owner.reports.pdf', request()->query()) }}" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-file-pdf"></i> Cetak PDF
            </a>
            <a href="{{ route('owner.reports.export.excel', request()->query()) }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-file-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Grafik Batang Produk Terlaris (Only for terlaris) -->
    @if($type === 'terlaris' && count($data) > 0)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-semibold">Visualisasi Qty Terjual</div>
            <div class="card-body" style="position: relative; height: 320px;">
                <canvas id="chartTerlaris"></canvas>
            </div>
        </div>
    @endif

    <!-- Data Preview Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    @if($type === 'terlaris')
                        <!-- 1. Laporan 10 Produk Terlaris -->
                        <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-3" style="width: 60px;">No</th>
                            <th>Barcode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-end">Qty Terjual</th>
                            <th class="text-end">Total Penjualan</th>
                            <th class="text-end">Harga Modal</th>
                            <th class="text-end">Harga Jual</th>
                            <th class="text-end pe-3">Estimasi Laba</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($data as $index => $row)
                            <tr>
                                <td class="ps-3">{{ $index + 1 }}</td>
                                <td class="font-monospace small text-secondary">{{ $row->barcode ?? '—' }}</td>
                                <td class="fw-semibold text-dark">{{ $row->nama_produk }}</td>
                                <td>{{ $row->nama_kategori ?? '—' }}</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($row->qty_terjual, 0, ',', '.') }}</td>
                                <td class="text-end text-success fw-bold">Rp {{ number_format($row->total_penjualan, 0, ',', '.') }}</td>
                                <td class="text-end text-muted">Rp {{ number_format($row->harga_modal, 0, ',', '.') }}</td>
                                <td class="text-end text-muted">Rp {{ number_format($row->harga_jual, 0, ',', '.') }}</td>
                                <td class="text-end pe-3 text-info fw-bold">Rp {{ number_format($row->estimasi_laba, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Tidak ada data penjualan pada kriteria ini.</td>
                            </tr>
                        @endforelse
                        </tbody>

                    @elseif($type === 'produk')
                        <!-- 2. Laporan Daftar Produk -->
                        <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-3">Barcode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Supplier</th>
                            <th class="text-end">Harga Modal</th>
                            <th class="text-end">Harga Jual Ecer</th>
                            <th class="text-end">Harga Jual Grosir</th>
                            <th class="text-end">Harga Bal</th>
                            <th class="text-end">Isi per Bal</th>
                            <th class="text-end pe-3">Minimum Stok</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td class="ps-3 font-monospace small text-secondary">{{ $row->barcode }}</td>
                                <td class="fw-semibold text-dark">{{ $row->nama }}</td>
                                <td>{{ $row->category?->nama ?? '—' }}</td>
                                <td>{{ $row->supplierTerakhir() ?? '—' }}</td>
                                <td class="text-end">Rp {{ number_format($row->harga_beli, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($row->harga_jual, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($row->harga_grosir, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($row->harga_bal, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $row->isi_per_bal ?? 1 }}</td>
                                <td class="text-end pe-3">{{ $row->stok_minimum }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">Tidak ada produk yang sesuai kriteria.</td>
                            </tr>
                        @endforelse
                        </tbody>

                    @elseif($type === 'restok')
                        <!-- 3. Laporan Produk Perlu Restok -->
                        <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-3">Barcode</th>
                            <th>Nama Produk</th>
                            <th>Supplier</th>
                            <th>Kategori</th>
                            <th class="text-end">Stok Saat Ini</th>
                            <th class="text-end">Minimum Stok</th>
                            <th class="text-end">Selisih</th>
                            <th class="text-center pe-3">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $totalRestokProduk = count($data);
                        @endphp
                        @forelse($data as $row)
                            @php
                                $selisih = max(0, $row->stok_minimum - $row->stok);
                            @endphp
                            <tr>
                                <td class="ps-3 font-monospace small text-secondary">{{ $row->barcode }}</td>
                                <td class="fw-semibold text-dark">{{ $row->nama }}</td>
                                <td>{{ $row->supplierTerakhir() ?? '—' }}</td>
                                <td>{{ $row->category?->nama ?? '—' }}</td>
                                <td class="text-end fw-bold text-warning">{{ $row->stok }}</td>
                                <td class="text-end text-muted">{{ $row->stok_minimum }}</td>
                                <td class="text-end fw-bold text-danger">{{ $selisih }}</td>
                                <td class="text-center pe-3"><span class="badge bg-warning text-dark">Perlu Restok</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Tidak ada produk yang perlu restok.</td>
                            </tr>
                        @endforelse
                        </tbody>

                    @elseif($type === 'persediaan')
                        <!-- 4. Laporan Persediaan Barang -->
                        <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-3">Barcode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Supplier</th>
                            <th class="text-end">Stok</th>
                            <th class="text-center pe-3">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($data as $row)
                            @php
                                $status = $row->stokStatus();
                            @endphp
                            <tr>
                                <td class="ps-3 font-monospace small text-secondary">{{ $row->barcode }}</td>
                                <td class="fw-semibold text-dark">{{ $row->nama }}</td>
                                <td>{{ $row->category?->nama ?? '—' }}</td>
                                <td>{{ $row->supplierTerakhir() ?? '—' }}</td>
                                <td class="text-end fw-bold">{{ $row->stok }}</td>
                                <td class="text-center pe-3">
                                    @if($status === 'restok')
                                        <span class="badge bg-warning text-dark">Perlu Restok</span>
                                    @else
                                        <span class="badge bg-success">Aman</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada data persediaan barang.</td>
                            </tr>
                        @endforelse
                        </tbody>



                    @elseif($type === 'kartu_stok')
                        <!-- 6. Laporan Kartu Stok -->
                        <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-3" style="width: 60px;">No</th>
                            <th>Tanggal</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Tipe Transaksi</th>
                            <th class="text-end">Qty Masuk</th>
                            <th class="text-end pe-3">Qty Keluar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($data as $index => $row)
                            <tr>
                                <td class="ps-3 text-secondary">{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y H:i') }}</td>
                                <td class="fw-semibold text-dark">{{ $row->nama_produk }}</td>
                                <td>{{ $row->nama_kategori ?? '—' }}</td>
                                <td>
                                    <span class="badge @if($row->tipe === 'Pembelian') bg-primary-subtle text-primary-emphasis @elseif($row->tipe === 'Penjualan') bg-success-subtle text-success-emphasis @elseif($row->tipe === 'Stok Masuk') bg-info-subtle text-info-emphasis @else bg-warning-subtle text-warning-emphasis @endif">
                                        {{ $row->tipe }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-success">{{ number_format($row->masuk, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold text-danger pe-3">{{ number_format($row->keluar, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Tidak ada pergerakan kartu stok pada kriteria ini.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Blocks / Totals (If applicable) -->
    @if($type === 'restok' && count($data) > 0)
        <div class="row g-3 mt-1">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 border-start border-4 border-warning bg-white py-2 px-3">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Jumlah Produk Perlu Restok</div>
                    <div class="fs-4 fw-bold text-warning">{{ $totalRestokProduk }} Produk</div>
                </div>
            </div>
        </div>

    @endif

    <!-- Pagination (Only for paginated listings) -->
    @if($type !== 'terlaris' && method_exists($data, 'links'))
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
            <div class="small text-muted">
                Menampilkan {{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }} dari {{ $data->total() }} data.
            </div>
            <div>
                {{ $data->links() }}
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @if($type === 'terlaris' && count($data) > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('chartTerlaris').getContext('2d');
                const rawData = @json($data);
                
                const labels = rawData.map(r => r.nama_produk);
                const values = rawData.map(r => r.qty_terjual);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Qty Terjual',
                            data: values,
                            backgroundColor: 'rgba(13, 110, 253, 0.75)',
                            borderColor: 'rgb(13, 110, 253)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
@endpush
