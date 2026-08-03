<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\StokLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class OwnerStockController extends Controller
{
    /**
     * Dashboard owner: monitoring stok + ringkasan analisis penjualan.
     */
    public function __invoke(Request $request): View
    {
        // 1. KPI Cards data
        $totalProducts = Product::count();
        $totalSuppliers = Supplier::count();
        
        // Nilai persediaan: stok * harga_beli
        $nilaiPersediaan = Product::sum(DB::raw('stok * harga_beli'));
        
        $barangAman = Product::whereColumn('stok', '>', 'stok_minimum')->count();
        $barangRestok = Product::whereColumn('stok', '<=', 'stok_minimum')->count();

        $totalPenjualan = (float) Transaksi::sum('total');
        $totalPembelian = (float) DB::table('pembelians')->sum('total');
        
        $totalLaba = (float) DB::table('detail_transaksi as dt')
            ->join('produks as p', 'p.id', '=', 'dt.produk_id')
            ->sum(DB::raw('dt.subtotal - (COALESCE(NULLIF(dt.qty_pcs, 0), dt.qty) * p.harga_beli)'));

        // 2. Warnings / Notifications
        $countRestok = $barangRestok;
        $restokProducts = Product::whereColumn('stok', '<=', 'stok_minimum')->with(['category', 'satuanModel'])->orderBy('stok')->get();
        $countNegatif = Product::where('stok', '<', 0)->count();
        $negatifProducts = Product::where('stok', '<', 0)->with('category')->get();

        // 3. Tables data
        // Top 10 Produk Terlaris
        $topProducts = DetailTransaksi::query()
            ->select('detail_transaksi.produk_id')
            ->selectRaw('SUM(COALESCE(NULLIF(detail_transaksi.qty_pcs, 0), detail_transaksi.qty)) as total_qty')
            ->selectRaw('SUM(detail_transaksi.subtotal) as total_subtotal')
            ->join('transaksi', 'transaksi.id', '=', 'detail_transaksi.transaksi_id')
            ->groupBy('detail_transaksi.produk_id')
            ->orderByDesc('total_qty')
            ->with('product.category')
            ->limit(10)
            ->get();

        // Top 10 Produk Tidak Laku (Bottom sales)
        $bottomSales = DB::table('produks as p')
            ->leftJoin('detail_transaksi as dt', 'dt.produk_id', '=', 'p.id')
            ->select('p.id')
            ->selectRaw('COALESCE(SUM(dt.qty_pcs), 0) as total_qty')
            ->groupBy('p.id')
            ->orderBy('total_qty', 'asc')
            ->limit(10)
            ->get();

        $bottomProductIds = $bottomSales->pluck('id')->all();
        $productsMap = Product::whereIn('id', $bottomProductIds)
            ->with('category')
            ->get()
            ->keyBy('id');

        $bottomProducts = $bottomSales->map(function ($row) use ($productsMap) {
            $p = $productsMap->get($row->id);
            if ($p) {
                $p->total_qty = $row->total_qty;
            }
            return $p;
        })->filter();

        // Produk Perlu Restok (stok <= stok_minimum)
        $restokTableProducts = Product::query()
            ->with('category')
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('stok')
            ->limit(10)
            ->get();

        // Riwayat Aktivitas Terbaru (stok logs)
        $recentActivities = StokLog::query()
            ->with(['product', 'user'])
            ->latest('tanggal')
            ->limit(10)
            ->get();

        // 4. Monthly Charts data (last 12 months)
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateFormat = $isSqlite ? "strftime('%Y-%m', tanggal)" : "DATE_FORMAT(tanggal, '%Y-%m')";

        $monthlySales = DB::table('transaksi')
            ->selectRaw("$dateFormat as month, SUM(total) as total")
            ->where('tanggal', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $monthlyPurchases = DB::table('pembelians')
            ->selectRaw("$dateFormat as month, SUM(total) as total")
            ->where('tanggal', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $monthlyLaba = DB::table('detail_transaksi as dt')
            ->join('transaksi as t', 't.id', '=', 'dt.transaksi_id')
            ->join('produks as p', 'p.id', '=', 'dt.produk_id')
            ->selectRaw("$dateFormat as month, SUM(dt.subtotal - (COALESCE(NULLIF(dt.qty_pcs, 0), dt.qty) * p.harga_beli)) as total")
            ->where('t.tanggal', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $chartMonths = [];
        $salesDataset = [];
        $purchaseDataset = [];
        $labaDataset = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthKey = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M Y');
            
            $chartMonths[] = $monthLabel;
            $salesDataset[] = (float) ($monthlySales->get($monthKey)->total ?? 0);
            $purchaseDataset[] = (float) ($monthlyPurchases->get($monthKey)->total ?? 0);
            $labaDataset[] = (float) ($monthlyLaba->get($monthKey)->total ?? 0);
        }

        // 5. Stock Movement Chart data (last 30 days)
        $stokMovement = DB::table('stok_logs')
            ->selectRaw('DATE(tanggal) as date, SUM(masuk) as total_in, SUM(keluar) as total_out')
            ->where('tanggal', '>=', now()->subDays(30)->startOfDay())
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $movementLabels = [];
        $inboundData = [];
        $outboundData = [];

        for ($i = 29; $i >= 0; $i--) {
            $dt = now()->subDays($i)->toDateString();
            $label = now()->subDays($i)->format('d/M');
            
            $movementLabels[] = $label;
            $inboundData[] = (int) ($stokMovement->get($dt)->total_in ?? 0);
            $outboundData[] = (int) ($stokMovement->get($dt)->total_out ?? 0);
        }

        // 6. Payment method statistics
        $statsCashCount = Transaksi::where(function($q) {
            $q->where('metode_pembayaran', 'Cash')->orWhereNull('metode_pembayaran');
        })->count();
        $statsCashAmount = (float) Transaksi::where(function($q) {
            $q->where('metode_pembayaran', 'Cash')->orWhereNull('metode_pembayaran');
        })->sum('total');

        $statsTransferCount = Transaksi::where('metode_pembayaran', 'Transfer Bank')->count();
        $statsTransferAmount = (float) Transaksi::where('metode_pembayaran', 'Transfer Bank')->sum('total');

        $statsQrisCount = Transaksi::where('metode_pembayaran', 'QRIS')->count();
        $statsQrisAmount = (float) Transaksi::where('metode_pembayaran', 'QRIS')->sum('total');

        return view('owner.stok', compact(
            'totalProducts',
            'totalSuppliers',
            'nilaiPersediaan',
            'barangAman',
            'barangRestok',
            'totalPenjualan',
            'totalPembelian',
            'totalLaba',
            'countRestok',
            'restokProducts',
            'countNegatif',
            'negatifProducts',
            'topProducts',
            'bottomProducts',
            'restokTableProducts',
            'recentActivities',
            'chartMonths',
            'salesDataset',
            'purchaseDataset',
            'labaDataset',
            'movementLabels',
            'inboundData',
            'outboundData',
            'statsCashCount',
            'statsCashAmount',
            'statsTransferCount',
            'statsTransferAmount',
            'statsQrisCount',
            'statsQrisAmount'
        ));
    }
}
