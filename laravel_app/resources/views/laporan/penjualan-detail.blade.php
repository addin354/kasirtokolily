@extends('layouts.app')

@section('title', 'Detail Penjualan Tanggal ' . \Illuminate\Support\Carbon::parse($tanggal)->format('d/m/Y') . ' — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">
            <a href="{{ route('laporan.penjualan') }}" class="btn btn-outline-secondary btn-sm me-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            Detail Penjualan: {{ \Illuminate\Support\Carbon::parse($tanggal)->format('d F Y') }}
        </h1>
    </div>

    @if ($transaksis->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body p-4 text-center text-muted">
                Tidak ada data transaksi pada tanggal ini.
            </div>
        </div>
    @else
        @foreach ($transaksis as $trx)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                    <div>
                        <span class="fs-6 fw-bold text-dark me-2">TRX #{{ str_pad($trx->id, 6, '0', STR_PAD_LEFT) }}</span>
                        <span class="badge bg-secondary font-monospace me-2"><i class="bi bi-clock me-1"></i>{{ \Illuminate\Support\Carbon::parse($trx->tanggal)->format('H:i') }}</span>
                        
                        @php
                            $metode = $trx->metode_pembayaran ?? 'Cash';
                        @endphp
                        @if ($metode === 'Cash')
                            <span class="badge bg-success"><i class="bi bi-cash me-1"></i> Cash</span>
                        @elseif ($metode === 'Transfer Bank')
                            <span class="badge bg-primary"><i class="bi bi-bank me-1"></i> Transfer ({{ $trx->nama_bank }})</span>
                            @if ($trx->nomor_referensi)
                                <small class="text-muted font-monospace ms-1" style="font-size:0.75rem;">Ref: {{ $trx->nomor_referensi }}</small>
                            @endif
                        @elseif ($metode === 'QRIS')
                            <span class="badge bg-info text-dark"><i class="bi bi-qr-code-scan me-1"></i> QRIS</span>
                            @if ($trx->nomor_referensi)
                                <small class="text-muted font-monospace ms-1" style="font-size:0.75rem;">Ref: {{ $trx->nomor_referensi }}</small>
                            @endif
                        @endif
                    </div>
                    <div class="text-end">
                        <span class="small text-muted me-2">Kasir: <strong class="text-dark">{{ $trx->cashier_name ?? 'Kasir' }}</strong></span>
                        <span class="small text-muted">Pelanggan: <strong class="text-dark">{{ $trx->nama_pelanggan ?? 'Umum' }}</strong></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover text-nowrap mb-0 align-middle">
                            <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-3">No</th>
                                <th class="text-wrap-cell">Nama Produk</th>
                                <th class="text-center">Jenis Harga</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($trx->detailTransaksis as $index => $detail)
                                <tr>
                                    <td class="ps-3 text-secondary">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $detail->product?->nama ?? 'Produk #' . $detail->produk_id }}
                                        </div>
                                        @if($detail->product?->barcode)
                                            <div class="small text-muted font-monospace" style="font-size: 0.72rem;">
                                                Barcode: {{ $detail->product->barcode }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">
                                            {{ \App\Models\Product::labelJenisHarga($detail->jenis_harga ?? 'eceran') }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-medium">{{ $detail->qty_input ?? $detail->qty }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                    <td class="text-end pe-3 fw-bold text-dark">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold font-monospace">
                            <tr>
                                <td colspan="5" class="text-end ps-3">TOTAL TRANSAKSI:</td>
                                <td class="text-end pe-3 text-primary">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end ps-3 text-secondary">BAYAR:</td>
                                <td class="text-end pe-3 text-secondary">Rp {{ number_format($trx->bayar, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end ps-3 text-secondary">KEMBALIAN:</td>
                                <td class="text-end pe-3 text-secondary">Rp {{ number_format($trx->kembalian, 0, ',', '.') }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection
