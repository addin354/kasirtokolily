@extends('layouts.app')

@section('title', 'Pusat Pengelolaan Persediaan (Inventory) — ' . config('app.name'))

@push('styles')
<style>
    .nav-tabs .nav-link {
        font-weight: 500;
        color: #555;
        border: none;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        padding: 0.8rem 1.2rem;
    }
    .nav-tabs .nav-link.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        background: transparent;
    }
    .badge-status {
        width: 14px;
        height: 14px;
        display: inline-block;
        border-radius: 50%;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
    <!-- Top Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0 fw-bold">Inventory &amp; Persediaan Barang</h1>
            <p class="text-muted small mb-0">Pusat tata kelola persediaan, opname fisik, kartu kontrol stok, dan penyesuaian.</p>
        </div>
        @can('write-data')
        <div class="d-flex gap-2">
            <a href="{{ route('pembelian.index') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-cart-plus"></i> Transaksi Pembelian Supplier
            </a>
        </div>
        @endcan
    </div>

    <!-- Alert status -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Multi-Tab Menu -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white p-0 border-bottom">
            <ul class="nav nav-tabs border-0" id="inventoryTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'masuk' ? 'active' : '' }}" href="{{ route('stok-masuk.index', ['tab' => 'masuk']) }}">
                        <i class="bi bi-box-arrow-in-down me-1"></i> A. Input Stok Masuk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'opname' ? 'active' : '' }}" href="{{ route('stok-masuk.index', ['tab' => 'opname']) }}">
                        <i class="bi bi-check2-square me-1"></i> B. Stock Opname
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'penyesuaian' ? 'active' : '' }}" href="{{ route('stok-masuk.index', ['tab' => 'penyesuaian']) }}">
                        <i class="bi bi-sliders me-1"></i> C. Penyesuaian Stok
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'riwayat' ? 'active' : '' }}" href="{{ route('stok-masuk.index', ['tab' => 'riwayat']) }}">
                        <i class="bi bi-clock-history me-1"></i> D. Riwayat Perubahan (Kartu Stok)
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body bg-light bg-opacity-25">
            
            @if($tab === 'opname')
                <div class="row g-4">
                    @can('write-data')
                    <!-- Form -->
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white fw-bold">Input Stock Opname</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('inventory.stock-opname.store') }}" id="form-opname">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="produk_id" class="form-label small fw-semibold">Produk</label>
                                        <select name="produk_id" id="produk_id" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach ($productsList as $p)
                                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->barcode }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stok_sistem" class="form-label small fw-semibold">Stok Sistem (Awal)</label>
                                        <input type="text" id="stok_sistem" class="form-control form-control-sm bg-light" readonly placeholder="Pilih produk...">
                                    </div>
                                    <div class="mb-3">
                                        <label for="stok_fisik" class="form-label small fw-semibold">Stok Fisik (Nyata)</label>
                                        <input type="number" name="stok_fisik" id="stok_fisik" class="form-control form-control-sm" required min="0">
                                    </div>
                                    <div class="mb-3">
                                        <label for="alasan_opname" class="form-label small fw-semibold">Alasan / Catatan</label>
                                        <input type="text" name="alasan" id="alasan_opname" class="form-control form-control-sm" required placeholder="Contoh: Hitung bulanan toko...">
                                    </div>
                                    <button type="submit" class="btn btn-warning btn-sm w-100 text-dark font-weight-bold">Simpan Opname Fisik</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- History Table -->
                    <div class="col-12 @can('write-data') col-md-8 @else col-md-12 @endcan">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white fw-bold">Riwayat Stock Opname</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover text-nowrap align-middle mb-0 small">
                                        <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Tanggal</th>
                                            <th>Produk</th>
                                            <th class="text-end">Stok Sistem</th>
                                            <th class="text-end">Stok Fisik</th>
                                            <th class="text-end fw-bold">Selisih</th>
                                            <th>Alasan</th>
                                            <th class="pe-3">User</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($history as $op)
                                            <tr>
                                                <td class="ps-3">{{ $op->created_at->format('d/m/Y') }}</td>
                                                <td class="fw-semibold text-dark">{{ $op->product?->nama ?? '—' }}</td>
                                                <td class="text-end">{{ $op->stok_sistem }}</td>
                                                <td class="text-end fw-bold">{{ $op->stok_fisik }}</td>
                                                <td class="text-end fw-bold @if($op->selisih > 0) text-success @elseif($op->selisih < 0) text-danger @endif">
                                                    {{ $op->selisih > 0 ? '+' . $op->selisih : $op->selisih }}
                                                </td>
                                                <td>{{ $op->alasan }}</td>
                                                <td class="pe-3">{{ $op->user?->name ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat stock opname.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3">
                                    {{ $history->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- B. PENYESUAIAN STOK TAB -->
            @elseif($tab === 'penyesuaian')
                <div class="row g-4">
                    @can('write-data')
                    <!-- Form -->
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white fw-bold">Form Penyesuaian Stok</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('inventory.penyesuaian.store') }}" id="form-penyesuaian">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="produk_id_adj" class="form-label small fw-semibold">Produk</label>
                                        <select name="produk_id" id="produk_id_adj" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach ($productsList as $p)
                                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->barcode }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Jenis Penyesuaian</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis" id="jenis_tambah" value="Tambah" requiredChecked checked>
                                                <label class="form-check-label text-success fw-bold" for="jenis_tambah">
                                                    <i class="bi bi-plus-circle"></i> Tambah Stok
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis" id="jenis_kurang" value="Kurang" required>
                                                <label class="form-check-label text-danger fw-bold" for="jenis_kurang">
                                                    <i class="bi bi-dash-circle"></i> Kurangi Stok
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="jumlah_adj" class="form-label small fw-semibold">Kuantitas (Qty)</label>
                                        <input type="number" name="jumlah" id="jumlah_adj" class="form-control form-control-sm" required min="1">
                                    </div>
                                    <div class="mb-3">
                                        <label for="alasan_adj" class="form-label small fw-semibold">Alasan</label>
                                        <select name="alasan" id="alasan_adj" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih Alasan --</option>
                                            <option value="Barang Rusak">Barang Rusak</option>
                                            <option value="Barang Hilang">Barang Hilang</option>
                                            <option value="Barang Pecah">Barang Pecah</option>
                                            <option value="Kesalahan Input">Kesalahan Input</option>
                                            <option value="Lain-lain">Lain-lain (Tulis Keterangan)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 d-none" id="alasan_kustom_block">
                                        <label for="alasan_kustom" class="form-label small fw-semibold">Keterangan Alasan</label>
                                        <input type="text" id="alasan_kustom" class="form-control form-control-sm" placeholder="Tulis alasan...">
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-sm w-100 font-weight-bold">Simpan Penyesuaian</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- History Table -->
                    <div class="col-12 @can('write-data') col-md-8 @else col-md-12 @endcan">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white fw-bold">Riwayat Penyesuaian Stok</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover text-nowrap align-middle mb-0 small">
                                        <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Tanggal</th>
                                            <th>Produk</th>
                                            <th>Jenis</th>
                                            <th class="text-end">Jumlah</th>
                                            <th>Alasan</th>
                                            <th class="pe-3">User</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($history as $adj)
                                            <tr>
                                                <td class="ps-3">{{ $adj->created_at->format('d/m/Y') }}</td>
                                                <td class="fw-semibold text-dark">{{ $adj->product?->nama ?? '—' }}</td>
                                                <td>
                                                    @php
                                                        $isTambah = in_array(strtolower(trim($adj->jenis)), ['tambah', 'masuk', 'plus', '+']);
                                                    @endphp
                                                    <span class="badge {{ $isTambah ? 'bg-success' : 'bg-danger' }}">
                                                        {{ ucfirst($adj->jenis) }}
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold">{{ $adj->jumlah }}</td>
                                                <td>{{ $adj->alasan }}</td>
                                                <td class="pe-3">{{ $adj->user?->name ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat penyesuaian stok.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3">
                                    {{ $history->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- C. RIWAYAT PERUBAHAN STOK TAB -->
            @elseif($tab === 'riwayat')
                <!-- Search & Filters -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('stok-masuk.index') }}" class="row g-3">
                            <input type="hidden" name="tab" value="riwayat">
                            <div class="col-12 col-md-3">
                                <label for="tanggal_dari" class="form-label small fw-semibold">Tanggal Dari</label>
                                <input type="date" name="tanggal_dari" id="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="tanggal_sampai" class="form-label small fw-semibold">Tanggal Sampai</label>
                                <input type="date" name="tanggal_sampai" id="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="produk_id_riwayat" class="form-label small fw-semibold">Produk</label>
                                <select name="produk_id" id="produk_id_riwayat" class="form-select form-select-sm">
                                    <option value="">-- Semua Produk --</option>
                                    @foreach($productsList as $p)
                                        <option value="{{ $p->id }}" @selected(request('produk_id') == $p->id)>{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="jenis_filter" class="form-label small fw-semibold">Jenis Transaksi</label>
                                <select name="jenis" id="jenis_filter" class="form-select form-select-sm">
                                    <option value="">-- Semua Jenis --</option>
                                    <option value="Pembelian" @selected(request('jenis') === 'Pembelian')>Pembelian Barang</option>
                                    <option value="Penjualan" @selected(request('jenis') === 'Penjualan')>Penjualan</option>
                                    <option value="Retur" @selected(request('jenis') === 'Retur')>Retur Penjualan</option>
                                    <option value="Stock Opname" @selected(request('jenis') === 'Stock Opname')>Stock Opname</option>
                                    <option value="Penyesuaian" @selected(request('jenis') === 'Penyesuaian')>Penyesuaian Stok</option>
                                    <option value="Stok Masuk" @selected(request('jenis') === 'Stok Masuk')>Stok Masuk (Lama)</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="user_id_riwayat" class="form-label small fw-semibold">User</label>
                                <select name="user_id" id="user_id_riwayat" class="form-select form-select-sm">
                                    <option value="">-- Semua User --</option>
                                    @foreach($usersList as $u)
                                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-between align-items-end mt-3 border-top pt-3">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('inventory.export-riwayat.pdf', request()->query()) }}" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-file-pdf"></i> PDF
                                    </a>
                                    <a href="{{ route('inventory.export-riwayat.excel', request()->query()) }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-file-excel"></i> Excel
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">Terapkan Filter</button>
                                    <a href="{{ route('stok-masuk.index', ['tab' => 'riwayat']) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                                </div>
                            </div>                    </div>
                        </form>
                    </div>
                </div>

                <!-- History Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover text-nowrap align-middle mb-0 small">
                                <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Tanggal</th>
                                    <th>Produk</th>
                                    <th>Jenis</th>
                                    <th class="text-end text-success">Masuk</th>
                                    <th class="text-end text-danger">Keluar</th>
                                    <th class="text-end fw-bold">Saldo Akhir</th>
                                    <th>Referensi</th>
                                    <th class="pe-3">User</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($history as $log)
                                    <tr>
                                        <td class="ps-3">{{ $log->tanggal->format('d/m/Y H:i') }}</td>
                                        <td class="fw-semibold text-dark">{{ $log->product?->nama ?? '—' }}</td>
                                        <td>
                                            <span class="badge @if($log->jenis === 'Pembelian') bg-primary-subtle text-primary-emphasis @elseif($log->jenis === 'Penjualan') bg-success-subtle text-success-emphasis @elseif($log->jenis === 'Stok Masuk') bg-info-subtle text-info-emphasis @else bg-warning-subtle text-warning-emphasis @endif">
                                                @if($log->jenis === 'Pembelian')
                                                    Pembelian Barang
                                                @elseif($log->jenis === 'Retur')
                                                    Retur Penjualan
                                                @elseif($log->jenis === 'Penyesuaian')
                                                    Penyesuaian Stok
                                                @else
                                                    {{ $log->jenis }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-end text-success fw-bold">{{ $log->masuk > 0 ? '+' . $log->masuk : '—' }}</td>
                                        <td class="text-end text-danger fw-bold">{{ $log->keluar > 0 ? '-' . $log->keluar : '—' }}</td>
                                        <td class="text-end fw-bold">{{ $log->saldo }}</td>
                                        <td class="font-monospace">{{ $log->referensi ?? '—' }}</td>
                                        <td class="pe-3">{{ $log->user?->name ?? 'System' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada riwayat perubahan stok.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    {{ $history->links() }}
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Stock Opname select helper
    const opnameSelect = document.getElementById('produk_id');
    const opnameStokSistem = document.getElementById('stok_sistem');
    if (opnameSelect && opnameStokSistem) {
        const products = @json($productsList ?? []);
        opnameSelect.addEventListener('change', function() {
            const pid = parseInt(this.value);
            const found = products.find(p => p.id === pid);
            if (found) {
                opnameStokSistem.value = parseFloat(found.stok);
            } else {
                opnameStokSistem.value = '';
            }
        });
    }

    // 2. Custom reason field toggle
    const reasonSelect = document.getElementById('alasan_adj');
    const customReasonBlock = document.getElementById('alasan_kustom_block');
    const customReasonInput = document.getElementById('alasan_kustom');
    
    if (reasonSelect && customReasonBlock && customReasonInput) {
        reasonSelect.addEventListener('change', function() {
            if (this.value === 'Lain-lain') {
                customReasonBlock.classList.remove('d-none');
                customReasonInput.setAttribute('name', 'alasan');
                customReasonInput.required = true;
                this.removeAttribute('name');
            } else {
                customReasonBlock.classList.add('d-none');
                customReasonInput.removeAttribute('name');
                customReasonInput.required = false;
                this.setAttribute('name', 'alasan');
            }
        });
    }
});
</script>
@endpush
