<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockPredictionService;
use Illuminate\Http\JsonResponse;

class StockPredictionController extends Controller
{
    /**
     * Contoh: GET /owner/produk/{produk}/prediksi-stok (role owner) → JSON prediksi.
     */
    public function show(Product $produk, StockPredictionService $stockPrediction): JsonResponse
    {
        $data = $stockPrediction->predict($produk->id);

        return response()->json($data);
    }
}
