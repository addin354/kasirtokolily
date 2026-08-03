<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RekapPenjualanTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_rekap_penjualan_report_and_see_grouped_calculations(): void
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
            'kode' => 'PRD-TEST-REKAP',
            'barcode' => '888777',
            'nama' => 'Minyak Goreng 1L',
            'kategori_id' => $kategoriId,
            'stok' => 100,
            'stok_minimum' => 10,
            'harga_beli' => 15000,
            'harga_jual' => 18000,
            'is_active' => true,
        ]);

        // 2. Create multiple sale transactions on same day: 2026-07-28
        $t1 = DB::table('transaksi')->insertGetId([
            'nama_pelanggan' => 'Umum',
            'tanggal' => '2026-07-28 10:00:00',
            'total' => 36000, // 2 items
            'bayar' => 40000,
            'kembalian' => 4000,
            'created_at' => '2026-07-28 10:00:00',
            'updated_at' => '2026-07-28 10:00:00',
        ]);

        DB::table('detail_transaksi')->insert([
            'transaksi_id' => $t1,
            'produk_id' => $product->id,
            'jenis_harga' => 'eceran',
            'qty_input' => 2,
            'qty_pcs' => 2,
            'qty' => 2,
            'harga' => 18000,
            'subtotal' => 36000,
            'created_at' => '2026-07-28 10:00:00',
            'updated_at' => '2026-07-28 10:00:00',
        ]);

        $t2 = DB::table('transaksi')->insertGetId([
            'nama_pelanggan' => 'Budi',
            'tanggal' => '2026-07-28 11:30:00',
            'total' => 18000, // 1 item
            'bayar' => 20000,
            'kembalian' => 2000,
            'created_at' => '2026-07-28 11:30:00',
            'updated_at' => '2026-07-28 11:30:00',
        ]);

        DB::table('detail_transaksi')->insert([
            'transaksi_id' => $t2,
            'produk_id' => $product->id,
            'jenis_harga' => 'eceran',
            'qty_input' => 1,
            'qty_pcs' => 1,
            'qty' => 1,
            'harga' => 18000,
            'subtotal' => 18000,
            'created_at' => '2026-07-28 11:30:00',
            'updated_at' => '2026-07-28 11:30:00',
        ]);

        // 3. Test endpoint Laporan Penjualan (Halaman Utama)
        $response = $this->get(route('laporan.penjualan', [
            'tanggal_dari' => '2026-07-28',
            'tanggal_sampai' => '2026-07-28',
        ]));

        $response->assertOk();

        // Calculations check:
        // Total Transaksi = 2
        // Total Omzet = 54.000 (36.000 + 18.000)
        // Total Barang Terjual = 3 unit
        // Total HPP = 3 * 15.000 = 45.000
        // Total Laba Kotor = 54.000 - 45.000 = 9.000
        $response->assertSee('54.000'); // Omzet
        $response->assertSee('45.000'); // HPP
        $response->assertSee('9.000');  // Laba Kotor
        $response->assertSee('3 Unit'); // Barang Terjual
        $response->assertSee('2 Kali'); // Total Transaksi

        // Check if individual transaction table row is NOT visible on main page
        $response->assertDontSee('TRX-000001');

        // 4. Test Detail endpoint Laporan Penjualan
        $detailResponse = $this->get(route('laporan.penjualan.detail', [
            'tanggal' => '2026-07-28',
        ]));

        $detailResponse->assertOk();
        $detailResponse->assertSee('TRX #000001');
        $detailResponse->assertSee('TRX #000002');
        $detailResponse->assertSee('Minyak Goreng 1L');
        $detailResponse->assertSee('Budi');
    }

    public function test_owner_can_export_rekap_penjualan_pdf_and_excel(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        // PDF check
        $pdfResponse = $this->get(route('laporan.export.pdf', [
            'tanggal_dari' => '2026-07-01',
            'tanggal_sampai' => '2026-07-31',
        ]));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');

        // Excel / CSV check
        $excelResponse = $this->get(route('laporan.export.excel', [
            'tanggal_dari' => '2026-07-01',
            'tanggal_sampai' => '2026-07-31',
        ]));
        $excelResponse->assertOk();
        $excelResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
