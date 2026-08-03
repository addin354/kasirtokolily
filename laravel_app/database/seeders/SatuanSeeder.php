<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Seeder;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Pcs', 'kg', 'Liter', 'Pak', 'Dus', 'Ikat'] as $nama) {
            Satuan::query()->firstOrCreate(['nama' => $nama]);
        }
    }
}
