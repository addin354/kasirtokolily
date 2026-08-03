<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>
        @if($type === 'terlaris') Laporan 10 Produk Terlaris
        @elseif($type === 'produk') Laporan Daftar Produk
        @elseif($type === 'restok') Laporan Produk Perlu Restok
        @elseif($type === 'persediaan') Laporan Persediaan Barang
        @elseif($type === 'kartu_stok') Laporan Kartu Stok
        @endif
    </title>
    <style>
        @page {
            margin: 110px 30px 50px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        header {
            position: fixed;
            top: -95px;
            left: 0;
            right: 0;
            height: 85px;
            border-bottom: 2px solid #1a3c75;
            padding-bottom: 5px;
        }
        footer {
            position: fixed;
            bottom: -35px;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 8px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .header-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .header-table td, .footer-table td {
            border: none;
            padding: 0;
        }
        .logo-img {
            height: 48px;
            width: 48px;
            border-radius: 50%;
        }
        .shop-name {
            font-size: 16px;
            font-weight: bold;
            color: #1a3c75;
            margin: 0;
        }
        .shop-address {
            font-size: 8px;
            color: #666;
            margin: 2px 0 0 0;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            margin: 0;
            color: #333;
            text-transform: uppercase;
        }
        .report-period {
            font-size: 9px;
            text-align: right;
            color: #555;
            margin: 3px 0 0 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #1a3c75;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            padding: 6px;
            border: 1px solid #ddd;
        }
        table.data-table td {
            padding: 5px 6px;
            border: 1px solid #ddd;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f9fbfd;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .text-success {
            color: #198754;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-warning {
            color: #b27a00;
        }
        .summary-box {
            margin-top: 15px;
            width: 100%;
            border-collapse: collapse;
        }
        .summary-box td {
            border: 1px solid #ddd;
            padding: 6px 10px;
        }
        .summary-title {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #555;
            width: 70%;
        }
        .summary-value {
            font-weight: bold;
            font-size: 11px;
        }
        .page-number:before {
            content: "Halaman " counter(page);
        }
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .bg-primary-subtle { background-color: #cfe2ff; color: #084298; }
        .bg-success-subtle { background-color: #d1e7dd; color: #0f5132; }
        .bg-info-subtle { background-color: #cff4fc; color: #087990; }
        .bg-warning-subtle { background-color: #fff3cd; color: #664d03; }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <table class="header-table">
            <tr>
                <td style="width: 55px; vertical-align: middle;">
                    @if(file_exists(public_path('logo.png')))
                        <img src="{{ public_path('logo.png') }}" class="logo-img" alt="Logo">
                    @endif
                </td>
                <td style="vertical-align: middle;">
                    <div class="shop-name">Lily Sembako</div>
                    <div class="shop-address">Jl. Griya Permata Raya 1 No. 54, Handil Bakti, Kecamatan Alalak, Kabupaten Barito Kuala, Kalimantan Selatan, Telp: 0812-3456-7890</div>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    <div class="report-title">
                        @if($type === 'terlaris') Laporan 10 Produk Terlaris
                        @elseif($type === 'produk') Laporan Daftar Produk
                        @elseif($type === 'restok') Laporan Produk Perlu Restok
                        @elseif($type === 'persediaan') Laporan Persediaan Barang
                        @elseif($type === 'kartu_stok') Laporan Kartu Stok
                        @endif
                    </div>
                    <div class="report-period">
                        Periode: 
                        @if($filters['tanggal_dari'] && $filters['tanggal_sampai'])
                            {{ \Carbon\Carbon::parse($filters['tanggal_dari'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($filters['tanggal_sampai'])->format('d/m/Y') }}
                        @elseif($filters['tanggal_dari'])
                            Dari {{ \Carbon\Carbon::parse($filters['tanggal_dari'])->format('d/m/Y') }}
                        @elseif($filters['tanggal_sampai'])
                            Sampai {{ \Carbon\Carbon::parse($filters['tanggal_sampai'])->format('d/m/Y') }}
                        @else
                            Semua Waktu
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <!-- Footer -->
    <footer>
        <table class="footer-table">
            <tr>
                <td>Dicetak pada: {{ $tanggalCetak->format('d/m/Y H:i') }} | Oleh: {{ $user->name }} ({{ $user->roleLabel() }})</td>
                <td style="text-align: right;" class="page-number"></td>
            </tr>
        </table>
    </footer>

    <!-- Main Content Data -->
    <table class="data-table">
        @if($type === 'terlaris')
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">No</th>
                    <th style="width: 70px;">Barcode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th class="text-end" style="width: 70px;">Qty Terjual</th>
                    <th class="text-end" style="width: 100px;">Total Penjualan</th>
                    <th class="text-end" style="width: 80px;">Harga Modal</th>
                    <th class="text-end" style="width: 80px;">Harga Jual</th>
                    <th class="text-end" style="width: 100px;">Estimasi Laba</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td style="font-family: monospace;">{{ $row->barcode ?? '—' }}</td>
                        <td class="fw-bold">{{ $row->nama_produk }}</td>
                        <td>{{ $row->nama_kategori ?? '—' }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->qty_terjual, 0, ',', '.') }}</td>
                        <td class="text-end text-success fw-bold">Rp {{ number_format($row->total_penjualan, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->harga_modal, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->harga_jual, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-success">Rp {{ number_format($row->estimasi_laba, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>

        @elseif($type === 'produk')
            <thead>
                <tr>
                    <th style="width: 80px;">Barcode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Supplier</th>
                    <th class="text-end">Harga Modal</th>
                    <th class="text-end">Harga Jual Ecer</th>
                    <th class="text-end">Harga Jual Grosir</th>
                    <th class="text-end">Harga Bal</th>
                    <th class="text-end">Isi per Bal</th>
                    <th class="text-end" style="width: 70px;">Min Stok</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td style="font-family: monospace;">{{ $row->barcode }}</td>
                        <td class="fw-bold">{{ $row->nama }}</td>
                        <td>{{ $row->category?->nama ?? '—' }}</td>
                        <td>{{ $row->supplierTerakhir() ?? '—' }}</td>
                        <td class="text-end">Rp {{ number_format($row->harga_beli, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->harga_jual, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->harga_grosir, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->harga_bal, 0, ',', '.') }}</td>
                        <td class="text-end">{{ $row->isi_per_bal ?? 1 }}</td>
                        <td class="text-end fw-bold">{{ $row->stok_minimum }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>

        @elseif($type === 'restok')
            <thead>
                <tr>
                    <th style="width: 80px;">Barcode</th>
                    <th>Nama Produk</th>
                    <th>Supplier</th>
                    <th>Kategori</th>
                    <th class="text-end" style="width: 70px;">Stok Saat Ini</th>
                    <th class="text-end" style="width: 70px;">Minimum Stok</th>
                    <th class="text-end" style="width: 70px;">Selisih</th>
                    <th class="text-center" style="width: 80px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalRestokProduk = count($data);
                @endphp
                @forelse($data as $row)
                    @php
                        $selisih = max(0, $row->stok_minimum - $row->stok);
                    @endphp
                    <tr>
                        <td style="font-family: monospace;">{{ $row->barcode }}</td>
                        <td class="fw-bold">{{ $row->nama }}</td>
                        <td>{{ $row->supplierTerakhir() ?? '—' }}</td>
                        <td>{{ $row->category?->nama ?? '—' }}</td>
                        <td class="text-end fw-bold text-warning">{{ $row->stok }}</td>
                        <td class="text-end text-muted">{{ $row->stok_minimum }}</td>
                        <td class="text-end fw-bold text-danger">{{ $selisih }}</td>
                        <td class="text-center text-warning fw-bold">Perlu Restok</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>

        @elseif($type === 'persediaan')
            <thead>
                <tr>
                    <th style="width: 90px;">Barcode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Supplier</th>
                    <th class="text-end" style="width: 70px;">Stok</th>
                    <th class="text-center" style="width: 80px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    @php
                        $status = $row->stokStatus();
                    @endphp
                    <tr>
                        <td style="font-family: monospace;">{{ $row->barcode }}</td>
                        <td class="fw-bold">{{ $row->nama }}</td>
                        <td>{{ $row->category?->nama ?? '—' }}</td>
                        <td>{{ $row->supplierTerakhir() ?? '—' }}</td>
                        <td class="text-end fw-bold">{{ $row->stok }}</td>
                        <td class="text-center fw-bold {{ $status === 'restok' ? 'text-warning' : 'text-success' }}">
                            {{ $status === 'restok' ? 'Perlu Restok' : 'Aman' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>

        @elseif($type === 'kartu_stok')
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">No</th>
                    <th style="width: 90px;">Tanggal</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th style="width: 90px;">Tipe Transaksi</th>
                    <th class="text-end" style="width: 70px;">Qty Masuk</th>
                    <th class="text-end" style="width: 70px;">Qty Keluar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $row)
                    <tr>
                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y H:i') }}</td>
                        <td class="fw-bold">{{ $row->nama_produk }}</td>
                        <td>{{ $row->nama_kategori ?? '—' }}</td>
                        <td>
                            <span class="badge @if($row->tipe === 'Pembelian') bg-primary-subtle @elseif($row->tipe === 'Penjualan') bg-success-subtle @elseif($row->tipe === 'Stok Masuk') bg-info-subtle @else bg-warning-subtle @endif">
                                {{ $row->tipe }}
                            </span>
                        </td>
                        <td class="text-end text-success fw-bold">{{ number_format($row->masuk, 0, ',', '.') }}</td>
                        <td class="text-end text-danger fw-bold">{{ number_format($row->keluar, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada pergerakan kartu stok.</td>
                    </tr>
                @endforelse
            </tbody>
        @endif
    </table>

    <!-- Totals Box (PDF view) -->
    @if($type === 'restok' && count($data) > 0)
        <table class="summary-box">
            <tr>
                <td class="summary-title text-end">Jumlah Produk Perlu Restok:</td>
                <td class="summary-value text-warning text-end">{{ $totalRestokProduk }} Produk</td>
            </tr>
        </table>

    @endif
</body>
</html>
