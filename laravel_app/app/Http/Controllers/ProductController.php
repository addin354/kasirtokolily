<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Satuan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * CRUD produk + (di halaman yang sama) tab kategori & satuan.
 *
 * Rute (prefix URI `/produk`, middleware `auth` + `role:admin,owner`):
 * | Method | URI                | Aksi         | Name              |
 * |--------|--------------------|-------------|-------------------|
 * | GET    | /produk            | index (tab) | products.index   |
 * | GET    | /produk/create     | create      | products.create  |
 * | POST   | /produk            | store       | products.store   |
 * | GET    | /produk/{id}/edit  | edit        | products.edit    |
 * | PUT    | /produk/{id}       | update      | products.update  |
 * | DELETE | /produk/{id}       | destroy     | products.destroy |
 *
 * Form tambah/edit: dropdown kategori & satuan + modal quick-add (`products.partials.kategori-satuan-fields`)
 * memanggil `POST` `categories.store-json` & `satuans.store-json`.
 */
class ProductController extends Controller
{
    /**
     * Daftar: tab produk / kategori / satuan; CRUD kategori & satuan di tab masing-masing.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $tab = $request->query('tab', 'produk');
        if (! in_array($tab, ['produk', 'kategori', 'satuan'], true)) {
            $tab = 'produk';
        }

        $productQuery = Product::query()->with(['category', 'satuanModel']);
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $pattern = '%'.addcslashes($search, '%_\\').'%';
            $productQuery->where(function ($qry) use ($pattern) {
                $qry->where('nama', 'LIKE', $pattern)
                    ->orWhere('kode', 'LIKE', $pattern)
                    ->orWhere('barcode', 'LIKE', $pattern);
            })->orderBy('nama');
        } else {
            $productQuery->latest();
        }

        $products = $productQuery->paginate(10, ['*'], 'produk_page');

        $categories = Category::query()
            ->withCount('products')
            ->orderBy('nama')
            ->paginate(15, ['*'], 'kategori_page');

        $satuans = Satuan::query()
            ->withCount('products')
            ->orderBy('nama')
            ->paginate(20, ['*'], 'satuan_page');

        return view('products.index', compact('products', 'categories', 'satuans', 'tab'));
    }

    /**
     * Saran pencarian cepat (AJAX) untuk kolom cari di halaman daftar produk.
     */
    public function searchSuggestions(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $pattern = '%'.addcslashes($q, '%_\\').'%';

        $rows = Product::query()
            ->with(['category', 'satuanModel'])
            ->where(function ($query) use ($pattern) {
                $query->where('nama', 'LIKE', $pattern)
                    ->orWhere('kode', 'LIKE', $pattern)
                    ->orWhere('barcode', 'LIKE', $pattern);
            })
            ->orderBy('nama')
            ->limit(15)
            ->get(['id', 'nama', 'kode', 'barcode', 'stok']);

        return response()->json($rows->map(fn (Product $p) => [
            'id' => $p->id,
            'nama' => $p->nama,
            'kode' => $p->kode,
            'barcode' => $p->barcode,
            'stok' => (int) $p->stok,
            'kategori' => $p->category?->nama ?? '—',
            'satuan' => $p->satuanModel?->nama ?? '—',
        ]));
    }

    public function exportProdukPdf()
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with(['category', 'satuanModel'])
            ->orderBy('nama')
            ->get();

        $pdf = Pdf::loadView('produk.pdf.index', [
            'products' => $products,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('data-produk-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportProdukExcel()
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with(['category', 'satuanModel'])
            ->orderBy('nama')
            ->get();

        $rows = [];
        $rows[] = ['Nama', 'Kode', 'Barcode', 'Kategori', 'Satuan', 'Harga Beli', 'Harga Jual', 'Harga Grosir', 'Harga Bal', 'Stok'];

        foreach ($products as $product) {
            $rows[] = [
                $product->nama,
                $product->kode,
                $product->barcode,
                $product->category?->nama ?? '—',
                $product->satuanModel?->nama ?? '—',
                (float) $product->harga_beli,
                (float) $product->harga_jual,
                (float) ($product->harga_grosir ?? 0),
                (float) ($product->harga_bal ?? 0),
                (int) $product->stok,
            ];
        }

        $filename = 'data-produk-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            // write UTF-8 BOM so Excel recognizes UTF-8 encoding
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $index => $row) {
                // format header row as-is
                if ($index === 0) {
                    fputcsv($handle, $row, ',');
                    continue;
                }

                // format currency and stok for readability
                $row[5] = 'Rp ' . number_format((float) $row[5], 0, ',', '.');
                $row[6] = 'Rp ' . number_format((float) $row[6], 0, ',', '.');
                $row[7] = 'Rp ' . number_format((float) $row[7], 0, ',', '.');
                $row[8] = 'Rp ' . number_format((float) $row[8], 0, ',', '.');
                $row[9] = number_format((int) $row[9], 0, ',', '.');

                fputcsv($handle, $row, ',');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Product::class);

        $categories = Category::orderBy('nama')->get();
        $satuans = Satuan::orderBy('nama')->get();

        return view('products.create', compact('categories', 'satuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $request->merge([
            'satuan_id' => $request->filled('satuan_id') ? (int) $request->satuan_id : null,
        ]);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'satuan_id' => ['nullable', 'exists:satuans,id'],
            'barcode' => ['required', 'string', 'max:255', 'unique:produks,barcode'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'harga_grosir' => ['required', 'numeric', 'min:0'],
            'harga_bal' => ['required', 'numeric', 'min:0'],
            'isi_per_bal' => ['nullable', 'integer', 'min:1'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
        ]);

        Product::create([
            'kode' => 'PRD-' . now()->format('YmdHis') . '-' . random_int(100, 999),
            'barcode' => $validated['barcode'],
            'nama' => $validated['nama'],
            'kategori_id' => $validated['kategori_id'],
            'satuan_id' => $validated['satuan_id'] ?? null,
            'harga_beli' => $validated['harga_beli'],
            'harga_jual' => $validated['harga_jual'],
            'harga_grosir' => $validated['harga_grosir'],
            'harga_bal' => $validated['harga_bal'],
            'isi_per_bal' => $validated['isi_per_bal'] ?? null,
            'stok' => $validated['stok'],
            'stok_minimum' => $validated['stok_minimum'],
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return redirect()->route('products.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::orderBy('nama')->get();
        $satuans = Satuan::orderBy('nama')->get();

        return view('products.edit', compact('product', 'categories', 'satuans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->merge([
            'satuan_id' => $request->filled('satuan_id') ? (int) $request->satuan_id : null,
        ]);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'satuan_id' => ['nullable', 'exists:satuans,id'],
            'barcode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('produks', 'barcode')->ignore($product->id),
            ],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'harga_grosir' => ['required', 'numeric', 'min:0'],
            'harga_bal' => ['required', 'numeric', 'min:0'],
            'isi_per_bal' => ['nullable', 'integer', 'min:1'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        try {
            $product->delete();

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()
                ->route('products.index')
                ->with('error', 'Produk "' . $product->nama . '" tidak dapat dihapus karena sudah memiliki riwayat transaksi atau stok.');
        }
    }
}
