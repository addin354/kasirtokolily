<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pengeluaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PengeluaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_admin_can_access_pengeluaran_index(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $admin = User::factory()->create(['role' => 'admin']);
        $kasir = User::factory()->create(['role' => 'kasir']);

        // Owner can access
        $this->actingAs($owner);
        $response = $this->get(route('pengeluaran.index'));
        $response->assertOk();

        // Admin can access
        $this->actingAs($admin);
        $response = $this->get(route('pengeluaran.index'));
        $response->assertOk();

        // Kasir cannot access (redirects to their default route)
        $this->actingAs($kasir);
        $response = $this->get(route('pengeluaran.index'));
        $response->assertRedirect(route('kasir.index'));
    }

    public function test_can_create_and_validate_pengeluaran(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Invalid validation check
        $response = $this->post(route('pengeluaran.store'), [
            'tanggal' => '',
            'kategori' => 'Kategori Palsu', // Not in categories list
            'nominal' => -100, // Minimal 0
        ]);

        $response->assertSessionHasErrors(['tanggal', 'kategori', 'nominal']);

        // Valid storage check
        $response = $this->post(route('pengeluaran.store'), [
            'tanggal' => '2026-07-23',
            'kategori' => 'Listrik',
            'keterangan' => 'Tagihan bulan Juli',
            'nominal' => 150000,
        ]);

        $response->assertRedirect(route('pengeluaran.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengeluarans', [
            'kategori' => 'Listrik',
            'keterangan' => 'Tagihan bulan Juli',
            'nominal' => 150000.00,
            'user_id' => $admin->id,
        ]);

        $pengeluaran = Pengeluaran::first();
        $this->assertNotNull($pengeluaran->nomor_pengeluaran);
        $expectedPrefix = 'OUT-' . now()->format('Ymd') . '-';
        $this->assertStringStartsWith($expectedPrefix, $pengeluaran->nomor_pengeluaran);
    }

    public function test_can_edit_and_update_pengeluaran(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $pengeluaran = Pengeluaran::create([
            'nomor_pengeluaran' => 'OUT-20260723-0001',
            'tanggal' => '2026-07-23',
            'kategori' => 'Listrik',
            'keterangan' => 'Tagihan',
            'nominal' => 100000.00,
            'user_id' => $admin->id,
        ]);

        $response = $this->put(route('pengeluaran.update', $pengeluaran), [
            'tanggal' => '2026-07-24',
            'kategori' => 'Internet',
            'keterangan' => 'Tagihan Baru',
            'nominal' => 200000.00,
        ]);

        $response->assertRedirect(route('pengeluaran.index'));
        $this->assertDatabaseHas('pengeluarans', [
            'id' => $pengeluaran->id,
            'kategori' => 'Internet',
            'keterangan' => 'Tagihan Baru',
            'nominal' => 200000.00,
        ]);
    }

    public function test_can_delete_pengeluaran(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $pengeluaran = Pengeluaran::create([
            'nomor_pengeluaran' => 'OUT-20260723-0001',
            'tanggal' => '2026-07-23',
            'kategori' => 'Listrik',
            'keterangan' => 'Tagihan',
            'nominal' => 100000.00,
            'user_id' => $admin->id,
        ]);

        $response = $this->delete(route('pengeluaran.destroy', $pengeluaran));
        $response->assertRedirect(route('pengeluaran.index'));

        $this->assertDatabaseMissing('pengeluarans', ['id' => $pengeluaran->id]);
    }

    public function test_dashboard_kecil_computes_correctly(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        // We will mock/insert records for different dates
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $lastMonth = now()->subMonth()->toDateString();
        $lastYear = now()->subYear()->toDateString();

        Pengeluaran::create([
            'nomor_pengeluaran' => 'OUT-1',
            'tanggal' => $today,
            'kategori' => 'Listrik',
            'nominal' => 50000.00,
            'user_id' => $owner->id,
        ]);

        Pengeluaran::create([
            'nomor_pengeluaran' => 'OUT-2',
            'tanggal' => $today,
            'kategori' => 'Water',
            'kategori' => 'Air',
            'nominal' => 25000.00,
            'user_id' => $owner->id,
        ]);

        // Insert a record for last month (not this month, but this year)
        // Make sure it doesn't cross into last year if current month is January!
        $monthOffset = now()->month == 1 ? now()->startOfMonth()->toDateString() : $lastMonth;
        Pengeluaran::create([
            'nomor_pengeluaran' => 'OUT-3',
            'tanggal' => $monthOffset,
            'kategori' => 'Gaji',
            'nominal' => 100000.00,
            'user_id' => $owner->id,
        ]);

        $response = $this->get(route('pengeluaran.index'));
        $response->assertOk();

        // Total today should be 75,000
        $response->assertViewHas('pengeluaranHariIni', 75000.00);
    }
}
