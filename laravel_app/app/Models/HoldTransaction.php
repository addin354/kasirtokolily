<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoldTransaction extends Model
{
    protected $table = 'hold_transactions';

    protected $fillable = [
        'code',
        'kasir_id',
        'pelanggan',
        'catatan',
        'cart_data',
        'total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cart_data' => 'array',
            'total' => 'decimal:2',
        ];
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    /**
     * Generate next hold transaction code, e.g., H001, H002, etc.
     */
    public static function generateCode(): string
    {
        $last = self::query()->orderBy('id', 'desc')->first();
        if (!$last) {
            return 'H001';
        }
        $lastNum = (int) substr($last->code, 1);
        $nextNum = $lastNum + 1;
        return 'H' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
}
