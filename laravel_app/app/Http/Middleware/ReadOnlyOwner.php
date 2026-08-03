<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memblokir semua request write (POST, PUT, PATCH, DELETE) untuk akun Owner.
 *
 * Owner hanya boleh READ ONLY — monitoring dan laporan saja.
 * Beberapa route seperti logout, update profile, dan approval retur dikecualikan.
 */
class ReadOnlyOwner
{
    private const WRITE_METHODS = ["POST", "PUT", "PATCH", "DELETE"];

    private const EXEMPT_ROUTES = [
        "logout",
        "profile.update",
        "profile.destroy",
        "laporan.retur.approve",
        "laporan.retur.reject",
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user &&
            $user->isOwner() &&
            in_array(strtoupper($request->method()), self::WRITE_METHODS, true)
        ) {
            // Cek apakah route termasuk yang dikecualikan
            $routeName = $request->route() ? $request->route()->getName() : null;
            if ($routeName && in_array($routeName, self::EXEMPT_ROUTES, true)) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    "message" => "Akun Owner tidak diizinkan melakukan perubahan data.",
                ], 403);
            }

            return redirect()
                ->back()
                ->with("error", "Akses ditolak: akun Owner hanya dapat melihat data (read only).");
        }

        return $next($request);
    }
}
