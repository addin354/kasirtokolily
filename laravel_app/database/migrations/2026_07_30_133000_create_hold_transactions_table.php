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
        Schema::create('hold_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('kasir_id')->nullable();
            $table->string('pelanggan')->nullable();
            $table->text('catatan')->nullable();
            $table->json('cart_data');
            $table->decimal('total', 15, 2);
            $table->string('status')->default('hold');
            $table->timestamps();

            $table->foreign('kasir_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hold_transactions');
    }
};
