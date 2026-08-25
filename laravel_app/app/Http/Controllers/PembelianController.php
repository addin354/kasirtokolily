<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Product;
use App\Models\Supplier;
use App\Http\Requests\StorePembelianRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Pembelian::class);

        $q = trim((string) $request->query('q', ''));
        $cariNomor = trim((string) $request->query('cari_nomor', ''));
        $cariSupplier = trim((string) $request->query('cari_supplier', ''));
        $cariProduk = trim((string) $request->query('cari_produk', ''));
        $supplierId = $request->query('supplier_id');
        $userId = $request->query('user_id');
        $tanggalDari = $request->query('tanggal_dari');
        $tanggalSampai = $request->query('tanggal_sampai');

        // Page limit pagination
        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        // 1. Dashboard statistics (overall, unaffected by listing filters)
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $totalHariIni = (float) Pembelian::whereDate('tanggal', $today)->sum('total');
        $totalBulanIni = (float) Pembelian::whereDate('tanggal', '>=', $startOfMonth)->whereDate('tanggal', '<=', $endOfMonth)->sum('total');
        $totalNominal = (float) Pembelian::sum('total');
        $jumlahSupplier = (int) Pembelian::distinct('supplier_id')->count('supplier_id');

        // 2. Chart data: Total Pembelian per Bulan on the current year (database-agnostic)
        $currentYear = now()->year;
        $purchasesThisYear = Pembelian::whereDate('tanggal', '>=', $currentYear . '-01-01')
            ->whereDate('tanggal', '<=', $currentYear . '-12-31')
            ->select('tanggal', 'total')
            ->get();

        $monthlyData = array_fill(1, 12, 0.0);
        foreach ($purchasesThisYear as $p) {
            $month = (int) $p->tanggal->format('n');
            $monthlyData[$month] += (float) $p->total;
        }
        $monthlyChartValues = array_values($monthlyData);

        // 3. Build filtered query
        $query = Pembelian::query()
            ->with(['supplier', 'user'])
            ->withCount('detailPembelians');

        // General search
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_pembelian', 'LIKE', "%{$q}%")
                    ->orWhere('keterangan', 'LIKE', "%{$q}%")
                    ->orWhereHas('supplier', function ($sup) use ($q) {
                        $sup->where('nama_supplier', 'LIKE', "%{$q}%");
                    })
                    ->orWhereHas('detailPembelians.product', function ($prod) use ($q) {
                        $prod->where('nama', 'LIKE', "%{$q}%");
                    });
            });
        }

        // Specific Cari Nomor Pembelian
        if ($cariNomor !== '') {
            $query->where('nomor_pembelian', 'LIKE', "%{$cariNomor}%");
        }

        // Specific Cari Supplier
        if ($cariSupplier !== '') {
            $query->whereHas('supplier', function ($sup) use ($cariSupplier) {
                $sup->where('nama_supplier', 'LIKE', "%{$cariSupplier}%");
            });
        }

        // Specific Cari Produk
        if ($cariProduk !== '') {
            $query->whereHas('detailPembelians.product', function ($prod) use ($cariProduk) {
                $prod->where('nama', 'LIKE', "%{$cariProduk}%");
            });
        }

        // Supplier Dropdown
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        // User Dropdown
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Date range
        if ($tanggalDari) {
            $query->whereDate('tanggal', '>=', $tanggalDari);
        }
        if ($tanggalSampai) {
            $query->whereDate('tanggal', '<=', $tanggalSampai);
        }

        $pembelians = $query->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('pembelian.index', compact(
            'pembelians',
            'suppliers',
            'users',
            'totalHariIni',
            'totalBulanIni',
            'totalNominal',
            'jumlahSupplier',
            'monthlyChartValues'
        ));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Pembelian::class);

        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $products = Product::where('is_active', true)->orderBy('nama')->get();

        // Prediksi nomor pembelian otomatis
        $today = now()->format('Ymd');
        $prefix = 'PB-' . $today . '-';
        $latest = Pembelian::where('nomor_pembelian', 'LIKE', $prefix . '%')
            ->orderByDesc('nomor_pembelian')
            ->first();

        if ($latest) {
            $parts = explode('-', $latest->nomor_pembelian);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        $predictedNomor = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);

        $prefilledProduct = null;
        $prefilledQty = 1;
        if ($request->has('produk_id')) {
            $prefilledProduct = Product::find($request->input('produk_id'));
            if ($prefilledProduct) {
                $prefilledQty = max(1, (int) $request->input('qty', $prefilledProduct->rekomendasiJumlahOrder()));
            }
        }

        $recommendedRestokProducts = Product::where('is_active', true)
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('nama')
            ->get();

        return view('pembelian.create', compact(
            'suppliers',
            'predictedNomor',
            'products',
            'prefilledProduct',
            'prefilledQty',
            'recommendedRestokProducts'
        ));
    }

    public function store(StorePembelianRequest $request)
    {
        $this->authorize('create', Pembelian::class);

        $validated = $request->validated();

        try {
            $pembelian = DB::transaction(function () use ($validated) {
                // Generate nomor pembelian final di dalam transaksi untuk mencegah balapan (race condition)
                $today = now()->format('Ymd');
                $prefix = 'PB-' . $today . '-';
                $latest = Pembelian::where('nomor_pembelian', 'LIKE', $prefix . '%')
                    ->lockForUpdate()
                    ->orderByDesc('nomor_pembelian')
                    ->first();

                if ($latest) {
                    $parts = explode('-', $latest->nomor_pembelian);
                    $num = intval(end($parts)) + 1;
                } else {
                    $num = 1;
                }
                $nomorPembelian = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);

                // Hitung total pembelian
                $total = 0;
                foreach ($validated['items'] as $item) {
                    $total += $item['qty'] * $item['harga_beli'];
                }

                $pembelian = Pembelian::create([
                    'nomor_pembelian' => $nomorPembelian,
                    'supplier_id' => $validated['supplier_id'],
                    'tanggal' => $validated['tanggal'],
                    'total' => $total,
                    'keterangan' => $validated['keterangan'] ?? null,
                    'metode_pembayaran' => $validated['metode_pembayaran'] ?? 'Cash',
                    'user_id' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $subtotal = $item['qty'] * $item['harga_beli'];

                    DetailPembelian::create([
                        'pembelian_id' => $pembelian->id,
                        'produk_id' => $item['produk_id'],
                        'qty' => $item['qty'],
                        'harga_beli' => $item['harga_beli'],
                        'subtotal' => $subtotal,
                    ]);

                    // Update stok produk
                    $product = Product::findOrFail($item['produk_id']);
                    $product->increment('stok', $item['qty']);

                    // Update harga modal produk ke harga beli terakhir
                    $product->update([
                        'harga_beli' => $item['harga_beli']
                    ]);

                    \App\Models\StokLog::logChange($product->id, 'Pembelian', $item['qty'], 0, $pembelian->nomor_pembelian, auth()->id(), 'Pembelian Supplier');
                }

                return $pembelian;
            });

            return redirect()
                ->route('pembelian.index')
                ->with('success', 'Pembelian ' . $pembelian->nomor_pembelian . ' berhasil disimpan.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pembelian: ' . $e->getMessage());
        }
    }

    public function show(Pembelian $pembelian)
    {
        $this->authorize('view', $pembelian);

        $pembelian->load(['supplier', 'user', 'detailPembelians.product']);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json($pembelian);
        }

        return view('pembelian.show', compact('pembelian'));
    }

    public function edit(Pembelian $pembelian)
    {
        $this->authorize('update', $pembelian);

        $pembelian->load(['detailPembelians.product']);
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $products = Product::where('is_active', true)->orderBy('nama')->get();

        return view('pembelian.edit', compact('pembelian', 'suppliers', 'products'));
    }

    public function update(StorePembelianRequest $request, Pembelian $pembelian)
    {
        $this->authorize('update', $pembelian);

        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated, $pembelian) {
                // Revert/kembalikan stok lama
                foreach ($pembelian->detailPembelians as $oldDetail) {
                    $product = Product::find($oldDetail->produk_id);
                    if ($product) {
                        $product->decrement('stok', $oldDetail->qty);
                        \App\Models\StokLog::logChange($product->id, 'Pembelian', 0, $oldDetail->qty, $pembelian->nomor_pembelian, auth()->id(), 'Koreksi Pembelian (Revert)');
                    }
                }

                // Hapus detail pembelian lama
                $pembelian->detailPembelians()->delete();

                // Hitung total pembelian baru
                $total = 0;
                foreach ($validated['items'] as $item) {
                    $total += $item['qty'] * $item['harga_beli'];
                }

                // Update Pembelian
                $pembelian->update([
                    'supplier_id' => $validated['supplier_id'],
                    'tanggal' => $validated['tanggal'],
                    'total' => $total,
                    'keterangan' => $validated['keterangan'] ?? null,
                    'metode_pembayaran' => $validated['metode_pembayaran'] ?? 'Cash',
                ]);

                // Simpan detail baru & tambahkan stok baru
                foreach ($validated['items'] as $item) {
                    $subtotal = $item['qty'] * $item['harga_beli'];

                    DetailPembelian::create([
                        'pembelian_id' => $pembelian->id,
                        'produk_id' => $item['produk_id'],
                        'qty' => $item['qty'],
                        'harga_beli' => $item['harga_beli'],
                        'subtotal' => $subtotal,
                    ]);

                    $product = Product::findOrFail($item['produk_id']);
                    $product->increment('stok', $item['qty']);

                    // Update harga modal produk
                    $product->update([
                        'harga_beli' => $item['harga_beli']
                    ]);

                    \App\Models\StokLog::logChange($product->id, 'Pembelian', $item['qty'], 0, $pembelian->nomor_pembelian, auth()->id(), 'Koreksi Pembelian (Update)');
                }
            });

            return redirect()
                ->route('pembelian.index')
                ->with('success', 'Pembelian ' . $pembelian->nomor_pembelian . ' berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pembelian: ' . $e->getMessage());
        }
    }

    public function destroy(Pembelian $pembelian)
    {
        $this->authorize('delete', $pembelian);

        try {
            DB::transaction(function () use ($pembelian) {
                // Kurangi stok kembali sesuai jumlah pembelian
                foreach ($pembelian->detailPembelians as $detail) {
                    $product = Product::find($detail->produk_id);
                    if ($product) {
                        $product->decrement('stok', $detail->qty);
                        \App\Models\StokLog::logChange($product->id, 'Pembelian', 0, $detail->qty, $pembelian->nomor_pembelian, auth()->id(), 'Pembelian Dihapus (Revert)');
                    }
                }

                // Hapus detail pembelian
                $pembelian->detailPembelians()->delete();

                // Hapus pembelian
                $pembelian->delete();
            });

            return redirect()
                ->route('pembelian.index')
                ->with('success', 'Pembelian berhasil dihapus dan stok dikembalikan.');

        } catch (\Exception $e) {
            return redirect()
                ->route('pembelian.index')
                ->with('error', 'Gagal menghapus pembelian: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('viewAny', Pembelian::class);

        $pembelians = $this->buildFilteredQuery($request)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $pdf = Pdf::loadView('pembelian.pdf', [
            'pembelians' => $pembelians,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-pembelian-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('viewAny', Pembelian::class);

        $pembelians = $this->buildFilteredQuery($request)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $rows = [];
        $rows[] = ['Nomor Pembelian', 'Tanggal', 'Supplier', 'Keterangan', 'Jumlah Item', 'Total Pembelian', 'User'];

        foreach ($pembelians as $p) {
            $rows[] = [
                $p->nomor_pembelian,
                $p->tanggal->format('d/m/Y'),
                $p->supplier?->nama_supplier ?? '—',
                $p->keterangan ?? '',
                $p->detail_pembelians_count,
                (float) $p->total,
                $p->user?->name ?? '—',
            ];
        }

        $filename = 'laporan-pembelian-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    fputcsv($handle, $row, ',');
                    continue;
                }
                $row[5] = 'Rp ' . number_format($row[5], 0, ',', '.');
                fputcsv($handle, $row, ',');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function cetak(Pembelian $pembelian)
    {
        $this->authorize('view', $pembelian);

        $pembelian->load(['supplier', 'user', 'detailPembelians.product']);

        return view('pembelian.cetak', compact('pembelian'));
    }

    private function buildFilteredQuery(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $cariNomor = trim((string) $request->query('cari_nomor', ''));
        $cariSupplier = trim((string) $request->query('cari_supplier', ''));
        $cariProduk = trim((string) $request->query('cari_produk', ''));
        $supplierId = $request->query('supplier_id');
        $userId = $request->query('user_id');
        $tanggalDari = $request->query('tanggal_dari');
        $tanggalSampai = $request->query('tanggal_sampai');

        $query = Pembelian::query()
            ->with(['supplier', 'user'])
            ->withCount('detailPembelians');

        // General search
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_pembelian', 'LIKE', "%{$q}%")
                    ->orWhere('keterangan', 'LIKE', "%{$q}%")
                    ->orWhereHas('supplier', function ($sup) use ($q) {
                        $sup->where('nama_supplier', 'LIKE', "%{$q}%");
                    })
                    ->orWhereHas('detailPembelians.product', function ($prod) use ($q) {
                        $prod->where('nama', 'LIKE', "%{$q}%");
                    });
            });
        }

        // Specific Cari Nomor Pembelian
        if ($cariNomor !== '') {
            $query->where('nomor_pembelian', 'LIKE', "%{$cariNomor}%");
        }

        // Specific Cari Supplier
        if ($cariSupplier !== '') {
            $query->whereHas('supplier', function ($sup) use ($cariSupplier) {
                $sup->where('nama_supplier', 'LIKE', "%{$cariSupplier}%");
            });
        }

        // Specific Cari Produk
        if ($cariProduk !== '') {
            $query->whereHas('detailPembelians.product', function ($prod) use ($cariProduk) {
                $prod->where('nama', 'LIKE', "%{$cariProduk}%");
            });
        }

        // Supplier Dropdown
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        // User Dropdown
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Date range
        if ($tanggalDari) {
            $query->whereDate('tanggal', '>=', $tanggalDari);
        }
        if ($tanggalSampai) {
            $query->whereDate('tanggal', '<=', $tanggalSampai);
        }

        return $query;
    }
}
