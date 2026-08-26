<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StokLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturStockMovementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
    }

    public function test_retur_approval_increments_stock_and_logs_stok_movement(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $cat = \App\Models\Category::create(['nama' => 'Umum']);
        $product = Product::create([
            'kategori_id' => $cat->id,
            'nama' => 'Produk Retur Test',
            'kode' => 'PRT-01',
            'barcode' => '88880001',
            'stok' => 50,
            'stok_minimum' => 10,
            'harga_beli' => 10000,
            'harga_jual' => 15000,
            'is_active' => true,
        ]);

        // 1. Create retur (Menunggu) as admin
        $this->actingAs($admin);
        $response = $this->postJson('/api/laporan/retur', [
            'produk_id' => $product->id,
            'produk_nama' => $product->nama,
            'qty' => 5,
            'alasan' => 'Barang Cacat',
            'status' => 'Menunggu',
        ]);

        $response->assertCreated();
        $returId = $response->json('data.retur_id');

        // Stock should not change yet
        $this->assertEquals(50, $product->fresh()->stok);

        // 2. Approve retur as owner
        $this->actingAs($owner);
        $approveRes = $this->postJson("/api/laporan/retur/{$returId}/approve");
        $approveRes->assertOk();

        // Stock should increase to 55
        $this->assertEquals(55, $product->fresh()->stok);

        // Check StokLog created
        $this->assertDatabaseHas('stok_logs', [
            'produk_id' => $product->id,
            'jenis' => 'Retur',
            'masuk' => 5,
            'saldo' => 55,
        ]);
    }

    public function test_rejecting_previously_approved_retur_reverts_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $cat = \App\Models\Category::create(['nama' => 'Umum']);
        $product = Product::create([
            'kategori_id' => $cat->id,
            'nama' => 'Produk Reject Test',
            'kode' => 'PRT-02',
            'barcode' => '88880002',
            'stok' => 20,
            'stok_minimum' => 5,
            'harga_beli' => 10000,
            'harga_jual' => 15000,
            'is_active' => true,
        ]);

        // Create approved retur as admin
        $this->actingAs($admin);
        $response = $this->postJson('/api/laporan/retur', [
            'produk_id' => $product->id,
            'produk_nama' => $product->nama,
            'qty' => 10,
            'alasan' => 'Retur Langsung',
            'status' => 'Diterima',
        ]);
        $response->assertCreated();
        $returId = $response->json('data.retur_id');

        // Stock should be 30
        $this->assertEquals(30, $product->fresh()->stok);

        // Now reject the retur as owner
        $this->actingAs($owner);
        $rejectRes = $this->postJson("/api/laporan/retur/{$returId}/reject");
        $rejectRes->assertOk();

        // Stock should be reverted back to 20
        $this->assertEquals(20, $product->fresh()->stok);
    }

    public function test_deleting_approved_retur_reverts_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat = \App\Models\Category::create(['nama' => 'Umum']);
        $product = Product::create([
            'kategori_id' => $cat->id,
            'nama' => 'Produk Delete Test',
            'kode' => 'PRT-03',
            'barcode' => '88880003',
            'stok' => 15,
            'stok_minimum' => 5,
            'harga_beli' => 10000,
            'harga_jual' => 15000,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        // Approve retur
        $response = $this->postJson('/api/laporan/retur', [
            'produk_id' => $product->id,
            'produk_nama' => $product->nama,
            'qty' => 5,
            'alasan' => 'Retur Langsung Diterima',
            'status' => 'Diterima',
        ]);
        $response->assertCreated();
        $returId = $response->json('data.retur_id');

        $this->assertEquals(20, $product->fresh()->stok);

        // Delete retur
        $delRes = $this->deleteJson("/api/laporan/retur/{$returId}");
        $delRes->assertOk();

        // Stock should drop back to 15
        $this->assertEquals(15, $product->fresh()->stok);
    }
}
