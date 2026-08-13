<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Data Produk</title>
    @include('pdf.partials.bootstrap-pdf')
    <style>
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 3px; }
        .header-table td { border: none; padding: 0; }
        .logo-img { height: 50px; }
        .shop-name { font-size: 18px; font-weight: bold; color: #111; }
        .shop-address { font-size: 9px; color: #555; }
        .report-title { font-size: 14px; font-weight: bold; text-align: right; color: #0d6efd; text-transform: uppercase; }
        .report-period { font-size: 9.5px; text-align: right; color: #555; margin-top: 2px; }
    </style>
</head>
<body>
<div class="container-fluid">
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
                <div class="report-title">Data Produk</div>
                <div class="report-period">
                    Tanggal cetak: {{ $tanggalCetak->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="table table-bordered table-sm table-striped">
        <thead class="table-dark">
        <tr>
            <th style="width: 32px;">#</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th class="text-end">Eceran</th>
            <th class="text-end">Grosir</th>
            <th class="text-end">Bal</th>
            <th class="text-end">Stok</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($products as $i => $product)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>
                    <div class="fw-semibold">{{ $product->nama }}</div>
                    <div class="text-muted small">{{ $product->kode }}</div>
                </td>
                <td>{{ $product->category?->nama ?? '—' }}</td>
                <td>{{ $product->satuanModel?->nama ?? '—' }}</td>
                <td class="text-end">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($product->harga_grosir ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($product->harga_bal ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($product->stok, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-3 pt-2 border-top text-end small text-muted">
        Total {{ number_format($products->count(), 0, ',', '.') }} produk
    </div>
</div>
</body>
</html>
