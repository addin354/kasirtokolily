<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengeluaranSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::whereIn('role', ['owner', 'admin'])->first() ?? User::first();
        if (!$user) {
            return;
        }

        $expenses = [
            [
                'kategori' => 'Listrik & Air',
                'keterangan' => 'Pembayaran token listrik toko & pompa air bulan Agustus',
                'nominal' => 450000,
                'metode_pembayaran' => 'Transfer Bank',
                'days_ago' => 2,
            ],
            [
                'kategori' => 'Gaji Karyawan',
                'keterangan' => 'Gaji bulanan kasir & pramuniaga Toko Lily Sembako',
                'nominal' => 2500000,
                'metode_pembayaran' => 'Transfer Bank',
                'days_ago' => 5,
            ],
            [
                'kategori' => 'Perlengkapan Kasir',
                'keterangan' => 'Kertas thermal roll 58mm (10 roll) + plastik kresek',
                'nominal' => 125000,
                'metode_pembayaran' => 'Cash',
                'days_ago' => 1,
            ],
            [
                'kategori' => 'Operasional Toko',
                'keterangan' => 'Iuran kebersihan sampah & keharmonisan pasar',
                'nominal' => 75000,
                'metode_pembayaran' => 'Cash',
                'days_ago' => 8,
            ],
            [
                'kategori' => 'Perawatan & Perbaikan',
                'keterangan' => 'Cuci AC & isi freon ruangan kasir',
                'nominal' => 200000,
                'metode_pembayaran' => 'Cash',
                'days_ago' => 12,
            ],
            [
                'kategori' => 'Transportasi & Kurir',
                'keterangan' => 'Bensin motor inventaris & uang tol sewa angkut sembako',
                'nominal' => 85000,
                'metode_pembayaran' => 'Cash',
                'days_ago' => 3,
            ],
            [
                'kategori' => 'Operasional Toko',
                'keterangan' => 'ATK kasir, nota manual, lakban cokelat toko',
                'nominal' => 45000,
                'metode_pembayaran' => 'Cash',
                'days_ago' => 6,
            ],
            [
                'kategori' => 'Operasional Toko',
                'keterangan' => 'Air minum & minum kopi tim kurir pembongkaran muatan',
                'nominal' => 60000,
                'metode_pembayaran' => 'Cash',
                'days_ago' => 10,
            ],
            [
                'kategori' => 'Sewa Tempat',
                'keterangan' => 'Sewa ruko Toko Lily Sembako bulanan',
                'nominal' => 1500000,
                'metode_pembayaran' => 'Transfer Bank',
                'days_ago' => 15,
            ],
            [
                'kategori' => 'Perawatan & Perbaikan',
                'keterangan' => 'Kalibrasi timbangan digital & cairan pembersih kaca/rak',
                'nominal' => 110000,
                'metode_pembayaran' => 'Cash',
                'days_ago' => 18,
            ],
            [
                'kategori' => 'Perlengkapan Kasir',
                'keterangan' => 'Tinta printer laporan owner & 1 rim kertas HVS A4',
                'nominal' => 95000,
                'metode_pembayaran' => 'Cash',
                'days_ago' => 4,
            ],
            [
                'kategori' => 'Operasional Toko',
                'keterangan' => 'Langganan Wi-Fi Indihome toko bulan Agustus',
                'nominal' => 330000,
                'metode_pembayaran' => 'Transfer Bank',
                'days_ago' => 7,
            ],
        ];

        foreach ($expenses as $index => $item) {
            $date = Carbon::now()->subDays($item['days_ago'])->setTime(rand(8, 17), rand(0, 59));
            $numFormatted = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $nomorPengeluaran = 'EXP-' . $date->format('Ymd') . '-' . $numFormatted;

            DB::table('pengeluarans')->updateOrInsert(
                ['nomor_pengeluaran' => $nomorPengeluaran],
                [
                    'tanggal' => $date->toDateString(),
                    'kategori' => $item['kategori'],
                    'keterangan' => $item['keterangan'],
                    'nominal' => $item['nominal'],
                    'metode_pembayaran' => $item['metode_pembayaran'],
                    'user_id' => $user->id,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]
            );
        }
    }
}
