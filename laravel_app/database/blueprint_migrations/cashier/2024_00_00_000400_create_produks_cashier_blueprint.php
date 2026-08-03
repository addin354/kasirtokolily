<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')
                ->constrained('kategoris')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('satuan_id')
                ->nullable()
                ->constrained('satuans')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('kode')->unique();
            $table->string('barcode', 100)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('harga_grosir', 15, 2)->default(0);
            $table->decimal('harga_bal', 15, 2)->default(0);
            $table->unsignedInteger('stok')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
