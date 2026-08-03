<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create stok_logs table
        Schema::create('stok_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')
                ->constrained('produks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->dateTime('tanggal');
            $table->string('jenis'); // 'Pembelian', 'Penjualan', 'Retur', 'Stock Opname', 'Penyesuaian'
            $table->integer('masuk')->default(0);
            $table->integer('keluar')->default(0);
            $table->integer('saldo');
            $table->string('referensi')->nullable();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 2. Create stok_opnames table
        Schema::create('stok_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')
                ->constrained('produks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->integer('stok_sistem');
            $table->integer('stok_fisik');
            $table->integer('selisih');
            $table->string('alasan')->nullable();
            $table->date('tanggal');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });

        // 3. Create penyesuaian_stoks table
        Schema::create('penyesuaian_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')
                ->constrained('produks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('jenis'); // 'Tambah', 'Kurang'
            $table->integer('jumlah');
            $table->string('alasan');
            $table->date('tanggal');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });

        // 4. Populate historical data into stok_logs
        $this->populateHistory();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_stoks');
        Schema::dropIfExists('stok_opnames');
        Schema::dropIfExists('stok_logs');
    }

    /**
     * Populate stok_logs from historical transactions.
     */
    private function populateHistory(): void
    {
        // Get all products
        $productIds = DB::table('produks')->pluck('id');

        foreach ($productIds as $produkId) {
            $events = [];

            // A. Incoming (stok_masuk)
            $stokMasuk = DB::table('stok_masuk')
                ->where('produk_id', $produkId)
                ->get();
            foreach ($stokMasuk as $sm) {
                $events[] = [
                    'tanggal' => $sm->tanggal,
                    'jenis' => 'Stok Masuk',
                    'masuk' => $sm->jumlah,
                    'keluar' => 0,
                    'referensi' => 'SM-' . $sm->id,
                    'user_id' => null,
                    'keterangan' => $sm->keterangan,
                ];
            }

            // B. Pembelian (detail_pembelians)
            $pembelian = DB::table('detail_pembelians as dp')
                ->join('pembelians as p', 'p.id', '=', 'dp.pembelian_id')
                ->where('dp.produk_id', $produkId)
                ->select(['dp.qty', 'p.tanggal', 'p.nomor_pembelian', 'p.user_id', 'p.keterangan'])
                ->get();
            foreach ($pembelian as $pb) {
                $events[] = [
                    'tanggal' => $pb->tanggal . ' 00:00:00',
                    'jenis' => 'Pembelian',
                    'masuk' => $pb->qty,
                    'keluar' => 0,
                    'referensi' => $pb->nomor_pembelian,
                    'user_id' => $pb->user_id,
                    'keterangan' => $pb->keterangan,
                ];
            }

            // C. Penjualan (detail_transaksi)
            $penjualan = DB::table('detail_transaksi as dt')
                ->join('transaksi as t', 't.id', '=', 'dt.transaksi_id')
                ->where('dt.produk_id', $produkId)
                ->select(['dt.qty_pcs', 't.tanggal', 't.id'])
                ->get();
            foreach ($penjualan as $pj) {
                $events[] = [
                    'tanggal' => $pj->tanggal,
                    'jenis' => 'Penjualan',
                    'masuk' => 0,
                    'keluar' => $pj->qty_pcs,
                    'referensi' => 'TRX-' . $pj->id,
                    'user_id' => null,
                    'keterangan' => 'Penjualan Kasir',
                ];
            }

            // D. Retur disetujui (retur)
            $retur = DB::table('retur')
                ->where('produk_id', $produkId)
                ->where('status', 'disetujui')
                ->get();
            foreach ($retur as $rt) {
                $events[] = [
                    'tanggal' => ($rt->tanggal_retur ?? now()->toDateString()) . ' 00:00:00',
                    'jenis' => 'Retur',
                    'masuk' => $rt->qty,
                    'keluar' => 0,
                    'referensi' => $rt->no_retur ?? ('RT-' . $rt->id),
                    'user_id' => $rt->user_id ?? null,
                    'keterangan' => $rt->alasan,
                ];
            }

            // Sort events chronologically by tanggal
            usort($events, function ($a, $b) {
                return strcmp($a['tanggal'], $b['tanggal']);
            });

            // Calculate running balance and save
            $saldo = 0;
            foreach ($events as $event) {
                $saldo = $saldo + $event['masuk'] - $event['keluar'];
                DB::table('stok_logs')->insert([
                    'produk_id' => $produkId,
                    'tanggal' => $event['tanggal'],
                    'jenis' => $event['jenis'],
                    'masuk' => $event['masuk'],
                    'keluar' => $event['keluar'],
                    'saldo' => $saldo,
                    'referensi' => $event['referensi'],
                    'user_id' => $event['user_id'],
                    'keterangan' => $event['keterangan'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
