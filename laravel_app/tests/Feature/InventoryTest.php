<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private int $kategoriId;
    private int $produkId;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic category and product
        $this->kategoriId = DB::table('kategoris')->insertGetId([
            'nama' => 'Makanan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->produkId = DB::table('produks')->insertGetId([
            'kategori_id' => $this->kategoriId,
            'kode' => 'PRD-INVENT-TEST',
            'nama' => 'Minyak Goreng',
            'harga_beli' => 15000.00,
            'harga_jual' => 18000.00,
            'stok' => 50,
            'stok_minimum' => 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_inventory_index_is_accessible(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $response = $this->get(route('stok-masuk.index', ['tab' => 'opname']));
        $response->assertOk();
        $response->assertSee('Minyak Goreng');
    }

    public function test_stock_opname_updates_stock_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('inventory.stock-opname.store'), [
            'produk_id' => $this->produkId,
            'stok_fisik' => 45,
            'alasan' => 'Ada barang bocor',
        ]);

        $response->assertRedirect();
        
        // Assert product stock was updated to 45
        $this->assertDatabaseHas('produks', [
            'id' => $this->produkId,
            'stok' => 45,
        ]);

        // Assert opname record was created
        $this->assertDatabaseHas('stok_opnames', [
            'produk_id' => $this->produkId,
            'stok_sistem' => 50,
            'stok_fisik' => 45,
            'selisih' => -5,
            'alasan' => 'Ada barang bocor',
        ]);

        // Assert stock log was created
        $this->assertDatabaseHas('stok_logs', [
            'produk_id' => $this->produkId,
            'jenis' => 'Stock Opname',
            'masuk' => 0,
            'keluar' => 5,
            'saldo' => 45,
        ]);
    }

    public function test_stock_adjustment_adds_and_subtracts_stock_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Test Subtract Adjustment
        $response = $this->post(route('inventory.penyesuaian.store'), [
            'produk_id' => $this->produkId,
            'jenis' => 'Kurang',
            'jumlah' => 10,
            'alasan' => 'Barang Pecah',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('produks', [
            'id' => $this->produkId,
            'stok' => 40,
        ]);

        $this->assertDatabaseHas('penyesuaian_stoks', [
            'produk_id' => $this->produkId,
            'jenis' => 'Kurang',
            'jumlah' => 10,
            'alasan' => 'Barang Pecah',
        ]);

        // Test Add Adjustment
        $response = $this->post(route('inventory.penyesuaian.store'), [
            'produk_id' => $this->produkId,
            'jenis' => 'Tambah',
            'jumlah' => 5,
            'alasan' => 'Kesalahan Input',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('produks', [
            'id' => $this->produkId,
            'stok' => 45,
        ]);
    }

    public function test_inventory_exports_work(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        // Export PDF
        $response = $this->get(route('inventory.export.pdf'));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        // Export Excel (CSV)
        $response = $this->get(route('inventory.export.excel'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        // Export Riwayat PDF
        $response = $this->get(route('inventory.export-riwayat.pdf'));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        // Export Riwayat Excel (CSV)
        $response = $this->get(route('inventory.export-riwayat.excel'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
