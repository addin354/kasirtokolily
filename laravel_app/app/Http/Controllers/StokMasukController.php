<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Product;
use App\Models\StokMasuk;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokMasukController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', StokMasuk::class);

        $records = StokMasuk::query()
            ->with(['product', 'supplier'])
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15);

        $stokKeluar = DetailTransaksi::query()
            ->with(['product', 'transaksi'])
            ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->orderByDesc('transaksi.tanggal')
            ->orderByDesc('detail_transaksi.id')
            ->select('detail_transaksi.*')
            ->paginate(15, ['*'], 'keluar_page');

        $suppliers = Supplier::query()->orderBy('nama_supplier')->get();
        $oldProduct = null;
        $oldPid = old('produk_id');
        if ($oldPid) {
            $oldProduct = Product::query()->find((int) $oldPid);
        }

        return view('stok_masuk.index', compact('records', 'stokKeluar', 'suppliers', 'oldProduct'));
    }

    /**
     * Pencarian produk untuk input stok masuk (nama, kode, barcode).
     */
    public function searchProducts(Request $request)
    {
        $this->authorize('create', StokMasuk::class);

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        if (preg_match('/^[0-9]{8,}$/', $q)) {
            $byBarcode = Product::query()->where('barcode', $q)->first();
            if ($byBarcode) {
                return response()->json([$this->productSearchRow($byBarcode)]);
            }
        }

        $pattern = '%'.addcslashes($q, '%_\\').'%';

        $rows = Product::query()
            ->where(function ($qq) use ($pattern) {
                $qq->where('nama', 'LIKE', $pattern)
                    ->orWhere('kode', 'LIKE', $pattern)
                    ->orWhere('barcode', 'LIKE', $pattern);
            })
            ->orderBy('nama')
            ->limit(15)
            ->get();

        return response()->json($rows->map(fn (Product $p) => $this->productSearchRow($p))->values()->all());
    }

    /**
     * @return array{id: int, nama: string, kode: string|null, barcode: string|null, stok: int}
     */
    private function productSearchRow(Product $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'kode' => $p->kode,
            'barcode' => $p->barcode,
            'stok' => (int) $p->stok,
        ];
    }

    public function store(Request $request)
    {
        $this->authorize('create', StokMasuk::class);

        $validated = $request->validate([
            'produk_id' => ['required', 'integer', 'exists:produks,id'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated) {
            StokMasuk::create([
                'produk_id' => $validated['produk_id'],
                'supplier_id' => $validated['supplier_id'],
                'jumlah' => $validated['jumlah'],
                'harga_beli' => $validated['harga_beli'],
                'tanggal' => $validated['tanggal'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $product = Product::query()->whereKey($validated['produk_id'])->lockForUpdate()->first();
            if ($product) {
                $product->increment('stok', $validated['jumlah']);
                $product->update([
                    'harga_beli' => $validated['harga_beli'],
                ]);
            }
        });

        return redirect()
            ->route('stok-masuk.index')
            ->with('success', 'Stok masuk tercatat, stok produk bertambah, dan harga beli produk diperbarui.');
    }
}
