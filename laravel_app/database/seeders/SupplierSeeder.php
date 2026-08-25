<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'nama_supplier' => 'PT Indofood Sukses Makmur Tbk',
                'alamat' => 'Jl. Jend. Sudirman Kav. 76-78, Jakarta Selatan',
                'no_hp' => '081288991001',
            ],
            [
                'nama_supplier' => 'PT Unilever Indonesia Tbk',
                'alamat' => 'BSD Green Office Park, Jl. BSD Boulevard Barat, Tangerang',
                'no_hp' => '081288991002',
            ],
            [
                'nama_supplier' => 'PT Mayora Indah Tbk',
                'alamat' => 'Gedung Mayora, Jl. Tomang Raya No. 21-23, Jakarta Barat',
                'no_hp' => '081288991003',
            ],
            [
                'nama_supplier' => 'PT Wings Surya (Wings Group)',
                'alamat' => 'Jl. Tipar Cakung No. 20, Jakarta Timur',
                'no_hp' => '081288991004',
            ],
            [
                'nama_supplier' => 'CV Sumber Sembako Makmur',
                'alamat' => 'Jl. Raya Pasar Induk Kramat Jati No. 45, Jakarta Timur',
                'no_hp' => '081399881005',
            ],
            [
                'nama_supplier' => 'PT Wilmar Nabati Indonesia',
                'alamat' => 'Multivision Tower Lt. 12, Jl. Kuningan Mulia, Jakarta Selatan',
                'no_hp' => '081399881006',
            ],
            [
                'nama_supplier' => 'CV Berkah Jaya Pangan',
                'alamat' => 'Pertokoan Pasar Induk Cipinang Blok A No. 12, Jakarta Timur',
                'no_hp' => '081399881007',
            ],
            [
                'nama_supplier' => 'PT Frisian Flag Indonesia',
                'alamat' => 'Jl. Raya Bogor Km. 5, Pasar Rebo, Jakarta Timur',
                'no_hp' => '081399881008',
            ],
            [
                'nama_supplier' => 'UD Sinar Utama Sembako',
                'alamat' => 'Pergudangan Niaga Megah Blok C3, Surabaya',
                'no_hp' => '081399881009',
            ],
            [
                'nama_supplier' => 'PT Ajinomoto Indonesia',
                'alamat' => 'Jl. Laksda Yos Sudarso No. 77, Sunter, Jakarta Utara',
                'no_hp' => '081399881010',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::query()->firstOrCreate(
                ['nama_supplier' => $data['nama_supplier']],
                [
                    'alamat' => $data['alamat'],
                    'no_hp' => $data['no_hp'],
                ]
            );
        }
    }
}
