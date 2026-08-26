@extends('layouts.app')

@section('title', 'Inventory — Stok masuk — ' . config('app.name'))

@push('styles')
    <style>
        #stok-produk-suggestions {
            z-index: 1050;
            max-height: 280px;
            overflow-y: auto;
        }
        #stok-qr-reader video {
            max-width: 100%;
        }
        #stok-camera-toggle.stok-scan-btn {
            min-width: 2.75rem;
            min-height: calc(2.25rem + 2px);
            padding-left: 0.65rem;
            padding-right: 0.65rem;
        }
        #stok-camera-toggle .stok-scan-icon {
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Inventory &amp; stok masuk</h1>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-primary btn-sm">Kelola supplier</a>
    </div>

    @if ($suppliers->isEmpty())
        <div class="alert alert-warning">Belum ada supplier. <a href="{{ route('suppliers.create') }}">Tambah supplier</a> sebelum mencatat stok masuk.</div>
    @endif

    <!-- Multi-Tab Menu -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white p-0 border-bottom">
            <ul class="nav nav-tabs border-0" id="inventoryTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('stok-masuk.index', ['tab' => 'masuk']) }}">
                        <i class="bi bi-box-arrow-in-down me-1"></i> A. Input Stok Masuk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('stok-masuk.index', ['tab' => 'opname']) }}">
                        <i class="bi bi-check2-square me-1"></i> B. Stock Opname
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('stok-masuk.index', ['tab' => 'penyesuaian']) }}">
                        <i class="bi bi-sliders me-1"></i> C. Penyesuaian Stok
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('stok-masuk.index', ['tab' => 'riwayat']) }}">
                        <i class="bi bi-clock-history me-1"></i> D. Riwayat Perubahan (Kartu Stok)
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Input stok masuk (restok)</div>
        <div class="card-body">
            <form id="stok-masuk-form" action="{{ route('stok-masuk.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="stok-produk-search" class="form-label">Produk</label>
                        <input
                            type="hidden"
                            name="produk_id"
                            id="produk_id"
                            value="{{ old('produk_id') }}"
                            class="@error('produk_id') is-invalid @enderror"
                        >
                        <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch">
                            <div class="flex-grow-1 position-relative">
                                <input
                                    type="text"
                                    id="stok-produk-search"
                                    class="form-control"
                                    autocomplete="off"
                                    placeholder="Scan barcode atau ketik nama / kode…"
                                    aria-autocomplete="list"
                                    aria-controls="stok-produk-suggestions"
                                    aria-expanded="false"
                                    data-search-url="{{ route('stok-masuk.search-product') }}"
                                    @unless(old('produk_id')) autofocus @endunless
                                >
                                <div
                                    id="stok-produk-suggestions"
                                    class="list-group shadow-sm position-absolute w-100 mt-1"
                                    style="display: none;"
                                    hidden
                                    role="listbox"
                                ></div>
                            </div>
                            <button
                                type="button"
                                class="btn btn-outline-secondary flex-shrink-0 stok-scan-btn"
                                id="stok-camera-toggle"
                                aria-expanded="false"
                                aria-label="Buka kamera untuk scan barcode"
                                title="Buka kamera untuk scan barcode"
                            >
                                {{-- Ikon scan (bingkai + area baca) — label teks di span tersembunyi & diperbarui oleh JS --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="stok-scan-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 4h4V2H4a2 2 0 0 0-2 2v4h2V4zm0 12H2v4a2 2 0 0 0 2 2h4v-2H4v-4zm16-4h2V4a2 2 0 0 0-2-2h-4v2h4v8zm0 8h-4v2h4a2 2 0 0 0 2-2v-4h-2v4zM8 8h8v8H8V8zm2 2v4h4v-4h-4z"/>
                                </svg>
                                <span class="visually-hidden" id="stok-camera-toggle-label">Buka kamera untuk scan barcode</span>
                            </button>
                        </div>
                        @error('produk_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="stok-produk-chip" class="mt-2 small">
                            @if ($oldProduct)
                                <div class="rounded border bg-light px-2 py-1 d-inline-flex align-items-center gap-2 flex-wrap">
                                    <span><strong>{{ $oldProduct->nama }}</strong> ({{ $oldProduct->kode }}) — stok saat ini: {{ $oldProduct->stok }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="stok-produk-ubah">Ubah</button>
                                </div>
                            @else
                                <span class="text-muted">Belum ada produk dipilih.</span>
                            @endif
                        </div>
                        <div id="stok-qr-reader" class="mt-2 rounded border bg-dark overflow-hidden" style="display: none; max-width: 400px;"></div>
                        <div class="form-text small mt-1">Ketik untuk saran, Enter untuk cari, atau tombol <strong>ikon scan</strong> / scanner USB.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select
                            id="supplier_id"
                            name="supplier_id"
                            class="form-select @error('supplier_id') is-invalid @enderror"
                            required
                        >
                            <option value="">-- Pilih supplier --</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}" @selected(old('supplier_id') == $sup->id)>
                                    {{ $sup->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input
                            type="number"
                            id="jumlah"
                            name="jumlah"
                            value="{{ old('jumlah', 1) }}"
                            min="1"
                            class="form-control @error('jumlah') is-invalid @enderror"
                            required
                        >
                        @error('jumlah')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="harga_beli" class="form-label">Harga beli / unit</label>
                        <input
                            type="number"
                            id="harga_beli"
                            name="harga_beli"
                            value="{{ old('harga_beli', 0) }}"
                            min="0"
                            step="0.01"
                            class="form-control @error('harga_beli') is-invalid @enderror"
                            required
                        >
                        @error('harga_beli')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text small">Akan memperbarui harga beli produk (harga terakhir).</div>
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input
                            type="datetime-local"
                            id="tanggal"
                            name="tanggal"
                            value="{{ old('tanggal', now()->format('Y-m-d\TH:i')) }}"
                            class="form-control @error('tanggal') is-invalid @enderror"
                            required
                        >
                        @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="keterangan" class="form-label">Keterangan <span class="text-muted">(opsional)</span></label>
                        <input
                            type="text"
                            id="keterangan"
                            name="keterangan"
                            value="{{ old('keterangan') }}"
                            maxlength="500"
                            class="form-control @error('keterangan') is-invalid @enderror"
                            placeholder="No. faktur, keterangan tambahan"
                        >
                        @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary" {{ $suppliers->isEmpty() ? 'disabled' : '' }}>Simpan stok masuk</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Riwayat stok masuk</div>
        <div class="card-body p-0">
            @if ($records->isEmpty())
                <p class="text-muted p-3 mb-0">Belum ada riwayat.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-nowrap mb-0 align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Supplier</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end">Harga beli</th>
                            <th>Keterangan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($records as $row)
                            <tr>
                                <td class="small">{{ $row->tanggal->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="fw-medium">{{ $row->product?->nama ?? '-' }}</span>
                                    @if ($row->product)
                                        <div class="small text-muted">{{ $row->product->kode }}</div>
                                    @endif
                                </td>
                                <td class="small">{{ $row->supplier?->nama_supplier ?? '—' }}</td>
                                <td class="text-end text-success">+{{ number_format($row->jumlah, 0, ',', '.') }}</td>
                                <td class="text-end small">Rp {{ number_format($row->harga_beli ?? 0, 0, ',', '.') }}</td>
                                <td class="small text-muted">{{ $row->keterangan ?: '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Riwayat stok keluar (penjualan)</div>
        <div class="card-body p-0">
            @if ($stokKeluar->isEmpty())
                <p class="text-muted p-3 mb-0">Belum ada penjualan.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-nowrap mb-0 align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Transaksi</th>
                            <th>Produk</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Harga jual</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($stokKeluar as $d)
                            <tr>
                                <td class="small">{{ $d->transaksi?->tanggal?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="small">#{{ $d->transaksi_id }}</td>
                                <td>
                                    <span class="fw-medium">{{ $d->product?->nama ?? '—' }}</span>
                                    <div class="small text-muted">{{ \App\Models\Product::labelJenisHarga($d->jenis_harga ?? 'eceran') }}</div>
                                </td>
                                <td class="text-end text-danger">-{{ number_format($d->qty, 0, ',', '.') }}</td>
                                <td class="text-end small">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                                <td class="text-end small">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $stokKeluar->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    var form = document.getElementById('stok-masuk-form');
    var hiddenId = document.getElementById('produk_id');
    var searchInput = document.getElementById('stok-produk-search');
    var suggestions = document.getElementById('stok-produk-suggestions');
    var chip = document.getElementById('stok-produk-chip');
    if (!form || !hiddenId || !searchInput || !suggestions || !chip) return;

    var searchUrl = searchInput.getAttribute('data-search-url');
    var debounceTimer = null;
    var lastItems = [];
    var selectedIdx = -1;

    function escapeHtml(t) {
        var d = document.createElement('div');
        d.textContent = String(t ?? '');
        return d.innerHTML;
    }

    function formatChip(row) {
        return (
            '<div class="rounded border bg-light px-2 py-1 d-inline-flex align-items-center gap-2 flex-wrap">' +
                '<span><strong>' + escapeHtml(row.nama) + '</strong> (' + escapeHtml(row.kode || '—') + ') — stok saat ini: ' + escapeHtml(row.stok) +
                '</span>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary" id="stok-produk-ubah">Ubah</button>' +
            '</div>'
        );
    }

    function showChip(row) {
        chip.innerHTML = formatChip(row);
        hiddenId.value = String(row.id);
    }

    function clearProduct() {
        hiddenId.value = '';
        chip.innerHTML = '<span class="text-muted">Belum ada produk dipilih.</span>';
        searchInput.focus();
    }

    function hideSuggestions() {
        suggestions.style.display = 'none';
        suggestions.hidden = true;
        suggestions.innerHTML = '';
        searchInput.setAttribute('aria-expanded', 'false');
        lastItems = [];
        selectedIdx = -1;
    }

    function showSuggestions() {
        suggestions.style.display = 'block';
        suggestions.hidden = false;
        searchInput.setAttribute('aria-expanded', 'true');
    }

    function renderItems(items) {
        suggestions.innerHTML = '';
        lastItems = items;
        selectedIdx = items.length ? 0 : -1;
        if (!items.length) {
            hideSuggestions();
            return;
        }
        items.forEach(function (row, idx) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className =
                'list-group-item list-group-item-action text-start py-2' +
                (idx === 0 ? ' active' : '');
            btn.setAttribute('role', 'option');

            var title = document.createElement('span');
            title.className = 'fw-medium d-block';
            title.textContent = row.nama;

            var meta = document.createElement('small');
            meta.className = 'text-muted';
            meta.textContent = [row.kode ? 'Kode: ' + row.kode : '', row.barcode ? 'Barcode: ' + row.barcode : '', 'Stok: ' + row.stok]
                .filter(Boolean)
                .join(' · ');

            btn.appendChild(title);
            btn.appendChild(meta);

            btn.addEventListener('mousedown', function (e) {
                e.preventDefault();
            });
            btn.addEventListener('click', function () {
                pickProduct(row);
            });

            suggestions.appendChild(btn);
        });
        showSuggestions();
    }

    function pickProduct(row) {
        hideSuggestions();
        showChip(row);
        searchInput.value = '';
    }

    function fetchSuggestions(q, onDone) {
        var sep = searchUrl.indexOf('?') === -1 ? '?' : '&';
        var url = searchUrl + sep + 'q=' + encodeURIComponent(q);

        fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('search failed');
                }
                return r.json();
            })
            .then(function (data) {
                onDone(Array.isArray(data) ? data : []);
            })
            .catch(function () {
                onDone([]);
            });
    }

    function commitSearchQuery(raw) {
        var v = String(raw == null ? '' : raw).trim();
        if (v.length < 2) {
            alert('Kode terlalu pendek. Scan atau ketik minimal 2 karakter.');
            return;
        }
        searchInput.value = v;
        fetchSuggestions(v, function (items) {
            if (items.length === 1) {
                pickProduct(items[0]);
            } else if (items.length > 1) {
                renderItems(items);
            } else {
                alert('Produk tidak ditemukan.');
            }
        });
    }

    function highlightSuggestions() {
        var nodes = suggestions.querySelectorAll('[role="option"]');
        nodes.forEach(function (n, i) {
            if (i === selectedIdx) n.classList.add('active');
            else n.classList.remove('active');
        });
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var q = searchInput.value.trim();
        debounceTimer = setTimeout(function () {
            if (q.length < 2) {
                hideSuggestions();
                return;
            }
            fetchSuggestions(q, renderItems);
        }, 300);
    });

    searchInput.addEventListener('keydown', function (e) {
        var open = suggestions.style.display !== 'none' && lastItems.length > 0;

        if (e.key === 'ArrowDown') {
            if (!open) return;
            e.preventDefault();
            selectedIdx = Math.min(selectedIdx + 1, lastItems.length - 1);
            highlightSuggestions();
        } else if (e.key === 'ArrowUp') {
            if (!open) return;
            e.preventDefault();
            selectedIdx = Math.max(selectedIdx - 1, 0);
            highlightSuggestions();
        } else if (e.key === 'Escape') {
            hideSuggestions();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (open) {
                if (selectedIdx >= 0 && lastItems[selectedIdx]) {
                    pickProduct(lastItems[selectedIdx]);
                }
                return;
            }
            commitSearchQuery(searchInput.value);
        }
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
            hideSuggestions();
        }
    });

    chip.addEventListener('click', function (e) {
        var t = e.target;
        if (t && t.id === 'stok-produk-ubah') {
            clearProduct();
        }
    });

    form.addEventListener('submit', function (e) {
        if (!hiddenId.value) {
            e.preventDefault();
            alert('Pilih produk dari pencarian atau scan barcode dahulu.');
        }
    });

    var readerEl = document.getElementById('stok-qr-reader');
    var camBtn = document.getElementById('stok-camera-toggle');
    var html5QrCode = null;
    var scanning = false;
    var stokCamLabelEl = document.getElementById('stok-camera-toggle-label');

    function setStokCamToggleUi(cameraOn) {
        if (!camBtn) return;
        var offLab = 'Buka kamera untuk scan barcode';
        var onLab = 'Matikan kamera';
        var lab = cameraOn ? onLab : offLab;
        camBtn.setAttribute('aria-label', lab);
        camBtn.setAttribute('title', lab);
        if (stokCamLabelEl) stokCamLabelEl.textContent = lab;
    }

    function stopStokCamera() {
        if (!html5QrCode || !scanning) {
            if (readerEl) readerEl.style.display = 'none';
            if (camBtn) {
                camBtn.setAttribute('aria-expanded', 'false');
                setStokCamToggleUi(false);
            }
            return;
        }
        scanning = false;
        html5QrCode.stop().catch(function () {});
        html5QrCode.clear().catch(function () {});
        if (readerEl) readerEl.style.display = 'none';
        if (camBtn) {
            camBtn.setAttribute('aria-expanded', 'false');
            setStokCamToggleUi(false);
        }
        searchInput.focus();
    }

    if (camBtn && readerEl) {
        setStokCamToggleUi(false);
        camBtn.addEventListener('click', function () {
            if (scanning) {
                stopStokCamera();
                return;
            }
            if (typeof Html5Qrcode === 'undefined') {
                alert('Library kamera tidak dimuat. Periksa koneksi internet.');
                return;
            }
            readerEl.style.display = 'block';
            camBtn.setAttribute('aria-expanded', 'true');
            setStokCamToggleUi(true);

            if (!readerEl.querySelector('.scanner-laser-overlay')) {
                var laserLine = document.createElement('div');
                laserLine.className = 'scanner-laser-overlay';
                readerEl.appendChild(laserLine);
            }

            var barcodeFormats = (typeof Html5QrcodeSupportedFormats !== 'undefined') ? [
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.ITF,
                Html5QrcodeSupportedFormats.CODE_93
            ] : undefined;

            html5QrCode = new Html5Qrcode('stok-qr-reader', barcodeFormats ? { formatsToSupport: barcodeFormats } : undefined);
            scanning = true;
            html5QrCode
                .start(
                    { facingMode: 'environment' },
                    {
                        fps: 15,
                        qrbox: function(w, h) {
                            var minEdge = Math.min(w, h);
                            return {
                                width: Math.floor(minEdge * 0.85),
                                height: Math.floor(minEdge * 0.50)
                            };
                        },
                        videoConstraints: {
                            facingMode: { ideal: 'environment' },
                            focusMode: { ideal: 'continuous' }
                        }
                    },
                    function (decodedText) {
                        var text = String(decodedText).trim();
                        html5QrCode
                            .stop()
                            .then(function () {
                                scanning = false;
                                readerEl.style.display = 'none';
                                camBtn.setAttribute('aria-expanded', 'false');
                                setStokCamToggleUi(false);
                                commitSearchQuery(text);
                            })
                            .catch(function () {
                                scanning = false;
                                readerEl.style.display = 'none';
                                camBtn.setAttribute('aria-expanded', 'false');
                                setStokCamToggleUi(false);
                                commitSearchQuery(text);
                            });
                    },
                    function () {}
                )
                .catch(function (err) {
                    scanning = false;
                    readerEl.style.display = 'none';
                    camBtn.setAttribute('aria-expanded', 'false');
                    setStokCamToggleUi(false);
                    alert('Tidak bisa membuka kamera: ' + (err && err.message ? err.message : String(err)));
                });
        });
    }
})();
</script>
@endpush
