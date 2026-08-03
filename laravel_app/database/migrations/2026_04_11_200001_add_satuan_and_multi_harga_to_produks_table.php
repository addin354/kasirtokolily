<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->foreignId('satuan_id')
                ->nullable()
                ->after('stok')
                ->constrained('satuans')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('harga_grosir', 15, 2)->default(0)->after('harga_jual');
            $table->decimal('harga_bal', 15, 2)->default(0)->after('harga_grosir');
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('satuan_id');
            $table->dropColumn(['harga_grosir', 'harga_bal']);
        });
    }
};
