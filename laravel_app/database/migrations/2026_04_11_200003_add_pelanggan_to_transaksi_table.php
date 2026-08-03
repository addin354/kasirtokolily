<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreignId('pelanggan_id')
                ->nullable()
                ->after('id')
                ->constrained('pelanggans')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('nama_pelanggan')->nullable()->after('pelanggan_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pelanggan_id');
            $table->dropColumn('nama_pelanggan');
        });
    }
};
