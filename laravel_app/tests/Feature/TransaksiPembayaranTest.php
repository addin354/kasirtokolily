<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransaksiPembayaranTest extends TestCase
{
    use RefreshDatabase;

    private int $kategoriId;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->kategoriId = DB::table('kategoris')->insertGetId([
            'nama' => 'Bahan Pokok',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'kategori_id' => $this->kategoriId,
            'kode' => 'PRD-' . uniqid(),
            'nama' => 'Produk Test',
            'harga_beli' => 10000,
            'harga_jual' => 15000,
            'stok' => 10,
            'is_active' => true,
        ], $attributes));
    }

    private function setupCartSession(Product $product, int $qty = 1): void
    {
        session()->put('pos_cart', [
            [
                'produk_id' => $product->id,
                'jenis_harga' => 'eceran',
                'qty' => $qty,
            ]
        ]);
    }

    public function test_cash_payment_stores_successfully_with_correct_kembalian(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $product = $this->createProduct([
            'nama' => 'Kecap Manis',
            'harga_jual' => 15000,
            'harga_beli' => 10000,
            'stok' => 10,
        ]);

        $this->actingAs($kasir);
        $this->setupCartSession($product, 2); // Total = 30,000

        $response = $this->post(route('kasir.transaksi.store'), [
            'metode_pembayaran' => 'Cash',
            'bayar' => 50000,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('transaksi', [
            'metode_pembayaran' => 'Cash',
            'total' => 30000,
            'bayar' => 50000,
            'kembalian' => 20000,
            'nama_bank' => null,
            'nomor_referensi' => null,
        ]);
    }

    public function test_transfer_bank_payment_stores_successfully_with_bank_details(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $product = $this->createProduct([
            'nama' => 'Minyak Goreng',
            'harga_jual' => 20000,
            'harga_beli' => 15000,
            'stok' => 10,
        ]);

        $this->actingAs($kasir);
        $this->setupCartSession($product, 1); // Total = 20,000

        $response = $this->post(route('kasir.transaksi.store'), [
            'metode_pembayaran' => 'Transfer Bank',
            'nama_bank' => 'BCA',
            'nomor_referensi' => 'TRF-BCA-98213',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('transaksi', [
            'metode_pembayaran' => 'Transfer Bank',
            'total' => 20000,
            'bayar' => 20000, // Automatically set to total
            'kembalian' => 0,
            'nama_bank' => 'BCA',
            'nomor_referensi' => 'TRF-BCA-98213',
        ]);
    }

    public function test_qris_payment_stores_successfully_with_reference(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $product = $this->createProduct([
            'nama' => 'Beras Pandan',
            'harga_jual' => 60000,
            'harga_beli' => 50000,
            'stok' => 5,
        ]);

        $this->actingAs($kasir);
        $this->setupCartSession($product, 1); // Total = 60,000

        $response = $this->post(route('kasir.transaksi.store'), [
            'metode_pembayaran' => 'QRIS',
            'nomor_referensi' => 'QRIS-REF-998811',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('transaksi', [
            'metode_pembayaran' => 'QRIS',
            'total' => 60000,
            'bayar' => 60000, // Automatically set to total
            'kembalian' => 0,
            'nama_bank' => null,
            'nomor_referensi' => 'QRIS-REF-998811',
        ]);
    }

    public function test_laporan_penjualan_filters_by_payment_method(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $product = $this->createProduct(['harga_jual' => 10000, 'harga_beli' => 8000, 'stok' => 100]);

        // Cash transaction
        Transaksi::create([
            'pelanggan_id' => null,
            'nama_pelanggan' => 'Umum',
            'tanggal' => now(),
            'total' => 10000,
            'bayar' => 10000,
            'kembalian' => 0,
            'metode_pembayaran' => 'Cash',
        ]);

        // QRIS transaction
        Transaksi::create([
            'pelanggan_id' => null,
            'nama_pelanggan' => 'Umum',
            'tanggal' => now(),
            'total' => 20000,
            'bayar' => 20000,
            'kembalian' => 0,
            'metode_pembayaran' => 'QRIS',
            'nomor_referensi' => 'QRIS-123',
        ]);

        $this->actingAs($owner);

        // Filter Cash
        $response = $this->get(route('laporan.penjualan', ['metode_pembayaran' => 'Cash']));
        $response->assertOk();
        $response->assertSee('Cash');

        // Filter QRIS
        $response = $this->get(route('laporan.penjualan', ['metode_pembayaran' => 'QRIS']));
        $response->assertOk();
        $response->assertSee('QRIS');
    }

    public function test_owner_dashboard_shows_correct_payment_method_stats(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        // Cash transaction
        Transaksi::create([
            'pelanggan_id' => null,
            'nama_pelanggan' => 'Umum',
            'tanggal' => now(),
            'total' => 50000,
            'bayar' => 50000,
            'kembalian' => 0,
            'metode_pembayaran' => 'Cash',
        ]);

        // Transfer Bank transaction
        Transaksi::create([
            'pelanggan_id' => null,
            'nama_pelanggan' => 'Umum',
            'tanggal' => now(),
            'total' => 150000,
            'bayar' => 150000,
            'kembalian' => 0,
            'metode_pembayaran' => 'Transfer Bank',
            'nama_bank' => 'BRI',
        ]);

        $this->actingAs($owner);
        $response = $this->get(route('owner.stok'));
        $response->assertOk();

        // Check if stats are rendered
        $response->assertSee('Rp 50.000');
        $response->assertSee('Rp 150.000');
        $response->assertSee('1 Transaksi');
    }
}
