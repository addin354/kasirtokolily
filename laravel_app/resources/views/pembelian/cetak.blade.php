<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Pembelian {{ $pembelian->nomor_pembelian }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        .invoice-title {
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #0d6efd;
        }
        .info-table td {
            padding: 4px 8px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <!-- Tombol Cetak / Kembali -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print bg-light p-3 rounded">
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">Tutup Halaman</button>
            <button onclick="window.print()" class="btn btn-primary btn-sm">Cetak Sekarang</button>
        </div>

        <!-- Header Nota -->
        <div class="row align-items-center mb-4">
            <div class="col-6">
                <h2 class="invoice-title mb-0">NOTA PEMBELIAN</h2>
                <div class="text-muted small">Toko Lily Sembako</div>
            </div>
            <div class="col-6 text-end">
                <h4 class="text-muted mb-0">{{ $pembelian->nomor_pembelian }}</h4>
                <div class="text-muted small">Tanggal: {{ $pembelian->tanggal->format('d/m/Y') }}</div>
            </div>
        </div>

        <hr class="border-secondary opacity-25">

        <!-- Informasi Supplier & Transaksi -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-muted text-uppercase mb-2">Supplier</h6>
                <div class="fw-semibold">{{ $pembelian->supplier?->nama_supplier ?? '—' }}</div>
                <div class="text-muted small">{{ $pembelian->supplier?->alamat ?? 'Alamat: —' }}</div>
                <div class="text-muted small">No HP: {{ $pembelian->supplier?->no_hp ?? '—' }}</div>
            </div>
            <div class="col-6 text-end">
                <h6 class="fw-bold text-muted text-uppercase mb-2">Penerima / User</h6>
                <div class="fw-semibold">{{ $pembelian->user?->name ?? '—' }}</div>
                <div class="text-muted small">Peran: {{ $pembelian->user?->roleLabel() ?? '—' }}</div>
                <div class="text-muted small">Catatan: {{ $pembelian->keterangan ?? '—' }}</div>
            </div>
        </div>

        <!-- Tabel Detail Barang -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th>Nama Produk</th>
                    <th style="width: 100px;" class="text-end">Qty</th>
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
                <tr class="fw-bold">
                    <td colspan="4" class="text-end text-uppercase">Grand Total</td>
                    <td class="text-end text-success">Rp {{ number_format($pembelian->total, 0, ',', '.') }}</td>
                </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan / Persetujuan -->
        <div class="row mt-5 pt-3">
            <div class="col-4 text-center">
                <div class="text-muted small">Diserahkan Oleh,</div>
                <div style="height: 70px;"></div>
                <div class="fw-semibold border-top d-inline-block px-4">Supplier</div>
            </div>
            <div class="col-4"></div>
            <div class="col-4 text-center">
                <div class="text-muted small">Diterima Oleh,</div>
                <div style="height: 70px;"></div>
                <div class="fw-semibold border-top d-inline-block px-4">{{ $pembelian->user?->name ?? 'Petugas Toko' }}</div>
            </div>
        </div>
    </div>

    <script>
        // Otomatis memicu dialog print saat halaman terbuka
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
