<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_predictions')) {
            return;
        }

        Schema::create('stock_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')
                ->constrained('produks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->date('periode_mulai');
            $table->date('periode_akhir')->nullable();
            $table->unsignedInteger('prediksi_stok');
            $table->string('metode', 50)->default('moving_average');
            $table->decimal('confidence', 5, 2)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_predictions');
    }
};
