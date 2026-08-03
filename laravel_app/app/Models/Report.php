<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'user_id',
        'type',
        'report_date',
        'data',
        'notes',
        'status',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public const TYPE_HARIAN = 'harian';
    public const TYPE_TRANSAKSI = 'transaksi';

    public const STATUS_TERKIRIM = 'terkirim';
    public const STATUS_DIBACA = 'dibaca';

    public static function typeList(): array
    {
        return [
            self::TYPE_HARIAN => 'Harian (ringkasan penjualan)',
            self::TYPE_TRANSAKSI => 'Per transaksi (detail transaksi hari itu)',
        ];
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_TERKIRIM => 'Terkirim',
            self::STATUS_DIBACA => 'Dibaca',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusList()[$this->status] ?? $this->status;
    }
}
