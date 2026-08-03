<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class StockPredictionService
{
    /**
     * Prediksi stok berdasarkan rata-rata penjualan harian (simple moving average)
     * dari total qty dalam jendela waktu dibagi jumlah hari jendela.
     *
     * Sumber: detail_transaksi + transaksi (filter tanggal transaksi).
     */
    public function predict(int $productId): array
    {
        $product = Product::query()
            ->select(['id', 'nama', 'stok'])
            ->whereKey($productId)
            ->first();

        if ($product === null) {
            throw (new ModelNotFoundException)->setModel(Product::class, $productId);
        }

        $windowDays = max(1, (int) config('pos.stock_prediction.window_days', 30));
        $coverDays = max(1, (int) config('pos.stock_prediction.cover_days', 14));

        $from = now()->subDays($windowDays)->startOfDay();

        $totalTerjual = (int) DB::table('detail_transaksi as d')
            ->join('transaksi as t', 't.id', '=', 'd.transaksi_id')
            ->where('d.produk_id', $productId)
            ->where('t.tanggal', '>=', $from)
            ->sum('d.qty');

        $rataHarian = $totalTerjual / $windowDays;
        $stok = (int) $product->stok;

        $estimasiHariHinggaHabis = null;
        $estimasiTanggalHabis = null;

        if ($rataHarian > 0) {
            $estimasiHariHinggaHabis = (int) floor($stok / $rataHarian);
            $estimasiTanggalHabis = now()->addDays($estimasiHariHinggaHabis)->toDateString();
        }

        $targetStokAman = $rataHarian > 0
            ? (int) ceil($coverDays * $rataHarian)
            : $stok;

        $rekomendasiRestock = $rataHarian > 0
            ? max(0, $targetStokAman - $stok)
            : 0;

        $catatan = $rataHarian <= 0
            ? 'Tidak ada penjualan pada periode; rata harian 0, estimasi stok habis tidak dihitung.'
            : null;

        return [
            'produk_id' => (int) $product->id,
            'nama' => $product->nama,
            'stok_saat_ini' => $stok,
            'periode_hari' => $windowDays,
            'total_terjual_periode' => $totalTerjual,
            'rata_harian' => round($rataHarian, 4),
            'estimasi_hari_hingga_habis' => $estimasiHariHinggaHabis,
            'estimasi_tanggal_habis' => $estimasiTanggalHabis,
            'cakupan_restock_hari' => $coverDays,
            'rekomendasi_restock' => $rekomendasiRestock,
            'target_stok_aman' => $targetStokAman,
            'metode' => 'moving_average_sederhana',
            'catatan' => $catatan,
        ];
    }
}
