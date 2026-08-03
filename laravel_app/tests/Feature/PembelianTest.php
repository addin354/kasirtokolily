<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Pembelian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PembelianTest extends TestCase
{
    use RefreshDatabase;

    private int $kategoriId;
    private int $produkId;
    private int $supplierId;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic category, product, and supplier
        $this->kategoriId = DB::table('kategoris')->insertGetId([
            'nama' => 'Makanan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->produkId = DB::table('produks')->insertGetId([
            'kategori_id' => $this->kategoriId,
            'kode' => 'PRD-TEST-123',
            'nama' => 'Susu Enak Sekali',
            'harga_beli' => 5000.00,
            'harga_jual' => 7000.00,
            'stok' => 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->supplierId = DB::table('suppliers')->insertGetId([
            'nama_supplier' => 'PT Jaya Bersama',
            'alamat' => 'Jakarta',
            'no_hp' => '08123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_owner_and_admin_can_access_pembelian_index(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $admin = User::factory()->create(['role' => 'admin']);
        $kasir = User::factory()->create(['role' => 'kasir']);

        // Owner can access
        $this->actingAs($owner);
        $response = $this->get(route('pembelian.index'));
        $response->assertOk();

        // Admin can access
        $this->actingAs($admin);
        $response = $this->get(route('pembelian.index'));
        $response->assertOk();

        // Kasir cannot access (redirects to dashboard)
        $this->actingAs($kasir);
        $response = $this->get(route('pembelian.index'));
        $response->assertRedirect(route('kasir.index'));
    }

    public function test_can_create_pembelian_and_update_stok_and_harga_modal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('pembelian.store'), [
            'supplier_id' => $this->supplierId,
            'tanggal' => '2026-07-23',
            'keterangan' => 'Pembelian pertama',
            'items' => [
                [
                    'produk_id' => $this->produkId,
                    'qty' => 5,
                    'harga_beli' => 5500.00, // modal baru
                ]
            ]
        ]);

        $response->assertRedirect(route('pembelian.index'));
        $response->assertSessionHas('success');

        // Check Pembelian created
        $this->assertDatabaseHas('pembelians', [
            'supplier_id' => $this->supplierId,
            'total' => 27500.00, // 5 * 5500
        ]);

        $pembelian = Pembelian::first();
        $this->assertEquals('2026-07-23', $pembelian->tanggal->format('Y-m-d'));

        // Check DetailPembelian created
        $this->assertDatabaseHas('detail_pembelians', [
            'produk_id' => $this->produkId,
            'qty' => 5,
            'harga_beli' => 5500.00,
            'subtotal' => 27500.00,
        ]);

        // Check Product stok incremented (10 old + 5 new = 15)
        // and harga_beli updated to 5500
        $product = Product::find($this->produkId);
        $this->assertEquals(15, $product->stok);
        $this->assertEquals(5500.00, $product->harga_beli);
    }

    public function test_can_edit_pembelian_and_revert_stok_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // 1. Create a purchase first
        $pembelian = Pembelian::create([
            'nomor_pembelian' => 'PB-20260723-0001',
            'supplier_id' => $this->supplierId,
            'tanggal' => '2026-07-23',
            'total' => 25000.00,
            'user_id' => $admin->id,
        ]);
        DB::table('detail_pembelians')->insert([
            'pembelian_id' => $pembelian->id,
            'produk_id' => $this->produkId,
            'qty' => 5,
            'harga_beli' => 5000.00,
            'subtotal' => 25000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Update product stok to match (10 old + 5 purchase = 15)
        Product::find($this->produkId)->increment('stok', 5);

        // 2. Perform update
        $response = $this->put(route('pembelian.update', $pembelian), [
            'supplier_id' => $this->supplierId,
            'tanggal' => '2026-07-23',
            'keterangan' => 'Diubah',
            'items' => [
                [
                    'produk_id' => $this->produkId,
                    'qty' => 8, // Ucapkan 8 sekarang (dari 5)
                    'harga_beli' => 6000.00,
                ]
            ]
        ]);

        $response->assertRedirect(route('pembelian.index'));

        // Check updated Pembelian
        $this->assertDatabaseHas('pembelians', [
            'id' => $pembelian->id,
            'total' => 48000.00, // 8 * 6000
        ]);

        // Check Product stok: 15 (before update) - 5 (old qty) + 8 (new qty) = 18
        // and harga_beli updated to 6000
        $product = Product::find($this->produkId);
        $this->assertEquals(18, $product->stok);
        $this->assertEquals(6000.00, $product->harga_beli);
    }

    public function test_can_delete_pembelian_and_revert_stok(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // 1. Create a purchase first
        $pembelian = Pembelian::create([
            'nomor_pembelian' => 'PB-20260723-0001',
            'supplier_id' => $this->supplierId,
            'tanggal' => '2026-07-23',
            'total' => 25000.00,
            'user_id' => $admin->id,
        ]);
        DB::table('detail_pembelians')->insert([
            'pembelian_id' => $pembelian->id,
            'produk_id' => $this->produkId,
            'qty' => 5,
            'harga_beli' => 5000.00,
            'subtotal' => 25000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Product::find($this->produkId)->increment('stok', 5); // stok = 15

        // 2. Perform delete
        $response = $this->delete(route('pembelian.destroy', $pembelian));
        $response->assertRedirect(route('pembelian.index'));

        // Check database does not have Pembelian & detail
        $this->assertDatabaseMissing('pembelians', ['id' => $pembelian->id]);
        $this->assertDatabaseMissing('detail_pembelians', ['pembelian_id' => $pembelian->id]);

        // Check Product stok decreased back: 15 - 5 = 10
        $product = Product::find($this->produkId);
        $this->assertEquals(10, $product->stok);
    }

    public function test_pembelian_index_has_statistics_and_chart_data(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        // Create purchases
        $today = now()->toDateString();
        $p1 = Pembelian::create([
            'nomor_pembelian' => 'PB-1',
            'supplier_id' => $this->supplierId,
            'tanggal' => $today,
            'total' => 15000.00,
            'user_id' => $owner->id,
        ]);
        $p2 = Pembelian::create([
            'nomor_pembelian' => 'PB-2',
            'supplier_id' => $this->supplierId,
            'tanggal' => $today,
            'total' => 20000.00,
            'user_id' => $owner->id,
        ]);

        $response = $this->get(route('pembelian.index'));
        $response->assertOk();

        // Verify statistics in view
        $response->assertViewHas('totalHariIni', 35000.00);
        $response->assertViewHas('totalBulanIni', 35000.00);
        $response->assertViewHas('totalNominal', 35000.00);
        $response->assertViewHas('jumlahSupplier', 1);

        // Verify chart data is passed
        $response->assertViewHas('monthlyChartValues');
        $chartValues = $response->viewData('monthlyChartValues');
        $currentMonthIndex = (int)now()->format('n') - 1;
        $this->assertEquals(35000.00, $chartValues[$currentMonthIndex]);
    }

    public function test_pembelian_index_supports_advanced_filters_and_pagination(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        // Create a few purchases
        $p1 = Pembelian::create([
            'nomor_pembelian' => 'PB-MATCH',
            'supplier_id' => $this->supplierId,
            'tanggal' => '2026-07-20',
            'total' => 10000.00,
            'keterangan' => 'Special purchase',
            'user_id' => $owner->id,
        ]);
        $p2 = Pembelian::create([
            'nomor_pembelian' => 'PB-OTHER',
            'supplier_id' => $this->supplierId,
            'tanggal' => '2026-07-25',
            'total' => 20000.00,
            'keterangan' => 'Regular purchase',
            'user_id' => $owner->id,
        ]);

        // Filter by specific number
        $response = $this->get(route('pembelian.index', ['cari_nomor' => 'MATCH']));
        $response->assertOk();
        $pembelians = $response->viewData('pembelians');
        $this->assertCount(1, $pembelians);
        $this->assertEquals('PB-MATCH', $pembelians[0]->nomor_pembelian);

        // Filter by date range
        $response = $this->get(route('pembelian.index', [
            'tanggal_dari' => '2026-07-19',
            'tanggal_sampai' => '2026-07-22'
        ]));
        $response->assertOk();
        $pembelians = $response->viewData('pembelians');
        $this->assertCount(1, $pembelians);
        $this->assertEquals('PB-MATCH', $pembelians[0]->nomor_pembelian);

        // Dynamic page limit pagination test
        $response = $this->get(route('pembelian.index', ['per_page' => 25]));
        $response->assertOk();
        $pembelians = $response->viewData('pembelians');
        $this->assertEquals(25, $pembelians->perPage());
    }

    public function test_pembelian_show_supports_json_response(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $pembelian = Pembelian::create([
            'nomor_pembelian' => 'PB-JSON',
            'supplier_id' => $this->supplierId,
            'tanggal' => '2026-07-23',
            'total' => 50000.00,
            'user_id' => $owner->id,
        ]);

        // Request JSON output
        $response = $this->getJson(route('pembelian.show', $pembelian));
        $response->assertOk();
        $response->assertJsonFragment([
            'nomor_pembelian' => 'PB-JSON',
            'total' => '50000.00'
        ]);
    }

    public function test_pembelian_create_supports_prefilled_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('pembelian.create', [
            'produk_id' => $this->produkId,
        ]));

        $response->assertOk();
        $response->assertSee('Susu Enak Sekali');
        $response->assertSee('PRD-TEST-123');
    }
}
