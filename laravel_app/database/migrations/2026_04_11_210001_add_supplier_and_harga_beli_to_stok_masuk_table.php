<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_masuk', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                ->nullable()
                ->after('produk_id')
                ->constrained('suppliers')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('harga_beli', 15, 2)->default(0)->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('stok_masuk', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn('harga_beli');
        });
    }
};
