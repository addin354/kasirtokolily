<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;

class OwnerReportController extends Controller
{
    /**
     * Halaman hub laporan dengan pratinjau data & filter.
     */
    public function __invoke(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        $type = $request->query('report_type', 'terlaris');
        if ($type === 'nilai_persediaan') {
            return redirect()->route('owner.reports', ['report_type' => 'terlaris']);
        }
        $filters = $this->getFilters($request);
        
        $categories = Category::orderBy('nama')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $users = User::whereIn('role', ['admin', 'owner', 'kasir'])->orderBy('name')->get();

        $data = $this->queryReportData($type, $filters, true);

        return view('owner.reports_index', compact('type', 'filters', 'categories', 'suppliers', 'users', 'data'));
    }

    /**
     * Cetak Laporan PDF.
     */
    public function exportPdf(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        $type = $request->query('report_type', 'terlaris');
        if ($type === 'nilai_persediaan') {
            return redirect()->route('owner.reports', ['report_type' => 'terlaris']);
        }
        $filters = $this->getFilters($request);

        $data = $this->queryReportData($type, $filters, false);

        $pdf = Pdf::loadView('owner.reports_pdf', [
            'type' => $type,
            'filters' => $filters,
            'data' => $data,
            'tanggalCetak' => now(),
            'user' => auth()->user(),
        ])->setPaper('a4', in_array($type, ['terlaris', 'restok', 'kartu_stok']) ? 'portrait' : 'landscape');

        $filename = 'laporan-' . $type . '-' . now()->format('Y-m-d-His') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Excel (CSV).
     */
    public function exportExcel(Request $request)
    {
        Gate::authorize('view-laporan-finansial');

        $type = $request->query('report_type', 'terlaris');
        if ($type === 'nilai_persediaan') {
            return redirect()->route('owner.reports', ['report_type' => 'terlaris']);
        }
        $filters = $this->getFilters($request);

        $data = $this->queryReportData($type, $filters, false);

        $filename = 'laporan-' . $type . '-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($type, $data): void {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            if ($type === 'terlaris') {
                fputcsv($handle, ['No', 'Barcode', 'Nama Produk', 'Kategori', 'Qty Terjual', 'Total Penjualan', 'Harga Modal', 'Harga Jual', 'Estimasi Laba']);
                foreach ($data as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->barcode ?? '—',
                        $row->nama_produk,
                        $row->nama_kategori ?? '—',
                        (int) $row->qty_terjual,
                        'Rp ' . number_format((float) $row->total_penjualan, 0, ',', '.'),
                        'Rp ' . number_format((float) $row->harga_modal, 0, ',', '.'),
                        'Rp ' . number_format((float) $row->harga_jual, 0, ',', '.'),
                        'Rp ' . number_format((float) $row->estimasi_laba, 0, ',', '.')
                    ]);
                }
            } elseif ($type === 'produk') {
                fputcsv($handle, ['Barcode', 'Nama Produk', 'Kategori', 'Supplier', 'Harga Modal', 'Harga Jual Ecer', 'Harga Jual Grosir', 'Harga Bal', 'Isi per Bal', 'Minimum Stok']);
                foreach ($data as $row) {
                    fputcsv($handle, [
                        $row->barcode,
                        $row->nama,
                        $row->category?->nama ?? '—',
                        $row->supplierTerakhir() ?? '—',
                        'Rp ' . number_format((float) $row->harga_beli, 0, ',', '.'),
                        'Rp ' . number_format((float) $row->harga_jual, 0, ',', '.'),
                        'Rp ' . number_format((float) $row->harga_grosir, 0, ',', '.'),
                        'Rp ' . number_format((float) $row->harga_bal, 0, ',', '.'),
                        (int) ($row->isi_per_bal ?? 1),
                        (int) $row->stok_minimum
                    ]);
                }
            } elseif ($type === 'restok') {
                fputcsv($handle, ['Barcode', 'Nama Produk', 'Supplier', 'Kategori', 'Stok Saat Ini', 'Minimum Stok', 'Selisih', 'Status']);
                $totalProduk = count($data);
                foreach ($data as $row) {
                    $selisih = max(0, $row->stok_minimum - $row->stok);
                    fputcsv($handle, [
                        $row->barcode,
                        $row->nama,
                        $row->supplierTerakhir() ?? '—',
                        $row->category?->nama ?? '—',
                        (int) $row->stok,
                        (int) $row->stok_minimum,
                        $selisih,
                        'Perlu Restok'
                    ]);
                }
                fputcsv($handle, []);
                fputcsv($handle, ['Jumlah Produk Perlu Restok', $totalProduk]);
            } elseif ($type === 'persediaan') {
                fputcsv($handle, ['Barcode', 'Nama Produk', 'Kategori', 'Supplier', 'Stok', 'Status']);
                foreach ($data as $row) {
                    $status = $row->stokStatus() === 'restok' ? 'Perlu Restok' : 'Aman';
                    fputcsv($handle, [
                        $row->barcode,
                        $row->nama,
                        $row->category?->nama ?? '—',
                        $row->supplierTerakhir() ?? '—',
                        (int) $row->stok,
                        $status
                    ]);
                }
            } elseif ($type === 'nilai_persediaan') {
                fputcsv($handle, ['Barcode', 'Nama Produk', 'Kategori', 'Supplier', 'Harga Modal', 'Stok Saat Ini', 'Nilai Persediaan']);
                $totalJenis = count($data);
                $totalUnit = 0;
                $totalNilai = 0;
                foreach ($data as $row) {
                    $nilai = $row->harga_beli * $row->stok;
                    $totalUnit += $row->stok;
                    $totalNilai += $nilai;
                    fputcsv($handle, [
                        $row->barcode,
                        $row->nama,
                        $row->category?->nama ?? '—',
                        $row->supplierTerakhir() ?? '—',
                        'Rp ' . number_format((float) $row->harga_beli, 0, ',', '.'),
                        (int) $row->stok,
                        'Rp ' . number_format((float) $nilai, 0, ',', '.')
                    ]);
                }
                fputcsv($handle, []);
                fputcsv($handle, ['Total Produk', $totalJenis]);
                fputcsv($handle, ['Total Unit Barang', $totalUnit]);
                fputcsv($handle, ['Total Nilai Persediaan', 'Rp ' . number_format($totalNilai, 0, ',', '.')]);
            } elseif ($type === 'kartu_stok') {
                fputcsv($handle, ['No', 'Tanggal', 'Nama Produk', 'Kategori', 'Tipe Transaksi', 'Qty Masuk', 'Qty Keluar']);
                foreach ($data as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->tanggal,
                        $row->nama_produk,
                        $row->nama_kategori ?? '—',
                        $row->tipe,
                        (int) $row->masuk,
                        (int) $row->keluar
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Parsing filter query string parameters.
     */
    private function getFilters(Request $request): array
    {
        return [
            'q' => trim((string) $request->query('q', '')),
            'tanggal_dari' => $request->query('tanggal_dari'),
            'tanggal_sampai' => $request->query('tanggal_sampai'),
            'kategori_id' => $request->query('kategori_id'),
            'supplier_id' => $request->query('supplier_id'),
            'user_id' => $request->query('user_id'),
            'status' => $request->query('status'),
            'limit' => (int) $request->query('limit', 10),
            'per_page' => (int) $request->query('per_page', 10),
        ];
    }

    /**
     * Query data laporan secara konsisten.
     */
    private function queryReportData(string $type, array $filters, bool $paginate)
    {
        if ($type === 'terlaris') {
            $base = DB::table('detail_transaksi as dt')
                ->join('transaksi as t', 't.id', '=', 'dt.transaksi_id')
                ->join('produks as p', 'p.id', '=', 'dt.produk_id')
                ->leftJoin('kategoris as k', 'k.id', '=', 'p.kategori_id')
                ->select([
                    'dt.produk_id',
                    'p.barcode as barcode',
                    'p.nama as nama_produk',
                    'k.nama as nama_kategori',
                    'p.harga_beli as harga_modal',
                    'p.harga_jual as harga_jual',
                ])
                ->selectRaw('SUM(COALESCE(NULLIF(dt.qty_pcs, 0), dt.qty)) as qty_terjual')
                ->selectRaw('SUM(dt.subtotal) as total_penjualan')
                ->selectRaw('SUM(dt.subtotal) - SUM(COALESCE(NULLIF(dt.qty_pcs, 0), dt.qty) * p.harga_beli) as estimasi_laba');

            // Apply filters
            if ($filters['tanggal_dari']) {
                $base->whereDate('t.tanggal', '>=', $filters['tanggal_dari']);
            }
            if ($filters['tanggal_sampai']) {
                $base->whereDate('t.tanggal', '<=', $filters['tanggal_sampai']);
            }
            if ($filters['kategori_id']) {
                $base->where('p.kategori_id', $filters['kategori_id']);
            }
            if ($filters['user_id']) {
                $base->where('t.user_id', $filters['user_id']);
            }
            if ($filters['supplier_id']) {
                $productIds = $this->getProductIdsBySupplier($filters['supplier_id']);
                $base->whereIn('p.id', $productIds);
            }
            if ($filters['q']) {
                $pattern = '%' . addcslashes($filters['q'], '%_\\') . '%';
                $base->where(function ($qry) use ($pattern) {
                    $qry->where('p.nama', 'LIKE', $pattern)
                        ->orWhere('p.kode', 'LIKE', $pattern);
                });
            }

            $base->groupBy(['dt.produk_id', 'p.barcode', 'p.nama', 'k.nama', 'p.harga_beli', 'p.harga_jual'])
                ->orderByDesc('qty_terjual')
                ->limit($filters['limit']);

            return $base->get();
        }

        if ($type === 'kartu_stok') {
            $unionQuery = $this->queryKartuStok($filters);
            $unionQuery->orderBy('tanggal', 'desc');

            if ($paginate) {
                // Paginate raw SQL union results using LengthAwarePaginator helper
                $total = DB::table(DB::raw("({$unionQuery->toSql()}) as sub"))
                    ->mergeBindings($unionQuery)
                    ->count();

                $perPage = $filters['per_page'];
                $page = request()->query('page', 1);
                $offset = ($page - 1) * $perPage;

                $paginatedResults = DB::table(DB::raw("({$unionQuery->toSql()}) as sub"))
                    ->mergeBindings($unionQuery)
                    ->orderBy('tanggal', 'desc')
                    ->offset($offset)
                    ->limit($perPage)
                    ->get();

                return new \Illuminate\Pagination\LengthAwarePaginator(
                    $paginatedResults,
                    $total,
                    $perPage,
                    $page,
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            }

            return $unionQuery->get();
        }

        // Laporan produk basis query (produk, restok, persediaan, nilai_persediaan)
        $query = Product::query()->with(['category', 'satuanModel'])->orderBy('nama');

        if ($type === 'restok') {
            $query->whereColumn('stok', '<=', 'stok_minimum');
        }

        // Apply filters for product list
        if ($filters['kategori_id']) {
            $query->where('kategori_id', $filters['kategori_id']);
        }
        if ($filters['status']) {
            if ($filters['status'] === 'aman') {
                $query->whereColumn('stok', '>', 'stok_minimum');
            } elseif ($filters['status'] === 'restok') {
                $query->whereColumn('stok', '<=', 'stok_minimum');
            }
        }
        if ($filters['supplier_id']) {
            $productIds = $this->getProductIdsBySupplier($filters['supplier_id']);
            $query->whereIn('id', $productIds);
        }
        if ($filters['tanggal_dari']) {
            $query->whereDate('created_at', '>=', $filters['tanggal_dari']);
        }
        if ($filters['tanggal_sampai']) {
            $query->whereDate('created_at', '<=', $filters['tanggal_sampai']);
        }
        if ($filters['q']) {
            $pattern = '%' . addcslashes($filters['q'], '%_\\') . '%';
            $query->where(function ($qry) use ($pattern) {
                $qry->where('nama', 'LIKE', $pattern)
                    ->orWhere('kode', 'LIKE', $pattern)
                    ->orWhere('barcode', 'LIKE', $pattern);
            });
        }

        if ($paginate) {
            return $query->paginate($filters['per_page'])->withQueryString();
        }

        return $query->get();
    }

    /**
     * Get list of Product IDs associated with a specific Supplier.
     */
    private function getProductIdsBySupplier(int $supplierId): array
    {
        $productIdsFromPembelian = DB::table('detail_pembelians as dp')
            ->join('pembelians as p', 'p.id', '=', 'dp.pembelian_id')
            ->where('p.supplier_id', $supplierId)
            ->pluck('dp.produk_id');

        $productIdsFromStokMasuk = DB::table('stok_masuk')
            ->where('supplier_id', $supplierId)
            ->pluck('produk_id');

        return $productIdsFromPembelian->merge($productIdsFromStokMasuk)->unique()->all();
    }

    /**
     * Construct Kartu Stok Subqueries.
     */
    private function queryKartuStok(array $filters)
    {
        $sales = DB::table('detail_transaksi as dt')
            ->join('transaksi as t', 't.id', '=', 'dt.transaksi_id')
            ->join('produks as p', 'p.id', '=', 'dt.produk_id')
            ->leftJoin('kategoris as k', 'k.id', '=', 'p.kategori_id');

        $purchases = DB::table('detail_pembelians as dp')
            ->join('pembelians as pb', 'pb.id', '=', 'dp.pembelian_id')
            ->join('produks as p', 'p.id', '=', 'dp.produk_id')
            ->leftJoin('kategoris as k', 'k.id', '=', 'p.kategori_id');

        $incoming = DB::table('stok_masuk as sm')
            ->join('produks as p', 'p.id', '=', 'sm.produk_id')
            ->leftJoin('kategoris as k', 'k.id', '=', 'p.kategori_id');

        $returns = DB::table('retur as r')
            ->join('produks as p', 'p.id', '=', 'r.produk_id')
            ->leftJoin('kategoris as k', 'k.id', '=', 'p.kategori_id')
            ->where('r.status', 'disetujui');

        if ($filters['tanggal_dari']) {
            $sales->whereDate('t.tanggal', '>=', $filters['tanggal_dari']);
            $purchases->whereDate('pb.tanggal', '>=', $filters['tanggal_dari']);
            $incoming->whereDate('sm.tanggal', '>=', $filters['tanggal_dari']);
            $returns->whereDate('r.tanggal_retur', '>=', $filters['tanggal_dari']);
        }
        if ($filters['tanggal_sampai']) {
            $sales->whereDate('t.tanggal', '<=', $filters['tanggal_sampai']);
            $purchases->whereDate('pb.tanggal', '<=', $filters['tanggal_sampai']);
            $incoming->whereDate('sm.tanggal', '<=', $filters['tanggal_sampai']);
            $returns->whereDate('r.tanggal_retur', '<=', $filters['tanggal_sampai']);
        }
        if ($filters['kategori_id']) {
            $sales->where('p.kategori_id', $filters['kategori_id']);
            $purchases->where('p.kategori_id', $filters['kategori_id']);
            $incoming->where('p.kategori_id', $filters['kategori_id']);
            $returns->where('p.kategori_id', $filters['kategori_id']);
        }
        if ($filters['supplier_id']) {
            $productIds = $this->getProductIdsBySupplier($filters['supplier_id']);
            $sales->whereIn('p.id', $productIds);
            $purchases->whereIn('p.id', $productIds);
            $incoming->whereIn('p.id', $productIds);
            $returns->whereIn('p.id', $productIds);
        }
        if ($filters['q']) {
            $pattern = '%' . addcslashes($filters['q'], '%_\\') . '%';
            $sales->where('p.nama', 'LIKE', $pattern);
            $purchases->where('p.nama', 'LIKE', $pattern);
            $incoming->where('p.nama', 'LIKE', $pattern);
            $returns->where('p.nama', 'LIKE', $pattern);
        }

        $salesSelect = $sales->select([
            't.tanggal as tanggal',
            'p.nama as nama_produk',
            'k.nama as nama_kategori',
            DB::raw("'Penjualan' as tipe"),
            DB::raw("0 as masuk"),
            DB::raw("COALESCE(NULLIF(dt.qty_pcs, 0), dt.qty) as keluar"),
        ]);

        $purchasesSelect = $purchases->select([
            'pb.tanggal as tanggal',
            'p.nama as nama_produk',
            'k.nama as nama_kategori',
            DB::raw("'Pembelian' as tipe"),
            'dp.qty as masuk',
            DB::raw("0 as keluar"),
        ]);

        $incomingSelect = $incoming->select([
            DB::raw("DATE(sm.tanggal) as tanggal"),
            'p.nama as nama_produk',
            'k.nama as nama_kategori',
            DB::raw("'Stok Masuk' as tipe"),
            'sm.jumlah as masuk',
            DB::raw("0 as keluar"),
        ]);

        $returnsSelect = $returns->select([
            'r.tanggal_retur as tanggal',
            'p.nama as nama_produk',
            'k.nama as nama_kategori',
            DB::raw("'Retur' as tipe"),
            'r.qty as masuk',
            DB::raw("0 as keluar"),
        ]);

        return $salesSelect->union($purchasesSelect)->union($incomingSelect)->union($returnsSelect);
    }
}
