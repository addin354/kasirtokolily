<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportLaporanController extends Controller
{
    /**
     * Menampilkan laporan retur penjualan berdasarkan rentang tanggal.
     *
     * Alur:
     * 1. Terima input tanggal dari frontend melalui request.
     * 2. Validasi format tanggal.
     * 3. Query tabel retur dengan whereBetween pada kolom created_at atau tanggal_retur.
     * 4. Kembalikan data dalam format JSON agar frontend bisa langsung dipakai.
     */
    public function returPenjualan(Request $request)
    {
        // Ambil input tanggal, search, dan limit dari frontend.
        $tanggalDari = $request->query('tanggal_dari');
        $tanggalSampai = $request->query('tanggal_sampai');
        $search = $request->query('q');
        $limit = (int) $request->query('limit', 10);
        $limit = max(1, min(100, $limit));

        // Validasi sederhana agar format tanggal tetap konsisten.
        $request->validate([
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date'],
        ]);

        // Jika tabel target belum tersedia pada database lokal, kembalikan struktur kosong agar endpoint tetap aman.
        if (! $this->tableExists('retur')) {
            return response()->json([
                'filters' => [
                    'tanggal_dari' => $tanggalDari,
                    'tanggal_sampai' => $tanggalSampai,
                    'q' => $search,
                ],
                'summary' => [
                    'total_unit_diretur' => 0,
                    'total_nominal_retur' => 0,
                ],
                'data' => [],
            ]);
        }

        $query = $this->buildReturQuery($request);

        // Hitung total unit dan nominal untuk data yang terfilter.
        $summaryQuery = clone $query;
        $summaryData = $summaryQuery
            ->select(
                'r.qty',
                DB::raw('COALESCE(dt.harga, p.harga_jual, 0) as harga')
            )
            ->get();

        $totalUnit = (int) $summaryData->sum('qty');
        $totalNominal = (float) $summaryData->sum(fn ($row) => $row->qty * $row->harga);

        $summary = [
            'total_unit_diretur' => $totalUnit,
            'total_nominal_retur' => $totalNominal,
        ];

        // Paginate data.
        $paginated = $query
            ->select(
                'r.id as retur_id',
                'r.no_retur',
                'r.transaksi_id',
                'r.tanggal_retur',
                DB::raw('COALESCE(p.nama, r.produk_nama, "-") as produk_nama'),
                'r.qty',
                'r.alasan',
                'r.status',
                'r.foto',
                'u.name as kasir_nama',
                DB::raw('COALESCE(dt.harga, p.harga_jual, 0) as harga'),
                DB::raw('(r.qty * COALESCE(dt.harga, p.harga_jual, 0)) as total')
            )
            ->orderByDesc('r.tanggal_retur')
            ->orderByDesc('r.id')
            ->paginate($limit);

        return response()->json([
            'filters' => [
                'tanggal_dari' => $tanggalDari,
                'tanggal_sampai' => $tanggalSampai,
                'q' => $search,
            ],
            'summary' => $summary,
            'data' => $paginated->items(),
            'pagination' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function storeRetur(Request $request)
    {
        $validated = $request->validate([
            'transaksi_id' => ['nullable', 'integer', 'exists:transaksi,id'],
            'produk_id' => ['nullable', 'integer', 'exists:produks,id'],
            'produk_nama' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'alasan' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'tanggal_retur' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $product = null;
        if (!empty($validated['produk_id'])) {
            $product = Product::query()->find($validated['produk_id']);
        }

        $productName = $product?->nama ?? $validated['produk_nama'];

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('retur', 'public');
        }

        $status = 'Menunggu';
        if (auth()->user()->isOwner() || auth()->user()->isAdmin()) {
            $status = $validated['status'] ?? 'Menunggu';
        }

        $payload = [
            'transaksi_id' => $validated['transaksi_id'] ?? null,
            'produk_id' => $product?->id,
            'user_id' => auth()->id(),
            'produk_nama' => $productName,
            'qty' => (int) $validated['qty'],
            'alasan' => $validated['alasan'] ?? null,
            'status' => $status,
            'tanggal_retur' => $validated['tanggal_retur'] ?? now()->toDateString(),
            'foto' => $fotoPath,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($this->tableExists('retur')) {
            $id = DB::table('retur')->insertGetId($payload);

            return response()->json([
                'message' => 'Produk retur berhasil ditambahkan.',
                'data' => ['retur_id' => $id],
            ], 201);
        }

        return response()->json([
            'message' => 'Tabel retur belum tersedia di database. Data tidak disimpan.',
        ], 422);
    }

    public function searchProducts(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $pattern = '%'.addcslashes($q, '%_\\').'%';

        $products = Product::query()
            ->where(function ($query) use ($pattern) {
                $query->where('nama', 'LIKE', $pattern)
                    ->orWhere('kode', 'LIKE', $pattern)
                    ->orWhere('barcode', 'LIKE', $pattern);
            })
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nama', 'kode', 'barcode', 'stok']);

        return response()->json($products->map(fn (Product $product) => [
            'id' => $product->id,
            'nama' => $product->nama,
            'kode' => $product->kode,
            'barcode' => $product->barcode,
            'stok' => (int) $product->stok,
        ]));
    }


    public function updateRetur(Request $request, $id)
    {
        $validated = $request->validate([
            'transaksi_id' => ['nullable', 'integer', 'exists:transaksi,id'],
            'produk_id' => ['nullable', 'integer', 'exists:produks,id'],
            'produk_nama' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'alasan' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'tanggal_retur' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $product = null;
        if (!empty($validated['produk_id'])) {
            $product = Product::query()->find($validated['produk_id']);
        }
        $productName = $product?->nama ?? $validated['produk_nama'];

        $payload = [
            'transaksi_id' => $validated['transaksi_id'] ?? null,
            'produk_id' => $product?->id,
            'produk_nama' => $productName,
            'qty' => (int) $validated['qty'],
            'alasan' => $validated['alasan'] ?? null,
            'status' => $validated['status'] ?? 'Dalam Proses',
            'tanggal_retur' => $validated['tanggal_retur'] ?? now()->toDateString(),
            'updated_at' => now(),
        ];

        if ($request->hasFile('foto')) {
            $oldFoto = DB::table('retur')->where('id', $id)->value('foto');
            if ($oldFoto) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldFoto);
            }
            $payload['foto'] = $request->file('foto')->store('retur', 'public');
        }

        DB::table('retur')->where('id', $id)->update($payload);

        return response()->json([
            'message' => 'Produk retur berhasil diperbarui.',
        ]);
    }

    public function showDetailPage($id)
    {
        if (!$this->tableExists('retur')) {
            abort(404, 'Tabel retur belum tersedia.');
        }

        $retur = DB::table('retur as r')
            ->leftJoin('transaksi as t', 't.id', '=', 'r.transaksi_id')
            ->leftJoin('produks as p', 'p.id', '=', 'r.produk_id')
            ->leftJoin('detail_transaksi as dt', function ($join) {
                $join->on('dt.transaksi_id', '=', 'r.transaksi_id')
                     ->on('dt.produk_id', '=', 'r.produk_id');
            })
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->select([
                'r.id as retur_id',
                'r.no_retur',
                'r.transaksi_id',
                'r.tanggal_retur',
                DB::raw('COALESCE(p.nama, r.produk_nama, "-") as produk_nama'),
                'r.qty',
                'r.alasan',
                'r.status',
                'r.foto',
                'u.name as kasir_nama',
                DB::raw('COALESCE(dt.harga, p.harga_jual, 0) as harga'),
                DB::raw('(r.qty * COALESCE(dt.harga, p.harga_jual, 0)) as total')
            ])
            ->where('r.id', $id)
            ->first();

        if (!$retur) {
            abort(404, 'Data retur tidak ditemukan.');
        }

        return view('laporan.retur-detail', compact('retur'));
    }

    public function destroyRetur($id)
    {
        $oldFoto = DB::table('retur')->where('id', $id)->value('foto');
        if ($oldFoto) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldFoto);
        }

        DB::table('retur')->where('id', $id)->delete();

        return response()->json([
            'message' => 'Produk retur berhasil dihapus.',
        ]);
    }

    public function approveRetur($id)
    {
        $retur = DB::table('retur')->where('id', $id)->first();
        if (!$retur) {
            return response()->json(['message' => 'Data retur tidak ditemukan.'], 404);
        }

        if ($retur->status !== 'Menunggu') {
            return response()->json(['message' => 'Hanya retur dengan status Menunggu yang dapat disetujui.'], 422);
        }

        // 1. Update status to Diterima
        DB::table('retur')->where('id', $id)->update([
            'status' => 'Diterima',
            'updated_at' => now(),
        ]);

        // 2. Increment stock if produk_id is set
        $produkNama = $retur->produk_nama;
        if ($retur->produk_id) {
            $product = Product::find($retur->produk_id);
            if ($product) {
                $product->increment('stok', $retur->qty);
                $produkNama = $product->nama;
                \App\Models\StokLog::logChange($product->id, 'Retur', $retur->qty, 0, $retur->no_retur ?? ('RT-' . $retur->id), auth()->id() ?? null, 'Retur Penjualan disetujui');
            }
        }

        // 3. Save audit log
        DB::table('audit_logs')->insert([
            'model_type' => 'Retur',
            'model_id' => $id,
            'user_id' => auth()->id(),
            'action' => 'Approve',
            'details' => "Status retur #{$id} disetujui (Diterima) oleh " . auth()->user()->name . ". Stok produk '{$produkNama}' bertambah sebanyak {$retur->qty}.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Retur berhasil disetujui, stok produk telah disesuaikan.',
        ]);
    }

    public function rejectRetur($id)
    {
        $retur = DB::table('retur')->where('id', $id)->first();
        if (!$retur) {
            return response()->json(['message' => 'Data retur tidak ditemukan.'], 404);
        }

        if ($retur->status !== 'Menunggu') {
            return response()->json(['message' => 'Hanya retur dengan status Menunggu yang dapat ditolak.'], 422);
        }

        // 1. Update status to Ditolak
        DB::table('retur')->where('id', $id)->update([
            'status' => 'Ditolak',
            'updated_at' => now(),
        ]);

        // 2. Save audit log
        DB::table('audit_logs')->insert([
            'model_type' => 'Retur',
            'model_id' => $id,
            'user_id' => auth()->id(),
            'action' => 'Reject',
            'details' => "Status retur #{$id} ditolak (Ditolak) oleh " . auth()->user()->name . ". Stok tidak mengalami perubahan.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Retur berhasil ditolak.',
        ]);
    }

    public function returStats(Request $request)
    {
        if (!$this->tableExists('retur')) {
            return response()->json([
                'status_breakdown' => [],
                'top_products' => [],
                'daily_trends' => [],
                'common_reasons' => [],
            ]);
        }

        $query = $this->buildReturQuery($request);

        // 1. Distribusi Status
        $statusQuery = clone $query;
        $statusBreakdown = $statusQuery
            ->select('r.status', DB::raw('COUNT(*) as count'), DB::raw('SUM(r.qty * COALESCE(dt.harga, p.harga_jual, 0)) as total_nominal'))
            ->groupBy('r.status')
            ->get();

        // 2. Top Produk
        $productQuery = clone $query;
        $topProducts = $productQuery
            ->select('r.produk_nama', DB::raw('SUM(r.qty) as total_qty'))
            ->groupBy('r.produk_nama')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 3. Tren Harian
        $trendQuery = clone $query;
        $dailyTrends = $trendQuery
            ->select('r.tanggal_retur', DB::raw('COUNT(*) as count'), DB::raw('SUM(r.qty * COALESCE(dt.harga, p.harga_jual, 0)) as total_nominal'))
            ->groupBy('r.tanggal_retur')
            ->orderBy('r.tanggal_retur', 'ASC')
            ->limit(15)
            ->get();

        // 4. Alasan Retur Terbanyak
        $reasonQuery = clone $query;
        $commonReasons = $reasonQuery
            ->select('r.alasan', DB::raw('COUNT(*) as count'))
            ->groupBy('r.alasan')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json([
            'status_breakdown' => $statusBreakdown,
            'top_products' => $topProducts,
            'daily_trends' => $dailyTrends,
            'common_reasons' => $commonReasons,
        ]);
    }

    private function buildReturQuery(Request $request)
    {
        $query = DB::table('retur as r')
            ->leftJoin('transaksi as t', 't.id', '=', 'r.transaksi_id')
            ->leftJoin('produks as p', 'p.id', '=', 'r.produk_id')
            ->leftJoin('detail_transaksi as dt', function ($join) {
                $join->on('dt.transaksi_id', '=', 'r.transaksi_id')
                     ->on('dt.produk_id', '=', 'r.produk_id');
            })
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->leftJoin('pelanggans as pl', 'pl.id', '=', 't.pelanggan_id');

        $tanggalDari = $request->query('tanggal_dari');
        $tanggalSampai = $request->query('tanggal_sampai');
        if ($tanggalDari && $tanggalSampai) {
            $query->whereBetween('r.tanggal_retur', [$tanggalDari, $tanggalSampai]);
        } elseif ($tanggalDari) {
            $query->where('r.tanggal_retur', '>=', $tanggalDari);
        } elseif ($tanggalSampai) {
            $query->where('r.tanggal_retur', '<=', $tanggalSampai);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $pattern = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(function ($q) use ($pattern, $search) {
                $q->where('r.produk_nama', 'LIKE', $pattern)
                  ->orWhere('p.nama', 'LIKE', $pattern)
                  ->orWhere('r.alasan', 'LIKE', $pattern)
                  ->orWhere('r.no_retur', 'LIKE', $pattern)
                  ->orWhere('u.name', 'LIKE', $pattern);
                
                if (is_numeric($search)) {
                    $q->orWhere('r.transaksi_id', '=', (int)$search);
                }
            });
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('r.status', $status);
        }

        $produkId = $request->query('produk_id');
        if ($produkId) {
            $query->where('r.produk_id', (int) $produkId);
        }

        $userId = $request->query('user_id');
        if ($userId) {
            $query->where('r.user_id', (int) $userId);
        }

        $jenis = $request->query('jenis');
        if ($jenis === 'dengan_transaksi') {
            $query->whereNotNull('r.transaksi_id');
        } elseif ($jenis === 'tanpa_transaksi') {
            $query->whereNull('r.transaksi_id');
        }

        return $query;
    }

    public function exportPdf(Request $request)
    {
        if (!$this->tableExists('retur')) {
            abort(404, 'Tabel retur belum tersedia.');
        }

        $query = $this->buildReturQuery($request);

        $returs = $query
            ->select([
                'r.id as retur_id',
                'r.no_retur',
                'r.transaksi_id',
                'r.tanggal_retur',
                DB::raw('COALESCE(p.nama, r.produk_nama, "-") as produk_nama'),
                'r.qty',
                'r.alasan',
                'r.status',
                'u.name as kasir_nama',
                DB::raw('COALESCE(dt.harga, p.harga_jual, 0) as harga'),
                DB::raw('(r.qty * COALESCE(dt.harga, p.harga_jual, 0)) as total')
            ])
            ->orderByDesc('r.tanggal_retur')
            ->orderByDesc('r.id')
            ->get();

        $totalUnit = $returs->sum('qty');
        $totalNominal = $returs->sum('total');

        $pdf = Pdf::loadView('laporan.pdf.retur', [
            'returs' => $returs,
            'totalUnit' => $totalUnit,
            'totalNominal' => $totalNominal,
            'tanggalCetak' => now(),
            'dari' => $request->query('tanggal_dari'),
            'sampai' => $request->query('tanggal_sampai'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-retur-penjualan-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        if (!$this->tableExists('retur')) {
            abort(404, 'Tabel retur belum tersedia.');
        }

        $query = $this->buildReturQuery($request);

        $returs = $query
            ->select([
                'r.id as retur_id',
                'r.no_retur',
                'r.transaksi_id',
                'r.tanggal_retur',
                DB::raw('COALESCE(p.nama, r.produk_nama, "-") as produk_nama'),
                'r.qty',
                'r.alasan',
                'r.status',
                'u.name as kasir_nama',
                DB::raw('COALESCE(dt.harga, p.harga_jual, 0) as harga'),
                DB::raw('(r.qty * COALESCE(dt.harga, p.harga_jual, 0)) as total')
            ])
            ->orderByDesc('r.tanggal_retur')
            ->orderByDesc('r.id')
            ->get();

        $rows = [];
        $rows[] = ['No', 'No. Retur', 'No. Transaksi', 'Tanggal', 'Nama Produk', 'Harga Satuan', 'Qty', 'Total', 'Kasir', 'Alasan', 'Status'];

        foreach ($returs as $index => $row) {
            $rows[] = [
                $index + 1,
                $row->no_retur ?? $row->retur_id,
                $row->transaksi_id ? '#' . $row->transaksi_id : '—',
                $row->tanggal_retur,
                $row->produk_nama,
                (float) $row->harga,
                (int) $row->qty,
                (float) $row->total,
                $row->kasir_nama ?? '—',
                $row->alasan ?? '—',
                $row->status
            ];
        }

        $filename = 'laporan-retur-penjualan-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    fputcsv($handle, $row, ',');
                    continue;
                }

                // Format values for Excel readability
                $row[5] = number_format((float) $row[5], 0, ',', '.');
                $row[6] = number_format((int) $row[6], 0, ',', '.');
                $row[7] = number_format((float) $row[7], 0, ',', '.');

                fputcsv($handle, $row, ',');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Helper sederhana untuk mengecek apakah tabel ada sebelum menjalankan query.
     */
    private function tableExists(string $table): bool
    {
        return DB::connection()->getSchemaBuilder()->hasTable($table);
    }
}
