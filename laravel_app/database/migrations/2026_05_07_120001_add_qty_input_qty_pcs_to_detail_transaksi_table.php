<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->unsignedInteger('qty_input')
                ->default(1)
                ->after('jenis_harga')
                ->comment('Qty yang diinput kasir (pcs untuk ecer/grosir, bal untuk bal)');
            $table->unsignedInteger('qty_pcs')
                ->default(0)
                ->after('qty_input')
                ->comment('Konversi ke pcs untuk pengurangan stok & laporan');
        });

        DB::table('detail_transaksi')->update([
            'qty_input' => DB::raw('qty'),
            'qty_pcs' => DB::raw('qty'),
        ]);
    }

    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn(['qty_input', 'qty_pcs']);
        });
    }
};
