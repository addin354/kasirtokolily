<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    /**
     * Katalog publik baca: produk, stok, harga (role pelanggan) — pencarian & filter.
     */
    public function __invoke(Request $request): View
    {
        $this->authorize('viewCatalog', Product::class);

        $categories = Category::query()->orderBy('nama')->get();

        $products = Product::query()
            ->with(['category', 'satuanModel'])
            ->where('is_active', true)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->string('q')->trim() . '%';
                $query->where(function ($w) use ($term) {
                    $w->where('nama', 'like', $term)
                        ->orWhere('kode', 'like', $term)
                        ->orWhere('barcode', 'like', $term);
                });
            })
            ->when(
                $request->filled('kategori_id'),
                fn ($q) => $q->where('kategori_id', (int) $request->kategori_id)
            )
            ->when(
                $request->boolean('tersedia'),
                fn ($q) => $q->where('stok', '>', 0)
            )
            ->orderBy('nama')
            ->paginate(20)
            ->appends($request->query());

        return view('katalog.index', compact('products', 'categories'));
    }
}
