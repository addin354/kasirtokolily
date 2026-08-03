```html
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Struk</title>
</head>
<body>

<script>

let struk = `
        LILY SEMBAKO
================================
Jl. Griya Permata Raya 1 No.54
Handil Bakti, Alalak
Barito Kuala, Kalimantan Selatan

HP : 0813-5602-1350
     0853-4906-3081
================================
No. Transaksi : {{ $transaksi->id }}
Tanggal       : {{ $transaksi->tanggal->format('d/m/Y H:i') }}
Kasir         : {{ Auth::user()->name }}
Pelanggan     : {{ optional($transaksi->pelanggan)->nama ?? 'Umum' }}
Total Item    : {{ $transaksi->detailTransaksis->sum('qty_input') }}
--------------------------------
@foreach($transaksi->detailTransaksis as $item)
{{ $item->product->nama }}
{{ $item->qty_input }} x {{ number_format($item->harga,0,',','.') }}      Rp {{ number_format($item->subtotal,0,',','.') }}
@endforeach

--------------------------------
TOTAL      : Rp {{ number_format($transaksi->total,0,',','.') }}
BAYAR      : Rp {{ number_format($transaksi->bayar,0,',','.') }}
KEMBALIAN  : Rp {{ number_format($transaksi->kembalian,0,',','.') }}
@php
    $metode = $transaksi->metode_pembayaran ?? 'Cash';
@endphp
METODE PMB : {{ $metode === 'Transfer Bank' ? 'Transfer ' . ($transaksi->nama_bank ?? '') : $metode }}
@if($transaksi->nomor_referensi)
REFERENSI  : {{ $transaksi->nomor_referensi }}
@endif
================================
Barang yang sudah dibeli tidak
dapat ditukar atau dikembalikan
kecuali terdapat kesalahan pada
transaksi.

      TERIMA KASIH
 Atas Kunjungan Anda
================================
`;

window.location.href =
'rawbt:base64,' +
btoa(unescape(encodeURIComponent(struk)));

setTimeout(function() {
    window.location.href = "{{ route('kasir.index') }}";
}, 2000);

</script>

</body>
</html>
```
