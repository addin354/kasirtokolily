<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\Product;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan seluruh produk memiliki harga_beli (modal) yang realistis di bawah harga_jual (margin 25%)
        \Illuminate\Support\Facades\DB::table('produks')
            ->whereRaw('harga_jual > 0 AND (harga_beli >= harga_jual OR harga_beli <= 0 OR harga_beli IS NULL)')
            ->update([
                'harga_beli' => \Illuminate\Support\Facades\DB::raw('ROUND(harga_jual * 0.75, -2)')
            ]);

        // 2. Ambil produk aktif yang valid
        $products = Product::where('is_active', true)->where('harga_jual', '>', 0)->get();
        if ($products->isEmpty()) {
            return;
        }

        $paymentMethods = ['Cash', 'Transfer Bank', 'QRIS'];
        $bankNames = ['BCA', 'Mandiri', 'BRI', 'BNI'];

        // 3. Buat 35 transaksi penjualan baru yang menguntungkan dari 30 hari yang lalu hingga HARI INI
        for ($i = 0; $i < 35; $i++) {
            $daysAgo = rand(0, 30);
            $date = Carbon::now()->subDays($daysAgo)->setTime(rand(8, 20), rand(0, 59));
            $metode = $paymentMethods[array_rand($paymentMethods)];

            $transaksi = Transaksi::create([
                'nama_pelanggan' => rand(0, 1) ? 'Pelanggan Umum' : 'Pelanggan ' . rand(1, 10),
                'tanggal' => $date,
                'total' => 0,
                'bayar' => 0,
                'kembalian' => 0,
                'metode_pembayaran' => $metode,
                'nama_bank' => $metode === 'Transfer Bank' ? $bankNames[array_rand($bankNames)] : null,
                'nomor_referensi' => $metode !== 'Cash' ? 'REF-' . strtoupper(bin2hex(random_bytes(4))) : null,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $total = 0;
            $itemCount = rand(2, 5);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 5);
                $hargaJual = (float) $product->harga_jual;
                $subtotal = $hargaJual * $qty;

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $product->id,
                    'jenis_harga' => 'eceran',
                    'qty_input' => $qty,
                    'qty_pcs' => $qty,
                    'qty' => $qty,
                    'harga' => $hargaJual,
                    'subtotal' => $subtotal,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $total += $subtotal;
            }

            $bayar = $metode === 'Cash' ? (max($total, ceil($total / 50000) * 50000)) : $total;
            $kembalian = max(0, $bayar - $total);

            $transaksi->update([
                'total' => $total,
                'bayar' => $bayar,
                'kembalian' => $kembalian,
            ]);
        }
    }
}
