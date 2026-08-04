<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dynamic Public Path detection for Shared Hosting
        if (file_exists(base_path('../public_html/build/manifest.json'))) {
            $this->app->usePublicPath(realpath(base_path('../public_html')));
        } elseif (file_exists(base_path('public_html/build/manifest.json'))) {
            $this->app->usePublicPath(realpath(base_path('public_html')));
        }

        Paginator::useBootstrapFive();

        Gate::define('manage-products', function ($user) {
            return in_array($user->role, ['admin', 'owner'], true);
        });

        Gate::define('view-reports', function ($user) {
            return in_array($user->role, ['kasir', 'owner', 'admin'], true);
        });

        /** Dashboard operasional (statistik) — hanya owner (bukan admin toko). */
        Gate::define('view-dashboard', function (User $user): bool {
            return $user->isOwner();
        });

        /** Laporan penjualan & laba — owner: selalu; admin toko: dari konfigurasi. */
        Gate::define('view-laporan-finansial', function (User $user): bool {
            if ($user->isOwner()) {
                return true;
            }
            if ($user->isAdmin()) {
                return (bool) config('pos.admin_toko.can_view_laporan_finansial', true);
            }

            return false;
        });

        /**
         * Write-data: apakah user boleh membuat / mengubah / menghapus data.
         * Owner bersifat READ ONLY — hanya monitoring & laporan.
         * Admin dan Kasir boleh write sesuai scope masing-masing.
         */
        Gate::define('write-data', function (User $user): bool {
            return ! $user->isOwner();
        });
    }
}
