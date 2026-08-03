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
        Schema::create('retur', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksi_id')->nullable();
            $table->unsignedBigInteger('produk_id')->nullable();
            $table->string('no_retur')->nullable();
            $table->string('produk_nama')->nullable();
            $table->integer('qty')->default(0);
            $table->text('alasan')->nullable();
            $table->string('status')->default('Dalam Proses');
            $table->date('tanggal_retur')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur');
    }
};
