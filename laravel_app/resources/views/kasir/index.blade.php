@extends('layouts.app', ['wrapperClass' => 'container-fluid py-3 flex-grow-1'])

@section('title', 'Penjualan — ' . config('app.name'))

@push('styles')
<style>
/* ==========================================================================
   POS MODERN DESIGN SYSTEM
   ========================================================================== */

#suggestions {
    z-index: 9999;
    max-height: 300px;
    overflow-y: auto;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
}

#kasir-search-loading-inline {
    width: 2.25rem;
}

.card {
    border: none;
    border-radius: 14px;
    transition: all 0.25s ease;
}

.card-header {
    background: #fff;
    font-weight: 600;
    border-bottom: 1px solid #f0f0f0;
}

/* Keranjang / Daftar Belanja */
#kasir-cart-body {
    max-height: 400px;
    overflow-y: auto;
    padding-bottom: 10px;
}

#kasir-cart-body table {
    min-width: 650px;
}

#kasir-cart-body th,
#kasir-cart-body td {
    white-space: nowrap;
}

/* Custom scrollbars */
#kasir-cart-body::-webkit-scrollbar,
#suggestions::-webkit-scrollbar {
    width: 6px;
}
#kasir-cart-body::-webkit-scrollbar-track,
#suggestions::-webkit-scrollbar-track {
    background: #f1f1f1;
}
#kasir-cart-body::-webkit-scrollbar-thumb,
#suggestions::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}

/* Total Box */
.kasir-total-box {
    background: #f4fbf7;
    border: 1px solid #d1ebd9;
    border-radius: 12px;
}

.kasir-total-box strong {
    color: #198754;
}

/* Catalog Card Animations */
.kasir-catalog-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.kasir-catalog-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06) !important;
    border-color: #3b82f6 !important;
}

.btn-catalog-add {
    transition: all 0.2s ease;
}

.kasir-catalog-card:hover .btn-catalog-add {
    background-color: #3b82f6;
    color: #fff;
}

/* Rotate Chevron inside Collapse */
#catalog-card-header[aria-expanded="true"] #catalog-toggle-icon {
    transform: rotate(180deg);
}
#catalog-toggle-icon {
    transition: transform 0.25s ease-in-out;
}

/* Keyboard Shortcut info Badge */
.shortcut-key {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 1px 5px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-family: monospace;
    font-weight: bold;
}

/* Desktop Sidebar Sticky */
@media(min-width:1200px){
    #kasir-sidebar {
        position: sticky;
        top: 85px;
    }
}

/* Responsive Overrides */
@media(max-width:991px){
    .kasir-total-box strong {
        font-size: 1.75rem !important;
    }
}
</style>
@endpush

