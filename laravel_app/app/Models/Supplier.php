<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'nama_supplier',
        'alamat',
        'no_hp',
    ];

    public function stokMasuks(): HasMany
    {
        return $this->hasMany(StokMasuk::class, 'supplier_id');
    }
}
