@extends('layouts.app')

@section('title', 'Daftar Barang Perlu Restok — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Barang Perlu Restok</h1>
            <p class="text-muted small mb-0">Daftar seluruh produk dengan stok saat ini kurang dari atau sama dengan batas minimum stok.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Dashboard</a>
            <a href="{{ route('owner.stok') }}" class="btn btn-primary btn-sm">Monitoring Stok</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning-subtle text-warning-emphasis fw-semibold py-3 border-0">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Daftar Produk Harus Segera Dibeli
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover text-nowrap mb-0 align-middle">
                    <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th class="ps-3">Nama Produk</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th class="text-end" style="width: 110px;">Stok Saat Ini</th>
                        <th class="text-end" style="width: 100px;">Minimum</th>
                        <th class="text-center" style="width: 170px;">Rekomendasi Order</th>
                        <th class="ps-3">Supplier Terakhir</th>
                        <th class="text-center pe-3" style="width: 140px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $p)
                        @php
                            $selisih = max(0, $p->stok_minimum - $p->stok);
                            $rekomendasiOrder = $p->rekomendasiJumlahOrder();
                            $avgHarian = $p->rataRataPenjualanHarian(30);
                        @endphp
                        <tr>
                            <td class="ps-3 fw-semibold text-dark">
                                {{ $p->nama }}
                                <div class="text-muted small font-monospace">Kode: {{ $p->kode }}</div>
                            </td>
                            <td>{{ $p->category?->nama ?? '—' }}</td>
                            <td>{{ $p->satuanModel?->nama ?? $p->satuan ?? '—' }}</td>
                            <td class="text-end fw-bold text-warning">{{ (int) $p->stok }}</td>
                            <td class="text-end text-muted">{{ (int) $p->stok_minimum }}</td>
                            <td class="text-center">
                                <span class="badge bg-success fs-6 px-2 py-1 shadow-sm">+ {{ $rekomendasiOrder }} {{ $p->satuanModel?->nama ?? 'pcs' }}</span>
                                <div class="text-muted small" style="font-size: 0.72rem;">Selisih: {{ $selisih }} · Rata-rata: {{ $avgHarian }}/hari</div>
                            </td>
                            <td class="ps-3 text-secondary">{{ $p->supplierTerakhir() ?? '—' }}</td>
                            <td class="text-center pe-3">
                                <a href="{{ route('pembelian.create', ['produk_id' => $p->id, 'qty' => $rekomendasiOrder]) }}" class="btn btn-sm btn-success fw-bold d-inline-flex align-items-center gap-1 shadow-sm" title="Buat nota pembelian dengan rekomendasi order {{ $rekomendasiOrder }} pcs">
                                    <i class="bi bi-cart-plus-fill"></i> Beli ({{ $rekomendasiOrder }})
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Semua produk saat ini dalam kondisi aman (stok di atas minimum).</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
