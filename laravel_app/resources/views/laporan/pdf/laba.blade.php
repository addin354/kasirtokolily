<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Laba Rugi</title>
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
        .laba-bersih-row { background-color: #e8f5e9; }
        .laba-bersih-row-danger { background-color: #ffebee; }

        .metrics-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .metrics-table td { padding: 8px; text-align: center; border: 1px solid #dee2e6; background-color: #f8f9fa; }
        .metrics-title { font-size: 8px; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 2px; }
        .metrics-value { font-size: 11px; font-weight: bold; color: #111; }

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
                @include('pdf.partials.logo')
            </td>
            <td style="vertical-align: middle;">
                <div class="shop-name">Lily Sembako</div>
                <div class="shop-address">Jl. Griya Permata Raya 1 No. 54, Handil Bakti, Kecamatan Alalak, Kabupaten Barito Kuala, Kalimantan Selatan, Telp: 0812-3456-7890</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="report-title">Laporan Laba Rugi</div>
                <div class="report-period">{{ $periodeLabel }}</div>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Keuangan -->
    <div class="summary-title">Ringkasan Keuangan</div>
    <table class="summary-table">
        <tr>
            <td class="summary-label">Pendapatan Penjualan</td>
            <td class="summary-value text-primary">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Harga Pokok Penjualan (HPP)</td>
            <td class="summary-value text-warning">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #f1f3f5;">
            <td class="summary-label" style="font-weight: bold;">Laba Kotor (Pendapatan - HPP)</td>
            <td class="summary-value" style="color: #0b7285;">Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Total Pengeluaran Operasional</td>
            <td class="summary-value text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
        </tr>
        <tr class="{{ $labaBersih >= 0 ? 'laba-bersih-row' : 'laba-bersih-row-danger' }}">
            <td class="summary-label" style="font-weight: bold; background-color: transparent;">Laba Bersih</td>
            <td class="summary-value @if($labaBersih >= 0) text-success @else text-danger @endif" style="font-size: 11px;">
                Rp {{ number_format($labaBersih, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <!-- Saldo Kas Toko -->
    <div class="summary-title" style="margin-top: 15px;">Saldo Kas Toko Saat Ini</div>
    <table class="summary-table">
        <tr>
            <td class="summary-label">Saldo Awal Kas</td>
            <td class="summary-value" style="color: #495057;">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">+ Total Penjualan Cash</td>
            <td class="summary-value text-success">Rp {{ number_format($totalPenjualanCash, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">- Total Pembelian Cash</td>
            <td class="summary-value text-danger">Rp {{ number_format($totalPembelianCash, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">- Total Pengeluaran Cash</td>
            <td class="summary-value text-danger">Rp {{ number_format($totalPengeluaranCash, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #e8f5e9;">
            <td class="summary-label" style="font-weight: bold; background-color: transparent;">= Saldo Kas Saat Ini</td>
            <td class="summary-value text-success" style="font-size: 11px;">
                Rp {{ number_format($saldoKasSaatIni, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <!-- Ringkasan Tambahan (Metrics) -->
    <table class="metrics-table">
        <tr>
            <td>
                <div class="metrics-title">Jumlah Transaksi</div>
                <div class="metrics-value">{{ $jumlahTransaksi }} Kali</div>
            </td>
            <td>
                <div class="metrics-title">Jumlah Pengeluaran</div>
                <div class="metrics-value">{{ $jumlahPengeluaran }} Kali</div>
            </td>
            <td>
                <div class="metrics-title">Margin Laba Kotor</div>
                <div class="metrics-value">{{ number_format($marginLabaKotor, 2, ',', '.') }}%</div>
            </td>
            <td>
                <div class="metrics-title">Margin Laba Bersih</div>
                <div class="metrics-value" style="color: @if($marginLabaBersih >= 0) #198754 @else #dc3545 @endif;">
                    {{ number_format($marginLabaBersih, 2, ',', '.') }}%
                </div>
            </td>
    </table>

    @if(!empty($chartImage))
        <div class="summary-title" style="margin-top: 15px;">Grafik Tren Laba Rugi</div>
        <div style="text-align: center; margin-bottom: 15px; background: #fff; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px;">
            <img src="{{ $chartImage }}" style="width: 100%; height: auto; max-height: 200px;" />
        </div>
    @endif

    <!-- Daily Summary Table -->
    <div class="summary-title" style="margin-top: 15px;">Ringkasan Laporan Harian (Daily Summary)</div>
    <table class="data-table">
        <thead>
        <tr>
            <th style="width: 70px;" class="text-center">Tanggal</th>
            <th class="text-end">Pendapatan Penjualan</th>
            <th class="text-end">Harga Pokok Penjualan (HPP)</th>
            <th class="text-end">Pembelian Barang</th>
            <th class="text-end">Pengeluaran Operasional</th>
            <th class="text-end">Saldo Kas Harian</th>
            <th style="width: 80px;" class="text-end">Laba Bersih</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($lines as $row)
            <tr>
                <td class="text-center font-mono" style="font-size: 8px;">
                    {{ \Illuminate\Support\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                </td>
                <td class="text-end text-primary fw-semibold">
                    Rp {{ number_format($row['pendapatan'], 0, ',', '.') }}
                </td>
                <td class="text-end text-warning fw-semibold">
                    Rp {{ number_format($row['hpp'], 0, ',', '.') }}
                </td>
                <td class="text-end text-secondary">
                    Rp {{ number_format($row['pembelian'], 0, ',', '.') }}
                </td>
                <td class="text-end text-danger">
                    Rp {{ number_format($row['pengeluaran'], 0, ',', '.') }}
                </td>
                <td class="text-end text-dark fw-semibold">
                    Rp {{ number_format($row['saldo_kas_harian'], 0, ',', '.') }}
                </td>
                <td class="text-end fw-bold" style="color: @if($row['laba_bersih'] >= 0) #198754 @else #dc3545 @endif;">
                    Rp {{ number_format($row['laba_bersih'], 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-3" style="font-style: italic;">Tidak ada catatan transaksi keuangan pada periode ini.</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot style="background-color: #f8f9fa; font-weight: bold; border-top: 1.5px solid #333;" class="font-mono">
        <tr>
            <td style="text-align: right; font-weight: bold; padding: 6px;">TOTAL:</td>
            <td class="text-end text-primary" style="padding: 6px;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            <td class="text-end text-warning" style="padding: 6px;">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
            <td class="text-end text-secondary" style="padding: 6px;">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</td>
            <td class="text-end text-danger" style="padding: 6px;">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
            <td class="text-end text-dark" style="padding: 6px;">Rp {{ number_format($saldoKasSaatIni, 0, ',', '.') }}</td>
            <td class="text-end @if($labaBersih >= 0) text-success @else text-danger @endif" style="padding: 6px;">
                Rp {{ number_format($labaBersih, 0, ',', '.') }}
            </td>
        </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; font-size: 8px; text-align: right; color: #666;">
        Dicetak oleh: {{ auth()->user()->name ?? 'System' }} | Waktu Cetak: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</div>
</body>
</html>
