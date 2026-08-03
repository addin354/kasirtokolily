@extends('layouts.app')

@section('title', 'Pembelian Barang — ' . config('app.name'))

@section('content')
    <!-- Header Halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0">Pembelian Barang</h1>
            <p class="text-muted small mb-0">Kelola transaksi pembelian barang dari supplier, lihat riwayat pembelian, dan cetak laporan.</p>
        </div>
        <div class="d-flex gap-2">
            @can('write-data')
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-people"></i>
                Kelola Supplier
            </a>
            <a href="{{ route('pembelian.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Tambah Pembelian
            </a>
            @endcan
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold small bg-light text-uppercase">Filter & Pencarian</div>
        <div class="card-body">
            <form method="GET" action="{{ route('pembelian.index') }}" class="row g-3" id="filter-form">
                <!-- Keep pagination size parameter -->
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

                <!-- Cari Nomor Pembelian -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="cari_nomor" class="form-label small mb-1">Cari Nomor Pembelian</label>
                    <input type="text" name="cari_nomor" id="cari_nomor" value="{{ request('cari_nomor') }}" class="form-control form-control-sm" placeholder="No. PB-...">
                </div>

                <!-- Cari Supplier -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="cari_supplier" class="form-label small mb-1">Cari Supplier (Nama)</label>
                    <input type="text" name="cari_supplier" id="cari_supplier" value="{{ request('cari_supplier') }}" class="form-control form-control-sm" placeholder="Ketik nama supplier...">
                </div>

                <!-- Cari Produk -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="cari_produk" class="form-label small mb-1">Cari Produk (Nama)</label>
                    <input type="text" name="cari_produk" id="cari_produk" value="{{ request('cari_produk') }}" class="form-control form-control-sm" placeholder="Ketik nama produk...">
                </div>

                <!-- General Search -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="q" class="form-label small mb-1">Pencarian Umum (Keterangan/Semua)</label>
                    <input type="text" name="q" id="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="No. Faktur, dll...">
                </div>

                <!-- Tanggal Mulai -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <label for="tanggal_dari" class="form-label small mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_dari" id="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
                </div>

                <!-- Tanggal Selesai -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <label for="tanggal_sampai" class="form-label small mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_sampai" id="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
                </div>

                <!-- Supplier Dropdown -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <label for="supplier_id" class="form-label small mb-1">Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-select form-select-sm">
                        <option value="">-- Semua Supplier --</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}" @selected(request('supplier_id') == $sup->id)>
                                {{ $sup->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- User Dropdown -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <label for="user_id" class="form-label small mb-1">User / Petugas</label>
                    <select name="user_id" id="user_id" class="form-select form-select-sm">
                        <option value="">-- Semua User --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                {{ $u->name }} ({{ $u->roleLabel() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik Cards (Dashboard Kecil) -->
    <div class="row g-3 mb-4">
        <!-- Hari Ini -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-info">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Hari Ini</div>
                        <div class="fs-5 fw-bold text-dark">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-info fs-3">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulan Ini -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-primary">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Bulan Ini</div>
                        <div class="fs-5 fw-bold text-primary">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-primary fs-3">
                        <i class="bi bi-calendar3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Nominal -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-success">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Total Pembelian</div>
                        <div class="fs-5 fw-bold text-success">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-success fs-3">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jumlah Supplier -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-warning">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Supplier Terlibat</div>
                        <div class="fs-5 fw-bold text-warning">{{ $jumlahSupplier }} Supplier</div>
                    </div>
                    <div class="text-warning fs-3">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Tren Pembelian -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold small bg-light text-uppercase">Tren Pembelian Bulanan (Tahun Ini)</div>
        <div class="card-body" style="position: relative; height:280px; width:100%">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Tombol Ekspor -->
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2">
            <a href="{{ route('pembelian.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-file-pdf"></i> PDF
            </a>
            <a href="{{ route('pembelian.export.excel', request()->query()) }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-file-excel"></i> Excel (CSV)
            </a>
        </div>

        <!-- Pagination Size Selector -->
        <div class="d-flex align-items-center gap-2">
            <label for="per_page_select" class="small text-muted mb-0">Tampilkan:</label>
            <select id="per_page_select" class="form-select form-select-sm" style="width: 80px;">
                <option value="10" @selected(request('per_page') == 10)>10</option>
                <option value="25" @selected(request('per_page') == 25)>25</option>
                <option value="50" @selected(request('per_page') == 50)>50</option>
                <option value="100" @selected(request('per_page') == 100)>100</option>
            </select>
        </div>
    </div>

    <!-- Data Tabel -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th>Nomor Pembelian</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th class="text-end">Jumlah Item</th>
                        <th class="text-end">Total Pembelian</th>
                        <th>User</th>
                        <th>Status</th>
                        <th style="width: 250px;" class="text-center">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($pembelians as $p)
                        <tr>
                            <td class="fw-semibold text-dark">{{ $p->nomor_pembelian }}</td>
                            <td>{{ $p->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $p->supplier?->nama_supplier ?? '—' }}</td>
                            <td class="text-end">{{ $p->detail_pembelians_count }}</td>
                            <td class="text-end fw-bold text-success">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                            <td>{{ $p->user?->name ?? '—' }}</td>
                            <td><span class="badge bg-success-subtle text-success border border-success-subtle">Selesai</span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-info btn-sm btn-show-detail" data-id="{{ $p->id }}">Detail</button>
                                <a href="{{ route('pembelian.edit', $p) }}" class="btn btn-outline-warning btn-sm">Edit</a>
                                <a href="{{ route('pembelian.cetak', $p) }}" target="_blank" class="btn btn-outline-secondary btn-sm">Cetak</a>
                                <form action="{{ route('pembelian.destroy', $p) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-pembelian">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada transaksi pembelian.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination Info & Links -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
        <div class="small text-muted">
            Menampilkan {{ $pembelians->firstItem() ?? 0 }}–{{ $pembelians->lastItem() ?? 0 }} dari {{ $pembelians->total() }} data.
        </div>
        <div>
            {{ $pembelians->links() }}
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title h6" id="detailModalLabel">Nota Pembelian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="modal-content-area">
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <div>Memuat rincian nota...</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <a href="" id="modal-btn-print" target="_blank" class="btn btn-primary btn-sm">Cetak Nota</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // SweetAlert2 Confirmation Hapus
            document.querySelectorAll('.btn-delete-pembelian').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Detail pembelian akan terhapus dan stok dikurangi kembali!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Per Page Selector
            const perPageSelect = document.getElementById('per_page_select');
            perPageSelect.addEventListener('change', function() {
                const perPage = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', perPage);
                url.searchParams.set('page', 1); // Reset to page 1 on page limit change
                window.location.href = url.toString();
            });

            // Chart.js initialization
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyValues = @json($monthlyChartValues);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Total Pembelian (Rp)',
                        data: monthlyValues,
                        backgroundColor: 'rgba(13, 110, 253, 0.75)',
                        borderColor: 'rgb(13, 110, 253)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Total: Rp ' + Number(context.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // Detail Modal AJAX loading
            const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            const modalContentArea = document.getElementById('modal-content-area');
            const modalBtnPrint = document.getElementById('modal-btn-print');

            document.querySelectorAll('.btn-show-detail').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    
                    // Set print URL on the footer print button
                    modalBtnPrint.href = `/pembelian/${id}/cetak`;

                    // Open modal immediately with loader
                    modalContentArea.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                            <div>Memuat rincian nota...</div>
                        </div>
                    `;
                    detailModal.show();

                    // Fetch JSON data via AJAX
                    fetch(`/pembelian/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response not ok');
                        return response.json();
                    })
                    .then(data => {
                        const dateFormatted = new Date(data.tanggal).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });

                        let itemsHtml = '';
                        data.detail_pembelians.forEach((detail, index) => {
                            const subtotal = Number(detail.subtotal);
                            const harga = Number(detail.harga_beli);
                            
                            itemsHtml += `
                                <tr>
                                    <td class="text-center text-muted small">${index + 1}</td>
                                    <td>
                                        <div class="fw-semibold">${detail.product ? detail.product.nama : '—'}</div>
                                        <div class="text-muted small">Kode: ${detail.product ? detail.product.kode : '—'}</div>
                                    </td>
                                    <td class="text-end">${Number(detail.qty).toLocaleString('id-ID')}</td>
                                    <td class="text-end">Rp ${harga.toLocaleString('id-ID')}</td>
                                    <td class="text-end fw-semibold">Rp ${subtotal.toLocaleString('id-ID')}</td>
                                </tr>
                            `;
                        });

                        modalContentArea.innerHTML = `
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="text-muted small">Nomor Pembelian</div>
                                    <div class="fw-bold fs-6 text-dark">${data.nomor_pembelian}</div>
                                    <div class="text-muted small mt-2">Tanggal Pembelian</div>
                                    <div class="fw-semibold">${dateFormatted}</div>
                                    <div class="text-muted small mt-2">Metode Pembayaran</div>
                                    <div class="fw-semibold">${data.metode_pembayaran || 'Cash'}</div>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="text-muted small">Supplier</div>
                                    <div class="fw-bold fs-6 text-dark">${data.supplier ? data.supplier.nama_supplier : '—'}</div>
                                    <div class="text-muted small mt-2">Petugas Toko (User)</div>
                                    <div class="fw-semibold">${data.user ? data.user.name : '—'}</div>
                                </div>
                            </div>
                            
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-striped align-middle mb-0">
                                    <thead class="table-light text-uppercase small text-muted">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th>Nama Produk</th>
                                        <th style="width: 90px;" class="text-end">Qty</th>
                                        <th style="width: 140px;" class="text-end">Harga Beli</th>
                                        <th style="width: 150px;" class="text-end">Subtotal</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHtml}
                                    </tbody>
                                    <tfoot>
                                    <tr class="fw-bold table-light">
                                        <td colspan="4" class="text-end text-uppercase small">Grand Total</td>
                                        <td class="text-end text-success">Rp ${Number(data.total).toLocaleString('id-ID')}</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div>
                                <div class="text-muted small fw-semibold">Keterangan / Rincian:</div>
                                <div class="p-2 border rounded bg-light text-muted small mt-1" style="white-space: pre-wrap;">${data.keterangan || 'Tidak ada keterangan.'}</div>
                            </div>
                        `;
                    })
                    .catch(err => {
                        modalContentArea.innerHTML = `
                            <div class="alert alert-danger text-center mb-0">
                                <div>Gagal memuat rincian nota.</div>
                                <div class="small mt-1 text-muted">${err.message}</div>
                            </div>
                        `;
                    });
                });
            });
        });
    </script>
@endpush
