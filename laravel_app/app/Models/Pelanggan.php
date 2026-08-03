<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    protected $table = 'pelanggans';

    protected $fillable = [
        'nama',
    ];

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'pelanggan_id');
    }
}
