<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('view-dashboard');

        $startToday = now()->startOfDay();
        $endToday = now()->endOfDay();

        $totalProduk = Product::query()->count();

        $totalTransaksiHariIni = Transaksi::query()
            ->whereBetween('tanggal', [$startToday, $endToday])
            ->count();

        $pendapatanHariIni = (float) Transaksi::query()
            ->whereBetween('tanggal', [$startToday, $endToday])
            ->sum('total');

        $labaHariIni = (float) DB::table('detail_transaksi as dt')
            ->join('produks as p', 'p.id', '=', 'dt.produk_id')
            ->join('transaksi as t', 't.id', '=', 'dt.transaksi_id')
            ->whereBetween('t.tanggal', [$startToday, $endToday])
            ->selectRaw('COALESCE(SUM(dt.subtotal - (COALESCE(NULLIF(dt.qty_pcs, 0), dt.qty) * p.harga_beli)), 0) as laba')
            ->value('laba');

        $fromChart = now()->subDays(29)->startOfDay();
        $toChart = now()->endOfDay();

        $groupedPenjualan = Transaksi::query()
            ->whereBetween('tanggal', [$fromChart, $toChart])
            ->get(['tanggal', 'total'])
            ->groupBy(fn (Transaksi $t) => $t->tanggal->format('Y-m-d'));

        $chartLabels = [];
        $chartValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $key = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d/m');
            $chartValues[] = round((float) ($groupedPenjualan->get($key)?->sum('total') ?? 0), 2);
        }

        $countAman = Product::query()->whereColumn('stok', '>', 'stok_minimum')->count();
        $countRestok = Product::query()->whereColumn('stok', '<=', 'stok_minimum')->count();
        $restokProducts = Product::query()
            ->with(['satuanModel'])
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('nama')
            ->get();

        return view('dashboard.index', [
            'totalProduk' => $totalProduk,
            'totalTransaksiHariIni' => $totalTransaksiHariIni,
            'pendapatanHariIni' => $pendapatanHariIni,
            'labaHariIni' => $labaHariIni,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'countAman' => $countAman,
            'countRestok' => $countRestok,
            'restokProducts' => $restokProducts,
        ]);
    }
}
