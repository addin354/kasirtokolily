<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    protected $table = 'pengeluarans';

    protected $fillable = [
        'nomor_pengeluaran',
        'tanggal',
        'kategori',
        'keterangan',
        'nominal',
        'user_id',
        'metode_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    public static function categories(): array
    {
        return [
            'Listrik',
            'Air',
            'Internet',
            'Gaji',
            'BBM',
            'Transportasi',
            'Konsumsi',
            'ATK',
            'Perawatan',
            'Pajak',
            'Administrasi',
            'Lain-lain',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
