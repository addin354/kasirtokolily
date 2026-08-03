<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokLog extends Model
{
    protected $table = 'stok_logs';

    protected $fillable = [
        'produk_id',
        'tanggal',
        'jenis', // 'Pembelian', 'Penjualan', 'Retur', 'Stock Opname', 'Penyesuaian'
        'masuk',
        'keluar',
        'saldo',
        'referensi',
        'user_id',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'masuk' => 'integer',
            'keluar' => 'integer',
            'saldo' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper to log stock change.
     */
    public static function logChange(int $produkId, string $jenis, int $masuk, int $keluar, ?string $referensi = null, ?int $userId = null, ?string $keterangan = null): void
    {
        $product = Product::find($produkId);
        if (!$product) return;

        self::create([
            'produk_id' => $produkId,
            'tanggal' => now(),
            'jenis' => $jenis,
            'masuk' => $masuk,
            'keluar' => $keluar,
            'saldo' => (int) $product->stok,
            'referensi' => $referensi,
            'user_id' => $userId ?? auth()->id(),
            'keterangan' => $keterangan,
        ]);
    }
}
