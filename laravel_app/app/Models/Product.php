<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public const JENIS_ECERAN = 'eceran';

    public const JENIS_GROSIR = 'grosir';

    public const JENIS_BAL = 'bal';

    protected $table = 'produks';

    protected $fillable = [
        'kategori_id',
        'satuan_id',
        'kode',
        'barcode',
        'nama',
        'deskripsi',
        'harga_beli',
        'harga_jual',
        'harga_grosir',
        'harga_bal',
        'isi_per_bal',
        'stok',
        'stok_minimum',
        'satuan',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'kategori_id');
    }

    public function satuanModel(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function detailTransaksis(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'produk_id');
    }

    public function stokMasuks(): HasMany
    {
        return $this->hasMany(StokMasuk::class, 'produk_id');
    }

    public function stockPredictions(): HasMany
    {
        return $this->hasMany(StockPrediction::class, 'produk_id');
    }

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'harga_grosir' => 'decimal:2',
            'harga_bal' => 'decimal:2',
            'stok' => 'decimal:3',
            'stok_minimum' => 'decimal:3',
            'isi_per_bal' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function jenisHargaList(): array
    {
        return [
            self::JENIS_ECERAN => 'Eceran',
            self::JENIS_GROSIR => 'Grosir',
            self::JENIS_BAL => 'Bal',
        ];
    }

    public static function labelJenisHarga(string $jenis): string
    {
        return self::jenisHargaList()[$jenis] ?? $jenis;
    }

    public function hargaUntukJenis(string $jenis): float
    {
        return match ($jenis) {
            self::JENIS_GROSIR => (float) $this->harga_grosir,
            self::JENIS_BAL => (float) $this->harga_bal,
            default => (float) $this->harga_jual,
        };
    }

    /** Banyak pcs per satuan input saat jenis harga Bal (minimal 1). */
    public function pcsPerSatuanBal(): int
    {
        return max(1, (int) ($this->isi_per_bal ?? 1));
    }

    /**
     * qty_input: pcs untuk ecer/grosir, jumlah bal untuk bal.
     *
     * @return float pcs yang harus dikurangi dari stok & dipakai laporan laba (kolom qty)
     */
    public function hitungQtyPcs(string $jenis, float|int|string $qtyInput): float
    {
        $qtyFloat = (float) str_replace(',', '.', (string) $qtyInput);
        if ($qtyFloat <= 0) {
            return 0.0;
        }

        return match ($jenis) {
            self::JENIS_BAL => $qtyFloat * $this->pcsPerSatuanBal(),
            default => $qtyFloat,
        };
    }

    public function stokStatus(): string
    {
        $q = (int) $this->stok;
        $min = (int) ($this->stok_minimum ?? 10);
        if ($q <= $min) {
            return 'restok';
        }

        return 'aman';
    }

    public function supplierTerakhir(): ?string
    {
        // Try from Pembelian details first
        $lastDetail = DetailPembelian::query()
            ->where('produk_id', $this->id)
            ->with('pembelian.supplier')
            ->latest('id')
            ->first();
        if ($lastDetail && $lastDetail->pembelian && $lastDetail->pembelian->supplier) {
            return $lastDetail->pembelian->supplier->nama_supplier;
        }

        // Fallback to StokMasuk
        $lastStokMasuk = StokMasuk::query()
            ->where('produk_id', $this->id)
            ->with('supplier')
            ->latest('id')
            ->first();
        if ($lastStokMasuk && $lastStokMasuk->supplier) {
            return $lastStokMasuk->supplier->nama_supplier;
        }

        return '—';
    }
}
