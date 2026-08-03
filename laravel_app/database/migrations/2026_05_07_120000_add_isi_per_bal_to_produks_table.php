<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->unsignedInteger('isi_per_bal')
                ->nullable()
                ->after('harga_bal')
                ->comment('Jumlah pcs per 1 bal (untuk penjualan jenis Bal)');
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('isi_per_bal');
        });
    }
};
