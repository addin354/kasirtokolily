<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Satuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_export_all_products_and_critical_stock_files(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner']));

        $category = Category::create(['nama' => 'Kategori Uji']);
        $satuan = Satuan::create(['nama' => 'Pcs']);

        Product::create([
            'kode' => 'PRD-001',
            'barcode' => 'BAR-001',
            'nama' => 'Produk A',
            'kategori_id' => $category->id,
            'satuan_id' => $satuan->id,
            'harga_beli' => 10000,
            'harga_jual' => 15000,
            'harga_grosir' => 14000,
            'harga_bal' => 13000,
            'isi_per_bal' => 10,
            'stok' => 0,
        ]);

        $response = $this->get(route('produk.export.excel'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $response = $this->get(route('owner.stok.kritis.pdf'));
        $response->assertOk();

        $response = $this->get(route('owner.stok.kritis.excel'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
