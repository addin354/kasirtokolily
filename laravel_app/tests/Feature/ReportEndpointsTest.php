<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_return_item(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->postJson(route('laporan.retur.store'), [
            'produk_nama' => 'Beras Premium',
            'qty' => 2,
            'alasan' => 'Rusak kemasan',
            'status' => 'Dalam Proses',
            'tanggal_retur' => '2026-07-14',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('retur', [
            'produk_nama' => 'Beras Premium',
            'qty' => 2,
            'status' => 'Dalam Proses',
        ]);
    }

    public function test_laporan_retur_endpoint_is_accessible(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner']));

        $response = $this->getJson(route('laporan.retur.index', ['tanggal_dari' => '2026-01-01', 'tanggal_sampai' => '2026-01-31']));
        $response->assertOk();
        $response->assertJsonStructure([
            'filters',
            'summary',
            'data',
        ]);
    }

    public function test_owner_can_update_a_return_item(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $id = \Illuminate\Support\Facades\DB::table('retur')->insertGetId([
            'produk_nama' => 'Beras Premium',
            'qty' => 2,
            'alasan' => 'Rusak kemasan',
            'status' => 'Dalam Proses',
            'tanggal_retur' => '2026-07-14',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->putJson(route('laporan.retur.update', $id), [
            'produk_nama' => 'Beras Premium Super',
            'qty' => 5,
            'alasan' => 'Rusak parah',
            'status' => 'Diterima',
            'tanggal_retur' => '2026-07-15',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('retur', [
            'id' => $id,
            'produk_nama' => 'Beras Premium Super',
            'qty' => 5,
            'alasan' => 'Rusak parah',
            'status' => 'Diterima',
        ]);
    }

    public function test_owner_can_delete_a_return_item(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $id = \Illuminate\Support\Facades\DB::table('retur')->insertGetId([
            'produk_nama' => 'Beras Premium',
            'qty' => 2,
            'alasan' => 'Rusak kemasan',
            'status' => 'Dalam Proses',
            'tanggal_retur' => '2026-07-14',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson(route('laporan.retur.destroy', $id));

        $response->assertOk();
        $this->assertDatabaseMissing('retur', [
            'id' => $id,
        ]);
    }

    public function test_owner_can_filter_returns_by_various_parameters_together(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        \Illuminate\Support\Facades\DB::table('retur')->insert([
            'produk_id' => 10,
            'produk_nama' => 'Beras Premium',
            'qty' => 2,
            'status' => 'Diterima',
            'user_id' => $owner->id,
            'transaksi_id' => 20,
            'tanggal_retur' => '2026-07-14',
        ]);

        \Illuminate\Support\Facades\DB::table('retur')->insert([
            'produk_id' => 11,
            'produk_nama' => 'Minyak Goreng',
            'qty' => 3,
            'status' => 'Dalam Proses',
            'user_id' => $owner->id,
            'transaksi_id' => null,
            'tanggal_retur' => '2026-07-14',
        ]);

        // Test filter status
        $response = $this->getJson(route('laporan.retur.index', ['status' => 'Diterima']));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Beras Premium', $response->json('data.0.produk_nama'));

        // Test filter produk_id
        $response = $this->getJson(route('laporan.retur.index', ['produk_id' => 11]));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Minyak Goreng', $response->json('data.0.produk_nama'));

        // Test filter kasir (user_id)
        $response = $this->getJson(route('laporan.retur.index', ['user_id' => $owner->id]));
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        // Test filter jenis (dengan_transaksi)
        $response = $this->getJson(route('laporan.retur.index', ['jenis' => 'dengan_transaksi']));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Beras Premium', $response->json('data.0.produk_nama'));

        // Test filter jenis (tanpa_transaksi)
        $response = $this->getJson(route('laporan.retur.index', ['jenis' => 'tanpa_transaksi']));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Minyak Goreng', $response->json('data.0.produk_nama'));

        // Test combining multiple filters together
        $response = $this->getJson(route('laporan.retur.index', [
            'status' => 'Dalam Proses',
            'produk_id' => 11,
            'user_id' => $owner->id,
            'jenis' => 'tanpa_transaksi',
        ]));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Minyak Goreng', $response->json('data.0.produk_nama'));
    }

    public function test_owner_can_view_retur_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $id = \Illuminate\Support\Facades\DB::table('retur')->insertGetId([
            'produk_nama' => 'Beras Premium',
            'qty' => 2,
            'alasan' => 'Rusak kemasan',
            'status' => 'Dalam Proses',
            'tanggal_retur' => '2026-07-14',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('laporan.retur.show', $id));
        $response->assertOk();
        $response->assertSee('Detail Retur Penjualan');
        $response->assertSee('Beras Premium');
    }

    public function test_owner_can_upload_photo_when_creating_and_updating_retur(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $file = \Illuminate\Http\UploadedFile::fake()->image('bukti_retur.jpg');

        $response = $this->postJson(route('laporan.retur.store'), [
            'produk_nama' => 'Beras Premium',
            'qty' => 2,
            'alasan' => 'Rusak kemasan',
            'status' => 'Dalam Proses',
            'tanggal_retur' => '2026-07-14',
            'foto' => $file,
        ]);

        $response->assertCreated();
        $returId = $response->json('data.retur_id');

        $retur = \Illuminate\Support\Facades\DB::table('retur')->where('id', $returId)->first();
        $this->assertNotNull($retur->foto);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($retur->foto);

        // Update photo
        $newFile = \Illuminate\Http\UploadedFile::fake()->image('bukti_retur_baru.jpg');
        $response = $this->putJson(route('laporan.retur.update', $returId), [
            'produk_nama' => 'Beras Premium',
            'qty' => 2,
            'alasan' => 'Ubah alasan',
            'status' => 'Diterima',
            'tanggal_retur' => '2026-07-14',
            'foto' => $newFile,
        ]);

        $response->assertOk();
        $updatedRetur = \Illuminate\Support\Facades\DB::table('retur')->where('id', $returId)->first();
        $this->assertNotNull($updatedRetur->foto);
        $this->assertNotEquals($retur->foto, $updatedRetur->foto);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($updatedRetur->foto);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($retur->foto); // Old photo deleted!
    }

    public function test_cashier_can_create_retur_which_defaults_to_menunggu(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($cashier);

        $response = $this->postJson(route('laporan.retur.store'), [
            'produk_nama' => 'Beras Premium',
            'qty' => 5,
            'alasan' => 'Salah beli',
            'status' => 'Diterima', // Should be overridden to Menunggu
            'tanggal_retur' => '2026-07-14',
        ]);

        $response->assertCreated();
        $returId = $response->json('data.retur_id');

        $this->assertDatabaseHas('retur', [
            'id' => $returId,
            'status' => 'Menunggu', // Forced to Menunggu
            'user_id' => $cashier->id,
        ]);
    }

    public function test_cashier_cannot_approve_or_reject_retur(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($cashier);

        $id = \Illuminate\Support\Facades\DB::table('retur')->insertGetId([
            'produk_nama' => 'Beras Premium',
            'qty' => 2,
            'status' => 'Menunggu',
        ]);

        $responseApprove = $this->postJson(route('laporan.retur.approve', $id));
        $responseApprove->assertForbidden();

        $responseReject = $this->postJson(route('laporan.retur.reject', $id));
        $responseReject->assertForbidden();
    }

    public function test_owner_can_approve_retur_increments_stock_and_logs_audit(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $kategoriId = \Illuminate\Support\Facades\DB::table('kategoris')->insertGetId([
            'nama' => 'Sembako',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = \Illuminate\Support\Facades\DB::table('produks')->insertGetId([
            'kategori_id' => $kategoriId,
            'kode' => 'PRD001',
            'nama' => 'Indomie Goreng',
            'stok' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = \Illuminate\Support\Facades\DB::table('retur')->insertGetId([
            'produk_id' => $productId,
            'produk_nama' => 'Indomie Goreng',
            'qty' => 5,
            'status' => 'Menunggu',
            'user_id' => $owner->id,
            'tanggal_retur' => '2026-07-14',
        ]);

        $response = $this->postJson(route('laporan.retur.approve', $id));
        $response->assertOk();

        // Check status updated to Diterima
        $this->assertDatabaseHas('retur', [
            'id' => $id,
            'status' => 'Diterima',
        ]);

        // Check stock incremented
        $product = Product::find($productId);
        $this->assertEquals(15, $product->stok);

        // Check audit log saved
        $this->assertDatabaseHas('audit_logs', [
            'model_type' => 'Retur',
            'model_id' => $id,
            'user_id' => $owner->id,
            'action' => 'Approve',
        ]);
    }

    public function test_owner_can_reject_retur_keeps_stock_unchanged_and_logs_audit(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $kategoriId = \Illuminate\Support\Facades\DB::table('kategoris')->insertGetId([
            'nama' => 'Sembako',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = \Illuminate\Support\Facades\DB::table('produks')->insertGetId([
            'kategori_id' => $kategoriId,
            'kode' => 'PRD001',
            'nama' => 'Indomie Goreng',
            'stok' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = \Illuminate\Support\Facades\DB::table('retur')->insertGetId([
            'produk_id' => $productId,
            'produk_nama' => 'Indomie Goreng',
            'qty' => 5,
            'status' => 'Menunggu',
            'user_id' => $owner->id,
            'tanggal_retur' => '2026-07-14',
        ]);

        $response = $this->postJson(route('laporan.retur.reject', $id));
        $response->assertOk();

        // Check status updated to Ditolak
        $this->assertDatabaseHas('retur', [
            'id' => $id,
            'status' => 'Ditolak',
        ]);

        // Check stock unchanged
        $product = Product::find($productId);
        $this->assertEquals(10, $product->stok);

        // Check audit log saved
        $this->assertDatabaseHas('audit_logs', [
            'model_type' => 'Retur',
            'model_id' => $id,
            'user_id' => $owner->id,
            'action' => 'Reject',
        ]);
    }

    public function test_owner_can_access_retur_stats_api(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        \Illuminate\Support\Facades\DB::table('retur')->insert([
            'produk_nama' => 'Beras Premium',
            'qty' => 5,
            'status' => 'Diterima',
            'alasan' => 'Rusak kemasan',
            'tanggal_retur' => '2026-07-14',
            'user_id' => $owner->id,
        ]);

        $response = $this->getJson(route('laporan.retur.stats'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status_breakdown',
            'top_products',
            'daily_trends',
            'common_reasons',
        ]);

        $this->assertNotEmpty($response->json('status_breakdown'));
        $this->assertEquals('Diterima', $response->json('status_breakdown.0.status'));
        $this->assertEquals(1, $response->json('status_breakdown.0.count'));
    }

    public function test_owner_can_export_retur_pdf_with_filters(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        \Illuminate\Support\Facades\DB::table('retur')->insert([
            'produk_nama' => 'Beras Premium',
            'qty' => 5,
            'status' => 'Diterima',
            'alasan' => 'Rusak kemasan',
            'tanggal_retur' => '2026-07-14',
            'user_id' => $owner->id,
        ]);

        $response = $this->get(route('laporan.retur.export.pdf', [
            'status' => 'Diterima',
        ]));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_owner_can_export_retur_excel_with_filters(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        \Illuminate\Support\Facades\DB::table('retur')->insert([
            'produk_nama' => 'Beras Premium',
            'qty' => 5,
            'status' => 'Diterima',
            'alasan' => 'Rusak kemasan',
            'tanggal_retur' => '2026-07-14',
            'user_id' => $owner->id,
        ]);

        $response = $this->get(route('laporan.retur.export.excel', [
            'status' => 'Diterima',
        ]));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Beras Premium', $content);
    }
}
