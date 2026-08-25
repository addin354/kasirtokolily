<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::whereIn('role', ['owner', 'admin'])->first() ?? User::first();
        if (!$user) {
            return;
        }

        $products = Product::where('is_active', true)->limit(15)->get();
        if ($products->count() < 5) {
            $products = Product::limit(15)->get();
        }
        if ($products->isEmpty()) {
            return;
        }

        // 1. Seed 12+ Stock Opname Records
        $opnameData = [
            ['pas', 0, 'Pemeriksaan rutin mingguan - Pas'],
            ['lengkap', 0, 'Audit fisik bulan Juli - Sesuai'],
            ['kemasan rusak', -1, 'Kemasan bocor/rusak 1 pcs'],
            ['rusak', -2, 'Dus kemasan kerdus dimakan rayap'],
            ['bonus supplier', 2, 'Bonus pembelian grosir supplier belum terinput'],
            ['sesuai', 0, 'Stok opname bulanan - Lengkap'],
            ['tumpah', -1, 'Tumpah saat ditata di rak display'],
            ['audit fisik', 0, 'Audit bulanan - Sesuai'],
            ['penyok', -1, 'Kaleng penyok disingkirkan'],
            ['fisik pas', 0, 'Stok fisik pas'],
            ['rutin', 0, 'Opname rutin mingguan'],
            ['selisih fisik', -2, 'Hilang / belum ter-scan kasir'],
        ];

        foreach ($opnameData as $index => $item) {
            $product = $products[$index % $products->count()];
            $daysAgo = rand(1, 30);
            $date = Carbon::now()->subDays($daysAgo)->setTime(rand(9, 17), rand(0, 59));
            $stokSistem = max(10, (int) $product->stok);
            $selisih = $item[1];
            $stokFisik = max(0, $stokSistem + $selisih);

            DB::table('stok_opnames')->insert([
                'user_id' => $user->id,
                'produk_id' => $product->id,
                'stok_sistem' => $stokSistem,
                'stok_fisik' => $stokFisik,
                'selisih' => $selisih,
                'alasan' => $item[2],
                'tanggal' => $date->toDateString(),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // 2. Seed 12+ Penyesuaian Stok Records
        $adjustmentData = [
            ['kurang', 2, 'Penyesuaian barang rusak di rak'],
            ['tambah', 2, 'Koreksi sampel promosi supplier'],
            ['kurang', 1, 'Kemasan pecah saat ditata gudang'],
            ['kurang', 1, 'Kaleng penyok kadaluarsa'],
            ['tambah', 5, 'Koreksi sisa stok fisik toko'],
            ['tambah', 3, 'Retur dari pembeli dibatalkan'],
            ['kurang', 2, 'Kadaluarsa / rusak toko'],
            ['tambah', 4, 'Koreksi jumlah stok awal'],
            ['kurang', 1, 'Dimakan hama gudang'],
            ['tambah', 2, 'Bonus stok dari sales distributor'],
            ['kurang', 1, 'Dus rusak basah kena hujan'],
            ['tambah', 3, 'Koreksi hitung fisik kasir'],
        ];

        foreach ($adjustmentData as $index => $item) {
            $product = $products[($index + 3) % $products->count()];
            $daysAgo = rand(1, 30);
            $date = Carbon::now()->subDays($daysAgo)->setTime(rand(9, 17), rand(0, 59));

            DB::table('penyesuaian_stoks')->insert([
                'user_id' => $user->id,
                'produk_id' => $product->id,
                'jenis' => $item[0],
                'jumlah' => $item[1],
                'alasan' => $item[2],
                'tanggal' => $date->toDateString(),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
