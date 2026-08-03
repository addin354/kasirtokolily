<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class LaporanController extends Controller
{
    public function penjualan(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        [$query, $dari, $sampai, $metode] = $this->resolvePenjualanQuery($request);

        $transactions = (clone $query)->select(['id', 'tanggal', 'total'])->get();

        $detQuery = DB::table('detail_transaksi as dt')
            ->join('transaksi as t', 't.id', '=', 'dt.transaksi_id')
            ->join('produks as p', 'p.id', '=', 'dt.produk_id');

        if ($dari) {
            $detQuery->whereDate('t.tanggal', '>=', $dari);
        }
        if ($sampai) {
            $detQuery->whereDate('t.tanggal', '<=', $sampai);
        }
        if ($metode) {
            if ($metode === 'Cash') {
                $detQuery->where(function($q) {
                    $q->where('t.metode_pembayaran', 'Cash')->orWhereNull('t.metode_pembayaran');
                });
            } else {
                $detQuery->where('t.metode_pembayaran', $metode);
            }
        }

        $details = $detQuery->select([
            't.tanggal',
            'dt.qty',
            'dt.subtotal',
            'p.harga_beli',
        ])->get();

        $totalTransaksi = count($transactions);
        $totalPendapatan = (float) $transactions->sum('total');
        $totalBarangTerjual = (int) $details->sum('qty');
        $totalHpp = (float) $details->sum(fn($d) => $d->qty * $d->harga_beli);
        $totalLabaKotor = $totalPendapatan - $totalHpp;

        $dailyData = [];
        foreach ($transactions as $t) {
            $dateKey = \Carbon\Carbon::parse($t->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'jumlah_transaksi' => 0,
                    'barang_terjual' => 0,
                    'omzet' => 0.0,
                    'hpp' => 0.0,
                    'laba_kotor' => 0.0,
                ];
            }
            $dailyData[$dateKey]['jumlah_transaksi'] += 1;
            $dailyData[$dateKey]['omzet'] += (float) $t->total;
        }

        foreach ($details as $d) {
            $dateKey = \Carbon\Carbon::parse($d->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'jumlah_transaksi' => 0,
                    'barang_terjual' => 0,
                    'omzet' => 0.0,
                    'hpp' => 0.0,
                    'laba_kotor' => 0.0,
                ];
            }
            $dailyData[$dateKey]['barang_terjual'] += (int) $d->qty;
            $dailyData[$dateKey]['hpp'] += (float) ($d->qty * $d->harga_beli);
        }

        foreach ($dailyData as $dateKey => &$row) {
            $row['laba_kotor'] = $row['omzet'] - $row['hpp'];
        }
        unset($row);

        krsort($dailyData);
        $dailyLines = array_values($dailyData);

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($dailyLines, ($currentPage - 1) * $perPage, $perPage);
        $paginatedLines = new LengthAwarePaginator(
            $currentItems,
            count($dailyLines),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $chartData = $dailyData;
        ksort($chartData);

        $chartLabels = [];
        $chartOmzet = [];
        $chartCount = [];

        foreach ($chartData as $date => $cVal) {
            $chartLabels[] = \Carbon\Carbon::parse($date)->format('d/m/Y');
            $chartOmzet[] = (float) $cVal['omzet'];
            $chartCount[] = (int) $cVal['jumlah_transaksi'];
        }

        return view('laporan.penjualan', [
            'lines' => $paginatedLines,
            'totalTransaksi' => $totalTransaksi,
            'totalPendapatan' => $totalPendapatan,
            'totalBarangTerjual' => $totalBarangTerjual,
            'totalHpp' => $totalHpp,
            'totalLabaKotor' => $totalLabaKotor,
            'tanggal_dari' => $dari,
            'tanggal_sampai' => $sampai,
            'metode_pembayaran' => $metode,
            'chartLabels' => $chartLabels,
            'chartOmzet' => $chartOmzet,
            'chartCount' => $chartCount,
        ]);
    }

    public function penjualanDetail(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $tanggal = $request->input('tanggal');

        $transaksis = Transaksi::query()
            ->whereDate('tanggal', $tanggal)
            ->with(['detailTransaksis.product', 'pelanggan'])
            ->orderBy('tanggal')
            ->get();

        foreach ($transaksis as $trx) {
            $cashierId = DB::table('stok_logs')->where('referensi', 'TRX-' . $trx->id)->value('user_id');
            $trx->cashier_name = $cashierId ? DB::table('users')->where('id', $cashierId)->value('name') : 'Kasir';
        }

        return view('laporan.penjualan-detail', [
            'tanggal' => $tanggal,
            'transaksis' => $transaksis,
        ]);
    }

    public function exportPenjualanPdf(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        [$query, $dari, $sampai, $metode] = $this->resolvePenjualanQuery($request);

        $transactions = (clone $query)->select(['id', 'tanggal', 'total'])->get();

        $detQuery = DB::table('detail_transaksi as dt')
            ->join('transaksi as t', 't.id', '=', 'dt.transaksi_id')
            ->join('produks as p', 'p.id', '=', 'dt.produk_id');

        if ($dari) {
            $detQuery->whereDate('t.tanggal', '>=', $dari);
        }
        if ($sampai) {
            $detQuery->whereDate('t.tanggal', '<=', $sampai);
        }
        if ($metode) {
            if ($metode === 'Cash') {
                $detQuery->where(function($q) {
                    $q->where('t.metode_pembayaran', 'Cash')->orWhereNull('t.metode_pembayaran');
                });
            } else {
                $detQuery->where('t.metode_pembayaran', $metode);
            }
        }

        $details = $detQuery->select([
            't.tanggal',
            'dt.qty',
            'dt.subtotal',
            'p.harga_beli',
        ])->get();

        $totalTransaksi = count($transactions);
        $totalPendapatan = (float) $transactions->sum('total');
        $totalBarangTerjual = (int) $details->sum('qty');
        $totalHpp = (float) $details->sum(fn($d) => $d->qty * $d->harga_beli);
        $totalLabaKotor = $totalPendapatan - $totalHpp;

        $dailyData = [];
        foreach ($transactions as $t) {
            $dateKey = \Carbon\Carbon::parse($t->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'jumlah_transaksi' => 0,
                    'barang_terjual' => 0,
                    'omzet' => 0.0,
                    'hpp' => 0.0,
                    'laba_kotor' => 0.0,
                ];
            }
            $dailyData[$dateKey]['jumlah_transaksi'] += 1;
            $dailyData[$dateKey]['omzet'] += (float) $t->total;
        }

        foreach ($details as $d) {
            $dateKey = \Carbon\Carbon::parse($d->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'jumlah_transaksi' => 0,
                    'barang_terjual' => 0,
                    'omzet' => 0.0,
                    'hpp' => 0.0,
                    'laba_kotor' => 0.0,
                ];
            }
            $dailyData[$dateKey]['barang_terjual'] += (int) $d->qty;
            $dailyData[$dateKey]['hpp'] += (float) ($d->qty * $d->harga_beli);
        }

        foreach ($dailyData as $dateKey => &$row) {
            $row['laba_kotor'] = $row['omzet'] - $row['hpp'];
        }
        unset($row);

        ksort($dailyData);
        $dailyLines = array_values($dailyData);

        $periodeLabel = $this->formatPeriodePenjualan($dari, $sampai);
        if ($metode) {
            $periodeLabel .= ' | Metode: ' . $metode;
        }

        $pdf = Pdf::loadView('laporan.pdf.penjualan', [
            'lines' => $dailyLines,
            'totalTransaksi' => $totalTransaksi,
            'totalPendapatan' => $totalPendapatan,
            'totalBarangTerjual' => $totalBarangTerjual,
            'totalHpp' => $totalHpp,
            'totalLabaKotor' => $totalLabaKotor,
            'periodeLabel' => $periodeLabel,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-rekap-penjualan-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportPenjualanExcel(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        [$query, $dari, $sampai, $metode] = $this->resolvePenjualanQuery($request);

        $transactions = (clone $query)->select(['id', 'tanggal', 'total'])->get();

        $detQuery = DB::table('detail_transaksi as dt')
            ->join('transaksi as t', 't.id', '=', 'dt.transaksi_id')
            ->join('produks as p', 'p.id', '=', 'dt.produk_id');

        if ($dari) {
            $detQuery->whereDate('t.tanggal', '>=', $dari);
        }
        if ($sampai) {
            $detQuery->whereDate('t.tanggal', '<=', $sampai);
        }
        if ($metode) {
            if ($metode === 'Cash') {
                $detQuery->where(function($q) {
                    $q->where('t.metode_pembayaran', 'Cash')->orWhereNull('t.metode_pembayaran');
                });
            } else {
                $detQuery->where('t.metode_pembayaran', $metode);
            }
        }

        $details = $detQuery->select([
            't.tanggal',
            'dt.qty',
            'dt.subtotal',
            'p.harga_beli',
        ])->get();

        $dailyData = [];
        foreach ($transactions as $t) {
            $dateKey = \Carbon\Carbon::parse($t->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'jumlah_transaksi' => 0,
                    'barang_terjual' => 0,
                    'omzet' => 0.0,
                    'hpp' => 0.0,
                    'laba_kotor' => 0.0,
                ];
            }
            $dailyData[$dateKey]['jumlah_transaksi'] += 1;
            $dailyData[$dateKey]['omzet'] += (float) $t->total;
        }

        foreach ($details as $d) {
            $dateKey = \Carbon\Carbon::parse($d->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'jumlah_transaksi' => 0,
                    'barang_terjual' => 0,
                    'omzet' => 0.0,
                    'hpp' => 0.0,
                    'laba_kotor' => 0.0,
                ];
            }
            $dailyData[$dateKey]['barang_terjual'] += (int) $d->qty;
            $dailyData[$dateKey]['hpp'] += (float) ($d->qty * $d->harga_beli);
        }

        foreach ($dailyData as $dateKey => &$row) {
            $row['laba_kotor'] = $row['omzet'] - $row['hpp'];
        }
        unset($row);

        ksort($dailyData);

        $rows = [];
        $rows[] = ['Tanggal', 'Jumlah Transaksi', 'Total Barang Terjual', 'Total Pendapatan (Omzet)', 'Total HPP', 'Total Laba Kotor'];

        foreach ($dailyData as $date => $val) {
            $rows[] = [
                \Carbon\Carbon::parse($date)->format('d/m/Y'),
                $val['jumlah_transaksi'],
                $val['barang_terjual'],
                (int) $val['omzet'],
                (int) $val['hpp'],
                (int) $val['laba_kotor']
            ];
        }

        $filename = 'laporan-rekap-penjualan-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    fputcsv($handle, $row, ',');
                    continue;
                }

                $row[3] = number_format((int) $row[3], 0, ',', '.');
                $row[4] = number_format((int) $row[4], 0, ',', '.');
                $row[5] = number_format((int) $row[5], 0, ',', '.');

                fputcsv($handle, $row, ',');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function laba(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        [$salesQuery, $expensesQuery, $purchasesQuery, $tipe, $tanggal, $bulan, $tahun, $dari, $sampai] = $this->resolveLabaBase($request);

        $sales = (clone $salesQuery)->get();
        $expenses = (clone $expensesQuery)->get();
        $purchases = (clone $purchasesQuery)->get();

        $totalPendapatan = (float) $sales->sum('total');
        $totalHpp = (float) $sales->sum('hpp');
        $totalLabaKotor = $totalPendapatan - $totalHpp;
        $totalPengeluaran = (float) $expenses->sum('nominal');
        $totalPembelian = (float) $purchases->sum('total');
        $labaBersih = $totalLabaKotor - $totalPengeluaran;

        $jumlahTransaksi = count($sales);
        $jumlahPengeluaran = count($expenses);
        $marginLabaKotor = $totalPendapatan > 0 ? ($totalLabaKotor / $totalPendapatan) * 100 : 0;
        $marginLabaBersih = $totalPendapatan > 0 ? ($labaBersih / $totalPendapatan) * 100 : 0;

        // Resolve start and end dates for cash calculations
        $startDate = null;
        $endDate = null;
        if ($tipe === 'harian') {
            $startDate = $tanggal;
            $endDate = $tanggal;
        } elseif ($tipe === 'bulanan') {
            $startDate = \Carbon\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
            $endDate = \Carbon\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
        } elseif ($tipe === 'tahunan') {
            $startDate = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear()->toDateString();
            $endDate = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear()->toDateString();
        } elseif ($tipe === 'rentang') {
            $startDate = $dari;
            $endDate = $sampai;
        }

        // Calculate Saldo Awal (before start date)
        $globalSaldoAwal = floatval(config('pos.saldo_awal_kas', 0));
        
        $salesBefore = DB::table('transaksi')
            ->where('tanggal', '<', $startDate . ' 00:00:00')
            ->where(function ($q) {
                $q->where('metode_pembayaran', 'Cash')
                  ->orWhereNull('metode_pembayaran');
            })
            ->sum('total');
            
        $purchasesBefore = DB::table('pembelians')
            ->where('tanggal', '<', $startDate)
            ->where('metode_pembayaran', 'Cash')
            ->sum('total');
            
        $expensesBefore = DB::table('pengeluarans')
            ->where('tanggal', '<', $startDate)
            ->where('metode_pembayaran', 'Cash')
            ->sum('nominal');
            
        $saldoAwal = $globalSaldoAwal + floatval($salesBefore) - floatval($purchasesBefore) - floatval($expensesBefore);

        // Calculate period cash totals
        $totalPenjualanCash = DB::table('transaksi')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->where(function ($q) {
                $q->where('metode_pembayaran', 'Cash')
                  ->orWhereNull('metode_pembayaran');
            })
            ->sum('total');
            
        $totalPembelianCash = DB::table('pembelians')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->where('metode_pembayaran', 'Cash')
            ->sum('total');
            
        $totalPengeluaranCash = DB::table('pengeluarans')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->where('metode_pembayaran', 'Cash')
            ->sum('nominal');
            
        $saldoKasSaatIni = $saldoAwal + floatval($totalPenjualanCash) - floatval($totalPembelianCash) - floatval($totalPengeluaranCash);

        // Group into daily summaries
        $dailyData = [];

        foreach ($sales as $sale) {
            $dateKey = \Carbon\Carbon::parse($sale->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'pendapatan' => 0.0,
                    'hpp' => 0.0,
                    'pembelian' => 0.0,
                    'pengeluaran' => 0.0,
                ];
            }
            $dailyData[$dateKey]['pendapatan'] += (float) $sale->total;
            $dailyData[$dateKey]['hpp'] += (float) $sale->hpp;
        }

        foreach ($expenses as $expense) {
            $dateKey = \Carbon\Carbon::parse($expense->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'pendapatan' => 0.0,
                    'hpp' => 0.0,
                    'pembelian' => 0.0,
                    'pengeluaran' => 0.0,
                ];
            }
            $dailyData[$dateKey]['pengeluaran'] += (float) $expense->nominal;
        }

        foreach ($purchases as $purchase) {
            $dateKey = \Carbon\Carbon::parse($purchase->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'pendapatan' => 0.0,
                    'hpp' => 0.0,
                    'pembelian' => 0.0,
                    'pengeluaran' => 0.0,
                ];
            }
            $dailyData[$dateKey]['pembelian'] += (float) $purchase->total;
        }

        // Sort chronologically descending for display
        krsort($dailyData);

        $dailyLines = [];
        foreach ($dailyData as $dateKey => $data) {
            $dailyLines[] = [
                'tanggal' => $data['tanggal'],
                'pendapatan' => $data['pendapatan'],
                'hpp' => $data['hpp'],
                'pembelian' => $data['pembelian'],
                'pengeluaran' => $data['pengeluaran'],
                'saldo_kas_harian' => $this->getSaldoKasPadaTanggal($data['tanggal']),
                'laba_bersih' => $data['pendapatan'] - $data['hpp'] - $data['pengeluaran'],
            ];
        }

        // Paginate daily summary lines
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($dailyLines, ($currentPage - 1) * $perPage, $perPage);
        $paginatedLines = new LengthAwarePaginator(
            $currentItems,
            count($dailyLines),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Group Chart Data by Date (chronologically ascending)
        $chartDataGrouped = [];
        foreach ($sales as $sale) {
            $dateKey = \Carbon\Carbon::parse($sale->tanggal)->toDateString();
            if (!isset($chartDataGrouped[$dateKey])) {
                $chartDataGrouped[$dateKey] = ['pendapatan' => 0, 'hpp' => 0, 'pengeluaran' => 0];
            }
            $chartDataGrouped[$dateKey]['pendapatan'] += $sale->total;
            $chartDataGrouped[$dateKey]['hpp'] += $sale->hpp;
        }

        foreach ($expenses as $expense) {
            $dateKey = \Carbon\Carbon::parse($expense->tanggal)->toDateString();
            if (!isset($chartDataGrouped[$dateKey])) {
                $chartDataGrouped[$dateKey] = ['pendapatan' => 0, 'hpp' => 0, 'pengeluaran' => 0];
            }
            $chartDataGrouped[$dateKey]['pengeluaran'] += $expense->nominal;
        }

        ksort($chartDataGrouped);

        $chartLabels = [];
        $chartPendapatan = [];
        $chartHpp = [];
        $chartPengeluaran = [];
        $chartLabaBersih = [];

        foreach ($chartDataGrouped as $date => $cData) {
            $chartLabels[] = \Carbon\Carbon::parse($date)->format('d/m/Y');
            $chartPendapatan[] = (float) $cData['pendapatan'];
            $chartHpp[] = (float) $cData['hpp'];
            $chartPengeluaran[] = (float) $cData['pengeluaran'];
            $chartLabaBersih[] = (float) ($cData['pendapatan'] - $cData['hpp'] - $cData['pengeluaran']);
        }

        return view('laporan.laba', [
            'tipe' => $tipe,
            'tanggal' => $tanggal,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal_dari' => $dari,
            'tanggal_sampai' => $sampai,
            'totalPendapatan' => $totalPendapatan,
            'totalHpp' => $totalHpp,
            'totalLabaKotor' => $totalLabaKotor,
            'totalPembelian' => $totalPembelian,
            'totalPengeluaran' => $totalPengeluaran,
            'labaBersih' => $labaBersih,
            'jumlahTransaksi' => $jumlahTransaksi,
            'jumlahPengeluaran' => $jumlahPengeluaran,
            'marginLabaKotor' => $marginLabaKotor,
            'marginLabaBersih' => $marginLabaBersih,
            'lines' => $paginatedLines,
            'chartLabels' => $chartLabels,
            'chartPendapatan' => $chartPendapatan,
            'chartHpp' => $chartHpp,
            'chartPengeluaran' => $chartPengeluaran,
            'chartLabaBersih' => $chartLabaBersih,
            'saldoAwal' => $saldoAwal,
            'totalPenjualanCash' => $totalPenjualanCash,
            'totalPembelianCash' => $totalPembelianCash,
            'totalPengeluaranCash' => $totalPengeluaranCash,
            'saldoKasSaatIni' => $saldoKasSaatIni,
        ]);
    }

    public function exportLabaPdf(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        [$salesQuery, $expensesQuery, $purchasesQuery, $tipe, $tanggal, $bulan, $tahun, $dari, $sampai] = $this->resolveLabaBase($request);

        $sales = (clone $salesQuery)->get();
        $expenses = (clone $expensesQuery)->get();
        $purchases = (clone $purchasesQuery)->get();

        $totalPendapatan = (float) $sales->sum('total');
        $totalHpp = (float) $sales->sum('hpp');
        $totalLabaKotor = $totalPendapatan - $totalHpp;
        $totalPengeluaran = (float) $expenses->sum('nominal');
        $totalPembelian = (float) $purchases->sum('total');
        $labaBersih = $totalLabaKotor - $totalPengeluaran;

        $jumlahTransaksi = count($sales);
        $jumlahPengeluaran = count($expenses);
        $marginLabaKotor = $totalPendapatan > 0 ? ($totalLabaKotor / $totalPendapatan) * 100 : 0;
        $marginLabaBersih = $totalPendapatan > 0 ? ($labaBersih / $totalPendapatan) * 100 : 0;

        // Resolve start and end dates for cash calculations
        $startDate = null;
        $endDate = null;
        if ($tipe === 'harian') {
            $startDate = $tanggal;
            $endDate = $tanggal;
        } elseif ($tipe === 'bulanan') {
            $startDate = \Carbon\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
            $endDate = \Carbon\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
        } elseif ($tipe === 'tahunan') {
            $startDate = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear()->toDateString();
            $endDate = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear()->toDateString();
        } elseif ($tipe === 'rentang') {
            $startDate = $dari;
            $endDate = $sampai;
        }

        // Calculate Saldo Awal (before start date)
        $globalSaldoAwal = floatval(config('pos.saldo_awal_kas', 0));
        
        $salesBefore = DB::table('transaksi')
            ->where('tanggal', '<', $startDate . ' 00:00:00')
            ->where(function ($q) {
                $q->where('metode_pembayaran', 'Cash')
                  ->orWhereNull('metode_pembayaran');
            })
            ->sum('total');
            
        $purchasesBefore = DB::table('pembelians')
            ->where('tanggal', '<', $startDate)
            ->where('metode_pembayaran', 'Cash')
            ->sum('total');
            
        $expensesBefore = DB::table('pengeluarans')
            ->where('tanggal', '<', $startDate)
            ->where('metode_pembayaran', 'Cash')
            ->sum('nominal');
            
        $saldoAwal = $globalSaldoAwal + floatval($salesBefore) - floatval($purchasesBefore) - floatval($expensesBefore);

        // Calculate period cash totals
        $totalPenjualanCash = DB::table('transaksi')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->where(function ($q) {
                $q->where('metode_pembayaran', 'Cash')
                  ->orWhereNull('metode_pembayaran');
            })
            ->sum('total');
            
        $totalPembelianCash = DB::table('pembelians')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->where('metode_pembayaran', 'Cash')
            ->sum('total');
            
        $totalPengeluaranCash = DB::table('pengeluarans')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->where('metode_pembayaran', 'Cash')
            ->sum('nominal');
            
        $saldoKasSaatIni = $saldoAwal + floatval($totalPenjualanCash) - floatval($totalPembelianCash) - floatval($totalPengeluaranCash);

        // Group into daily summaries
        $dailyData = [];

        foreach ($sales as $sale) {
            $dateKey = \Carbon\Carbon::parse($sale->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'pendapatan' => 0.0,
                    'hpp' => 0.0,
                    'pembelian' => 0.0,
                    'pengeluaran' => 0.0,
                ];
            }
            $dailyData[$dateKey]['pendapatan'] += (float) $sale->total;
            $dailyData[$dateKey]['hpp'] += (float) $sale->hpp;
        }

        foreach ($expenses as $expense) {
            $dateKey = \Carbon\Carbon::parse($expense->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'pendapatan' => 0.0,
                    'hpp' => 0.0,
                    'pembelian' => 0.0,
                    'pengeluaran' => 0.0,
                ];
            }
            $dailyData[$dateKey]['pengeluaran'] += (float) $expense->nominal;
        }

        foreach ($purchases as $purchase) {
            $dateKey = \Carbon\Carbon::parse($purchase->tanggal)->toDateString();
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'tanggal' => $dateKey,
                    'pendapatan' => 0.0,
                    'hpp' => 0.0,
                    'pembelian' => 0.0,
                    'pengeluaran' => 0.0,
                ];
            }
            $dailyData[$dateKey]['pembelian'] += (float) $purchase->total;
        }

        // Sort chronologically ascending for PDF display
        ksort($dailyData);

        $dailyLines = [];
        foreach ($dailyData as $dateKey => $data) {
            $dailyLines[] = [
                'tanggal' => $data['tanggal'],
                'pendapatan' => $data['pendapatan'],
                'hpp' => $data['hpp'],
                'pembelian' => $data['pembelian'],
                'pengeluaran' => $data['pengeluaran'],
                'saldo_kas_harian' => $this->getSaldoKasPadaTanggal($data['tanggal']),
                'laba_bersih' => $data['pendapatan'] - $data['hpp'] - $data['pengeluaran'],
            ];
        }

        $periodeLabel = $this->formatPeriodeLaba($tipe, $tanggal, $bulan, $tahun, $dari, $sampai);
        $chartImage = $request->input('chart_image');

        $pdf = Pdf::loadView('laporan.pdf.laba', [
            'lines' => $dailyLines,
            'totalPendapatan' => $totalPendapatan,
            'totalHpp' => $totalHpp,
            'totalLabaKotor' => $totalLabaKotor,
            'totalPembelian' => $totalPembelian,
            'totalPengeluaran' => $totalPengeluaran,
            'labaBersih' => $labaBersih,
            'jumlahTransaksi' => $jumlahTransaksi,
            'jumlahPengeluaran' => $jumlahPengeluaran,
            'marginLabaKotor' => $marginLabaKotor,
            'marginLabaBersih' => $marginLabaBersih,
            'periodeLabel' => $periodeLabel,
            'tanggalCetak' => now(),
            'chartImage' => $chartImage,
            'saldoAwal' => $saldoAwal,
            'totalPenjualanCash' => $totalPenjualanCash,
            'totalPembelianCash' => $totalPembelianCash,
            'totalPengeluaranCash' => $totalPengeluaranCash,
            'saldoKasSaatIni' => $saldoKasSaatIni,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-laba-rugi-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportProdukTerlarisPdf(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        $range = (int) $request->query('periode', 30);
        $range = min(365, max(1, $range));
        $from = now()->subDays($range)->startOfDay();

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

        $pdf = Pdf::loadView('laporan.pdf.produk-terlaris', [
            'produkRows' => $produkRows,
            'periodeHari' => $range,
            'dariTanggal' => $from->toDateString(),
            'sampaiTanggal' => now()->toDateString(),
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-produk-terlaris-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportStokKritisPdf(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        [$products] = $this->stokKritisCollections($request);

        $pdf = Pdf::loadView('laporan.pdf.stok-kritis', [
            'products' => $products,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('stok-perlu-restok-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportStokKritisExcel(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        [$products] = $this->stokKritisCollections($request);

        $rows = [];
        $rows[] = ['Status', 'Nama', 'Kode', 'Satuan', 'Stok', 'Minimum', 'Selisih'];

        foreach ($products as $product) {
            $selisih = max(0, $product->stok_minimum - $product->stok);
            $rows[] = [
                'Perlu Restok',
                $product->nama,
                $product->kode,
                $product->satuanModel?->nama ?? $product->satuan ?? '—',
                (int) $product->stok,
                (int) $product->stok_minimum,
                $selisih
            ];
        }

        $filename = 'stok-perlu-restok-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    fputcsv($handle, $row, ',');
                    continue;
                }

                // format numeric columns for readability
                $row[4] = number_format((int) $row[4], 0, ',', '.');
                $row[5] = number_format((int) $row[5], 0, ',', '.');
                $row[6] = number_format((int) $row[6], 0, ',', '.');

                fputcsv($handle, $row, ',');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function retur(Request $request)
    {
        $validated = $request->validate([
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date'],
        ]);

        $dari = $validated['tanggal_dari'] ?? null;
        $sampai = $validated['tanggal_sampai'] ?? null;

        if ($dari && $sampai && $dari > $sampai) {
            throw ValidationException::withMessages([
                'tanggal_sampai' => ['Tanggal sampai harus sama atau setelah tanggal dari.'],
            ]);
        }

        $baseQuery = DB::table('retur as r')
            ->leftJoin('transaksi as t', 't.id', '=', 'r.transaksi_id')
            ->leftJoin('produks as p', 'p.id', '=', 'r.produk_id')
            ->select([
                'r.id',
                DB::raw('COALESCE(t.no_invoice, t.kode_invoice, CONCAT("INV-", t.id)) as invoice'),
                'r.tanggal',
                DB::raw('COALESCE(p.nama, r.produk_nama, "-") as produk'),
                'r.qty',
                'r.alasan',
                'r.status',
                DB::raw('COALESCE(r.subtotal, r.qty * r.harga_unit, 0) as nominal'),
            ])
            ->when($dari, fn ($query) => $query->whereDate('r.tanggal', '>=', $dari))
            ->when($sampai, fn ($query) => $query->whereDate('r.tanggal', '<=', $sampai))
            ->orderByDesc('r.tanggal')
            ->orderByDesc('r.id');

        $returs = (clone $baseQuery)
            ->paginate(20)
            ->withQueryString();

        $totalUnit = (clone $baseQuery)->sum('qty');
        $totalNominal = (clone $baseQuery)->sum(DB::raw('COALESCE(r.subtotal, r.qty * r.harga_unit, 0)'));

        return view('laporan.retur', [
            'returs' => $returs,
            'totalUnit' => $totalUnit,
            'totalNominal' => $totalNominal,
        ]);
    }

    private function stokKritisCollections(Request $request): array
    {
        $query = Product::query()->with(['category', 'satuanModel']);
        $query->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('nama');
        $products = $query->get();

        return [$products];
    }

    /**
     * @return array{0: \Illuminate\Database\Eloquent\Builder, 1: ?string, 2: ?string, 3: ?string}
     */
    private function resolvePenjualanQuery(Request $request): array
    {
        $validated = $request->validate([
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date'],
            'metode_pembayaran' => ['nullable', 'string', 'in:Cash,Transfer Bank,QRIS'],
        ]);

        $dari = $validated['tanggal_dari'] ?? null;
        $sampai = $validated['tanggal_sampai'] ?? null;
        $metode = $validated['metode_pembayaran'] ?? null;

        if ($dari && $sampai && $dari > $sampai) {
            throw ValidationException::withMessages([
                'tanggal_sampai' => ['Tanggal sampai harus sama atau setelah tanggal dari.'],
            ]);
        }

        $query = Transaksi::query()
            ->when($dari, fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai));

        if ($metode) {
            if ($metode === 'Cash') {
                $query->where(function($q) {
                    $q->where('metode_pembayaran', 'Cash')->orWhereNull('metode_pembayaran');
                });
            } else {
                $query->where('metode_pembayaran', $metode);
            }
        }

        return [$query, $dari, $sampai, $metode];
    }

    private function formatPeriodePenjualan(?string $dari, ?string $sampai): string
    {
        if (! $dari && ! $sampai) {
            return 'Semua tanggal';
        }
        if ($dari && $sampai) {
            return $dari . ' s/d ' . $sampai;
        }

        return $dari ? 'Dari ' . $dari : 'Sampai ' . $sampai;
    }    /**
     * @return array{0: \Illuminate\Database\Query\Builder, 1: \Illuminate\Database\Query\Builder, 2: string, 3: string, 4: string, 5: int, 6: string, 7: string}
     */
    private function resolveLabaBase(Request $request): array
    {
        $tipe = $request->input('tipe', 'bulanan');
        if (! in_array($tipe, ['harian', 'bulanan', 'tahunan', 'rentang'], true)) {
            $tipe = 'bulanan';
        }

        $validated = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'bulan' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date'],
        ]);

        $tanggal = $validated['tanggal'] ?? $request->input('tanggal', now()->toDateString());
        $bulan = $validated['bulan'] ?? $request->input('bulan', now()->format('Y-m'));
        if (! is_string($bulan) || ! preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }
        $tahun = isset($validated['tahun'])
            ? (int) $validated['tahun']
            : (int) $request->input('tahun', now()->year);
        $tahun = max(2000, min(2100, $tahun));

        $dari = $validated['tanggal_dari'] ?? $request->input('tanggal_dari', now()->startOfMonth()->toDateString());
        $sampai = $validated['tanggal_sampai'] ?? $request->input('tanggal_sampai', now()->toDateString());

        if ($tipe === 'rentang' && $dari && $sampai && $dari > $sampai) {
            throw ValidationException::withMessages([
                'tanggal_sampai' => ['Tanggal sampai harus sama atau setelah tanggal dari.'],
            ]);
        }

        $salesQuery = DB::table('transaksi as t')
            ->leftJoin(DB::raw('(SELECT dt.transaksi_id, SUM(COALESCE(NULLIF(dt.qty_pcs, 0), dt.qty) * p.harga_beli) as hpp FROM detail_transaksi dt JOIN produks p ON p.id = dt.produk_id GROUP BY dt.transaksi_id) as sub'), 'sub.transaksi_id', '=', 't.id')
            ->select(['t.id', 't.tanggal', 't.total', DB::raw('COALESCE(sub.hpp, 0) as hpp')]);

        $expensesQuery = DB::table('pengeluarans');

        $purchasesQuery = DB::table('pembelians');

        if ($tipe === 'harian') {
            $salesQuery->whereDate('t.tanggal', $tanggal);
            $expensesQuery->whereDate('tanggal', $tanggal);
            $purchasesQuery->whereDate('tanggal', $tanggal);
        } elseif ($tipe === 'bulanan') {
            [$y, $m] = array_map('intval', explode('-', $bulan, 2));
            $salesQuery->whereYear('t.tanggal', $y)->whereMonth('t.tanggal', $m);
            $expensesQuery->whereYear('tanggal', $y)->whereMonth('tanggal', $m);
            $purchasesQuery->whereYear('tanggal', $y)->whereMonth('tanggal', $m);
        } elseif ($tipe === 'tahunan') {
            $salesQuery->whereYear('t.tanggal', $tahun);
            $expensesQuery->whereYear('tanggal', $tahun);
            $purchasesQuery->whereYear('tanggal', $tahun);
        } elseif ($tipe === 'rentang') {
            $salesQuery->whereDate('t.tanggal', '>=', $dari)->whereDate('t.tanggal', '<=', $sampai);
            $expensesQuery->whereDate('tanggal', '>=', $dari)->whereDate('tanggal', '<=', $sampai);
            $purchasesQuery->whereDate('tanggal', '>=', $dari)->whereDate('tanggal', '<=', $sampai);
        }

        return [$salesQuery, $expensesQuery, $purchasesQuery, $tipe, $tanggal, $bulan, $tahun, $dari, $sampai];
    }

    private function formatPeriodeLaba(string $tipe, string $tanggal, string $bulan, int $tahun, ?string $dari = null, ?string $sampai = null): string
    {
        return match ($tipe) {
            'harian' => 'Harian: ' . $tanggal,
            'bulanan' => 'Bulanan: ' . $bulan,
            'tahunan' => 'Tahunan: ' . $tahun,
            'rentang' => 'Rentang: ' . ($dari ?? '') . ' s/d ' . ($sampai ?? ''),
        };
    }

    private function getSaldoKasPadaTanggal(string $date): float
    {
        $saldoAwal = floatval(config('pos.saldo_awal_kas', 0));

        $penjualanCash = DB::table('transaksi')
            ->where('tanggal', '<=', $date . ' 23:59:59')
            ->where(function ($q) {
                $q->where('metode_pembayaran', 'Cash')
                  ->orWhereNull('metode_pembayaran');
            })
            ->sum('total');

        $pembelianCash = DB::table('pembelians')
            ->where('tanggal', '<=', $date)
            ->where('metode_pembayaran', 'Cash')
            ->sum('total');

        $pengeluaranCash = DB::table('pengeluarans')
            ->where('tanggal', '<=', $date)
            ->where('metode_pembayaran', 'Cash')
            ->sum('nominal');

        return $saldoAwal + floatval($penjualanCash) - floatval($pembelianCash) - floatval($pengeluaranCash);
    }
}
