<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerRestokController extends Controller
{
    /**
     * Tampilkan daftar barang yang perlu restok.
     */
    public function __invoke(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'satuanModel'])
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('nama')
            ->get();

        return view('owner.restok', compact('products'));
    }
}
