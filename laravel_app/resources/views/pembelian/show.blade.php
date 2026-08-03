@extends('layouts.app')

@section('title', 'Detail Pembelian ' . $pembelian->nomor_pembelian . ' — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Detail Pembelian: {{ $pembelian->nomor_pembelian }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            <a href="{{ route('pembelian.cetak', $pembelian) }}" target="_blank" class="btn btn-primary btn-sm">Cetak</a>
        </div>
    </div>

    <div class="row g-3">
        <!-- Informasi Header -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-semibold">Informasi Pembelian</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Nomor Pembelian</td>
                            <td class="fw-semibold">: {{ $pembelian->nomor_pembelian }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>: {{ $pembelian->tanggal->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Supplier</td>
                            <td class="fw-semibold">: {{ $pembelian->supplier?->nama_supplier ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Metode Pembayaran</td>
                            <td class="fw-semibold">: {{ $pembelian->metode_pembayaran ?? 'Cash' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kasir / User</td>
                            <td>: {{ $pembelian->user?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Keterangan</td>
                            <td>: {{ $pembelian->keterangan ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Daftar Item Detail -->
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Daftar Produk yang Dibeli</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th>Nama Produk</th>
                                <th style="width: 120px;" class="text-end">Qty</th>
                                <th style="width: 180px;" class="text-end">Harga Beli</th>
                                <th style="width: 180px;" class="text-end">Subtotal</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($pembelian->detailPembelians as $idx => $d)
                                <tr>
                                    <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $d->product?->nama ?? '—' }}</div>
                                        <div class="text-muted small">Kode: {{ $d->product?->kode ?? '—' }}</div>
                                    </td>
                                    <td class="text-end">{{ number_format($d->qty, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="4" class="text-end text-uppercase small">Grand Total</td>
                                <td class="text-end text-success">Rp {{ number_format($pembelian->total, 0, ',', '.') }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
