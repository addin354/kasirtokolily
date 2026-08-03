<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->string('metode_pembayaran')->default('Cash')->after('total');
        });

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->string('metode_pembayaran')->default('Cash')->after('nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn('metode_pembayaran');
        });

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->dropColumn('metode_pembayaran');
        });
    }
};
