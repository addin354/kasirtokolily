<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembelian extends Model
{
    protected $table = 'pembelians';

    protected $fillable = [
        'nomor_pembelian',
        'supplier_id',
        'tanggal',
        'total',
        'keterangan',
        'user_id',
        'metode_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailPembelians(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'pembelian_id');
    }
}
