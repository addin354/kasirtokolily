@extends('layouts.app')

@section('title', 'Produk & master data — ' . config('app.name'))

@section('content')
    <div class="mb-3">
        <h1 class="h4 mb-2">Produk &amp; master data</h1>
        <p class="text-muted small mb-0">Kelola produk, kategori, dan satuan. Di form <strong>Tambah / Edit produk</strong> ada dropdown + tombol <strong>+ Baru</strong> untuk kategori &amp; satuan (modal), tanpa pindah halaman.</p>
    </div>

    <ul class="nav nav-tabs flex-nowrap overflow-auto gap-1 mb-3 pb-1" style="-webkit-overflow-scrolling: touch;" role="tablist">
        <li class="nav-item" role="presentation">
            <a
                class="nav-link @if($tab === 'produk') active @endif"
                href="{{ route('products.index', ['tab' => 'produk']) }}"
                role="tab"
            >Daftar produk</a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                class="nav-link @if($tab === 'kategori') active @endif"
                href="{{ route('products.index', ['tab' => 'kategori']) }}"
                role="tab"
            >Kategori</a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                class="nav-link @if($tab === 'satuan') active @endif"
                href="{{ route('products.index', ['tab' => 'satuan']) }}"
                role="tab"
            >Satuan</a>
        </li>
    </ul>

    @if($tab === 'produk')
        <div class="app-mobile-pad-bottom">
        <form method="GET" action="{{ route('products.index') }}" id="form-produk-cari" class="mb-3">
            <input type="hidden" name="tab" value="produk">
            <label for="produk-cari-q" class="form-label small fw-semibold mb-1">Cari produk</label>
            <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch">
                <div class="flex-grow-1 position-relative">
                    <input
                        type="search"
                        name="q"
                        id="produk-cari-q"
                        value="{{ request('q') }}"
                        class="form-control"
                        placeholder="Nama, kode, atau barcode…"
                        autocomplete="off"
                        inputmode="search"
                        role="combobox"
                        aria-autocomplete="list"
                        aria-controls="produk-search-suggestions"
                        aria-expanded="false"
                        data-search-suggestions-url="{{ route('products.search-suggestions') }}"
                    >
                    <div
                        id="produk-search-suggestions"
                        class="dropdown-menu shadow border-0"
                        role="listbox"
                        aria-label="Saran produk"
                    ></div>
                </div>
                <button
                    type="button"
                    class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-3 produk-scan-btn"
                    id="produk-cari-camera"
                    aria-expanded="false"
                    aria-label="Scan barcode dengan kamera"
                    title="Scan barcode dengan kamera"
                    style="min-width: 2.75rem; min-height: calc(2.25rem + 2px);"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="flex-shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 4h4V2H4a2 2 0 0 0-2 2v4h2V4zm0 12H2v4a2 2 0 0 0 2 2h4v-2H4v-4zm16-4h2V4a2 2 0 0 0-2-2h-4v2h4v8zm0 8h-4v2h4a2 2 0 0 0 2-2v-4h-2v4zM8 8h8v8H8V8zm2 2v4h4v-4h-4z"/>
                    </svg>
                    <span class="visually-hidden">Scan barcode dengan kamera</span>
                </button>
                <button type="submit" class="btn btn-primary px-3">Cari</button>
                @if(request()->filled('q'))
                    <a href="{{ route('products.index', ['tab' => 'produk']) }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
            <div id="produk-cari-qr-reader" class="mt-2 rounded border bg-dark overflow-hidden" style="display: none; max-width: 400px;"></div>
            <div class="form-text small mt-1">Minimal 2 karakter membuka daftar saran (↑↓ navigasi); Enter memilih atau menjalankan <strong>Cari</strong>; <kbd class="small">Esc</kbd> menutup saran tanpa mengubah kata kunci.</div>
        </form>

        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-stretch align-items-md-center gap-2 mb-3">
            <span class="text-muted small order-2 order-md-1">Stok dan harga per item</span>
            <div class="d-flex flex-column flex-sm-row gap-2 order-1 order-md-2 w-100 w-md-auto">
                <a href="{{ route('produk.export.pdf') }}" class="btn btn-outline-danger btn-sm btn-lg-touch" target="_blank" rel="noopener">Export PDF</a>
                @can('write-data')
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-lg d-md-none w-100 w-sm-auto">Tambah produk</a>
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm d-none d-md-inline-flex">Tambah produk</a>
                @endcan
            </div>
        </div>

        <div class="d-none d-md-block card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-nowrap mb-0 align-middle small">
                        <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th class="text-end">Beli</th>
                            <th class="text-end">Eceran</th>
                            <th class="text-end">Grosir</th>
                            <th class="text-end">Bal</th>
                            <th class="text-end">Stok</th>
                            <th style="min-width: 11rem;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->nama }}</td>
                                <td>{{ $product->category?->nama ?? '-' }}</td>
                                <td>{{ $product->satuanModel?->nama ?? '—' }}</td>
                                <td class="text-end">Rp {{ number_format($product->harga_beli, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($product->harga_grosir ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($product->harga_bal ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">
                                    {{ (float)$product->stok == (int)$product->stok ? (int)$product->stok : rtrim(rtrim(number_format((float)$product->stok, 3, ',', '.'), '0'), ',') }}
                                    <div class="small text-muted font-monospace" style="font-size: 0.75rem;">min: {{ (float)($product->stok_minimum ?? 10) == (int)($product->stok_minimum ?? 10) ? (int)($product->stok_minimum ?? 10) : rtrim(rtrim(number_format((float)($product->stok_minimum ?? 10), 3, ',', '.'), '0'), ',') }}</div>
                                </td>
                                <td>
                                    @can('write-data')
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk ini?')">Hapus</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    @if(request()->filled('q'))
                                        Tidak ada produk yang cocok dengan “{{ request('q') }}”.
                                    @else
                                        Belum ada data produk.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-md-none vstack gap-2 mb-2">
            @forelse($products as $product)
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="fw-semibold mb-2">{{ $product->nama }}</div>
                        <div class="small text-muted mb-2">
                            {{ $product->category?->nama ?? '—' }}
                            <span class="text-body-secondary">·</span> {{ $product->satuanModel?->nama ?? '—' }}
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline border-top pt-2 mt-1">
                            <span class="small">Stok</span>
                            <span class="fs-5 fw-bold">{{ (float)$product->stok == (int)$product->stok ? (int)$product->stok : rtrim(rtrim(number_format((float)$product->stok, 3, ',', '.'), '0'), ',') }} <span class="text-muted fs-6 font-monospace" style="font-size: 0.75rem;">/ min: {{ (float)($product->stok_minimum ?? 10) == (int)($product->stok_minimum ?? 10) ? (int)($product->stok_minimum ?? 10) : rtrim(rtrim(number_format((float)($product->stok_minimum ?? 10), 3, ',', '.'), '0'), ',') }}</span></span>
                        </div>
                        <ul class="list-unstyled small mb-0 mt-2 text-muted">
                            <li>Beli Rp {{ number_format($product->harga_beli, 0, ',', '.') }} ·
                                Eceran Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</li>
                            <li>Grosir Rp {{ number_format($product->harga_grosir ?? 0, 0, ',', '.') }} ·
                                Bal Rp {{ number_format($product->harga_bal ?? 0, 0, ',', '.') }}</li>
                        </ul>
                        @can('write-data')
                        <div class="d-grid gap-2 d-sm-flex flex-sm-row flex-wrap mt-3">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-lg-touch flex-sm-fill">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="flex-sm-fill" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100 btn-lg-touch">Hapus</button>
                            </form>
                        </div>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-center text-muted py-4">
                    @if(request()->filled('q'))
                        Tidak ada produk yang cocok dengan “{{ request('q') }}”.
                    @else
                        Belum ada data produk.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $products->withQueryString()->links() }}
        </div>

        @can('write-data')
        <div class="sticky-actions-mobile d-md-none">
            <a href="{{ route('products.create') }}" class="btn btn-primary w-100 btn-lg">Tambah produk</a>
        </div>
        @endcan
        </div>

    @elseif($tab === 'kategori')
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-stretch align-items-md-center gap-2 mb-3">
            <span class="text-muted small">Grup produk (mis. makanan, minuman)</span>
            @can('write-data')
            <a href="{{ route('categories.create') }}" class="btn btn-primary btn-lg d-md-none w-100 w-md-auto">Tambah kategori</a>
            <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm d-none d-md-inline-flex">Tambah kategori</a>
            @endcan
        </div>

        <div class="d-none d-md-block card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-nowrap mb-0 align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th class="text-end">Jumlah produk</th>
                            <th style="min-width: 11rem;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="fw-medium">{{ $category->nama }}</td>
                                <td class="text-muted small" style="max-width: 320px;">{{ $category->deskripsi ? \Illuminate\Support\Str::limit($category->deskripsi, 80) : '—' }}</td>
                                <td class="text-end">{{ $category->products_count }}</td>
                                <td>
                                    @can('write-data')
                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kategori ini?')">Hapus</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada kategori. Tambah dari sini atau saat <a href="{{ route('products.create') }}">tambah produk</a>.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-md-none vstack gap-2">
            @forelse($categories as $category)
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-semibold">{{ $category->nama }}</div>
                            <span class="badge bg-secondary">Produk: {{ $category->products_count }}</span>
                        </div>
                        @if($category->deskripsi)
                            <p class="text-muted small mb-0 mt-1">{{ \Illuminate\Support\Str::limit($category->deskripsi, 120) }}</p>
                        @endif
                        @can('write-data')
                        <div class="d-grid gap-2 d-sm-flex mt-3">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning btn-lg-touch flex-sm-fill">Edit</a>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="flex-sm-fill" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100 btn-lg-touch">Hapus</button>
                            </form>
                        </div>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-center text-muted py-4">Belum ada kategori. Tambah dari sini atau saat <a href="{{ route('products.create') }}">tambah produk</a>.</p>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $categories->withQueryString()->links() }}
        </div>

    @else
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-stretch align-items-md-center gap-2 mb-3">
            <span class="text-muted small">Pcs, dus, kg, dll.</span>
            @can('write-data')
            <a href="{{ route('satuans.create') }}" class="btn btn-primary btn-lg d-md-none w-100 w-md-auto">Tambah satuan</a>
            <a href="{{ route('satuans.create') }}" class="btn btn-primary btn-sm d-none d-md-inline-flex">Tambah satuan</a>
            @endcan
        </div>

        <div class="d-none d-md-block card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-nowrap mb-0 align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th class="text-end">Jumlah produk</th>
                            <th style="min-width: 11rem;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($satuans as $satuan)
                            <tr>
                                <td class="fw-medium">{{ $satuan->nama }}</td>
                                <td class="text-end">{{ $satuan->products_count }}</td>
                                <td>
                                    @can('write-data')
                                    <a href="{{ route('satuans.edit', $satuan) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('satuans.destroy', $satuan) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus satuan ini?')">Hapus</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Belum ada satuan. Tambah dari sini atau lewat <a href="{{ route('products.create') }}">tambah produk</a>.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-md-none vstack gap-2">
            @forelse($satuans as $satuan)
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-baseline">
                            <div class="fw-semibold">{{ $satuan->nama }}</div>
                            <span class="badge bg-secondary">Produk: {{ $satuan->products_count }}</span>
                        </div>
                        <div class="d-grid gap-2 d-sm-flex mt-3">
                            <a href="{{ route('satuans.edit', $satuan) }}" class="btn btn-warning btn-lg-touch flex-sm-fill">Edit</a>
                            <form action="{{ route('satuans.destroy', $satuan) }}" method="POST" class="flex-sm-fill" onsubmit="return confirm('Hapus satuan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100 btn-lg-touch">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted py-4">Belum ada satuan. Tambah dari sini atau lewat <a href="{{ route('products.create') }}">tambah produk</a>.</p>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $satuans->withQueryString()->links() }}
        </div>
    @endif
@endsection

@if($tab === 'produk')
@push('styles')
    <style>
        #produk-search-suggestions.dropdown-menu {
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            margin-top: 0.125rem;
            z-index: 1050;
            max-height: 280px;
            overflow-y: auto;
            display: none;
        }
        #produk-search-suggestions.dropdown-menu.show {
            display: block;
        }
        .produk-search-suggest-item.active {
            background-color: rgba(13, 110, 253, 0.12);
        }
    </style>
@endpush
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    var form = document.getElementById('form-produk-cari');
    var qInput = document.getElementById('produk-cari-q');
    var readerEl = document.getElementById('produk-cari-qr-reader');
    var camBtn = document.getElementById('produk-cari-camera');
    if (!form || !qInput || !readerEl || !camBtn) return;

    var html5QrCode = null;
    var scanning = false;

    function setCamUi(on) {
        camBtn.setAttribute('aria-expanded', on ? 'true' : 'false');
        var lab = on ? 'Matikan kamera' : 'Scan barcode dengan kamera';
        camBtn.setAttribute('aria-label', lab);
        camBtn.setAttribute('title', lab);
        var hidden = camBtn.querySelector('.visually-hidden');
        if (hidden) hidden.textContent = lab;
    }

    function stopCamera() {
        if (!html5QrCode || !scanning) {
            readerEl.style.display = 'none';
            setCamUi(false);
            return;
        }
        scanning = false;
        html5QrCode.stop().catch(function () {});
        html5QrCode.clear().catch(function () {});
        readerEl.style.display = 'none';
        setCamUi(false);
        qInput.focus();
    }

    setCamUi(false);

    camBtn.addEventListener('click', function () {
        if (scanning) {
            stopCamera();
            return;
        }
        if (typeof Html5Qrcode === 'undefined') {
            alert('Library kamera tidak dimuat. Periksa koneksi internet.');
            return;
        }
        readerEl.style.display = 'block';
        setCamUi(true);
        html5QrCode = new Html5Qrcode('produk-cari-qr-reader');
        scanning = true;
        html5QrCode
            .start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                function (decodedText) {
                    var text = String(decodedText || '').trim();
                    html5QrCode
                        .stop()
                        .then(function () {
                            scanning = false;
                            readerEl.style.display = 'none';
                            setCamUi(false);
                            qInput.value = text;
                            form.submit();
                        })
                        .catch(function () {
                            scanning = false;
                            readerEl.style.display = 'none';
                            setCamUi(false);
                            qInput.value = text;
                            form.submit();
                        });
                },
                function () {}
            )
            .catch(function (err) {
                scanning = false;
                readerEl.style.display = 'none';
                setCamUi(false);
                alert('Tidak bisa membuka kamera: ' + (err && err.message ? err.message : String(err)));
            });
    });
})();

