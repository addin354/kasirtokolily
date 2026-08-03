@extends('layouts.app')

@section('title', 'Katalog produk — ' . config('app.name'))

@php
    $b = (int) config('pos.stok_menipis_batas', 10);
    $stokLabel = function ($p) use ($b) {
        $n = (int) $p->stok;
        if ($n <= 0) {
            return 'habis';
        }
        if ($b > 0 && $n <= $b) {
            return 'menipis';
        }
        return 'aman';
    };
@endphp

@section('content')
    <div class="mb-3">
        <h1 class="h4 mb-1">Katalog produk</h1>
        <p class="text-muted small mb-0">Lihat stok dan harga (ecer, grosir, bal) — transaksi di kasir.</p>
    </div>

    <form method="get" action="{{ route('katalog.index') }}" class="card shadow-sm border-0 mb-3 app-data-list">
        <div class="card-body">
            <div class="row g-2 g-md-3 align-items-end">
                <div class="col-12 col-md-4 col-lg-4">
                    <label for="q" class="form-label small mb-0">Cari</label>
                    <input
                        type="search"
                        name="q"
                        id="q"
                        value="{{ request('q') }}"
                        class="form-control"
                        placeholder="Nama, kode, barcode…"
                        autocomplete="off"
                    >
                </div>
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <label for="kategori_id" class="form-label small mb-0">Kategori</label>
                    <select name="kategori_id" id="kategori_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected((string) request('kategori_id') === (string) $c->id)>{{ $c->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <div class="form-check mt-md-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="tersedia"
                            id="tersedia"
                            value="1"
                            @checked(request()->boolean('tersedia'))
                        >
                        <label class="form-check-label small" for="tersedia">Stok tersedia saja</label>
                    </div>
                </div>
                <div class="col-12 col-md-2 col-lg-3 d-grid d-sm-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-lg-touch">Terapkan</button>
                    <a href="{{ route('katalog.index') }}" class="btn btn-outline-secondary btn-lg-touch">Reset</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0 app-data-list">
        <div class="card-body p-0">
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-hover mb-0 align-middle small">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">Kode / nama</th>
                        <th scope="col">Kategori</th>
                        <th scope="col" class="text-nowrap">Satuan</th>
                        <th scope="col" class="text-end text-nowrap">Eceran</th>
                        <th scope="col" class="text-end text-nowrap">Grosir</th>
                        <th scope="col" class="text-end text-nowrap">Bal</th>
                        <th scope="col" class="text-end">Stok</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $product)
                        @php $sr = $stokLabel($product); @endphp
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $product->nama }}</div>
                                <div class="text-muted" style="font-size:0.8rem;">{{ $product->kode ?? '—' }}</div>
                            </td>
                            <td>{{ $product->category?->nama ?? '—' }}</td>
                            <td>{{ $product->satuanModel?->nama ?? '—' }}</td>
                            <td class="text-end text-nowrap">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                            <td class="text-end text-nowrap">Rp {{ number_format($product->harga_grosir, 0, ',', '.') }}</td>
                            <td class="text-end text-nowrap">Rp {{ number_format($product->harga_bal, 0, ',', '.') }}</td>
                            <td class="text-end text-nowrap">
                                <span class="fw-medium">{{ (int) $product->stok }}</span>
                                <span
                                    class="badge ms-1
                                        @if($sr === 'habis') text-bg-danger
                                        @elseif($sr === 'menipis') text-bg-warning
                                        @else text-bg-success
                                        @endif"
                                    style="font-size:0.65rem;"
                                >@if($sr === 'habis') Habis @elseif($sr === 'menipis') Menipis @else Tersedia @endif</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada produk yang cocok. Ubah pencarian atau filter.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-lg-none p-2 p-md-3 vstack gap-2">
                @forelse($products as $product)
                    @php $sr = $stokLabel($product); @endphp
                    <div class="app-card border rounded p-3 bg-white">
                        <div class="fw-semibold">{{ $product->nama }}</div>
                        <div class="small text-muted mb-2">{{ $product->kode ?? '—' }} · {{ $product->category?->nama ?? '—' }} · {{ $product->satuanModel?->nama ?? '—' }}</div>
                        <div class="vstack gap-1 small">
                            <div class="d-flex justify-content-between"><span class="text-muted">Eceran</span> <span>Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</span></div>
                            <div class="d-flex justify-content-between"><span class="text-muted">Grosir</span> <span>Rp {{ number_format($product->harga_grosir, 0, ',', '.') }}</span></div>
                            <div class="d-flex justify-content-between"><span class="text-muted">Bal</span> <span>Rp {{ number_format($product->harga_bal, 0, ',', '.') }}</span></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Stok</span>
                                <span>
                                    <span class="fw-medium">{{ (int) $product->stok }}</span>
                                    <span
                                        class="badge ms-1
                                            @if($sr === 'habis') text-bg-danger
                                            @elseif($sr === 'menipis') text-bg-warning
                                            @else text-bg-success
                                            @endif"
                                        style="font-size:0.65rem;"
                                    >@if($sr === 'habis') Habis @elseif($sr === 'menipis') Menipis @else Tersedia @endif</span>
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted py-3 mb-0">Tidak ada produk yang cocok.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $products->onEachSide(1)->links() }}
    </div>
@endsection
