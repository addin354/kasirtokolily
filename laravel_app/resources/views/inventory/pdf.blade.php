<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Daftar Stok Inventory</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 3px; }
        .header-table td { border: none; padding: 0; }
        .logo-img { height: 50px; }
        .shop-name { font-size: 18px; font-weight: bold; color: #111; }
        .shop-address { font-size: 9px; color: #555; }
        .report-title { font-size: 14px; font-weight: bold; text-align: right; color: #0d6efd; text-transform: uppercase; }
        .report-period { font-size: 9.5px; text-align: right; color: #555; margin-top: 2px; }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 55px; vertical-align: middle;">
                @include('pdf.partials.logo')
            </td>
            <td style="vertical-align: middle;">
                <div class="shop-name">Lily Sembako</div>
                <div class="shop-address">Jl. Griya Permata Raya 1 No. 54, Handil Bakti, Kecamatan Alalak, Kabupaten Barito Kuala, Kalimantan Selatan, Telp: 0812-3456-7890</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="report-title">Laporan Daftar Stok Inventory</div>
                <div class="report-period">
                    Pusat Pengelolaan Persediaan
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;">Tanggal Cetak</td>
            <td style="width: 35%;">: {{ $tanggalCetak->format('d F Y H:i') }}</td>
            <td style="width: 15%;">Operator</td>
            <td style="width: 35%;">: {{ $user->name }} ({{ ucfirst($user->role) }})</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 12%;">Barcode</th>
                <th>Nama Produk</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 20%;">Supplier Terakhir</th>
                <th class="text-right" style="width: 10%;">Modal</th>
                <th class="text-right" style="width: 10%;">Jual</th>
                <th class="text-right" style="width: 8%;">Stok</th>
                <th class="text-right" style="width: 8%;">Min</th>
                <th class="text-center" style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $prod)
                @php
                    $statusClass = 'badge-success';
                    $statusText = 'Aman';
                    if ($prod->stok <= 0) {
                        $statusClass = 'badge-danger';
                        $statusText = 'Habis';
                    } elseif ($prod->stok <= $prod->stok_minimum) {
                        $statusClass = 'badge-warning';
                        $statusText = 'Menipis';
                    }
                @endphp
                <tr>
                    <td><code>{{ $prod->barcode }}</code></td>
                    <td style="font-weight: bold;">{{ $prod->nama }}</td>
                    <td>{{ $prod->category?->nama ?? '—' }}</td>
                    <td>{{ $prod->supplierTerakhir() ?? '—' }}</td>
                    <td class="text-right">Rp {{ number_format($prod->harga_beli, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($prod->harga_jual, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ (int)$prod->stok }}</td>
                    <td class="text-right">{{ (int)$prod->stok_minimum }}</td>
                    <td class="text-center">
                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
