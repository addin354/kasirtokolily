<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Retur Penjualan</title>
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
                <div class="report-title">Laporan Retur Penjualan</div>
                <div class="report-period">
                    Periode: 
                    @if($dari && $sampai)
                        {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}
                    @elseif($dari)
                        Mulai {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }}
                    @elseif($sampai)
                        Sampai {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}
                    @else
                        Semua Periode
                    @endif
                    <br>
                    <small style="font-size: 8px;">Cetak: {{ $tanggalCetak->format('d/m/Y H:i') }}</small>
                </div>
            </td>
        </tr>
    </table>

    <div class="row mb-3 g-2">
        <div class="col-6">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Total Unit Diretur</div>
                <div class="fw-bold fs-6">{{ number_format($totalUnit, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Total Nominal Retur</div>
                <div class="fw-bold fs-6">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>

    <table class="table table-bordered table-sm table-striped">
        <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>No. Retur</th>
            <th>No. Transaksi</th>
            <th>Tanggal</th>
            <th>Nama Produk</th>
            <th class="text-end">Harga</th>
            <th class="text-center">Qty</th>
            <th class="text-end">Total</th>
            <th>Kasir</th>
            <th>Alasan</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($returs as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="fw-semibold">#{{ $row->no_retur ?? $row->retur_id }}</td>
                <td>{{ $row->transaksi_id ? '#' . $row->transaksi_id : '—' }}</td>
                <td>{{ $row->tanggal_retur }}</td>
                <td class="text-wrap" style="max-width: 150px;">{{ $row->produk_nama }}</td>
                <td class="text-end">Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($row->qty, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                <td>{{ $row->kasir_nama ?? '—' }}</td>
                <td class="text-wrap" style="max-width: 150px;">{{ $row->alasan ?? '—' }}</td>
                <td>{{ $row->status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center text-muted py-3">Tidak ada data.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-3 pt-2 border-top">
        <table class="table table-sm w-auto ms-auto mb-0" style="width: 280px;">
            <tr>
                <td class="fw-semibold">Total Unit</td>
                <td class="text-end">{{ number_format($totalUnit, 0, ',', '.') }}</td>
            </tr>
            <tr class="table-primary">
                <td class="fw-bold">Total Nominal</td>
                <td class="text-end fw-bold">Rp {{ number_format($totalNominal, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
