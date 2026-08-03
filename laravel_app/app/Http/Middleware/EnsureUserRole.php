<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses berdasarkan kolom `users.role`.
 *
 * Pendaftaran di `bootstrap/app.php`:
 *   'role' => \App\Http\Middleware\EnsureUserRole::class
 *
 * Contoh rute (lihat `routes/web.php`):
 *   Route::middleware('role:owner')->group(...);
 *   Route::middleware('role:admin,owner')->group(...);
 *   Route::middleware('role:pelanggan')->group(...);
 *
 * Peran (nilai `role`):
 * - owner: akses penuh (dashboard, pengguna, stok, kasir, laporan, dsb.)
 * - admin: kelola katalog (produk/kategori/satuan) + laporan sesuai gate config
 * - kasir: kasir + laporan ke owner
 * - pelanggan: hanya katalog produk baca (route `katalog.index`) + profil
 *
 * Bila role tidak diizinkan, pengguna autentik diarahkan ke `defaultDashboardRoute()`.
 */
class EnsureUserRole
{
    /**
     * Parameter setelah `role:` dipisah koma, contoh:
     * middleware('role:admin,kasir') → $roles = ['admin', 'kasir'].
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        if (! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                abort(403, 'Anda tidak punya akses ke halaman ini.');
            }

            return redirect()
                ->route($user->defaultDashboardRoute())
                ->with('error', 'Akses ditolak untuk halaman tersebut. Anda diarahkan ke menu sesuai role.');
        }

        return $next($request);
    }
}
