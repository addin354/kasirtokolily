@extends('layouts.app')

@section('title', 'Edit Produk — ' . config('app.name'))

@section('content')
    <h1 class="h4 mb-2">Edit Produk</h1>
    <p class="text-muted small mb-3">Kategori &amp; satuan: dropdown, atau <strong>+ Baru</strong> via popup.</p>

    <div class="app-mobile-pad-bottom">
    <div class="card shadow-sm">
        <div class="card-body">
            <form id="formProdukEdit" action="{{ route('products.update', $product) }}" method="POST" class="vstack gap-3">
                @csrf
                @method('PUT')

                <div class="mb-0">
                    <label for="nama" class="form-label">Nama</label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="{{ old('nama', $product->nama) }}"
                        class="form-control form-control-lg @error('nama') is-invalid @enderror"
                    >
                    @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label for="barcode" class="form-label">Barcode</label>
                    <input
                        type="text"
                        id="barcode"
                        name="barcode"
                        value="{{ old('barcode', $product->barcode) }}"
                        class="form-control form-control-lg @error('barcode') is-invalid @enderror"
                        autocomplete="off"
                        placeholder="Kode unik untuk scan di kasir"
                    >
                    @error('barcode')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <x-barcode-camera-field input-id="barcode" />
                </div>

                @include('products.partials.kategori-satuan-fields', [
                    'categories' => $categories,
                    'satuans' => $satuans,
                    'product' => $product,
                ])

                <div class="mb-0">
                    <label for="harga_beli" class="form-label">Harga beli (modal)</label>
                    <input
                        type="number"
                        id="harga_beli"
                        name="harga_beli"
                        value="{{ old('harga_beli', $product->harga_beli) }}"
                        class="form-control form-control-lg @error('harga_beli') is-invalid @enderror"
                        min="0"
                        step="0.01"
                    >
                    @error('harga_beli')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Harga pokok / modal pembelian barang.</div>
                </div>

                <div class="row g-3 mb-0">
                    <div class="col-12 col-md-4">
                        <label for="harga_jual" class="form-label">Harga eceran</label>
                        <input
                            type="number"
                            id="harga_jual"
                            name="harga_jual"
                            value="{{ old('harga_jual', $product->harga_jual) }}"
                            class="form-control form-control-lg @error('harga_jual') is-invalid @enderror"
                            min="0"
                            step="0.01"
                        >
                        @error('harga_jual')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="harga_grosir" class="form-label">Harga grosir</label>
                        <input
                            type="number"
                            id="harga_grosir"
                            name="harga_grosir"
                            value="{{ old('harga_grosir', $product->harga_grosir ?? 0) }}"
                            class="form-control form-control-lg @error('harga_grosir') is-invalid @enderror"
                            min="0"
                            step="0.01"
                        >
                        @error('harga_grosir')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="harga_bal" class="form-label">Harga bal</label>
                        <input
                            type="number"
                            id="harga_bal"
                            name="harga_bal"
                            value="{{ old('harga_bal', $product->harga_bal ?? 0) }}"
                            class="form-control form-control-lg @error('harga_bal') is-invalid @enderror"
                            min="0"
                            step="0.01"
                        >
                        @error('harga_bal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-0">
                    <label for="isi_per_bal" class="form-label">Isi per bal (pcs)</label>
                    <input
                        type="number"
                        id="isi_per_bal"
                        name="isi_per_bal"
                        value="{{ old('isi_per_bal', $product->isi_per_bal) }}"
                        class="form-control form-control-lg @error('isi_per_bal') is-invalid @enderror"
                        min="1"
                        step="1"
                        placeholder="Opsional, mis. 40"
                    >
                    @error('isi_per_bal')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Untuk penjualan <strong>Bal</strong> di kasir: 1 bal = berapa pcs.</div>
                </div>
                <p class="form-text small mb-3">Di kasir: Eceran, Grosir, atau Bal.</p>

                <div class="row g-3 mb-0">
                    <div class="col-12 col-md-6">
                        <label for="stok" class="form-label">Stok</label>
                        <input
                            type="number"
                            id="stok"
                            name="stok"
                            value="{{ old('stok', $product->stok) }}"
                            class="form-control form-control-lg @error('stok') is-invalid @enderror"
                            min="0"
                        >
                        @error('stok')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="stok_minimum" class="form-label">Stok Minimum (Restok)</label>
                        <input
                            type="number"
                            id="stok_minimum"
                            name="stok_minimum"
                            value="{{ old('stok_minimum', $product->stok_minimum ?? 10) }}"
                            class="form-control form-control-lg @error('stok_minimum') is-invalid @enderror"
                            min="0"
                        >
                        @error('stok_minimum')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-none d-md-flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="btn btn-primary btn-lg">Update</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <div class="sticky-actions-mobile d-md-none">
        <div class="d-grid gap-2">
            <button type="submit" form="formProdukEdit" class="btn btn-primary btn-lg w-100">Update</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg w-100">Kembali</a>
        </div>
    </div>
    </div>
@endsection
