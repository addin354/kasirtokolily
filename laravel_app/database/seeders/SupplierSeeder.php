<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::query()->firstOrCreate(
            ['nama_supplier' => 'Supplier Utama'],
            [
                'alamat' => 'Alamat contoh',
                'no_hp' => '081234567890',
            ]
        );
    }
}
