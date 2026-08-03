<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\Product;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a kasir user
        $kasir = User::whereRole('kasir')->first();
        if (!$kasir) {
            $kasir = User::factory()->create(['role' => 'kasir']);
        }

        // Get products
        $products = Product::limit(5)->get();
        if ($products->isEmpty()) {
            $this->command->info('No products found. Skipping transaksi seeding.');
            return;
        }

        // Create sample transactions for this month
        for ($i = 1; $i <= 5; $i++) {
            $date = Carbon::now()->subDays(rand(0, 20))->setTime(rand(8, 17), rand(0, 59));
            
            $transaksi = Transaksi::create([
                'user_id' => $kasir->id,
                'tanggal' => $date,
                'total' => 0,
                'jenis_pembayaran' => 'cash',
            ]);

            // Add 2-4 line items
            $total = 0;
            $itemCount = rand(2, 4);
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $qty = rand(1, 5);
                $subtotal = $product->harga * $qty;

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $product->id,
                    'qty' => $qty,
                    'harga' => $product->harga,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $transaksi->update(['total' => $total]);
        }

        $this->command->info('Transaksi seeding completed: ' . Transaksi::count() . ' transaksi created.');
    }
}
