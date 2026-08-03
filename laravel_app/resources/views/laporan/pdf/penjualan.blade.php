<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Rekap Penjualan</title>
    @include('pdf.partials.bootstrap-pdf')
    <style>
        body { font-size: 9.5px; font-family: sans-serif; color: #333; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 3px; }
        .header-table td { border: none; padding: 0; }
        .logo-img { height: 50px; }
        .shop-name { font-size: 18px; font-weight: bold; color: #111; }
        .shop-address { font-size: 9px; color: #555; }
        .report-title { font-size: 14px; font-weight: bold; text-align: right; color: #0d6efd; text-transform: uppercase; }
        .report-period { font-size: 9.5px; text-align: right; color: #555; margin-top: 2px; }
        
        .summary-title { font-size: 10.5px; font-weight: bold; margin-bottom: 8px; text-transform: uppercase; color: #111; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .summary-table td { padding: 6px 10px; border: 1px solid #dee2e6; }
        .summary-label { font-weight: bold; color: #495057; background-color: #f8f9fa; width: 45%; }
        .summary-value { font-weight: bold; text-align: right; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background-color: #343a40; color: #fff; text-transform: uppercase; font-size: 8px; font-weight: bold; padding: 5px 6px; border: 1px solid #454d55; }
        .data-table td { padding: 5px 6px; border: 1px solid #dee2e6; vertical-align: middle; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .fw-bold { font-weight: bold; }
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
                <div class="report-title">Laporan Rekap Penjualan</div>
                <div class="report-period">{{ $periodeLabel }}</div>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Keuangan -->
    <div class="summary-title">Ringkasan Penjualan</div>
    <table class="summary-table">
        <tr>
            <td class="summary-label">Total Transaksi</td>
            <td class="summary-value text-dark">{{ number_format($totalTransaksi, 0, ',', '.') }} Kali</td>
        </tr>
        <tr>
            <td class="summary-label">Total Barang Terjual</td>
            <td class="summary-value text-dark">{{ number_format($totalBarangTerjual, 0, ',', '.') }} Unit</td>
        </tr>
        <tr>
            <td class="summary-label">Total Pendapatan (Omzet)</td>
            <td class="summary-value text-primary">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Total Harga Pokok Penjualan (HPP)</td>
            <td class="summary-value text-warning">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #e8f5e9;">
            <td class="summary-label" style="font-weight: bold; background-color: transparent;">Total Laba Kotor</td>
            <td class="summary-value text-success" style="font-size: 11px;">
                Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <!-- Rekap Penjualan Harian Table -->
    <div class="summary-title" style="margin-top: 15px;">Data Rekap Penjualan Harian</div>
    <table class="data-table">
        <thead>
        <tr>
            <th style="width: 100px;" class="text-center">Tanggal</th>
            <th style="width: 100px;" class="text-center">Jumlah Transaksi</th>
            <th style="width: 100px;" class="text-center">Barang Terjual</th>
            <th class="text-end">Omzet / Pendapatan</th>
            <th class="text-end">HPP</th>
            <th class="text-end">Laba Kotor</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($lines as $row)
            <tr>
                <td class="text-center font-mono" style="font-size: 8px;">
                    {{ \Illuminate\Support\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                </td>
                <td class="text-center">{{ $row['jumlah_transaksi'] }}</td>
                <td class="text-center">{{ $row['barang_terjual'] }} unit</td>
                <td class="text-end text-primary fw-semibold">
                    Rp {{ number_format($row['omzet'], 0, ',', '.') }}
                </td>
                <td class="text-end text-warning">
                    Rp {{ number_format($row['hpp'], 0, ',', '.') }}
                </td>
                <td class="text-end text-success fw-bold">
                    Rp {{ number_format($row['laba_kotor'], 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-3" style="font-style: italic;">Tidak ada catatan transaksi penjualan pada periode ini.</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot style="background-color: #f8f9fa; font-weight: bold; border-top: 1.5px solid #333;" class="font-mono">
        <tr>
            <td style="text-align: right; font-weight: bold; padding: 6px;">TOTAL:</td>
            <td class="text-center" style="padding: 6px;">{{ number_format($totalTransaksi, 0, ',', '.') }}</td>
            <td class="text-center" style="padding: 6px;">{{ number_format($totalBarangTerjual, 0, ',', '.') }}</td>
            <td class="text-end text-primary" style="padding: 6px;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            <td class="text-end text-warning" style="padding: 6px;">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
            <td class="text-end text-success" style="padding: 6px;">Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}</td>
        </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; font-size: 8px; text-align: right; color: #666;">
        Dicetak oleh: {{ auth()->user()->name ?? 'System' }} | Waktu Cetak: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</div>
</body>
</html>
