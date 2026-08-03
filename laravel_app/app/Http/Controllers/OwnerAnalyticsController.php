<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerAnalyticsController extends Controller
{
    /**
     * Analisis penjualan: hari terlaris, jam ramai, produk terlaris (periode N hari ke belakang).
     *
     * @note Fungsi agregat WEEKDAY/HOUR memakai sintaks MySQL (sesuai XAMPP).
     */
    public function __invoke(Request $request): View
    {
        $range = (int) $request->query('periode', 30);
        $range = min(365, max(1, $range));
        $from = Carbon::now()->subDays($range)->startOfDay();

        // --- Hari terlaris: agregat per hari dalam minggu (WEEKDAY: 0=Senin .. 6=Minggu) — nilai = total penjualan ---
        $perHariMinggu = Transaksi::query()
            ->where('tanggal', '>=', $from)
            ->selectRaw('WEEKDAY(tanggal) as hari_minggu')
            ->selectRaw('COALESCE(SUM(total), 0) as total_penjualan')
            ->selectRaw('COUNT(*) as jumlah_transaksi')
            ->groupByRaw('WEEKDAY(tanggal)')
            ->get()
            ->keyBy('hari_minggu');

        $labelHari = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $dataPenjualanPerHari = [];
        for ($d = 0; $d < 7; $d++) {
            $row = $perHariMinggu->get($d);
            $dataPenjualanPerHari[] = $row
                ? (float) $row->total_penjualan
                : 0.0;
        }

        if (array_sum($dataPenjualanPerHari) <= 0) {
            $hariTerlarisLabel = '-';
            $hariTerlarisNilai = 0.0;
        } else {
            $idxHariTerlaris = (int) array_search(max($dataPenjualanPerHari), $dataPenjualanPerHari, true);
            $hariTerlarisLabel = $labelHari[$idxHariTerlaris] ?? '-';
            $hariTerlarisNilai = $dataPenjualanPerHari[$idxHariTerlaris] ?? 0.0;
        }

        // --- Jam ramai: jumlah transaksi per jam (0–23) ---
        $perJam = Transaksi::query()
            ->where('tanggal', '>=', $from)
            ->selectRaw('HOUR(tanggal) as jam')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupByRaw('HOUR(tanggal)')
            ->orderBy('jam')
            ->get()
            ->keyBy('jam');

        $labelJam = range(0, 23);
        $dataJumlahPerJam = [];
        foreach ($labelJam as $h) {
            $dataJumlahPerJam[] = (int) ($perJam->get($h)->jumlah ?? 0);
        }

        if (array_sum($dataJumlahPerJam) <= 0) {
            $jamRamaiJumlah = 0;
            $jamRamaiLabel = '-';
        } else {
            $idxJamRamai = (int) array_search(max($dataJumlahPerJam), $dataJumlahPerJam, true);
            $jamRamaiJumlah = $dataJumlahPerJam[$idxJamRamai] ?? 0;
            $jamRamaiLabel = sprintf('%02d:00 – %02d:59', $idxJamRamai, $idxJamRamai);
        }

        // --- Produk terlaris: top 10 by qty ---
        $produkRows = \App\Models\DetailTransaksi::query()
            ->select('detail_transaksi.produk_id')
            ->selectRaw('SUM(detail_transaksi.qty) as total_qty')
            ->selectRaw('SUM(detail_transaksi.subtotal) as total_subtotal')
            ->join('transaksi', 'transaksi.id', '=', 'detail_transaksi.transaksi_id')
            ->where('transaksi.tanggal', '>=', $from)
            ->groupBy('detail_transaksi.produk_id')
            ->orderByDesc('total_qty')
            ->with(['product' => function ($q) {
                $q->select('id', 'nama');
            }])
            ->limit(10)
            ->get();

        $labelProduk = $produkRows->map(function ($row) {
            return $row->product?->nama ?? ('#'.$row->produk_id);
        })->all();

        $dataQtyProduk = $produkRows->pluck('total_qty')->map(fn ($q) => (int) $q)->all();

        $produkTerlaris = $produkRows->isNotEmpty() ? $produkRows->first() : null;
        $produkTerlarisNama = $produkTerlaris?->product?->nama
            ?? ($produkTerlaris ? 'Produk #'.$produkTerlaris->produk_id : '-');
        $produkTerlarisQty = $produkTerlaris ? (int) $produkTerlaris->total_qty : 0;

        return view('owner.analisis', [
            'periodeHari' => $range,
            'dariTanggal' => $from->toDateString(),
            'sampaiTanggal' => now()->toDateString(),

            'hariTerlarisLabel' => $hariTerlarisLabel,
            'hariTerlarisNilai' => $hariTerlarisNilai,

            'jamRamaiLabel' => $jamRamaiLabel,
            'jamRamaiJumlah' => $jamRamaiJumlah,

            'produkTerlarisNama' => $produkTerlarisNama,
            'produkTerlarisQty' => $produkTerlarisQty,

            'chartHari' => [
                'labels' => $labelHari,
                'data' => $dataPenjualanPerHari,
            ],
            'chartJam' => [
                'labels' => $labelJam,
                'data' => $dataJumlahPerJam,
            ],
            'chartProduk' => [
                'labels' => $labelProduk,
                'data' => $dataQtyProduk,
            ],
        ]);
    }
}
