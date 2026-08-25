@extends('layouts.app')

@section('title', 'Laporan Retur Penjualan — ' . config('app.name'))

@section('content')
    @php
        $products = \App\Models\Product::orderBy('nama')->get(['id', 'nama']);
        $cashiers = \App\Models\User::whereIn('role', ['kasir', 'admin', 'owner'])->orderBy('name')->get(['id', 'name']);
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h4 mb-1">Laporan Retur Penjualan</h1>
            <p class="text-muted mb-0">Filter, cetak, dan tinjau daftar retur penjualan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('write-data')
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#tambahReturModal">
                Tambah Produk Retur
            </button>
            @endcan
            <a href="#" id="btn-export-pdf" class="btn btn-outline-primary shadow-sm d-inline-flex align-items-center fw-semibold">Cetak PDF</a>
            <a href="#" id="btn-export-excel" class="btn btn-outline-success shadow-sm d-inline-flex align-items-center fw-semibold">Cetak Excel</a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card shadow-sm border-0 mb-4 bg-white rounded-3">
        <div class="card-body p-3">
            <form id="filter-form">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-secondary fw-semibold">Cari Data</label>
                        <div class="input-group input-group-sm">
                            <input id="search-input" name="q" type="text" class="form-control form-control-sm" placeholder="Cari produk, kasir, alasan, no. retur/transaksi..." value="">
                            <button class="btn btn-outline-secondary" type="button" id="clear-search" style="display: none;">&times;</button>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-secondary fw-semibold">Mulai Tanggal</label>
                        <input id="tanggal-dari" name="tanggal_dari" type="date" class="form-control form-control-sm" value="">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-secondary fw-semibold">Sampai Tanggal</label>
                        <input id="tanggal-sampai" name="tanggal_sampai" type="date" class="form-control form-control-sm" value="">
                    </div>
                </div>
                
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-2">
                        <label for="filter-status" class="form-label small text-secondary fw-semibold">Status</label>
                        <select id="filter-status" name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Dalam Proses">Dalam Proses</option>
                            <option value="Diterima">Diterima</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="filter-jenis" class="form-label small text-secondary fw-semibold">Jenis Retur</label>
                        <select id="filter-jenis" name="jenis" class="form-select form-select-sm">
                            <option value="">Semua Jenis</option>
                            <option value="dengan_transaksi">Dengan Transaksi</option>
                            <option value="tanpa_transaksi">Tanpa Transaksi</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="filter-produk" class="form-label small text-secondary fw-semibold">Produk</label>
                        <select id="filter-produk" name="produk_id" class="form-select form-select-sm">
                            <option value="">Semua Produk</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="filter-kasir" class="form-label small text-secondary fw-semibold">Kasir</label>
                        <select id="filter-kasir" name="user_id" class="form-select form-select-sm">
                            <option value="">Semua Kasir</option>
                            @foreach ($cashiers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1 shadow-sm">Filter</button>
                        <button type="button" id="btn-reset-filters" class="btn btn-outline-secondary btn-sm flex-grow-1">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Area -->
    <div id="retur-status" class="alert d-none mb-3" role="alert"></div>


            <!-- Table Card -->
            <div class="card shadow-sm border-0 mb-4 bg-white rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0 small text-nowrap" style="min-width: 1300px;">
                            <thead class="table-dark">
                                <tr class="small text-uppercase">
                                    <th class="ps-3" style="width: 50px;">No</th>
                                    <th style="width: 100px;">ID Retur</th>
                                    <th style="width: 130px;">No. Transaksi</th>
                                    <th style="width: 110px;">Tanggal</th>
                                    <th style="min-width: 200px;">Nama Produk</th>
                                    <th class="text-end" style="width: 120px;">Harga</th>
                                    <th class="text-center" style="width: 70px;">Qty</th>
                                    <th class="text-end" style="width: 130px;">Total</th>
                                    <th style="width: 110px;">Kasir</th>
                                    <th style="min-width: 150px;">Alasan</th>
                                    <th class="text-center" style="width: 120px;">Status</th>
                                    <th class="text-center pe-3" style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="retur-table-body">
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Controls -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center p-3 border-top bg-light gap-2">
                        <div class="small text-muted" id="pagination-info">
                            Menampilkan 0 - 0 dari 0 data
                        </div>
                        <nav id="pagination-container" aria-label="Navigasi halaman retur"></nav>
                    </div>
                </div>
            </div>

            <!-- Summary Widgets -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded-3">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Total Unit Diretur</div>
                        <div id="retur-total-unit" class="h3 mb-0 fw-bold text-primary">0</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded-3">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Total Nominal Retur</div>
                        <div id="retur-total-nominal" class="h3 mb-0 fw-bold text-success">Rp 0</div>
                    </div>
                </div>
            </div>


    <!-- Modal Tambah Retur -->
    <div class="modal fade" id="tambahReturModal" tabindex="-1" aria-labelledby="tambahReturModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="retur-form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahReturModalLabel">Tambah Produk Retur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="transaksi_id" class="form-label small fw-semibold">ID Transaksi (Opsional)</label>
                            <input id="transaksi_id" name="transaksi_id" type="number" class="form-control" placeholder="Contoh: 12">
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="produk_nama" class="form-label small fw-semibold">Nama Produk</label>
                            <input id="produk_nama" name="produk_nama" type="text" class="form-control" autocomplete="off" required>
                            <input type="hidden" id="produk_id" name="produk_id">
                            <div id="retur-product-suggestions" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1055; max-height: 220px; overflow-y: auto; top: 100%; display: none;"></div>
                        </div>
                        <div class="mb-3">
                            <label for="qty" class="form-label small fw-semibold">Qty</label>
                            <input id="qty" name="qty" type="number" min="1" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="alasan" class="form-label small fw-semibold">Alasan</label>
                            <input id="alasan" name="alasan" type="text" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label small fw-semibold">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="Dalam Proses">Dalam Proses</option>
                                <option value="Diterima">Diterima</option>
                                <option value="Ditolak">Ditolak</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_retur" class="form-label small fw-semibold">Tanggal Retur</label>
                            <input id="tanggal_retur" name="tanggal_retur" type="date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Foto Barang Bukti (Opsional)</label>
                            
                            <ul class="nav nav-pills nav-fill mb-2 small" id="tambah-foto-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active py-1" id="tab-upload-btn" type="button" onclick="switchFotoTab('tambah', 'upload')">📁 Upload File</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-1" id="tab-kamera-btn" type="button" onclick="switchFotoTab('tambah', 'kamera')">📷 Ambil Kamera</button>
                                </li>
                            </ul>

                            <!-- Mode Upload File -->
                            <div id="tambah-foto-upload-box">
                                <input id="foto" name="foto" type="file" class="form-control form-control-sm" accept="image/*">
                                <div id="foto-file-preview" class="mt-2 d-none">
                                    <img id="foto-file-img" src="" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            </div>

                            <!-- Mode Ambil Kamera -->
                            <div id="tambah-foto-kamera-box" class="d-none text-center bg-light p-3 rounded border">
                                <video id="kamera-video" autoplay playsinline style="max-height: 250px; width: 100%; object-fit: contain;" class="rounded bg-black mb-2 d-none"></video>
                                <canvas id="kamera-canvas" class="d-none"></canvas>
                                <img id="kamera-preview-img" class="img-thumbnail mb-2 d-none" style="max-height: 200px; object-fit: contain;">
                                
                                <div id="kamera-actions" class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                                    <!-- Tombol Tangkap Foto dari Stream -->
                                    <button type="button" id="btn-capture-kamera" class="btn btn-success shadow-sm px-4 py-2 fw-semibold d-none" onclick="captureKamera('tambah')">
                                        📸 Tangkap Foto dari Kamera
                                    </button>

                                    <!-- Tombol Mulai Stream -->
                                    <button type="button" id="btn-start-kamera" class="btn btn-primary shadow-sm px-3 py-2" onclick="startKamera('tambah')">
                                        🎥 Buka Kamera WebCam
                                    </button>

                                    <!-- Tombol Langsung Perangkat -->
                                    <label for="foto_kamera_tambah" id="label-kamera-tambah" class="btn btn-outline-primary shadow-sm mb-0 px-3 py-2">
                                        📷 Kamera Perangkat HP/PC
                                        <input id="foto_kamera_tambah" type="file" accept="image/*" capture="environment" class="d-none" onchange="handleDirectKameraChange(this, 'tambah')">
                                    </label>

                                    <!-- Tombol Ambil Ulang -->
                                    <button type="button" id="btn-retake-kamera" class="btn btn-outline-secondary shadow-sm d-none px-3 py-2" onclick="startKamera('tambah')">
                                        🔄 Ambil Foto Ulang
                                    </button>

                                    <!-- Tombol Stop Stream -->
                                    <button type="button" id="btn-stop-kamera" class="btn btn-outline-danger shadow-sm d-none px-3 py-2" onclick="stopKamera('tambah')">
                                        ⏹️ Tutup Stream
                                    </button>
                                </div>
                                <span id="kamera-status-text" class="small text-muted d-block mt-1">Klik <b>'Buka Kamera WebCam'</b> atau <b>'Kamera Perangkat'</b> untuk mengambil foto bukti.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Retur -->
    <div class="modal fade" id="editReturModal" tabindex="-1" aria-labelledby="editReturModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="edit-retur-form">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editReturModalLabel">Edit Produk Retur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_transaksi_id" class="form-label small fw-semibold">ID Transaksi (Opsional)</label>
                            <input id="edit_transaksi_id" name="transaksi_id" type="number" class="form-control">
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="edit_produk_nama" class="form-label small fw-semibold">Nama Produk</label>
                            <input id="edit_produk_nama" name="produk_nama" type="text" class="form-control" autocomplete="off" required>
                            <input type="hidden" id="edit_produk_id" name="produk_id">
                            <div id="edit-retur-product-suggestions" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1055; max-height: 220px; overflow-y: auto; top: 100%; display: none;"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_qty" class="form-label small fw-semibold">Qty</label>
                            <input id="edit_qty" name="qty" type="number" min="1" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_alasan" class="form-label small fw-semibold">Alasan</label>
                            <input id="edit_alasan" name="alasan" type="text" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label small fw-semibold">Status</label>
                            <select id="edit_status" name="status" class="form-select">
                                <option value="Dalam Proses">Dalam Proses</option>
                                <option value="Diterima">Diterima</option>
                                <option value="Ditolak">Ditolak</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tanggal_retur" class="form-label small fw-semibold">Tanggal Retur</label>
                            <input id="edit_tanggal_retur" name="tanggal_retur" type="date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Foto Barang Bukti (Opsional)</label>
                            
                            <ul class="nav nav-pills nav-fill mb-2 small" id="edit-foto-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active py-1" id="edit-tab-upload-btn" type="button" onclick="switchFotoTab('edit', 'upload')">📁 Upload File</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-1" id="edit-tab-kamera-btn" type="button" onclick="switchFotoTab('edit', 'kamera')">📷 Ambil Kamera</button>
                                </li>
                            </ul>

                            <!-- Mode Upload File -->
                            <div id="edit-foto-upload-box">
                                <input id="edit_foto" name="foto" type="file" class="form-control form-control-sm" accept="image/*">
                                <div id="edit-foto-preview" class="mt-2 d-none">
                                    <span class="small text-muted d-block mb-1">Foto saat ini / terpilih:</span>
                                    <img id="edit-foto-img" src="" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            </div>

                            <!-- Mode Ambil Kamera -->
                            <div id="edit-foto-kamera-box" class="d-none text-center bg-light p-3 rounded border">
                                <video id="edit-kamera-video" autoplay playsinline style="max-height: 250px; width: 100%; object-fit: contain;" class="rounded bg-black mb-2 d-none"></video>
                                <canvas id="edit-kamera-canvas" class="d-none"></canvas>
                                <img id="edit-kamera-preview-img" class="img-thumbnail mb-2 d-none" style="max-height: 200px; object-fit: contain;">
                                
                                <div id="edit-kamera-actions" class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                                    <!-- Tombol Tangkap Foto dari Stream -->
                                    <button type="button" id="edit-btn-capture-kamera" class="btn btn-success shadow-sm px-4 py-2 fw-semibold d-none" onclick="captureKamera('edit')">
                                        📸 Tangkap Foto dari Kamera
                                    </button>

                                    <!-- Tombol Mulai Stream -->
                                    <button type="button" id="edit-btn-start-kamera" class="btn btn-primary shadow-sm px-3 py-2" onclick="startKamera('edit')">
                                        🎥 Buka Kamera WebCam
                                    </button>

                                    <!-- Tombol Langsung Perangkat -->
                                    <label for="foto_kamera_edit" id="edit-label-kamera" class="btn btn-outline-primary shadow-sm mb-0 px-3 py-2">
                                        📷 Kamera Perangkat HP/PC
                                        <input id="foto_kamera_edit" type="file" accept="image/*" capture="environment" class="d-none" onchange="handleDirectKameraChange(this, 'edit')">
                                    </label>

                                    <!-- Tombol Ambil Ulang -->
                                    <button type="button" id="edit-btn-retake-kamera" class="btn btn-outline-secondary shadow-sm d-none px-3 py-2" onclick="startKamera('edit')">
                                        🔄 Ambil Foto Ulang
                                    </button>

                                    <!-- Tombol Stop Stream -->
                                    <button type="button" id="edit-btn-stop-kamera" class="btn btn-outline-danger shadow-sm d-none px-3 py-2" onclick="stopKamera('edit')">
                                        ⏹️ Tutup Stream
                                    </button>
                                </div>
                                <span id="edit-kamera-status-text" class="small text-muted d-block mt-1">Klik <b>'Buka Kamera WebCam'</b> atau <b>'Kamera Perangkat'</b> untuk mengambil foto bukti.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Retur -->
    <div class="modal fade" id="detailReturModal" tabindex="-1" aria-labelledby="detailReturModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="detailReturModalLabel">Detail Retur Penjualan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-striped table-borderless mb-0 small">
                        <tbody>
                            <tr>
                                <th class="ps-3 py-2" style="width: 40%;">ID Retur</th>
                                <td id="detail_id" class="pe-3 py-2 fw-semibold"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">No. Retur</th>
                                <td id="detail_no_retur" class="pe-3 py-2"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">No. Transaksi</th>
                                <td id="detail_transaksi_id" class="pe-3 py-2"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">Tanggal Retur</th>
                                <td id="detail_tanggal_retur" class="pe-3 py-2"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">Kasir</th>
                                <td id="detail_kasir_nama" class="pe-3 py-2"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">Nama Produk</th>
                                <td id="detail_produk_nama" class="pe-3 py-2"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">Harga Satuan</th>
                                <td id="detail_harga" class="pe-3 py-2 text-primary font-monospace"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">Qty</th>
                                <td id="detail_qty" class="pe-3 py-2"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">Total Nilai Retur</th>
                                <td id="detail_total" class="pe-3 py-2 fw-bold text-success font-monospace"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">Alasan</th>
                                <td id="detail_alasan" class="pe-3 py-2 text-wrap"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">Status</th>
                                <td id="detail_status" class="pe-3 py-2"></td>
                            </tr>
                            <tr>
                                <th class="ps-3 py-2">Foto Bukti</th>
                                <td id="detail_foto" class="pe-3 py-2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btn-print-detail">Cetak Struk</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const returEndpoint = "{{ route('laporan.retur.index') }}";
        const statsEndpoint = "{{ route('laporan.retur.stats') }}";
        const searchProductsEndpoint = "{{ route('laporan.retur.search-products') }}";
        const currentUserRole = "{{ auth()->user()->role }}";
        const tableBody = document.getElementById('retur-table-body');
        const totalUnit = document.getElementById('retur-total-unit');
        const totalNominal = document.getElementById('retur-total-nominal');
        const statusAlert = document.getElementById('retur-status');
        const filterForm = document.getElementById('filter-form');
        const returForm = document.getElementById('retur-form');
        const editReturForm = document.getElementById('edit-retur-form');
        
        const productNameInput = document.getElementById('produk_nama');
        const productIdInput = document.getElementById('produk_id');
        const productSuggestions = document.getElementById('retur-product-suggestions');
        
        const editProductNameInput = document.getElementById('edit_produk_nama');
        const editProductIdInput = document.getElementById('edit_produk_id');
        const editProductSuggestions = document.getElementById('edit-retur-product-suggestions');
        
        const tanggalDari = document.getElementById('tanggal-dari');
        const tanggalSampai = document.getElementById('tanggal-sampai');
        const searchInput = document.getElementById('search-input');
        const clearSearchBtn = document.getElementById('clear-search');
        
        const filterStatus = document.getElementById('filter-status');
        const filterJenis = document.getElementById('filter-jenis');
        const filterProduk = document.getElementById('filter-produk');
        const filterKasir = document.getElementById('filter-kasir');
        const btnResetFilters = document.getElementById('btn-reset-filters');
        
        let searchTimeout;
        let editSearchTimeout;
        let currentPage = 1;
        let returItems = [];

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(value);
        }

        function showAlert(message, type = 'success') {
            statusAlert.textContent = message;
            statusAlert.className = `alert alert-${type} mb-3`;
            statusAlert.classList.remove('d-none');
            // Auto hide after 5 seconds
            setTimeout(() => {
                statusAlert.classList.add('d-none');
            }, 5000);
        }

        function renderReturTable(payload) {
            if (!payload?.data?.length) {
                tableBody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">Data retur tidak ditemukan.</td></tr>';
                totalUnit.textContent = formatNumber(0);
                totalNominal.textContent = 'Rp ' + formatNumber(0);
                returItems = [];
                return;
            }

            returItems = payload.data;
            const perPage = payload.pagination?.per_page || 10;
            const currentPageVal = payload.pagination?.current_page || 1;

            tableBody.innerHTML = returItems.map((item, index) => {
                const status = item.status || '-';
                let badgeClass = 'bg-secondary';
                if (status === 'Diterima') badgeClass = 'bg-success-subtle text-success';
                else if (status === 'Dalam Proses') badgeClass = 'bg-warning-subtle text-warning-emphasis';
                else if (status === 'Ditolak') badgeClass = 'bg-danger-subtle text-danger';
                else if (status === 'Menunggu') badgeClass = 'bg-warning text-dark';

                const noTransaksi = item.transaksi_id ? `#${item.transaksi_id}` : '-';
                const hargaUnit = 'Rp ' + formatNumber(item.harga || 0);
                const totalHarga = 'Rp ' + formatNumber(item.total || 0);
                const kasir = item.kasir_nama || '-';

                const rowIndex = (currentPageVal - 1) * perPage + index + 1;

                return `
                    <tr>
                        <td class="ps-3">${rowIndex}</td>
                        <td class="fw-semibold">${item.no_retur || item.retur_id || '-'}</td>
                        <td>${noTransaksi}</td>
                        <td>${item.tanggal_retur || '-'}</td>
                        <td class="text-wrap" style="max-width: 250px;">${item.produk_nama || '-'}</td>
                        <td class="text-end font-monospace">${hargaUnit}</td>
                        <td class="text-center">${formatNumber(item.qty || 0)}</td>
                        <td class="text-end fw-semibold text-success font-monospace">${totalHarga}</td>
                        <td>${kasir}</td>
                        <td class="text-wrap">${item.alasan || '-'}</td>
                        <td class="text-center">
                            <span class="badge ${badgeClass}">${status}</span>
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ url('laporan/retur') }}/${item.retur_id}" class="btn btn-outline-primary">Detail</a>
                                ${currentUserRole === 'admin' ? `
                                    <button type="button" class="btn btn-outline-warning" onclick="showEdit(${index})">Edit</button>
                                ` : ''}
                                <button type="button" class="btn btn-outline-secondary" onclick="printRetur(${index})">Cetak</button>
                                ${currentUserRole === 'admin' ? `
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteRetur(${item.retur_id})">Hapus</button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>`;
            }).join('');

            totalUnit.textContent = formatNumber(payload.summary?.total_unit_diretur || 0);
            totalNominal.textContent = 'Rp ' + formatNumber(payload.summary?.total_nominal_retur || 0);
        }

        function renderPagination(pagination) {
            const container = document.getElementById('pagination-container');
            const info = document.getElementById('pagination-info');
            
            if (!pagination || pagination.total === 0) {
                container.innerHTML = '';
                info.textContent = 'Menampilkan 0 - 0 dari 0 data';
                return;
            }

            const { current_page, last_page, per_page, total } = pagination;
            
            const from = (current_page - 1) * per_page + 1;
            const to = Math.min(current_page * per_page, total);
            info.textContent = `Menampilkan ${from} - ${to} dari ${total} data`;

            if (last_page <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination pagination-sm justify-content-end mb-0">';
            
            // Previous Button
            html += `
                <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
                    <button class="page-link" type="button" onclick="changePage(${current_page - 1})" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </button>
                </li>
            `;

            // Page numbers
            for (let i = 1; i <= last_page; i++) {
                if (i === 1 || i === last_page || (i >= current_page - 2 && i <= current_page + 2)) {
                    html += `
                        <li class="page-item ${i === current_page ? 'active' : ''}">
                            <button class="page-link" type="button" onclick="changePage(${i})">${i}</button>
                        </li>
                    `;
                } else if (i === current_page - 3 || i === current_page + 3) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Next Button
            html += `
                <li class="page-item ${current_page === last_page ? 'disabled' : ''}">
                    <button class="page-link" type="button" onclick="changePage(${current_page + 1})" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </button>
                </li>
            `;

            html += '</ul>';
            container.innerHTML = html;
        }

        async function loadReturData() {
            try {
                const params = new URLSearchParams();
                if (tanggalDari.value) params.append('tanggal_dari', tanggalDari.value);
                if (tanggalSampai.value) params.append('tanggal_sampai', tanggalSampai.value);
                if (searchInput.value.trim()) params.append('q', searchInput.value.trim());
                if (filterStatus.value) params.append('status', filterStatus.value);
                if (filterJenis.value) params.append('jenis', filterJenis.value);
                if (filterProduk.value) params.append('produk_id', filterProduk.value);
                if (filterKasir.value) params.append('user_id', filterKasir.value);

                // Perbarui link ekspor agar mencakup parameter penyaringan aktif
                document.getElementById('btn-export-pdf').href = `{{ url('laporan/retur/export/pdf') }}?${params.toString()}`;
                document.getElementById('btn-export-excel').href = `{{ url('laporan/retur/export/excel') }}?${params.toString()}`;

                params.append('page', currentPage);

                const response = await fetch(`${returEndpoint}?${params.toString()}`, {
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`Server returned ${response.status}`);
                }

                const payload = await response.json();
                renderReturTable(payload);
                renderPagination(payload.pagination);
                

            } catch (error) {
                console.error(error);
                showAlert('Gagal memuat data laporan retur.', 'danger');
            }
        }



        function showDetail(index) {
            const item = returItems[index];
            if (!item) return;

            document.getElementById('detail_id').textContent = item.retur_id || '-';
            document.getElementById('detail_no_retur').textContent = item.no_retur || '-';
            document.getElementById('detail_transaksi_id').textContent = item.transaksi_id || '-';
            document.getElementById('detail_tanggal_retur').textContent = item.tanggal_retur || '-';
            document.getElementById('detail_kasir_nama').textContent = item.kasir_nama || '-';
            document.getElementById('detail_produk_nama').textContent = item.produk_nama || '-';
            document.getElementById('detail_harga').textContent = 'Rp ' + formatNumber(item.harga || 0);
            document.getElementById('detail_qty').textContent = formatNumber(item.qty || 0);
            document.getElementById('detail_total').textContent = 'Rp ' + formatNumber(item.total || 0);
            document.getElementById('detail_alasan').textContent = item.alasan || '-';
            
            const status = item.status || '-';
            let badgeClass = 'bg-secondary';
            if (status === 'Diterima') badgeClass = 'bg-success-subtle text-success';
            else if (status === 'Dalam Proses') badgeClass = 'bg-warning-subtle text-warning-emphasis';
            else if (status === 'Ditolak') badgeClass = 'bg-danger-subtle text-danger';
            
            document.getElementById('detail_status').innerHTML = `<span class="badge ${badgeClass}">${status}</span>`;

            const detailFotoEl = document.getElementById('detail_foto');
            if (detailFotoEl) {
                if (item.foto) {
                    let cleanFoto = item.foto.replace(/^\/+/, '');
                    if (cleanFoto.startsWith('storage/')) cleanFoto = cleanFoto.substring(8);
                    if (cleanFoto.startsWith('public/')) cleanFoto = cleanFoto.substring(7);
                    const fotoUrl = `{{ url('storage') }}/${cleanFoto}`;
                    detailFotoEl.innerHTML = `
                        <div class="mb-2">
                            <img src="${fotoUrl}" alt="Foto Bukti Retur" class="img-thumbnail" style="max-height: 180px; object-fit: contain;">
                        </div>
                        <a href="${fotoUrl}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-2 small">Lihat Resolusi Penuh</a>
                    `;
                } else {
                    detailFotoEl.innerHTML = '<span class="text-muted small">Tidak ada foto bukti</span>';
                }
            }

            document.getElementById('btn-print-detail').onclick = function() {
                printRetur(index);
            };

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('detailReturModal'));
            modal.show();
        }

        function showEdit(index) {
            const item = returItems[index];
            if (!item) return;

            document.getElementById('edit_id').value = item.retur_id || '';
            document.getElementById('edit_transaksi_id').value = item.transaksi_id || '';
            document.getElementById('edit_produk_nama').value = item.produk_nama || '';
            document.getElementById('edit_produk_id').value = item.produk_id || '';
            document.getElementById('edit_qty').value = item.qty || '';
            document.getElementById('edit_alasan').value = item.alasan || '';
            document.getElementById('edit_status').value = item.status || 'Dalam Proses';
            document.getElementById('edit_tanggal_retur').value = item.tanggal_retur || '';

            // Handle foto preview
            const previewContainer = document.getElementById('edit-foto-preview');
            const previewImg = document.getElementById('edit-foto-img');
            const fileInput = document.getElementById('edit_foto');
            
            fileInput.value = '';
            
            if (item.foto) {
                let cleanFoto = item.foto.replace(/^\/+/, '');
                if (cleanFoto.startsWith('storage/')) cleanFoto = cleanFoto.substring(8);
                if (cleanFoto.startsWith('public/')) cleanFoto = cleanFoto.substring(7);
                previewImg.src = `{{ url('storage') }}/${cleanFoto}`;
                previewContainer.classList.remove('d-none');
            } else {
                previewImg.src = '';
                previewContainer.classList.add('d-none');
            }

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editReturModal'));
            modal.show();
        }

        async function deleteRetur(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus data retur ini?')) {
                return;
            }

            try {
                const response = await fetch(`${returEndpoint}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Gagal menghapus retur');
                }

                showAlert(result.message || 'Produk retur berhasil dihapus.', 'success');
                loadReturData();
            } catch (error) {
                console.error(error);
                showAlert(error.message || 'Gagal menghapus data retur.', 'danger');
            }
        }

        function printRetur(index) {
            const item = returItems[index];
            if (!item) return;

            const printWindow = window.open('', '_blank', 'width=600,height=700');
            if (!printWindow) {
                alert('Gagal membuka jendela cetak. Pastikan pop-up blocker dinatikan.');
                return;
            }

            const html = `
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk Retur #${item.retur_id}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            color: #000;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .toko { font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="center toko">LILY SEMBAKO</div>
    <div class="center">Bukti Retur Penjualan</div>
    <div class="divider"></div>
    <table>
        <tr><td>No. Retur:</td><td class="right">${item.no_retur || item.retur_id}</td></tr>
        <tr><td>No. Transaksi:</td><td class="right">${item.transaksi_id || '-'}</td></tr>
        <tr><td>Tanggal:</td><td class="right">${item.tanggal_retur || '-'}</td></tr>
        <tr><td>Kasir:</td><td class="right">${item.kasir_nama || '-'}</td></tr>
        <tr><td>Status:</td><td class="right">${item.status || '-'}</td></tr>
    </table>
    <div class="divider"></div>
    <div class="bold" style="margin-bottom: 4px;">${item.produk_nama}</div>
    <table>
        <tr>
            <td>${formatNumber(item.qty)} x Rp ${formatNumber(item.harga)}</td>
            <td class="right bold">Rp ${formatNumber(item.total)}</td>
        </tr>
    </table>
    <div class="divider"></div>
    <table>
        <tr><td>Alasan:</td><td class="right">${item.alasan || '-'}</td></tr>
    </table>
    <div class="divider"></div>
    <div class="footer">
        Terima kasih.<br>
        Dokumen ini adalah bukti resmi retur barang.
    </div>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 500);
        };
    <\/script>
</body>
</html>
            `;
            printWindow.document.write(html);
            printWindow.document.close();
        }

        function changePage(page) {
            currentPage = page;
            loadReturData();
        }

        // Global functions reference for HTML onclick callbacks
        window.changePage = changePage;
        window.showDetail = showDetail;
        window.showEdit = showEdit;
        window.deleteRetur = deleteRetur;
        window.printRetur = printRetur;

        // Auto-complete suggestions helper
        function hideProductSuggestions() {
            productSuggestions.style.display = 'none';
            productSuggestions.innerHTML = '';
        }

        async function searchProducts(query) {
            if (!query || query.trim().length < 2) {
                hideProductSuggestions();
                return;
            }

            try {
                const url = `${searchProductsEndpoint}?q=${encodeURIComponent(query)}`;
                const response = await fetch(url, {
                    method: 'GET',
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                if (!data || !Array.isArray(data) || data.length === 0) {
                    hideProductSuggestions();
                    return;
                }

                productSuggestions.innerHTML = data.map((product) => `
                    <button type="button" class="list-group-item list-group-item-action" data-product-id="${product.id}" data-product-name="${product.nama}">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">${product.nama}</div>
                                <div class="small text-muted">${product.kode || '-'} • stok ${product.stok ?? 0}</div>
                            </div>
                            <span class="badge text-bg-light">Pilih</span>
                        </div>
                    </button>
                `).join('');
                productSuggestions.style.display = 'block';
            } catch (error) {
                console.error('Search error:', error);
                hideProductSuggestions();
            }
        }

        productNameInput.addEventListener('input', function () {
            productIdInput.value = '';
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchProducts(this.value);
            }, 300);
        });

        productNameInput.addEventListener('focus', function () {
            if (this.value.length >= 2) {
                clearTimeout(searchTimeout);
                searchProducts(this.value);
            }
        });

        document.addEventListener('click', function (event) {
            if (productSuggestions.style.display !== 'none' && 
                !productSuggestions.contains(event.target) && 
                event.target !== productNameInput) {
                hideProductSuggestions();
            }
        });

        productSuggestions.addEventListener('click', function (event) {
            const button = event.target.closest('button[data-product-id]');
            if (!button) return;

            productNameInput.value = button.dataset.productName;
            productIdInput.value = button.dataset.productId;
            hideProductSuggestions();
        });

        // Edit Auto-complete suggestions helper
        function hideEditProductSuggestions() {
            editProductSuggestions.style.display = 'none';
            editProductSuggestions.innerHTML = '';
        }

        async function searchEditProducts(query) {
            if (!query || query.trim().length < 2) {
                hideEditProductSuggestions();
                return;
            }

            try {
                const url = `${searchProductsEndpoint}?q=${encodeURIComponent(query)}`;
                const response = await fetch(url, {
                    method: 'GET',
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                if (!data || !Array.isArray(data) || data.length === 0) {
                    hideEditProductSuggestions();
                    return;
                }

                editProductSuggestions.innerHTML = data.map((product) => `
                    <button type="button" class="list-group-item list-group-item-action" data-product-id="${product.id}" data-product-name="${product.nama}">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">${product.nama}</div>
                                <div class="small text-muted">${product.kode || '-'} • stok ${product.stok ?? 0}</div>
                            </div>
                            <span class="badge text-bg-light">Pilih</span>
                        </div>
                    </button>
                `).join('');
                editProductSuggestions.style.display = 'block';
            } catch (error) {
                console.error('Search error:', error);
                hideEditProductSuggestions();
            }
        }

        editProductNameInput.addEventListener('input', function () {
            editProductIdInput.value = '';
            clearTimeout(editSearchTimeout);
            editSearchTimeout = setTimeout(() => {
                searchEditProducts(this.value);
            }, 300);
        });

        editProductNameInput.addEventListener('focus', function () {
            if (this.value.length >= 2) {
                clearTimeout(editSearchTimeout);
                searchEditProducts(this.value);
            }
        });

        document.addEventListener('click', function (event) {
            if (editProductSuggestions.style.display !== 'none' && 
                !editProductSuggestions.contains(event.target) && 
                event.target !== editProductNameInput) {
                hideEditProductSuggestions();
            }
        });

        editProductSuggestions.addEventListener('click', function (event) {
            const button = event.target.closest('button[data-product-id]');
            if (!button) return;

            editProductNameInput.value = button.dataset.productName;
            editProductIdInput.value = button.dataset.productId;
            hideEditProductSuggestions();
        });

        // Search Input listeners
        searchInput.addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                clearSearchBtn.style.display = 'block';
            } else {
                clearSearchBtn.style.display = 'none';
            }
        });

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            currentPage = 1;
            loadReturData();
        });

        // Filter Form submit listener
        filterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            currentPage = 1;
            loadReturData();
        });

        // Reset Filters listener
        btnResetFilters.addEventListener('click', function () {
            filterForm.reset();
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            currentPage = 1;
            loadReturData();
        });

        // Kamera Capture Helpers
        let activeStreamTambah = null;
        let activeStreamEdit = null;
        let cameraBlobTambah = null;
        let cameraBlobEdit = null;

        function handleDirectKameraChange(input, mode) {
            const prefix = mode === 'tambah' ? '' : 'edit-';
            const imgPreview = document.getElementById(prefix + 'kamera-preview-img');
            const statusText = document.getElementById(prefix + 'kamera-status-text');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (mode === 'tambah') cameraBlobTambah = file;
                else cameraBlobEdit = file;

                imgPreview.src = URL.createObjectURL(file);
                imgPreview.classList.remove('d-none');
                statusText.innerHTML = '<span class="text-success fw-bold">✓ Foto dari kamera berhasil dipilih!</span>';
            }
        }
        window.handleDirectKameraChange = handleDirectKameraChange;

        function switchFotoTab(mode, tab) {
            const uploadBtn = document.getElementById(mode === 'tambah' ? 'tab-upload-btn' : 'edit-tab-upload-btn');
            const kameraBtn = document.getElementById(mode === 'tambah' ? 'tab-kamera-btn' : 'edit-tab-kamera-btn');
            const uploadBox = document.getElementById(mode === 'tambah' ? 'tambah-foto-upload-box' : 'edit-foto-upload-box');
            const kameraBox = document.getElementById(mode === 'tambah' ? 'tambah-foto-kamera-box' : 'edit-foto-kamera-box');

            if (tab === 'upload') {
                uploadBtn.classList.add('active');
                kameraBtn.classList.remove('active');
                uploadBox.classList.remove('d-none');
                kameraBox.classList.add('d-none');
                stopKamera(mode);
            } else {
                kameraBtn.classList.add('active');
                uploadBtn.classList.remove('active');
                kameraBox.classList.remove('d-none');
                uploadBox.classList.add('d-none');
                startKamera(mode);
            }
        }

        async function startKamera(mode) {
            const prefix = mode === 'tambah' ? '' : 'edit-';
            const video = document.getElementById(prefix + 'kamera-video');
            const imgPreview = document.getElementById(prefix + 'kamera-preview-img');
            const btnStart = document.getElementById(prefix + 'btn-start-kamera');
            const btnCapture = document.getElementById(prefix + 'btn-capture-kamera');
            const btnRetake = document.getElementById(prefix + 'btn-retake-kamera');
            const btnStop = document.getElementById(prefix + 'btn-stop-kamera');
            const statusText = document.getElementById(prefix + 'kamera-status-text');

            stopKamera(mode);

            // Cek jika diakses via HTTP biasa bukan HTTPS, atau browser memblokir WebRTC stream
            if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                statusText.innerHTML = '<span class="text-primary fw-semibold">💡 Kamera live WebCam membutuhkan koneksi HTTPS. Silakan klik "📷 Kamera Perangkat HP/PC".</span>';
                if (btnStart) btnStart.classList.remove('d-none');
                if (btnCapture) btnCapture.classList.add('d-none');
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } }
                });
                
                if (mode === 'tambah') activeStreamTambah = stream;
                else activeStreamEdit = stream;

                video.srcObject = stream;
                video.classList.remove('d-none');
                imgPreview.classList.add('d-none');
                
                if (btnStart) btnStart.classList.add('d-none');
                if (btnRetake) btnRetake.classList.add('d-none');
                if (btnCapture) btnCapture.classList.remove('d-none');
                if (btnStop) btnStop.classList.remove('d-none');
                statusText.innerHTML = '<span class="text-success fw-bold">🎥 Kamera Aktif! Posisikan barang bukti, lalu klik "📸 Tangkap Foto dari Kamera".</span>';
            } catch (err) {
                console.error('Kamera error:', err);
                statusText.innerHTML = '<span class="text-secondary small">Gagal membuka live webcam. Silakan gunakan tombol <b>"📷 Kamera Perangkat HP/PC"</b>.</span>';
                if (btnStart) btnStart.classList.remove('d-none');
                if (btnCapture) btnCapture.classList.add('d-none');
                if (btnStop) btnStop.classList.add('d-none');
            }
        }

        function captureKamera(mode) {
            const prefix = mode === 'tambah' ? '' : 'edit-';
            const video = document.getElementById(prefix + 'kamera-video');
            const canvas = document.getElementById(prefix + 'kamera-canvas');
            const imgPreview = document.getElementById(prefix + 'kamera-preview-img');
            const btnStart = document.getElementById(prefix + 'btn-start-kamera');
            const btnCapture = document.getElementById(prefix + 'btn-capture-kamera');
            const btnRetake = document.getElementById(prefix + 'btn-retake-kamera');
            const btnStop = document.getElementById(prefix + 'btn-stop-kamera');
            const statusText = document.getElementById(prefix + 'kamera-status-text');

            if (!video || !video.srcObject) return;

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function(blob) {
                if (mode === 'tambah') cameraBlobTambah = blob;
                else cameraBlobEdit = blob;

                const url = URL.createObjectURL(blob);
                imgPreview.src = url;
                imgPreview.classList.remove('d-none');
                video.classList.add('d-none');

                // Stop active video stream
                const stream = mode === 'tambah' ? activeStreamTambah : activeStreamEdit;
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    if (mode === 'tambah') activeStreamTambah = null;
                    else activeStreamEdit = null;
                }

                if (btnStart) btnStart.classList.add('d-none');
                if (btnCapture) btnCapture.classList.add('d-none');
                if (btnStop) btnStop.classList.add('d-none');
                if (btnRetake) btnRetake.classList.remove('d-none');
                statusText.innerHTML = '<span class="text-success fw-bold">✓ Foto barang bukti berhasil ditangkap! Siap disimpan.</span>';
            }, 'image/jpeg', 0.85);
        }

        function stopKamera(mode) {
            const prefix = mode === 'tambah' ? '' : 'edit-';
            const stream = mode === 'tambah' ? activeStreamTambah : activeStreamEdit;
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                if (mode === 'tambah') activeStreamTambah = null;
                else activeStreamEdit = null;
            }

            const video = document.getElementById(prefix + 'kamera-video');
            if (video) {
                video.srcObject = null;
                video.classList.add('d-none');
            }

            const btnStart = document.getElementById(prefix + 'btn-start-kamera');
            const btnCapture = document.getElementById(prefix + 'btn-capture-kamera');
            const btnStop = document.getElementById(prefix + 'btn-stop-kamera');

            if (btnStart && btnCapture && btnStop) {
                const imgPreview = document.getElementById(prefix + 'kamera-preview-img');
                if (!imgPreview || imgPreview.classList.contains('d-none')) {
                    btnStart.classList.remove('d-none');
                }
                btnCapture.classList.add('d-none');
                btnStop.classList.add('d-none');
            }
        }

        window.switchFotoTab = switchFotoTab;
        window.startKamera = startKamera;
        window.captureKamera = captureKamera;
        window.stopKamera = stopKamera;

        const modalTambah = document.getElementById('tambahReturModal');
        if (modalTambah) {
            modalTambah.addEventListener('hidden.bs.modal', function() {
                stopKamera('tambah');
                cameraBlobTambah = null;
                switchFotoTab('tambah', 'upload');
            });
        }

        const modalEdit = document.getElementById('editReturModal');
        if (modalEdit) {
            modalEdit.addEventListener('hidden.bs.modal', function() {
                stopKamera('edit');
                cameraBlobEdit = null;
                switchFotoTab('edit', 'upload');
            });
        }

        // Tambah Retur Form submit listener
        returForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const formData = new FormData(returForm);
            if (cameraBlobTambah) {
                formData.set('foto', cameraBlobTambah, 'foto_retur_kamera.jpg');
            }

            try {
                const response = await fetch(returEndpoint, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Gagal menyimpan retur');
                }

                showAlert(result.message || 'Produk retur berhasil ditambahkan.', 'success');
                returForm.reset();
                cameraBlobTambah = null;
                document.getElementById('tanggal_retur').value = '{{ date('Y-m-d') }}';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('tambahReturModal')).hide();
                loadReturData();
            } catch (error) {
                console.error(error);
                showAlert(error.message || 'Gagal menyimpan data retur.', 'danger');
            }
        });

        // Edit Retur Form submit listener
        editReturForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const id = document.getElementById('edit_id').value;
            const formData = new FormData(editReturForm);
            formData.delete('id');
            formData.append('_method', 'PUT');
            if (cameraBlobEdit) {
                formData.set('foto', cameraBlobEdit, 'foto_retur_kamera.jpg');
            }

            try {
                const response = await fetch(`${returEndpoint}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Gagal memperbarui retur');
                }

                showAlert(result.message || 'Produk retur berhasil diperbarui.', 'success');
                cameraBlobEdit = null;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editReturModal')).hide();
                loadReturData();
            } catch (error) {
                console.error(error);
                showAlert(error.message || 'Gagal memperbarui data retur.', 'danger');
            }
        });

        document.addEventListener('DOMContentLoaded', loadReturData);
    </script>
@endpush