(function () {
    var form = document.getElementById('form-produk-cari');
    var qInput = document.getElementById('produk-cari-q');
    var panel = document.getElementById('produk-search-suggestions');
    if (!form || !qInput || !panel) return;

    var url = qInput.getAttribute('data-search-suggestions-url');
    if (!url) return;

    var debounceTimer = null;
    var searchSeq = 0;
    var lastItems = [];
    var selectedIndex = -1;
    var minLen = 2;

    function setOpen(open) {
        panel.classList.toggle('show', open);
        qInput.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function hideSuggestions() {
        panel.classList.remove('show');
        panel.innerHTML = '';
        lastItems = [];
        selectedIndex = -1;
        qInput.setAttribute('aria-expanded', 'false');
    }

    function highlight() {
        var nodes = panel.querySelectorAll('.produk-search-suggest-item');
        nodes.forEach(function (n, i) {
            n.classList.toggle('active', i === selectedIndex);
        });
    }

    function applySelection(idx) {
        if (idx < 0 || idx >= lastItems.length) return false;
        var row = lastItems[idx];
        if (!row) return false;
        qInput.value = row.nama;
        hideSuggestions();
        form.submit();
        return true;
    }

    function renderSuggestions(items) {
        lastItems = items || [];
        selectedIndex = items && items.length ? 0 : -1;
        panel.innerHTML = '';
        if (!items || !items.length) {
            setOpen(false);
            return;
        }
        items.forEach(function (row, idx) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className =
                'dropdown-item text-start py-2 produk-search-suggest-item' +
                (idx === 0 ? ' active' : '');
            btn.setAttribute('role', 'option');
            btn.dataset.index = String(idx);

            var title = document.createElement('span');
            title.className = 'fw-medium d-block';
            title.textContent = row.nama;

            var meta = document.createElement('small');
            meta.className = 'text-muted d-block';
            var parts = [];
            if (row.kode) parts.push('Kode: ' + row.kode);
            if (row.barcode) parts.push('Barcode: ' + row.barcode);
            parts.push(row.kategori + ' · Stok ' + row.stok);
            meta.textContent = parts.join(' · ');

            btn.appendChild(title);
            btn.appendChild(meta);

            btn.addEventListener('mousedown', function (e) {
                e.preventDefault();
                applySelection(idx);
            });
            panel.appendChild(btn);
        });
        setOpen(true);
    }

    function runFetch(q) {
        if (q.length < minLen) {
            hideSuggestions();
            return;
        }
        var seq = ++searchSeq;
        fetch(url + '?q=' + encodeURIComponent(q), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (seq !== searchSeq) return;
                if (Array.isArray(data)) renderSuggestions(data);
                else hideSuggestions();
            })
            .catch(function () {
                if (seq !== searchSeq) return;
                hideSuggestions();
            });
    }

    qInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var q = qInput.value.trim();
        debounceTimer = setTimeout(function () {
            runFetch(q);
        }, 300);
    });

    qInput.addEventListener('keydown', function (e) {
        var open = panel.classList.contains('show') && lastItems.length > 0;
        if (e.key === 'Escape') {
            if (open) {
                e.preventDefault();
                hideSuggestions();
            }
            return;
        }
        if (!open || !lastItems.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, lastItems.length - 1);
            highlight();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, 0);
            highlight();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            var idx =
                selectedIndex >= 0 && selectedIndex < lastItems.length
                    ? selectedIndex
                    : 0;
            if (!applySelection(idx)) {
                form.submit();
            }
        }
    });

    document.addEventListener('click', function (e) {
        if (!panel.classList.contains('show')) return;
        var t = e.target;
        var wrap = qInput.closest('.position-relative');
        if (wrap && !wrap.contains(t)) {
            hideSuggestions();
        }
    });

    window.addEventListener('pageshow', function () {
        hideSuggestions();
    });
})();
</script>
@endpush
@endif
