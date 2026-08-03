<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pembelian Barang</title>
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
                @if(file_exists(public_path('logo.png')))
                    <img src="{{ public_path('logo.png') }}" class="logo-img" alt="Logo">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="shop-name">Lily Sembako</div>
                <div class="shop-address">Jl. Griya Permata Raya 1 No. 54, Handil Bakti, Kecamatan Alalak, Kabupaten Barito Kuala, Kalimantan Selatan, Telp: 0812-3456-7890</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="report-title">Laporan Pembelian Barang</div>
                <div class="report-period">
                    Tanggal cetak: {{ $tanggalCetak->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="table table-bordered table-sm table-striped">
        <thead class="table-dark">
        <tr>
            <th>Nomor Pembelian</th>
            <th>Tanggal</th>
            <th>Supplier</th>
            <th class="text-end">Jumlah Item</th>
            <th class="text-end">Total Pembelian</th>
            <th>User</th>
        </tr>
        </thead>
        <tbody>
        @php $grandTotal = 0; @endphp
        @forelse ($pembelians as $p)
            @php $grandTotal += (float)$p->total; @endphp
            <tr>
                <td class="fw-bold">{{ $p->nomor_pembelian }}</td>
                <td>{{ $p->tanggal->format('d/m/Y') }}</td>
                <td>{{ $p->supplier?->nama_supplier ?? '—' }}</td>
                <td class="text-end">{{ $p->detail_pembelians_count }}</td>
                <td class="text-end fw-semibold">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                <td>{{ $p->user?->name ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td>
            </tr>
        @endforelse
        </tbody>
        @if ($pembelians->isNotEmpty())
            <tfoot>
            <tr class="fw-bold table-light">
                <td colspan="4" class="text-end text-uppercase">Grand Total</td>
                <td class="text-end text-success">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            </tfoot>
        @endif
    </table>
</div>
</body>
</html>
