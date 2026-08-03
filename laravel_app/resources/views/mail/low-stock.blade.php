<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok di bawah minimum</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #222;">
    <h1 style="font-size: 1.1rem; margin: 0 0 0.5rem;">Stok di bawah minimum</h1>
    <p style="margin: 0 0 1rem; color: #555;">
        Berikut produk yang stoknya &lt; <strong>{{ $batasMinStok }}</strong> unit (hanya produk aktif):
    </p>
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
        <thead>
        <tr style="text-align: left; border-bottom: 2px solid #ccc;">
            <th style="padding: 0.4rem 0.5rem;">Kode</th>
            <th style="padding: 0.4rem 0.5rem;">Nama</th>
            <th style="padding: 0.4rem 0.5rem;">Stok</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($produkBermasalah as $p)
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 0.4rem 0.5rem;">{{ $p->kode ?? '—' }}</td>
                <td style="padding: 0.4rem 0.5rem;">{{ $p->nama }}</td>
                <td style="padding: 0.4rem 0.5rem;">{{ (int) $p->stok }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p style="margin: 1rem 0 0; font-size: 0.8rem; color: #888;">
        Pesan otomatis dari {{ config('app.name') }}.
    </p>
</body>
</html>
