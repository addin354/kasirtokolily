<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Barang Perlu Restok</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 3px; }
        .header-table td { border: none; padding: 0; }
        .logo-img { height: 50px; }
        .shop-name { font-size: 18px; font-weight: bold; color: #111; }
        .shop-address { font-size: 9px; color: #555; }
        .report-title { font-size: 14px; font-weight: bold; text-align: right; color: #0d6efd; text-transform: uppercase; }
        .report-period { font-size: 9.5px; text-align: right; color: #555; margin-top: 2px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .text-end { text-align: right; }
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
                <div class="report-title">Daftar Barang Perlu Restok</div>
                <div class="report-period">
                    Dicetak: {{ $tanggalCetak->translatedFormat('d F Y H:i') }}
                </div>
            </td>
        </tr>
    </table>
<table>
    <thead>
    <tr>
        <th>Status</th>
        <th>Nama</th>
        <th>Kode</th>
        <th>Satuan</th>
        <th class="text-end">Stok</th>
        <th class="text-end">Minimum</th>
        <th class="text-end">Selisih</th>
    </tr>
    </thead>
    <tbody>
    @forelse($products as $product)
        @php
            $selisih = max(0, $product->stok_minimum - $product->stok);
        @endphp
        <tr>
            <td>Perlu Restok</td>
            <td>{{ $product->nama }}</td>
            <td>{{ $product->kode }}</td>
            <td>{{ $product->satuanModel?->nama ?? $product->satuan ?? '—' }}</td>
            <td class="text-end">{{ number_format((int) $product->stok, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format((int) $product->stok_minimum, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($selisih, 0, ',', '.') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7">Tidak ada produk yang perlu restok saat ini.</td>
        </tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
