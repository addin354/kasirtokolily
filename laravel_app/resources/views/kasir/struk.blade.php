<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk #{{ $transaksi->id }} - Lily Sembako</title>

    <style>
        *{
            box-sizing:border-box;
        }

        body{
            font-family: Consolas, monospace;
            font-size:11px;
            width:58mm;
            margin:0 auto;
            padding:2mm;
            color:#000;
        }

        .center{
            text-align:center;
        }

        .toko{
            font-size:15px;
            font-weight:bold;
            text-transform:uppercase;
        }

        .alamat{
            font-size:10px;
            line-height:1.3;
            margin-top:3px;
        }

        hr{
            border:none;
            border-top:1px dashed #000;
            margin:6px 0;
        }

        .info{
            font-size:10px;
            line-height:1.5;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        td{
            padding:1px 0;
            vertical-align:top;
        }

        .harga,
        .subtotal{
            text-align:right;
            white-space:nowrap;
        }

        .qty{
            width:20px;
            text-align:center;
        }

        .nama-produk{
            font-weight:500;
        }

        .jenis{
            font-size:9px;
            color:#444;
        }

        .total-table td{
            padding:2px 0;
        }

        .grand-total{
            font-weight:bold;
        }

        .footer{
            text-align:center;
            font-size:10px;
            line-height:1.5;
        }

        .no-print{
            margin-top:15px;
            text-align:center;
        }

        .btn{
            padding:8px 12px;
            border:none;
            border-radius:5px;
            cursor:pointer;
            text-decoration:none;
            display:inline-block;
            font-size:13px;
        }

        .btn-primary{
            background:#0d6efd;
            color:white;
        }

        .btn-secondary{
            background:#6c757d;
            color:white;
        }

        @page{
            size:58mm auto;
            margin:0;
        }

        @media print{

            html,
            body{
                width:58mm;
                margin:0;
                padding:2mm;
            }

            .no-print{
                display:none !important;
            }
        }
    </style>
</head>
<body>

    <div class="center">
        <div class="toko">LILY SEMBAKO</div>

        <div class="alamat">
            Jl. Griya Permata Raya 1 No.54<br>
            Handil Bakti, Alalak<br>
            Barito Kuala
        </div>
    </div>

    <hr>

    <div class="info">
        No : TRX-{{ str_pad($transaksi->id, 6, '0', STR_PAD_LEFT) }}<br>
        Tanggal :
        {{ $transaksi->tanggal->timezone(config('app.timezone'))->format('d/m/Y H:i') }}

        @if ($transaksi->nama_pelanggan)
            <br>
            Pelanggan : {{ $transaksi->nama_pelanggan }}
        @endif
    </div>

    <hr>

    <table>
        <tbody>
        @foreach ($transaksi->detailTransaksis as $d)

            @php
                $nama = $d->product?->nama ?? 'Produk';
            @endphp

            <tr>
                <td colspan="4" class="nama-produk">
                    {{ $nama }}
                </td>
            </tr>

            <tr>
                <td class="qty">
                    {{ $d->qty_input ?? $d->qty }}
                </td>

                <td width="10">x</td>

                <td class="harga">
                    {{ number_format($d->harga, 0, ',', '.') }}
                </td>

                <td class="subtotal">
                    {{ number_format($d->subtotal, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td colspan="4" class="jenis">
                    {{ \App\Models\Product::labelJenisHarga($d->jenis_harga ?? 'eceran') }}

                    @if (($d->jenis_harga ?? '') === \App\Models\Product::JENIS_BAL && (int)($d->qty_pcs ?? 0) > 0)
                        · {{ number_format((int)$d->qty_pcs, 0, ',', '.') }} pcs
                    @endif
                </td>
            </tr>

        @endforeach
        </tbody>
    </table>

    <hr>

    <table class="total-table">
        <tr class="grand-total">
            <td>Total</td>
            <td align="right">
                Rp {{ number_format($transaksi->total, 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <td>Bayar</td>
            <td align="right">
                Rp {{ number_format($transaksi->bayar, 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <td>Kembalian</td>
            <td align="right">
                Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <hr>

    <div class="info" style="margin-bottom: 6px; font-size: 9px;">
        @php
            $metode = $transaksi->metode_pembayaran ?? 'Cash';
        @endphp
        Metode Pembayaran: {{ $metode === 'Transfer Bank' ? 'Transfer ' . ($transaksi->nama_bank ?? '') : $metode }}<br>
        @if ($transaksi->nomor_referensi)
            Referensi: {{ $transaksi->nomor_referensi }}<br>
        @endif
    </div>

    <hr>

    <div class="footer">
        Terima Kasih Atas Kunjungan Anda<br>
        Semoga Sehat dan Berkah
    </div>

    <div class="no-print" style="margin-top: 15px; display: flex; flex-direction: column; gap: 8px; align-items: center;">

        <div id="rpp02n-status-badge" style="font-size: 11px; padding: 4px 8px; border-radius: 12px; background: #e9ecef; color: #495057; font-weight: bold;">
            ⚪ RPP02N Belum Terhubung
        </div>

        <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">
            <button type="button" onclick="connectRPP02NBluetooth()" class="btn" style="background: #0d6efd; color: white;">
                📶 Hubungkan Bluetooth
            </button>
            <button type="button" onclick="connectRPP02NUSB()" class="btn" style="background: #17a2b8; color: white;">
                🔌 Hubungkan USB
            </button>
            <button type="button" onclick="printDirectRPP02N()" class="btn" style="background: #198754; color: white; font-weight: bold;">
                ⚡ Cetak RPP02N (Langsung)
            </button>
        </div>

        <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center; margin-top: 5px;">
            <button onclick="window.print()" class="btn btn-primary">
                🖨 Cetak Komputer (Dialog)
            </button>

            <a href="{{ route('kasir.rawbt',$transaksi) }}" class="btn btn-success">
                📱 Cetak RawBT (HP)
            </a>

            <a href="{{ route('kasir.index') }}" class="btn btn-secondary">
                Kembali ke Kasir
            </a>
        </div>

    </div>

    <script src="{{ asset('js/rpp02n-printer.js') }}"></script>
    <script>
    const receiptData = {
        toko_nama: "LILY SEMBAKO",
        toko_alamat: "Jl. Griya Permata Raya 1 No.54, Handil Bakti, Alalak, Barito Kuala",
        toko_hp: "0813-5602-1350 / 0853-4906-3081",
        no_transaksi: "TRX-{{ str_pad($transaksi->id, 6, '0', STR_PAD_LEFT) }}",
        tanggal: "{{ $transaksi->tanggal->timezone(config('app.timezone'))->format('d/m/Y H:i') }}",
        kasir: "{{ Auth::user()->name }}",
        pelanggan: "{{ $transaksi->nama_pelanggan ?? 'Umum' }}",
        total_item: "{{ $transaksi->detailTransaksis->sum('qty_input') }}",
        items: [
            @foreach ($transaksi->detailTransaksis as $d)
            {
                nama: {!! json_encode($d->product?->nama ?? 'Produk') !!},
                qty: "{{ $d->qty_input ?? $d->qty }}",
                harga: "{{ number_format($d->harga, 0, ',', '.') }}",
                subtotal: "{{ number_format($d->subtotal, 0, ',', '.') }}",
                jenis: "{{ \App\Models\Product::labelJenisHarga($d->jenis_harga ?? 'eceran') }}"
            },
            @endforeach
        ],
        total: "{{ number_format($transaksi->total, 0, ',', '.') }}",
        bayar: "{{ number_format($transaksi->bayar, 0, ',', '.') }}",
        kembalian: "{{ number_format($transaksi->kembalian, 0, ',', '.') }}",
        metode_pembayaran: "{{ $metode === 'Transfer Bank' ? 'Transfer ' . ($transaksi->nama_bank ?? '') : $metode }}",
        referensi: "{{ $transaksi->nomor_referensi ?? '' }}"
    };

    function updatePrinterStatus() {
        const badge = document.getElementById('rpp02n-status-badge');
        const savedName = localStorage.getItem('rpp02n_printer_name');
        if (window.RPP02NPrinter && window.RPP02NPrinter.isConnected()) {
            badge.style.background = '#d1e7dd';
            badge.style.color = '#0f5132';
            badge.innerHTML = '🟢 Terhubung: ' + (savedName || 'RPP02N Thermal Printer');
        } else if (savedName) {
            badge.style.background = '#fff3cd';
            badge.style.color = '#664d03';
            badge.innerHTML = '🟡 Perlu Hubungkan Ulang: ' + savedName;
        } else {
            badge.style.background = '#e9ecef';
            badge.style.color = '#495057';
            badge.innerHTML = '⚪ RPP02N Belum Terhubung';
        }
    }

    async function connectRPP02NBluetooth() {
        try {
            const name = await window.RPP02NPrinter.connectBluetooth();
            alert('Berhasil terhubung ke Bluetooth Printer: ' + name);
            updatePrinterStatus();
        } catch (e) {
            alert('Gagal menghubungkan Bluetooth: ' + e.message);
        }
    }

    async function connectRPP02NUSB() {
        try {
            const name = await window.RPP02NPrinter.connectUSB();
            alert('Berhasil terhubung ke USB Printer: ' + name);
            updatePrinterStatus();
        } catch (e) {
            alert('Gagal menghubungkan USB: ' + e.message);
        }
    }

    async function printDirectRPP02N() {
        if (!window.RPP02NPrinter.isConnected()) {
            const confirmConn = confirm('Printer RPP02N belum terhubung. Hubungkan via Bluetooth sekarang?');
            if (confirmConn) {
                await connectRPP02NBluetooth();
            } else {
                return;
            }
        }

        try {
            await window.RPP02NPrinter.printReceipt(receiptData);
        } catch (e) {
            alert('Gagal cetak ke RPP02N: ' + e.message);
        }
    }

    window.addEventListener('load', function() {
        updatePrinterStatus();

        @if(session('struk_autoprint'))
            if (window.RPP02NPrinter.isConnected()) {
                printDirectRPP02N();
            } else {
                const isAndroid = /Android/i.test(navigator.userAgent);
                if (isAndroid) {
                    window.location.href = "{{ route('kasir.rawbt',$transaksi) }}";
                } else {
                    setTimeout(function(){ window.print(); }, 300);
                }
            }
        @endif
    });
    </script>
</body>
</html>