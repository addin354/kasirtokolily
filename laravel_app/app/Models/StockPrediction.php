<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPrediction extends Model
{
    protected $table = 'stock_predictions';

    protected $fillable = [
        'produk_id',
        'periode_mulai',
        'periode_akhir',
        'prediksi_stok',
        'metode',
        'confidence',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date',
            'periode_akhir' => 'date',
            'prediksi_stok' => 'integer',
            'confidence' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function produk(): BelongsTo
    {
        return $this->product();
    }
}
