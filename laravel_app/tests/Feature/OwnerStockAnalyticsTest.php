<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerStockAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_stock_page_shows_analytics_summary(): void
    {
        $this->actingAs(
            \App\Models\User::factory()->create([
                'role' => 'owner',
            ])
        );

        $response = $this->get(route('owner.stok'));

        $response->assertOk();
        $response->assertSee('Analisis');
        $response->assertDontSee('Hari terlaris');
        $response->assertDontSee('Jam ramai');
        $response->assertSee('Produk Terlaris');
        $response->assertDontSee('Top 5 produk');
    }

    public function test_dashboard_displays_stock_counts_and_restok_notifications(): void
    {
        $owner = \App\Models\User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        // Create categories, suppliers, and products
        $kategoriId = \DB::table('kategoris')->insertGetId([
            'nama' => 'Makanan Ringan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product1 = \App\Models\Product::create([
            'kode' => 'PRD-TEST-A',
            'barcode' => '123456',
            'nama' => 'Kecap ABC',
            'kategori_id' => $kategoriId,
            'stok' => 8,
            'stok_minimum' => 10, // stok <= minimum => Restok
            'harga_beli' => 5000,
            'harga_jual' => 7000,
            'harga_grosir' => 6500,
            'harga_bal' => 60000,
            'is_active' => true,
        ]);

        $product2 = \App\Models\Product::create([
            'kode' => 'PRD-TEST-B',
            'barcode' => '789012',
            'nama' => 'Gula Pasir',
            'kategori_id' => $kategoriId,
            'stok' => 20,
            'stok_minimum' => 15, // stok > minimum => Aman
            'harga_beli' => 10000,
            'harga_jual' => 12000,
            'harga_grosir' => 11500,
            'harga_bal' => 110000,
            'is_active' => true,
        ]);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Verify view data
        $response->assertViewHas('countAman', 1);
        $response->assertViewHas('countRestok', 1);

        // Verify notification is rendered in HTML
        $response->assertSee('Kecap ABC');
        $response->assertSee('tinggal 8 pcs');
        $response->assertSee('(Minimum 10)');
        $response->assertDontSee('Gula Pasir'); // Gula Pasir is safe, so it shouldn't show in notifications
    }

    public function test_owner_restok_page_shows_correct_products(): void
    {
        $owner = \App\Models\User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $kategoriId = \DB::table('kategoris')->insertGetId([
            'nama' => 'Minuman Ringan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = \App\Models\Product::create([
            'kode' => 'PRD-TEST-C',
            'barcode' => '111111',
            'nama' => 'Minyak Goreng',
            'kategori_id' => $kategoriId,
            'stok' => 3,
            'stok_minimum' => 8, // Needs restock
            'harga_beli' => 15000,
            'harga_jual' => 17000,
            'harga_grosir' => 16500,
            'harga_bal' => 160000,
            'is_active' => true,
        ]);

        $response = $this->get(route('owner.stok.restok'));
        $response->assertOk();

        $response->assertSee('Minyak Goreng');
        $response->assertSee('Minimum');
        $response->assertSee('Selisih');
        $response->assertSee('Supplier Terakhir');
    }

    public function test_owner_reports_hub_access_and_filters(): void
    {
        $owner = \App\Models\User::factory()->create(['role' => 'owner']);
        $kasir = \App\Models\User::factory()->create(['role' => 'kasir']);

        // Kasir should be blocked / redirected (role restriction)
        $this->actingAs($kasir);
        $response = $this->get(route('owner.reports'));
        $response->assertRedirect();

        // Owner can access reports hub
        $this->actingAs($owner);
        $response = $this->get(route('owner.reports'));
        $response->assertOk();
        $response->assertSee('Laporan Terpadu');
        $response->assertSee('Pilih & Filter Laporan', false);

        // Preview type "produk"
        $response = $this->get(route('owner.reports', ['report_type' => 'produk']));
        $response->assertOk();
        $response->assertSee('Laporan Daftar Produk');

        // Preview type "restok"
        $response = $this->get(route('owner.reports', ['report_type' => 'restok']));
        $response->assertOk();
        $response->assertSee('Laporan Produk Perlu Restok');

        // Preview type "persediaan"
        $response = $this->get(route('owner.reports', ['report_type' => 'persediaan']));
        $response->assertOk();
        $response->assertSee('Laporan Persediaan Barang');

        // Preview type "nilai_persediaan" redirects to "terlaris"
        $response = $this->get(route('owner.reports', ['report_type' => 'nilai_persediaan']));
        $response->assertRedirect(route('owner.reports', ['report_type' => 'terlaris']));

        // Preview type "kartu_stok"
        $response = $this->get(route('owner.reports', ['report_type' => 'kartu_stok']));
        $response->assertOk();
        $response->assertSee('Laporan Kartu Stok');
    }

    public function test_owner_reports_export_endpoints(): void
    {
        $owner = \App\Models\User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        // Export PDF
        $response = $this->get(route('owner.reports.pdf', ['report_type' => 'produk']));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        // Export Excel (CSV)
        $response = $this->get(route('owner.reports.export.excel', ['report_type' => 'persediaan']));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
