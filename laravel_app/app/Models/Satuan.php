<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satuan extends Model
{
    protected $table = 'satuans';

    protected $fillable = [
        'nama',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'satuan_id');
    }
}
