<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public const ROLE_ADMIN = 'admin';

    public const ROLE_KASIR = 'kasir';

    public const ROLE_OWNER = 'owner';

    public const ROLE_PELANGGAN = 'pelanggan';

    protected $fillable = [
        'name',
        'email',
        'no_hp',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isKasir(): bool
    {
        return $this->role === self::ROLE_KASIR;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isPelanggan(): bool
    {
        return $this->role === self::ROLE_PELANGGAN;
    }

    /**
     * Notifikasi database (morph) — memakai model app agar tipe jelas.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /** Laporan yang dikirim kasir (role kasir) ke owner. */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'user_id');
    }

    /** Nama route tujuan setelah login / logo. */
    public function defaultDashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_KASIR => 'kasir.index',
            self::ROLE_OWNER => 'dashboard',
            self::ROLE_ADMIN => 'products.index',
            self::ROLE_PELANGGAN => 'katalog.index',
            default => 'products.index',
        };
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin toko',
            self::ROLE_KASIR => 'Kasir',
            self::ROLE_OWNER => 'Owner',
            self::ROLE_PELANGGAN => 'Pelanggan',
            default => $this->role ?? '—',
        };
    }
}
