<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Pergerakan Stok</title>
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
    </style>
</head>
<body>
    <!-- Header -->
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
                <div class="report-title">Laporan Riwayat Pergerakan Stok</div>
                <div class="report-period">
                    Log Audit Perubahan Persediaan
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
                <th style="width: 15%;">Tanggal</th>
                <th>Produk</th>
                <th style="width: 18%;">Jenis Transaksi</th>
                <th class="text-right" style="width: 10%;">Masuk</th>
                <th class="text-right" style="width: 10%;">Keluar</th>
                <th class="text-right" style="width: 10%;">Saldo Akhir</th>
                <th style="width: 15%;">Referensi</th>
                <th style="width: 12%;">User</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($history as $row)
                @php
                    $jenisLabel = $row->jenis;
                    if ($jenisLabel === 'Pembelian') $jenisLabel = 'Pembelian Barang';
                    if ($jenisLabel === 'Retur') $jenisLabel = 'Retur Penjualan';
                    if ($jenisLabel === 'Penyesuaian') $jenisLabel = 'Penyesuaian Stok';
                @endphp
                <tr>
                    <td>{{ $row->tanggal->format('d/m/Y H:i') }}</td>
                    <td style="font-weight: bold;">{{ $row->product?->nama ?? '—' }}</td>
                    <td>{{ $jenisLabel }}</td>
                    <td class="text-right" style="color: green; font-weight: bold;">{{ $row->masuk > 0 ? '+' . $row->masuk : '—' }}</td>
                    <td class="text-right" style="color: red; font-weight: bold;">{{ $row->keluar > 0 ? '-' . $row->keluar : '—' }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ (int)$row->saldo }}</td>
                    <td><code>{{ $row->referensi ?? '—' }}</code></td>
                    <td>{{ $row->user?->name ?? 'System' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
