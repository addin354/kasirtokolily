@extends('layouts.app')

@section('title', 'Tambah Pembelian — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Tambah Pembelian</h1>
        <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pembelian.store') }}" method="POST" id="pembelian-form">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header fw-semibold">Bagian Header</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-2">
                        <label for="nomor_pembelian" class="form-label small mb-1">Nomor Pembelian</label>
                        <input type="text" id="nomor_pembelian" class="form-control form-control-sm bg-light" value="{{ $predictedNomor }}" disabled readonly>
                        <div class="form-text small" style="font-size: 0.7rem;">Otomatis saat disimpan.</div>
                    </div>

                    <div class="col-12 col-md-2">
                        <label for="tanggal" class="form-label small mb-1">Tanggal Pembelian</label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" class="form-control form-control-sm" required>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="supplier_id_select" class="form-label small mb-1">Supplier</label>
                        <select name="supplier_id" id="supplier_id_select" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}" @selected(old('supplier_id') == $sup->id)>
                                    {{ $sup->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label for="metode_pembayaran" class="form-label small mb-1">Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-select form-select-sm" required>
                            <option value="Cash" @selected(old('metode_pembayaran', 'Cash') === 'Cash')>Cash</option>
                            <option value="Transfer Bank" @selected(old('metode_pembayaran') === 'Transfer Bank')>Transfer Bank</option>
                            <option value="Tempo" @selected(old('metode_pembayaran') === 'Tempo')>Tempo</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="keterangan" class="form-label small mb-1">Keterangan</label>
                        <input type="text" name="keterangan" id="keterangan" value="{{ old('keterangan') }}" class="form-control form-control-sm" placeholder="No. faktur, catatan, dll.">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                <span>Bagian Detail</span>
                <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1" id="btn-add-row">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                    </svg>
                    Tambah Baris
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="items-table">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 320px;">Cari Produk</th>
                            <th>Nama Produk</th>
                            <th style="width: 120px;" class="text-end">Qty</th>
                            <th style="width: 180px;" class="text-end">Harga Beli (Modal)</th>
                            <th style="width: 180px;" class="text-end">Subtotal</th>
                            <th style="width: 80px;" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody id="items-container">
                        <!-- Rows will be injected here by JS -->
                        </tbody>
                        <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end">Grand Total</td>
                            <td class="text-end text-success" id="grand-total-display">Rp 0</td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-primary px-4">Simpan Pembelian</button>
        </div>
    </form>

    <!-- Datalist untuk pencarian produk -->
    <datalist id="products-datalist">
        @foreach ($products as $prod)
            <option value="[{{ $prod->kode }}] {{ $prod->nama }} {{ $prod->barcode ? '('.$prod->barcode.')' : '' }}" 
                    data-id="{{ $prod->id }}" 
                    data-name="{{ $prod->nama }}" 
                    data-price="{{ (float) $prod->harga_beli }}"></option>
        @endforeach
    </datalist>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('items-container');
            const btnAddRow = document.getElementById('btn-add-row');
            const grandTotalDisplay = document.getElementById('grand-total-display');
            let rowIndex = 0;

            function formatRupiah(num) {
                return 'Rp ' + Number(num).toLocaleString('id-ID');
            }

            function addRow(data = null) {
                const tr = document.createElement('tr');
                tr.setAttribute('data-index', rowIndex);

                const currentIdx = rowIndex;

                tr.innerHTML = `
                    <td>
                        <input type="text" class="form-control form-control-sm product-search-input" list="products-datalist" placeholder="Scan barcode / ketik kode / nama..." required autocomplete="off">
                        <input type="hidden" name="items[${currentIdx}][produk_id]" class="product-id-input" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm product-name-display bg-light" disabled readonly placeholder="Nama produk terisi otomatis...">
                    </td>
                    <td>
                        <input type="number" name="items[${currentIdx}][qty]" class="form-control form-control-sm text-end qty-input" value="1" min="1" required>
                    </td>
                    <td>
                        <input type="number" name="items[${currentIdx}][harga_beli]" class="form-control form-control-sm text-end harga-input" value="0" min="0" step="0.01" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm text-end subtotal-display bg-light" disabled readonly value="Rp 0">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row">Hapus</button>
                    </td>
                `;

                container.appendChild(tr);

                const searchInput = tr.querySelector('.product-search-input');
                const idInput = tr.querySelector('.product-id-input');
                const nameDisplay = tr.querySelector('.product-name-display');
                const qtyInput = tr.querySelector('.qty-input');
                const hargaInput = tr.querySelector('.harga-input');
                const subtotalDisplay = tr.querySelector('.subtotal-display');
                const btnRemove = tr.querySelector('.btn-remove-row');

                // Datalist selection event handler
                searchInput.addEventListener('input', function() {
                    const val = this.value;
                    const datalist = document.getElementById('products-datalist');
                    const option = Array.from(datalist.options).find(opt => opt.value === val);

                    if (option) {
                        const id = option.getAttribute('data-id');
                        const name = option.getAttribute('data-name');
                        const price = option.getAttribute('data-price');

                        idInput.value = id;
                        nameDisplay.value = name;
                        hargaInput.value = price;
                        
                        calculateRow(tr);
                    } else {
                        // Clear if user changes input to something invalid
                        idInput.value = '';
                        nameDisplay.value = '';
                        calculateRow(tr);
                    }
                });

                qtyInput.addEventListener('input', () => calculateRow(tr));
                hargaInput.addEventListener('input', () => calculateRow(tr));

                btnRemove.addEventListener('click', function() {
                    tr.remove();
                    calculateGrandTotal();
                    ensureAtLeastOneRow();
                });

                if (data) {
                    searchInput.value = data.search_val || '';
                    idInput.value = data.produk_id || '';
                    nameDisplay.value = data.nama || '';
                    qtyInput.value = data.qty || 1;
                    hargaInput.value = data.harga_beli || 0;
                    calculateRow(tr);
                }

                rowIndex++;
                calculateGrandTotal();
            }

            function calculateRow(row) {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.harga-input').value) || 0;
                const subtotal = qty * price;
                row.querySelector('.subtotal-display').value = formatRupiah(subtotal);
                calculateGrandTotal();
            }

            function calculateGrandTotal() {
                let grandTotal = 0;
                container.querySelectorAll('tr').forEach(row => {
                    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                    const price = parseFloat(row.querySelector('.harga-input').value) || 0;
                    grandTotal += qty * price;
                });
                grandTotalDisplay.textContent = formatRupiah(grandTotal);
            }

            function ensureAtLeastOneRow() {
                if (container.querySelectorAll('tr').length === 0) {
                    addRow();
                }
            }

            btnAddRow.addEventListener('click', () => addRow());

            // Initialize with one row
            @if (old('items'))
                @foreach (old('items') as $oldItem)
                    @php
                        $prod = \App\Models\Product::find($oldItem['produk_id']);
                        $searchVal = $prod ? '[' . $prod->kode . '] ' . $prod->nama . ($prod->barcode ? ' (' . $prod->barcode . ')' : '') : '';
                        $prodName = $prod ? $prod->nama : '';
                    @endphp
                    addRow({
                        produk_id: "{{ $oldItem['produk_id'] }}",
                        search_val: "{{ $searchVal }}",
                        nama: "{{ $prodName }}",
                        qty: "{{ $oldItem['qty'] }}",
                        harga_beli: "{{ $oldItem['harga_beli'] }}"
                    });
                @endforeach
            @elseif (isset($prefilledProduct))
                @php
                    $searchVal = '[' . $prefilledProduct->kode . '] ' . $prefilledProduct->nama . ($prefilledProduct->barcode ? ' (' . $prefilledProduct->barcode . ')' : '');
                @endphp
                addRow({
                    produk_id: "{{ $prefilledProduct->id }}",
                    search_val: "{{ $searchVal }}",
                    nama: "{{ $prefilledProduct->nama }}",
                    qty: 1,
                    harga_beli: "{{ $prefilledProduct->harga_beli }}"
                });
            @else
                addRow();
            @endif
        });
    </script>
@endpush
