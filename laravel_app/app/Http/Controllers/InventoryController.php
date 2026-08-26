<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StokLog;
use App\Models\StokOpname;
use App\Models\PenyesuaianStok;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryController extends Controller
{
    /**
     * Tampilan utama Inventory: Multi-Tab (Daftar Stok, Kartu Stok, Stock Opname, Penyesuaian, Riwayat).
     */
    public function index(Request $request)
    {
        // Auto-seed inventory data if stock opname or penyesuaian count < 10
        if (StokOpname::query()->count() < 10 || PenyesuaianStok::query()->count() < 10) {
            (new \Database\Seeders\InventorySeeder())->run();
        }

        $tab = $request->query('tab', 'opname');

        if ($tab === 'masuk' || $tab === 'daftar' || $tab === 'kartu') {
            return redirect()->route('stok-masuk.index', ['tab' => 'opname']);
        }

        if ($tab === 'opname') {
            $productsList = Product::orderBy('nama')->get();
            $history = StokOpname::with(['product', 'user'])
                ->latest()
                ->paginate(10)
                ->withQueryString();

            return view('inventory.index', compact('tab', 'productsList', 'history'));
        }

        if ($tab === 'penyesuaian') {
            $productsList = Product::orderBy('nama')->get();
            $history = PenyesuaianStok::with(['product', 'user'])
                ->latest()
                ->paginate(10)
                ->withQueryString();

            return view('inventory.index', compact('tab', 'productsList', 'history'));
        }

        if ($tab === 'riwayat') {
            $productsList = Product::orderBy('nama')->get();
            $usersList = User::whereIn('role', ['admin', 'owner', 'kasir'])->orderBy('name')->get();

            $query = $this->queryRiwayatLogs($request);
            $history = $query->paginate(15)->withQueryString();

            return view('inventory.index', compact('tab', 'history', 'productsList', 'usersList'));
        }

        return redirect()->route('stok-masuk.index', ['tab' => 'opname']);
    }

    /**
     * Simpan transaksi Stock Opname.
     */
    public function storeStockOpname(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => ['required', 'integer', 'exists:produks,id'],
            'stok_fisik' => ['required', 'integer', 'min:0'],
            'alasan' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::lockForUpdate()->find($validated['produk_id']);
            $stokSistem = (int) $product->stok;
            $stokFisik = (int) $validated['stok_fisik'];
            $selisih = $stokFisik - $stokSistem;

            // Save Opname history
            $opname = StokOpname::create([
                'produk_id' => $product->id,
                'stok_sistem' => $stokSistem,
                'stok_fisik' => $stokFisik,
                'selisih' => $selisih,
                'alasan' => $validated['alasan'],
                'tanggal' => now()->toDateString(),
                'user_id' => auth()->id(),
            ]);

            // Update product stock
            $product->update([
                'stok' => $stokFisik
            ]);

            // Log change
            $masuk = $selisih > 0 ? $selisih : 0;
            $keluar = $selisih < 0 ? abs($selisih) : 0;
            StokLog::logChange($product->id, 'Stock Opname', $masuk, $keluar, 'SO-' . $opname->id, auth()->id(), $validated['alasan']);
        });

        return redirect()->route('stok-masuk.index', ['tab' => 'opname'])
            ->with('success', 'Stock Opname berhasil disimpan dan stok sistem telah diperbarui.');
    }

    /**
     * Simpan transaksi Penyesuaian Stok.
     */
    public function storePenyesuaian(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => ['required', 'integer', 'exists:produks,id'],
            'jenis' => ['required', 'string', 'in:Tambah,Kurang,tambah,kurang'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'alasan' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::lockForUpdate()->find($validated['produk_id']);
            $jumlah = (int) $validated['jumlah'];

            $isTambah = in_array(strtolower(trim($validated['jenis'])), ['tambah', 'masuk', 'plus', '+']);

            if ($isTambah) {
                $product->increment('stok', $jumlah);
                $masuk = $jumlah;
                $keluar = 0;
            } else {
                $product->decrement('stok', $jumlah);
                $masuk = 0;
                $keluar = $jumlah;
            }

            // Save adjustment
            $adj = PenyesuaianStok::create([
                'produk_id' => $product->id,
                'jenis' => $validated['jenis'],
                'jumlah' => $jumlah,
                'alasan' => $validated['alasan'],
                'tanggal' => now()->toDateString(),
                'user_id' => auth()->id(),
            ]);

            // Log change
            StokLog::logChange($product->id, 'Penyesuaian', $masuk, $keluar, 'ADJ-' . $adj->id, auth()->id(), $validated['alasan']);
        });

        return redirect()->route('stok-masuk.index', ['tab' => 'penyesuaian'])
            ->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    /**
     * Cetak PDF Daftar Stok.
     */
    public function exportPdf(Request $request)
    {
        $products = $this->queryDaftarStok($request, false);

        $pdf = Pdf::loadView('inventory.pdf', [
            'products' => $products,
            'tanggalCetak' => now(),
            'user' => auth()->user(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('daftar-stok-inventory-' . now()->format('Y-m-d-His') . '.pdf');
    }

    /**
     * Export Excel (CSV) Daftar Stok.
     */
    public function exportExcel(Request $request)
    {
        $products = $this->queryDaftarStok($request, false);

        $filename = 'daftar-stok-inventory-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($products): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['Barcode', 'Nama Produk', 'Kategori', 'Supplier Terakhir', 'Harga Modal', 'Harga Jual', 'Stok Saat Ini', 'Minimum Stok', 'Status']);

            foreach ($products as $row) {
                $statusText = $row->stok <= $row->stok_minimum ? 'Perlu Restok' : 'Aman';

                fputcsv($handle, [
                    $row->barcode,
                    $row->nama,
                    $row->category?->nama ?? '—',
                    $row->supplierTerakhir() ?? '—',
                    'Rp ' . number_format((float) $row->harga_beli, 0, ',', '.'),
                    'Rp ' . number_format((float) $row->harga_jual, 0, ',', '.'),
                    (int) $row->stok,
                    (int) $row->stok_minimum,
                    $statusText
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Cetak PDF Riwayat Pergerakan Stok.
     */
    public function exportRiwayatPdf(Request $request)
    {
        $history = $this->queryRiwayatLogs($request)->get();

        $pdf = Pdf::loadView('inventory.riwayat_pdf', [
            'history' => $history,
            'tanggalCetak' => now(),
            'user' => auth()->user(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-riwayat-stok-' . now()->format('Y-m-d-His') . '.pdf');
    }

    /**
     * Export Excel (CSV) Riwayat Pergerakan Stok.
     */
    public function exportRiwayatExcel(Request $request)
    {
        $history = $this->queryRiwayatLogs($request)->get();

        $filename = 'laporan-riwayat-stok-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($history): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['Tanggal', 'Produk', 'Jenis Transaksi', 'Masuk', 'Keluar', 'Saldo Akhir', 'Nomor Referensi', 'User']);

            foreach ($history as $row) {
                $jenisLabel = $row->jenis;
                if ($jenisLabel === 'Pembelian') $jenisLabel = 'Pembelian Barang';
                if ($jenisLabel === 'Retur') $jenisLabel = 'Retur Penjualan';
                if ($jenisLabel === 'Penyesuaian') $jenisLabel = 'Penyesuaian Stok';

                fputcsv($handle, [
                    $row->tanggal->format('d/m/Y H:i'),
                    $row->product?->nama ?? '—',
                    $jenisLabel,
                    (int) $row->masuk,
                    (int) $row->keluar,
                    (int) $row->saldo,
                    $row->referensi ?? '—',
                    $row->user?->name ?? 'System'
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Query Daftar Stok helper.
     */
    private function queryDaftarStok(Request $request, bool $paginate)
    {
        $query = Product::query()->with(['category', 'satuanModel'])->orderBy('nama');

        // Search
        if ($request->filled('q')) {
            $pattern = '%' . addcslashes($request->query('q'), '%_\\') . '%';
            $query->where(function ($qq) use ($pattern) {
                $qq->where('nama', 'LIKE', $pattern)
                  ->orWhere('kode', 'LIKE', $pattern)
                  ->orWhere('barcode', 'LIKE', $pattern);
            });
        }

        // Filter Category
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->query('kategori_id'));
        }

        // Filter Supplier
        if ($request->filled('supplier_id')) {
            $supplierId = (int) $request->query('supplier_id');
            // Get product IDs matching this supplier in purchases or incoming
            $productIdsFromPembelian = DB::table('detail_pembelians as dp')
                ->join('pembelians as p', 'p.id', '=', 'dp.pembelian_id')
                ->where('p.supplier_id', $supplierId)
                ->pluck('dp.produk_id');

            $productIdsFromStokMasuk = DB::table('stok_masuk')
                ->where('supplier_id', $supplierId)
                ->pluck('produk_id');

            $matchedProductIds = $productIdsFromPembelian->merge($productIdsFromStokMasuk)->unique()->all();
            $query->whereIn('id', $matchedProductIds);
        }

        // Filter Status
        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'restok') {
                $query->whereColumn('stok', '<=', 'stok_minimum');
            } elseif ($status === 'aman') {
                $query->whereColumn('stok', '>', 'stok_minimum');
            }
        }

        if ($paginate) {
            return $query->paginate(15)->withQueryString();
        }

        return $query->get();
    }

    /**
     * Query Riwayat Logs helper.
     */
    private function queryRiwayatLogs(Request $request)
    {
        $query = StokLog::with(['product', 'user'])->latest('tanggal');

        // Filter Tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->query('tanggal_dari'));
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->query('tanggal_sampai'));
        }

        // Filter Produk
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->query('produk_id'));
        }

        // Filter Jenis Transaksi
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->query('jenis'));
        }

        // Filter User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        return $query;
    }
}
