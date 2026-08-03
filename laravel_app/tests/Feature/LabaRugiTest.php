<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LabaRugiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_laba_rugi_report_and_see_correct_calculations(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        // 1. Create Category and Product
        $kategoriId = DB::table('kategoris')->insertGetId([
            'nama' => 'Bahan Pokok',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'kode' => 'PRD-TEST-X',
            'barcode' => '999111',
            'nama' => 'Beras Pandan Wangi',
            'kategori_id' => $kategoriId,
            'stok' => 50,
            'stok_minimum' => 10,
            'harga_beli' => 10000,
            'harga_jual' => 12000,
            'is_active' => true,
        ]);

        // 2. Create Sale Transaction
        $transaksiId = DB::table('transaksi')->insertGetId([
            'nama_pelanggan' => 'Umum',
            'tanggal' => '2026-07-28 10:00:00',
            'total' => 24000,
            'bayar' => 30000,
            'kembalian' => 6000,
            'created_at' => '2026-07-28 10:00:00',
            'updated_at' => '2026-07-28 10:00:00',
        ]);

        DB::table('detail_transaksi')->insert([
            'transaksi_id' => $transaksiId,
            'produk_id' => $product->id,
            'jenis_harga' => 'eceran',
            'qty_input' => 2,
            'qty_pcs' => 2,
            'qty' => 2,
            'harga' => 12000,
            'subtotal' => 24000,
            'created_at' => '2026-07-28 10:00:00',
            'updated_at' => '2026-07-28 10:00:00',
        ]);

        // 3. Create Operating Expense (Pengeluaran)
        DB::table('pengeluarans')->insert([
            'nomor_pengeluaran' => 'OUT-20260728-001',
            'tanggal' => '2026-07-28',
            'kategori' => 'Listrik',
            'keterangan' => 'Bayar token PLN',
            'nominal' => 2000,
            'user_id' => $owner->id,
            'created_at' => '2026-07-28 11:00:00',
            'updated_at' => '2026-07-28 11:00:00',
        ]);

        // 4. Test Laba Rugi endpoint for Date 2026-07-28
        $response = $this->get(route('laporan.laba', [
            'tipe' => 'harian',
            'tanggal' => '2026-07-28',
        ]));

        $response->assertOk();

        // Calculations check:
        // Pendapatan = 24.000
        // HPP = 2 * 10.000 = 20.000
        // Laba Kotor = 24.000 - 20.000 = 4.000
        // Total Pengeluaran = 2.000
        // Laba Bersih = 4.000 - 2.000 = 2.000
        $response->assertSee('Rp 24.000'); // Pendapatan
        $response->assertSee('Rp 20.000'); // HPP
        $response->assertSee('Rp 4.000');  // Laba Kotor
        $response->assertSee('Rp 2.000');  // Total Pengeluaran / Laba Bersih
    }

    public function test_owner_can_export_laba_rugi_pdf(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $response = $this->get(route('laporan.laba.export.pdf', [
            'tipe' => 'bulanan',
            'bulan' => '2026-07',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_saldo_kas_calculations_with_different_payment_methods(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        // Set Saldo Awal to 5,000,000
        config(['pos.saldo_awal_kas' => 5000000]);

        $kategoriId = DB::table('kategoris')->insertGetId([
            'nama' => 'Bahan Pokok',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'kode' => 'PRD-TEST-Y',
            'barcode' => '999222',
            'nama' => 'Beras Pandan',
            'kategori_id' => $kategoriId,
            'stok' => 100,
            'stok_minimum' => 10,
            'harga_beli' => 10000,
            'harga_jual' => 12000,
            'is_active' => true,
        ]);

        // 1. Cash Sale (Total = 2,000,000, Cash)
        DB::table('transaksi')->insertGetId([
            'nama_pelanggan' => 'Umum',
            'tanggal' => '2026-07-28 10:00:00',
            'total' => 2000000,
            'bayar' => 2000000,
            'kembalian' => 0,
            'metode_pembayaran' => 'Cash',
            'created_at' => '2026-07-28 10:00:00',
            'updated_at' => '2026-07-28 10:00:00',
        ]);

        // 2. Non-Cash Sale (Total = 1,500,000, QRIS)
        DB::table('transaksi')->insertGetId([
            'nama_pelanggan' => 'Umum',
            'tanggal' => '2026-07-28 11:00:00',
            'total' => 1500000,
            'bayar' => 1500000,
            'kembalian' => 0,
            'metode_pembayaran' => 'QRIS',
            'created_at' => '2026-07-28 11:00:00',
            'updated_at' => '2026-07-28 11:00:00',
        ]);

        // 3. Cash Purchase (Total = 500,000, Cash)
        $supplierId = DB::table('suppliers')->insertGetId([
            'nama_supplier' => 'Supplier A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pembelians')->insertGetId([
            'nomor_pembelian' => 'PB-20260728-0001',
            'supplier_id' => $supplierId,
            'tanggal' => '2026-07-28',
            'total' => 500000,
            'metode_pembayaran' => 'Cash',
            'user_id' => $owner->id,
            'created_at' => '2026-07-28 12:00:00',
            'updated_at' => '2026-07-28 12:00:00',
        ]);

        // 4. Non-Cash Purchase (Total = 1,000,000, Tempo)
        DB::table('pembelians')->insertGetId([
            'nomor_pembelian' => 'PB-20260728-0002',
            'supplier_id' => $supplierId,
            'tanggal' => '2026-07-28',
            'total' => 1000000,
            'metode_pembayaran' => 'Tempo',
            'user_id' => $owner->id,
            'created_at' => '2026-07-28 13:00:00',
            'updated_at' => '2026-07-28 13:00:00',
        ]);

        // 5. Cash Expense (Nominal = 200,000, Cash)
        DB::table('pengeluarans')->insertGetId([
            'nomor_pengeluaran' => 'OUT-20260728-001',
            'tanggal' => '2026-07-28',
            'kategori' => 'Listrik',
            'nominal' => 200000,
            'metode_pembayaran' => 'Cash',
            'user_id' => $owner->id,
            'created_at' => '2026-07-28 14:00:00',
            'updated_at' => '2026-07-28 14:00:00',
        ]);

        // 6. Non-Cash Expense (Nominal = 300,000, Transfer Bank)
        DB::table('pengeluarans')->insertGetId([
            'nomor_pengeluaran' => 'OUT-20260728-002',
            'tanggal' => '2026-07-28',
            'kategori' => 'Air',
            'nominal' => 300000,
            'metode_pembayaran' => 'Transfer Bank',
            'user_id' => $owner->id,
            'created_at' => '2026-07-28 15:00:00',
            'updated_at' => '2026-07-28 15:00:00',
        ]);

        // Hit route
        $response = $this->get(route('laporan.laba', [
            'tipe' => 'harian',
            'tanggal' => '2026-07-28',
        ]));

        $response->assertOk();

        // Check cash metrics
        $response->assertSee('Rp 5.000.000'); // Saldo Awal
        $response->assertSee('Rp 2.000.000'); // + Penjualan Cash
        $response->assertSee('Rp 500.000');   // - Pembelian Cash
        $response->assertSee('Rp 200.000');   // - Pengeluaran Cash
        $response->assertSee('Rp 6.300.000'); // = Saldo Kas Saat Ini
    }
}
