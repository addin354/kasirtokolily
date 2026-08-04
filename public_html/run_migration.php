<?php
/**
 * Helper Script: Run Database Migrations on Hosting safely.
 * Memperbarui struktur database ke versi terbaru tanpa menghapus data produk lama.
 * Akses via browser: https://kasirtokolily.id/run_migration.php
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

$laravelAppPath = file_exists(__DIR__ . '/../laravel_app/bootstrap/app.php')
    ? __DIR__ . '/../laravel_app'
    : __DIR__ . '/laravel_app';

if (!file_exists($laravelAppPath . '/bootstrap/app.php')) {
    die("<h3>Error: Direktori laravel_app tidak ditemukan.</h3>");
}

require $laravelAppPath . '/vendor/autoload.php';
/** @var Application $app */
$app = require_once $laravelAppPath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto;'>";
echo "<h2>🔄 Kasir Toko Lily - Update Database Schema</h2>";

try {
    // Run artisan migrate --force
    $output = Artisan::call('migrate', ['--force' => true]);
    $resultText = Artisan::output();

    echo "<h3 style='color: green;'>✓ Database Berhasil Diperbarui!</h3>";
    echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px; overflow: auto;'>" . htmlspecialchars($resultText) . "</pre>";
    echo "<p>Data produk lama Anda aman dan struktur tabel telah disesuaikan dengan versi terbaru.</p>";
    echo "<p><strong style='color: red;'>PENTING:</strong> Hapus file <code>run_migration.php</code> dari server demi keamanan setelah selesai.</p>";
} catch (\Exception $e) {
    echo "<h3 style='color: red;'>✗ Gagal memperbarui database:</h3>";
    echo "<pre style='background: #fee; padding: 15px; border-radius: 5px; color: #900;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

echo "</div>";
