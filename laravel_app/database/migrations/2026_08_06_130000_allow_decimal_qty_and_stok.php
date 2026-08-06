<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->decimal('stok', 12, 3)->default(0)->change();
            $table->decimal('stok_minimum', 12, 3)->default(10)->change();
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->decimal('qty', 12, 3)->default(1)->change();
            $table->decimal('qty_input', 12, 3)->default(1)->change();
            $table->decimal('qty_pcs', 12, 3)->default(1)->change();
        });

        if (Schema::hasTable('stok_masuk')) {
            Schema::table('stok_masuk', function (Blueprint $table) {
                $table->decimal('jumlah', 12, 3)->default(1)->change();
            });
        }

        if (Schema::hasTable('detail_pembelians')) {
            Schema::table('detail_pembelians', function (Blueprint $table) {
                $table->decimal('qty', 12, 3)->default(1)->change();
            });
        }

        if (Schema::hasTable('stok_logs')) {
            Schema::table('stok_logs', function (Blueprint $table) {
                $table->decimal('masuk', 12, 3)->default(0)->change();
                $table->decimal('keluar', 12, 3)->default(0)->change();
                $table->decimal('saldo', 12, 3)->default(0)->change();
            });
        }

        if (Schema::hasTable('stok_opnames')) {
            Schema::table('stok_opnames', function (Blueprint $table) {
                $table->decimal('stok_sistem', 12, 3)->default(0)->change();
                $table->decimal('stok_fisik', 12, 3)->default(0)->change();
                $table->decimal('selisih', 12, 3)->default(0)->change();
            });
        }

        if (Schema::hasTable('penyesuaian_stoks')) {
            Schema::table('penyesuaian_stoks', function (Blueprint $table) {
                $table->decimal('jumlah', 12, 3)->default(0)->change();
            });
        }
    }

    public function down(): void
    {
    }
};