@section('content')
    <!-- Dashboard Top Header Greetings -->
    <div class="card border-0 shadow-sm mb-4 card-greeting-role">
        <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1">Selamat Datang, {{ auth()->user()->name }} 👋</h4>
                <p class="mb-0 small opacity-90">{{ auth()->user()->roleLabel() }} Toko Lily Sembako · Semoga transaksi hari ini berjalan lancar.</p>
            </div>
            <div class="text-end px-3 py-2 rounded-3 time-box-role d-flex flex-column align-items-end justify-content-center">
                <div class="fw-semibold" style="font-size: 0.8rem;" id="realtime-day-date">Hari, Tanggal</div>
                <div class="fs-4 fw-bold lh-1 mt-1" id="realtime-clock">00:00:00</div>
            </div>
        </div>
    </div>

    <!-- Page Heading -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1 fw-bold text-dark">Penjualan</h1>
            <p class="text-muted small mb-0">Melayani transaksi penjualan pelanggan.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary d-flex align-items-center gap-1 py-2 px-3 rounded-pill shadow-xs small">
                <i class="bi bi-keyboard"></i>
                <span>Shortcuts: <kbd class="bg-dark text-white px-1 rounded small">F2</kbd> Cari · <kbd class="bg-dark text-white px-1 rounded small">F4</kbd> Bayar · <kbd class="bg-dark text-white px-1 rounded small">ESC</kbd> Reset</span>
            </span>
        </div>
    </div>

    @php
        $cartBlocked = $lines->contains(fn ($l) => !empty($l['stok_kurang']));
    @endphp

    <div class="row justify-content-center g-3">
        <!-- Main Column (Products and Cart List) -->
        <div class="col-12 col-xl-8">
            <!-- Search Product & Scanner Card -->
            <div class="card shadow-sm mb-3 border-0">
                <div class="card-header fw-bold text-dark py-3 bg-white">
                    <i class="bi bi-upc-scan text-primary me-2"></i> Scan atau Cari Produk
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <!-- Baris Pertama -->
                        <div class="col-md-3 col-12">
                            <label for="kasir-jenis-harga" class="form-label small fw-bold text-muted mb-1">Jenis Harga</label>
                            <select id="kasir-jenis-harga" name="jenis_harga" class="form-select form-select-lg py-2 fs-6 shadow-sm border-2">
                                @foreach ($jenisHargaList as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-9 col-12">
                            <label for="product-search" class="form-label small fw-bold text-muted mb-1">Barcode / Nama Produk</label>
                            <div class="position-relative">
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text px-3 bg-white border-2 border-end-0">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input
                                        type="text"
                                        id="product-search"
                                        class="form-control py-2 fs-5 border-2 border-start-0"
                                        autocomplete="off"
                                        placeholder="Scan barcode atau ketik nama produk..."
                                        aria-autocomplete="list"
                                        aria-controls="suggestions"
                                        aria-expanded="false"
                                        data-search-url="{{ route('kasir.search-product') }}"
                                        data-add-url="{{ route('kasir.add-to-cart') }}"
                                        style="border-radius: 0 0.375rem 0.375rem 0;"
                                    >
                                </div>
                                <div id="suggestions" class="dropdown-menu w-100 mt-1 p-0 shadow" role="listbox"></div>
                            </div>
                        </div>
                        
                        <!-- Baris Kedua -->
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-primary py-2 fs-6 fw-bold flex-fill shadow-sm align-items-center justify-content-center d-flex gap-2" id="kasir-unified-submit" style="height: 48px;">
                                <i class="bi bi-plus-circle"></i> Tambah Produk <span class="shortcut-key bg-white text-primary border-0 ms-1 d-none d-md-inline">F2</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary py-2 fs-6 fw-semibold flex-fill shadow-sm align-items-center justify-content-center d-flex gap-2" id="kasir-camera-toggle" aria-expanded="false" style="height: 48px;">
                                <i class="bi bi-camera"></i> Scan Kamera
                            </button>
                        </div>
                    </div>
                    <div id="kasir-qr-reader" class="mt-3 rounded border bg-dark overflow-hidden mx-auto" style="display: none; max-width: 400px;"></div>
                </div>
            </div>

            <!-- Manual Catalog Quick Card (Modernized Accordion) -->
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 14px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 py-3" style="cursor: pointer;" id="catalog-card-header" data-bs-toggle="collapse" data-bs-target="#collapseCatalog" aria-expanded="false" aria-controls="collapseCatalog">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold text-primary align-items-center d-flex gap-2" style="font-size: 1rem;">
                            <i class="bi bi-grid-3x3-gap-fill text-primary"></i> Pilih Produk Manual (Katalog Cepat)
                        </h5>
                        <i class="bi bi-chevron-down text-primary" id="catalog-toggle-icon"></i>
                    </div>
                </div>
                <div id="collapseCatalog" class="collapse" aria-labelledby="catalog-card-header">
                    <div class="card-body bg-light border-top p-3">
                        @if ($products->isEmpty())
                            <p class="text-muted mb-0 text-center py-3">Belum ada produk aktif dengan stok.</p>
                        @else
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-2">
                                @foreach ($products as $product)
                                    <div class="col">
                                        <div class="card h-100 border p-2 bg-white text-center d-flex flex-column justify-content-between kasir-catalog-card shadow-xs" 
                                             style="cursor: pointer; border-radius: 10px;"
                                             data-id="{{ $product->id }}"
                                             data-name="{{ $product->nama }}"
                                             data-price="{{ (float) $product->harga_jual }}"
                                             data-barcode="{{ $product->barcode }}"
                                             data-kode="{{ $product->kode }}">
                                            <div>
                                                <div class="fw-semibold text-dark small text-truncate" title="{{ $product->nama }}">{{ $product->nama }}</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">{{ $product->kode }}</div>
                                                <div class="small text-secondary mt-1">Stok: <strong>{{ (int) $product->stok }}</strong></div>
                                                <div class="fw-bold text-success mt-1" style="font-size: 0.85rem;">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</div>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-xs py-1 mt-2 w-100 btn-catalog-add" style="font-size: 0.72rem; pointer-events: none;">
                                                <i class="bi bi-plus-lg"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cart Card -->
            <div class="card shadow-sm border-0 mb-4 mb-xl-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark align-items-center d-flex gap-2" style="font-size: 1.1rem;">
                        <i class="bi bi-list-check text-primary"></i> Daftar Belanja
                    </h5>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6 shadow-xs" id="cart-item-count-badge">{{ $lines->sum('qty') }}</span>
                </div>
                <div id="kasir-cart-body" class="card-body p-0">
                    @include('kasir.partials.cart-body', ['lines' => $lines])
                </div>
            </div>
        </div>

        <!-- Sidebar Payment Panel -->
        <div class="col-12 col-xl-4" id="kasir-sidebar">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark align-items-center d-flex gap-2" style="font-size: 1.1rem;">
                        <i class="bi bi-credit-card-2-front text-primary"></i> Pembayaran
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="kasir-total-box text-center py-4 border rounded-3 mb-3 bg-light">
                        <div class="small text-uppercase text-muted fw-bold mb-1" style="letter-spacing: 0.05em;">TOTAL BELANJA</div>
                        <strong class="fs-2 text-success fw-bold d-block" id="kasir-total-display" data-amount="{{ $total }}">
                            Rp {{ number_format($total,0,',','.') }}
                        </strong>
                    </div>

                    <form action="{{ route('kasir.transaksi.store') }}" method="POST" id="checkout-form">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="pelanggan_id" class="form-label fw-bold small text-muted"><i class="bi bi-person me-1"></i> Pelanggan</label>
                            <select name="pelanggan_id" id="pelanggan_id" class="form-select form-select-lg border-2 shadow-sm fs-6">
                                <option value="">— Umum —</option>
                                @foreach ($pelanggans as $p)
                                    <option value="{{ $p->id }}" @selected(old('pelanggan_id') == $p->id)>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            @error('pelanggan_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="metode_pembayaran" class="form-label fw-bold small text-muted"><i class="bi bi-wallet2 me-1"></i> Metode Pembayaran</label>
                            <select name="metode_pembayaran" id="metode_pembayaran" class="form-select form-select-lg border-2 shadow-sm fs-6">
                                <option value="Cash" @selected(old('metode_pembayaran', 'Cash') === 'Cash')>Cash (Tunai)</option>
                                <option value="Transfer Bank" @selected(old('metode_pembayaran') === 'Transfer Bank')>Transfer Bank</option>
                                <option value="QRIS" @selected(old('metode_pembayaran') === 'QRIS')>QRIS (E-Wallet)</option>
                            </select>
                        </div>

                        <!-- Fields for Transfer Bank -->
                        <div id="payment-transfer-group" class="d-none border rounded-3 p-3 bg-light mb-3 border-primary border-opacity-25">
                            <div class="alert alert-primary text-center py-2 px-3 mb-3 fw-semibold small">
                                <i class="bi bi-bank me-1"></i> Status: Transfer Bank
                            </div>
                            <div class="mb-3">
                                <label for="nama_bank" class="form-label fw-bold small text-muted">Nama Bank</label>
                                <select name="nama_bank" id="nama_bank" class="form-select border-2 shadow-sm fs-6" disabled>
                                    <option value="">-- Pilih Bank --</option>
                                    <option value="BCA" @selected(old('nama_bank') === 'BCA')>BCA</option>
                                    <option value="BRI" @selected(old('nama_bank') === 'BRI')>BRI</option>
                                    <option value="BNI" @selected(old('nama_bank') === 'BNI')>BNI</option>
                                    <option value="Mandiri" @selected(old('nama_bank') === 'Mandiri')>Mandiri</option>
                                    <option value="Bank Kalsel" @selected(old('nama_bank') === 'Bank Kalsel')>Bank Kalsel</option>
                                    <option value="Bank Lainnya" @selected(old('nama_bank') === 'Bank Lainnya')>Bank Lainnya</option>
                                </select>
                                @error('nama_bank')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="nomor_referensi_trf" class="form-label fw-bold small text-muted">No. Referensi</label>
                                <input type="text" name="nomor_referensi" id="nomor_referensi_trf" value="{{ old('nomor_referensi') }}" class="form-control border-2 shadow-sm fs-6" placeholder="TRF-..." disabled>
                            </div>
                        </div>

                        <!-- Fields for QRIS -->
                        <div id="payment-qris-group" class="d-none border rounded-3 p-3 bg-light mb-3 border-info border-opacity-25">
                            <div class="alert alert-info text-center py-2 px-3 mb-3 fw-semibold small text-info-emphasis">
                                <i class="bi bi-qr-code-scan me-1"></i> Silakan scan QRIS.
                            </div>
                            <div>
                                <label for="nomor_referensi_qris" class="form-label fw-bold small text-muted">Nomor Referensi QRIS <span class="text-muted">(opsional)</span></label>
                                <input type="text" name="nomor_referensi" id="nomor_referensi_qris" value="{{ old('nomor_referensi') }}" class="form-control border-2 shadow-sm fs-6" placeholder="QRIS-..." disabled>
                            </div>
                        </div>

                        <!-- Jumlah Bayar for Cash -->
                        <div class="mb-3" id="bayar-container-group">
                            <label for="bayar" class="form-label fw-bold small text-muted"><i class="bi bi-cash-coin me-1"></i> Jumlah Bayar</label>
                            <input
                                type="number"
                                id="bayar"
                                name="bayar"
                                value="{{ old('bayar') }}"
                                class="form-control form-control-lg border-2 shadow-sm fs-5 fw-bold text-success"
                                min="0"
                                step="0.01"
                                {{ $lines->isEmpty() || $cartBlocked ? 'disabled' : '' }}
                            >
                            @error('bayar')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            
                            <div class="d-flex flex-wrap gap-1 mt-2" id="quick-cash-container">
                                <button type="button" class="btn btn-sm btn-outline-success py-1 px-2 fw-bold" id="btn-cash-exact" {{ $lines->isEmpty() || $cartBlocked ? 'disabled' : '' }}>Uang Pas</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-cash="5000" {{ $lines->isEmpty() || $cartBlocked ? 'disabled' : '' }}>5k</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-cash="10000" {{ $lines->isEmpty() || $cartBlocked ? 'disabled' : '' }}>10k</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-cash="20000" {{ $lines->isEmpty() || $cartBlocked ? 'disabled' : '' }}>20k</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-cash="50000" {{ $lines->isEmpty() || $cartBlocked ? 'disabled' : '' }}>50k</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-cash="100000" {{ $lines->isEmpty() || $cartBlocked ? 'disabled' : '' }}>100k</button>
                            </div>
                        </div>

                        <!-- Kembalian -->
                        @php
                            $bayarOld = old('bayar');
                            $kembalianPreview = (is_numeric($bayarOld) && !$lines->isEmpty())
                                ? max(0, (float) $bayarOld - $total)
                                : null;
                        @endphp
                        <div class="mb-4 p-2 bg-light border rounded-3 text-center fw-semibold text-muted small" id="kasir-kembalian-wrap">
                            @if ($lines->isEmpty())
                                <span>Masukkan bayar setelah ada item di keranjang.</span>
                            @elseif ($kembalianPreview !== null)
                                Kembalian: <strong class="text-danger fs-5 d-block mt-1" id="kasir-kembalian-preview">Rp {{ number_format($kembalianPreview, 0, ',', '.') }}</strong>
                            @else
                                <span>Masukkan bayar untuk melihat kembalian.</span>
                            @endif
                        </div>

                        @if ($cartBlocked)
                            <div class="alert alert-warning small mb-3 border-2 text-warning-emphasis" id="kasir-cart-blocked-alert">Sesuaikan qty agar tidak melebihi stok sebelum bayar.</div>
                        @else
                            <div class="alert alert-warning small mb-3 border-2 d-none" id="kasir-cart-blocked-alert" style="display: none;"></div>
                        @endif

                        <!-- Action Buttons -->
                        <button
                            type="submit"
                            id="kasir-checkout-btn"
                            class="btn btn-success w-100 py-3 fw-bold fs-5 shadow-sm mb-3 align-items-center justify-content-center d-flex gap-2">
                            <i class="bi bi-check2-circle fs-4"></i> Bayar <span class="shortcut-key bg-white text-success border-0 ms-1 d-none d-md-inline">F4</span>
                        </button>

                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-warning w-100 py-2.5 fw-bold fs-7 align-items-center justify-content-center d-flex gap-1" id="btn-hold-transaction">
                                    <i class="bi bi-pause-circle"></i> Simpan Sementara
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-info w-100 py-2.5 fw-bold fs-7 align-items-center justify-content-center d-flex gap-1 position-relative" id="btn-held-list" data-bs-toggle="modal" data-bs-target="#heldTransactionsModal">
                                    <i class="bi bi-play-circle"></i> Ditunda
                                    <span class="badge bg-danger rounded-pill ms-1" id="btn-held-list-count">{{ $heldCount ?? 0 }}</span>
                                </button>
                            </div>
                        </div>

                        @if(session('last_transaction_id'))
                            <a href="{{ route('kasir.rawbt', session('last_transaction_id')) }}"
                               class="btn btn-outline-primary w-100 py-2 mt-3 fw-bold align-items-center justify-content-center d-flex gap-2">
                                <i class="bi bi-printer"></i> Cetak Struk Terakhir
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Simpan Sementara -->
    <div class="modal fade" id="holdTransactionModal" tabindex="-1" aria-labelledby="holdTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark border-0 py-3">
                    <h5 class="modal-title fw-bold" id="holdTransactionModalLabel"><i class="bi bi-pause-circle-fill me-2"></i>Simpan Transaksi Sementara</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="hold-transaction-form">
                        <div class="mb-3">
                            <label for="hold-pelanggan" class="form-label fw-bold text-muted">Nama Pelanggan / Keterangan</label>
                            <input type="text" class="form-control border-2 py-2" id="hold-pelanggan" placeholder="Contoh: Pak Ahmad, Ibu Siti, Ambil Beras..." required>
                        </div>
                        <div class="mb-3">
                            <label for="hold-catatan" class="form-label fw-bold text-muted">Catatan Tambahan (opsional)</label>
                            <textarea class="form-control border-2" id="hold-catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                        </div>
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-light border fw-semibold me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning fw-bold px-4" id="btn-submit-hold">Simpan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Transaksi Ditunda List -->
    <div class="modal fade" id="heldTransactionsModal" tabindex="-1" aria-labelledby="heldTransactionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="heldTransactionsModalLabel"><i class="bi bi-play-circle-fill me-2"></i>Daftar Transaksi Ditunda</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4 bg-light">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle bg-white rounded shadow-sm overflow-hidden mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Kode</th>
                                    <th>Pelanggan</th>
                                    <th>Keterangan / Catatan</th>
                                    <th>Ringkasan</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center" style="width: 180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="held-transactions-tbody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Memuat data transaksi ditunda...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function ($) {
    'use strict';

    // Start Clock
    $(document).ready(function() {
        startRealtimeClock();
    });

    function startRealtimeClock() {
        function updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const dayName = days[now.getDay()];
            const day = String(now.getDate()).padStart(2, '0');
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            $('#realtime-day-date').text(`${dayName}, ${day} ${monthName} ${year}`);
            $('#realtime-clock').text(`${hours}:${minutes}:${seconds}`);
        }
        updateClock();
        setInterval(updateClock, 1000);
    }

    function isBarcode(input) {
        return /^[0-9]{8,}$/.test(String(input).trim());
    }

    function formatRp(n) {
        try {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);
        } catch (e) {
            return 'Rp ' + String(Math.round(n));
        }
    }

    // Replace old custom notification toast with Swal Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    function showKasirToast(message, variant) {
        variant = variant || 'success';
        Toast.fire({
            icon: variant,
            title: message
        });
    }

    var $input = $('#product-search');
    var $suggestions = $('#suggestions');
    var $jenis = $('#kasir-jenis-harga');
    var $spinner = $('#kasir-search-loading-inline');
    var searchUrl = $input.data('search-url');
    var addUrl = $input.data('add-url');

    var debounceTimer = null;
    var lastItems = [];
    var selectedIndex = -1;
    var searchSeq = 0;

    function hideSuggestions() {
        $suggestions.removeClass('show').empty();
        $input.attr('aria-expanded', 'false');
        lastItems = [];
        selectedIndex = -1;
    }

    function highlightSuggestion() {
        $suggestions.find('.kasir-suggest-item').removeClass('active');
        var $items = $suggestions.find('.kasir-suggest-item');
        if (selectedIndex >= 0 && selectedIndex < $items.length) {
            $items.eq(selectedIndex).addClass('active');
        }
    }

    function renderSuggestions(items) {
        lastItems = items;
        selectedIndex = items.length ? 0 : -1;
        $suggestions.empty();
        if (!items.length) {
            hideSuggestions();
            return;
        }
        items.forEach(function (row, idx) {
            var $btn = $('<button type="button" class="dropdown-item kasir-suggest-item text-start py-2"></button>');
            $btn.attr('role', 'option');
            $btn.attr('data-id', String(row.id));
            if (idx === 0) {
                $btn.addClass('active');
            }
            var $title = $('<span class="fw-medium d-block"></span>').text(row.nama);
            var metaParts = [];
            if (row.kode) metaParts.push('Kode: ' + row.kode);
            if (row.barcode) metaParts.push('Barcode: ' + row.barcode);
            metaParts.push('Stok: ' + row.stok);
            metaParts.push(formatRp(row.harga_jual));
            var $meta = $('<small class="text-muted"></small>').text(metaParts.join(' · '));
            $btn.append($title).append($meta);
            $suggestions.append($btn);
        });
        $suggestions.addClass('show');
        $input.attr('aria-expanded', 'true');
    }

    function updateKembalianFromTotal() {
        var $wrap = $('#kasir-kembalian-wrap');
        if (!$wrap.length) return;
        var cartEmpty = $('#kasir-cart-body').html().indexOf('Keranjang kosong') !== -1;
        if (cartEmpty) {
            $wrap.html('<span>Masukkan bayar setelah ada item di keranjang.</span>');
            return;
        }

        var method = $('#metode_pembayaran').val() || 'Cash';
        if (method !== 'Cash') {
            $wrap.html('<span class="badge bg-success text-white py-2 px-3 fs-7 w-100"><i class="bi bi-check-circle-fill me-1"></i> Pembayaran Lunas (Non-Tunai)</span>');
            return;
        }

        var total = parseFloat($('#kasir-total-display').attr('data-amount'), 10);
        var bayarVal = parseFloat($('#bayar').val(), 10);
        if (isNaN(total)) return;
        if (!isNaN(bayarVal)) {
            var km = Math.max(0, bayarVal - total);
            $wrap.html('Kembalian: <strong class="text-danger fs-5 d-block mt-1" id="kasir-kembalian-preview">Rp ' + km.toLocaleString('id-ID') + '</strong>');
        } else {
            $wrap.html('<span>Masukkan bayar untuk melihat kembalian.</span>');
        }
    }

    function isCartDisabled() {
        var cartEmpty = $('#kasir-cart-body').html().indexOf('Keranjang kosong') !== -1;
        var blocked = $('#kasir-cart-blocked-alert').is(':visible') && !$('#kasir-cart-blocked-alert').hasClass('d-none');
        return cartEmpty || blocked;
    }

    function handlePaymentMethodChange() {
        var method = $('#metode_pembayaran').val();
        var disabled = isCartDisabled();

        if (method === 'Cash') {
            $('#bayar-container-group').removeClass('d-none');
            $('#bayar').prop('disabled', disabled);
            $('#quick-cash-container button').prop('disabled', disabled);

            $('#payment-transfer-group').addClass('d-none');
            $('#nama_bank').prop('disabled', true);
            $('#nomor_referensi_trf').prop('disabled', true);

            $('#payment-qris-group').addClass('d-none');
            $('#nomor_referensi_qris').prop('disabled', true);
        } else if (method === 'Transfer Bank') {
            $('#bayar-container-group').addClass('d-none');
            $('#bayar').prop('disabled', true);
            $('#quick-cash-container button').prop('disabled', true);

            $('#payment-transfer-group').removeClass('d-none');
            $('#nama_bank').prop('disabled', false);
            $('#nomor_referensi_trf').prop('disabled', false);

            $('#payment-qris-group').addClass('d-none');
            $('#nomor_referensi_qris').prop('disabled', true);
        } else if (method === 'QRIS') {
            $('#bayar-container-group').addClass('d-none');
            $('#bayar').prop('disabled', true);
            $('#quick-cash-container button').prop('disabled', true);

            $('#payment-transfer-group').addClass('d-none');
            $('#nama_bank').prop('disabled', true);
            $('#nomor_referensi_trf').prop('disabled', true);

            $('#payment-qris-group').removeClass('d-none');
            $('#nomor_referensi_qris').prop('disabled', false);
        }

        updateKembalianFromTotal();
    }

    $('#metode_pembayaran').on('change', handlePaymentMethodChange);
    $(document).ready(function() {
        handlePaymentMethodChange();
    });

    function applyCartPayload(data) {
        if (data.cart_html !== undefined) {
            $('#kasir-cart-body').html(data.cart_html);
        }
        if (data.total_formatted !== undefined) {
            $('#kasir-total-display').attr('data-amount', data.total).text(data.total_formatted);
        }
        if (data.item_count !== undefined) {
            $('#cart-item-count-badge').text(data.item_count);
        }
        var empty = !!data.cart_empty;
        var blocked = !!data.cart_blocked;
        var method = $('#metode_pembayaran').val() || 'Cash';
        var isCash = (method === 'Cash');

        $('#bayar').prop('disabled', !isCash || empty || blocked);
        $('#quick-cash-container button').prop('disabled', !isCash || empty || blocked);
        $('#kasir-checkout-btn').prop('disabled', empty || blocked);
        if (blocked) {
            $('#kasir-cart-blocked-alert').removeClass('d-none').show().text('Sesuaikan qty agar tidak melebihi stok sebelum bayar.');
        } else {
            $('#kasir-cart-blocked-alert').addClass('d-none').hide();
        }
        updateKembalianFromTotal();
    }

    function postAdd(payload) {
        return $.ajax({
            url: addUrl,
            method: 'POST',
            dataType: 'json',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: $.extend({}, payload, {
                jenis_harga: $jenis.val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            })
        });
    }

    function addById(produkId) {
        postAdd({ id: produkId })
            .done(function (data) {
                if (data.ok) {
                    hideSuggestions();
                    $input.val('').focus();
                    applyCartPayload(data);
                    showKasirToast(data.message || 'Produk ditambahkan.', 'success');
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal menambah produk.', 'error');
                }
            })
            .fail(function (xhr) {
                var msg = 'Gagal menambah produk.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Gagal', msg, 'error');
            });
    }

    function addByCode(code) {
        postAdd({ code: code })
            .done(function (data) {
                if (data.ok) {
                    hideSuggestions();
                    $input.val('').focus();
                    applyCartPayload(data);
                    showKasirToast(data.message || 'Produk ditambahkan.', 'success');
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal menambah produk.', 'error');
                }
            })
            .fail(function (xhr) {
                var msg = 'Gagal menambah produk.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Gagal', msg, 'error');
            });
    }

    function runSearch(q) {
        var seq = ++searchSeq;
        if (q.length < 2) {
            hideSuggestions();
            return;
        }
        $spinner.removeClass('d-none');
        $.ajax({
            url: searchUrl,
            method: 'GET',
            dataType: 'json',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: { q: q }
        })
            .always(function () {
                if (seq === searchSeq) {
                    $spinner.addClass('d-none');
                }
            })
            .done(function (data) {
                if (seq !== searchSeq) return;
                if (Array.isArray(data)) {
                    renderSuggestions(data);
                } else {
                    hideSuggestions();
                }
            })
            .fail(function () {
                if (seq !== searchSeq) return;
                hideSuggestions();
            });
    }

    function submitUnified() {
        var raw = $input.val();
        var v = raw.trim();
        if ($suggestions.hasClass('show') && lastItems.length) {
            var idx = selectedIndex >= 0 ? selectedIndex : 0;
            var pick = lastItems[idx];
            if (pick) {
                addById(pick.id);
                return;
            }
        }
        if (!v.length) {
            return;
        }
        addByCode(v);
    }

    $input.on('input', function () {
        $('#kasir-barcode-hint').toggleClass('d-none', !isBarcode($input.val()));
        clearTimeout(debounceTimer);
        var q = $input.val().trim();
        debounceTimer = setTimeout(function () {
            runSearch(q);
        }, 300);
    });

    $input.on('keydown', function (e) {
        if (!$suggestions.hasClass('show') || !lastItems.length) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitUnified();
            }
            return;
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, lastItems.length - 1);
            highlightSuggestion();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, 0);
            highlightSuggestion();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            submitUnified();
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    $suggestions.on('mousedown', '.kasir-suggest-item', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) {
            addById(id);
        }
    });

    $('#kasir-unified-submit').on('click', function () {
        submitUnified();
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#product-search, #suggestions').length) {
            hideSuggestions();
        }
    });

    $(function () {
        $input.trigger('focus');
    });

    /* Kamera HP */
    var readerEl = document.getElementById('kasir-qr-reader');
    var camBtn = document.getElementById('kasir-camera-toggle');
    var html5QrCode = null;
    var scanning = false;

    function stopCamera() {
        if (!html5QrCode || !scanning) return;
        scanning = false;
        html5QrCode.stop().catch(function () {});
        html5QrCode.clear().catch(function () {});
        readerEl.style.display = 'none';
        camBtn.setAttribute('aria-expanded', 'false');
        camBtn.innerHTML = '<i class="bi bi-camera"></i> Scan Kamera';
        $input.trigger('focus');
    }

    if (camBtn && readerEl) {
        camBtn.addEventListener('click', function () {
            if (scanning) {
                stopCamera();
                return;
            }
            if (typeof Html5Qrcode === 'undefined') {
                Swal.fire('Kamera Gagal', 'Library kamera tidak dimuat. Periksa koneksi internet.', 'error');
                return;
            }
            readerEl.style.display = 'block';
            camBtn.setAttribute('aria-expanded', 'true');
            camBtn.textContent = 'Matikan kamera';
            html5QrCode = new Html5Qrcode('kasir-qr-reader');
            scanning = true;
            html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                function (decodedText) {
                    $input.val(decodedText);
                    html5QrCode.stop().then(function () {
                        scanning = false;
                        readerEl.style.display = 'none';
                        camBtn.setAttribute('aria-expanded', 'false');
                        camBtn.innerHTML = '<i class="bi bi-camera"></i> Scan Kamera';
                        addByCode(decodedText.trim());
                    }).catch(function () {
                        scanning = false;
                        readerEl.style.display = 'none';
                        camBtn.innerHTML = '<i class="bi bi-camera"></i> Scan Kamera';
                        addByCode(decodedText.trim());
                    });
                },
                function () {}
            ).catch(function (err) {
                scanning = false;
                readerEl.style.display = 'none';
                camBtn.setAttribute('aria-expanded', 'false');
                camBtn.innerHTML = '<i class="bi bi-camera"></i> Scan Kamera';
                Swal.fire('Kamera Error', 'Tidak bisa membuka kamera: ' + (err && err.message ? err.message : String(err)), 'error');
            });
        });
    }

    $('#bayar').on('input change', function () {
        updateKembalianFromTotal();
    });
    
    $(document).on('change', '.kasir-price-type', function () {
        let lineId = this.getAttribute('data-line-id');
        $.ajax({
            url: "{{ route('kasir.cart.update-price-type') }}",
            type: "POST",
            dataType: "json",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                line_id: lineId,
                jenis_harga: this.value
            },
            success: function(response){
                applyCartPayload(response);
            },
            error: function(xhr){
                console.log(xhr.responseJSON);
            }
        });
    });

    let qtyTimer;
    $(document).on('input', '.kasir-qty-input', function () {
        clearTimeout(qtyTimer);
        let input = $(this);
        qtyTimer = setTimeout(function () {
            $.ajax({
                url: "{{ route('kasir.cart.update') }}",
                type: "POST",
                dataType: "json",
                data: {
                    line_id: input.data('line-id'),
                    qty: input.val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    if(data.ok){
                        applyCartPayload(data);
                    }
                },
                error: function(xhr){
                    var msg = 'Stok tidak mencukupi atau qty tidak valid.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Stok Kurang', msg, 'warning');
                }
            });
        }, 500);
    });

    /* ==========================================
       MOBILE CART BUTTONS
       ========================================== */
    $(document).on('click', '.kasir-plus', function(e){
        e.preventDefault();
        let input = $(this).siblings('.kasir-qty');
        let valStr = (input.val() || '0').replace(',', '.');
        let qty = parseFloat(valStr) || 0;
        input.val(Math.round((qty + 1) * 1000) / 1000).trigger('change');
    });

    $(document).on('click', '.kasir-minus', function(e){
        e.preventDefault();
        let input = $(this).siblings('.kasir-qty');
        let valStr = (input.val() || '0').replace(',', '.');
        let qty = parseFloat(valStr) || 0;
        if(qty > 1){
            input.val(Math.round((qty - 1) * 1000) / 1000).trigger('change');
        }
    });

    $(document).on('change', '.kasir-qty', function(){
        $.post("{{ route('kasir.cart.update') }}", {
            _token: $('meta[name="csrf-token"]').attr('content'),
            line_id: $(this).data('line-id'),
            qty: $(this).val()
        }, function(response){
            applyCartPayload(response);
        }).fail(function(xhr){
            var msg = 'Gagal memperbarui qty.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            Swal.fire('Peringatan', msg, 'warning');
        });
    });

    $(document).on('click', '.kasir-remove', function(e){
        e.preventDefault();
        var lineId = $(this).attr('data-line-id');
        Swal.fire({
            title: 'Hapus Item?',
            text: "Apakah Anda yakin ingin menghapus item ini dari keranjang?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('kasir.cart.remove') }}", {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    line_id: lineId
                }, function(response){
                    applyCartPayload(response);
                    showKasirToast('Item berhasil dihapus.', 'success');
                }).fail(function(xhr){
                    console.log(xhr.responseJSON);
                });
            }
        });
    });

    // Quick cash buttons
    $(document).on('click', '#btn-cash-exact', function(e) {
        e.preventDefault();
        var total = parseFloat($('#kasir-total-display').attr('data-amount')) || 0;
        $('#bayar').val(total).trigger('input').trigger('change');
    });
    $(document).on('click', '#quick-cash-container [data-cash]', function(e) {
        e.preventDefault();
        var val = parseFloat($(this).attr('data-cash') || $(this).data('cash')) || 0;
        $('#bayar').val(val).trigger('input').trigger('change');
    });

    // Quick add catalog card click handler
    $(document).on('click', '.kasir-catalog-card', function() {
        var id = $(this).data('id');
        addById(id);
    });

    /* ==========================================
       HOLD & RESUME TRANSACTION FEATURES
       ========================================== */

    // Open hold modal if cart not empty
    $('#btn-hold-transaction').on('click', function(e) {
        e.preventDefault();
        var cartEmpty = $('#kasir-cart-body').html().indexOf('Keranjang kosong') !== -1;
        if (cartEmpty) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Silakan tambah produk ke keranjang terlebih dahulu sebelum menyimpan.',
                confirmButtonColor: '#e0a800'
            });
            return;
        }
        $('#holdTransactionModal').modal('show');
    });

    // Handle hold transaction form submission
    $('#hold-transaction-form').on('submit', function(e) {
        e.preventDefault();
        var name = $('#hold-pelanggan').val().trim();
        var note = $('#hold-catatan').val().trim();

        if (!name) {
            Swal.fire('Error', 'Nama / Keterangan wajib diisi.', 'error');
            return;
        }

        $.ajax({
            url: "{{ route('kasir.hold') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                pelanggan: name,
                catatan: note
            },
            success: function(response) {
                $('#holdTransactionModal').modal('hide');
                $('#hold-transaction-form')[0].reset();
                
                // Clear the active cart in UI
                applyCartPayload({
                    total: 0,
                    total_formatted: 'Rp 0',
                    cart_html: '<p class="text-muted p-3 mb-0">Keranjang kosong.</p>',
                    cart_empty: true,
                    cart_blocked: false,
                    item_count: 0
                });

                // Update badge count
                $('#btn-held-list-count').text(response.heldCount);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan',
                    text: response.message,
                    timer: 2500,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                var msg = 'Gagal menyimpan transaksi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // Base URLs for hold actions (avoid hardcoded paths that break in subdirectory installs)
    var holdBaseResumeUrl = "{{ route('kasir.hold.resume', '__ID__') }}";
    var holdBaseDeleteUrl = "{{ route('kasir.hold.delete', '__ID__') }}";

    // Load held transactions data when the modal opens
    $('#heldTransactionsModal').on('show.bs.modal', function() {
        loadHeldTransactions();
    });

    function loadHeldTransactions() {
        $('#held-transactions-tbody').html('<tr><td colspan="6" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-info me-2"></div>Memuat data transaksi ditunda...</td></tr>');
        
        $.get("{{ route('kasir.hold.list') }}", function(data) {
            var html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-info-circle me-1"></i> Tidak ada transaksi ditunda.</td></tr>';
            } else {
                data.forEach(function(item) {
                    var notes = item.catatan ? item.catatan : '-';
                    html += `<tr>
                        <td><span class="badge bg-secondary font-monospace">${item.code}</span></td>
                        <td class="fw-semibold">${item.pelanggan}</td>
                        <td class="text-muted text-wrap" style="max-width: 200px;">${notes}</td>
                        <td>
                            <div class="small fw-semibold text-dark">${item.item_count} Item</div>
                            <div class="text-muted small">${item.date_formatted} ${item.time}</div>
                        </td>
                        <td class="text-end fw-bold text-success">${item.total_formatted}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-success fw-bold btn-resume-held" data-id="${item.id}" data-code="${item.code}">
                                    <i class="bi bi-play-fill"></i> Lanjutkan
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-held" data-id="${item.id}" data-code="${item.code}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });
            }
            $('#held-transactions-tbody').html(html);
        }).fail(function() {
            $('#held-transactions-tbody').html('<tr><td colspan="6" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat data.</td></tr>');
        });
    }

    // Resume/Lanjutkan held transaction
    $(document).on('click', '.btn-resume-held', function() {
        var id = $(this).data('id');
        var code = $(this).data('code');

        Swal.fire({
            title: 'Lanjutkan Transaksi ' + code + '?',
            text: "Keranjang aktif saat ini akan digantikan oleh transaksi ini.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: holdBaseResumeUrl.replace('__ID__', id),
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#heldTransactionsModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Transaksi Dilanjutkan',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        // Reload page to restore full state
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memuat transaksi ditunda.', 'error');
                    }
                });
            }
        });
    });

    // Delete/Hapus held transaction
    $(document).on('click', '.btn-delete-held', function() {
        var id = $(this).data('id');
        var code = $(this).data('code');

        Swal.fire({
            title: 'Hapus Transaksi Ditunda?',
            text: `Apakah Anda yakin ingin menghapus transaksi ${code}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: holdBaseDeleteUrl.replace('__ID__', id),
                    type: "DELETE",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Refresh active held count badge
                        $('#btn-held-list-count').text(response.heldCount);
                        
                        showKasirToast(response.message || 'Transaksi ditunda dihapus.', 'success');
                        loadHeldTransactions(); // reload modal table
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghapus transaksi ditunda.', 'error');
                    }
                });
            }
        });
    });

    /* ==========================================
       KEYBOARD SHORTCUTS
       ========================================== */
    $(document).on('keydown', function(e) {
        // F2 - Focus barcode search
        if (e.key === 'F2') {
            e.preventDefault();
            $('#product-search').focus().select();
        }
        
        // F4 - Submit payment/checkout
        if (e.key === 'F4') {
            e.preventDefault();
            $('#checkout-form').submit();
        }
        
        // ESC - Empty cart
        if (e.key === 'Escape') {
            // Check if modals are open. If so, let Bootstrap handle Escape (close modal)
            if ($('.modal.show').length === 0) {
                var cartEmpty = $('#kasir-cart-body').html().indexOf('Keranjang kosong') !== -1;
                if (!cartEmpty) {
                    e.preventDefault();
                    clearCartConfirm();
                }
            }
        }
    });

    function clearCartConfirm() {
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            text: "Semua item di keranjang belanja akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Kosongkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('kasir.cart.clear') }}",
                    type: "POST",
                    dataType: "json",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.ok) {
                            applyCartPayload(response);
                            Swal.fire({
                                icon: 'success',
                                title: 'Keranjang Dikosongkan',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal mengosongkan keranjang.', 'error');
                    }
                });
            }
        });
    }

})(jQuery);
</script>

<!-- SweetAlert success/error triggers for redirects -->
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Sukses!',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false
    });
</script>
@endif
@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: "{{ session('error') }}",
        timer: 4000
    });
</script>
@endif
@endpush
