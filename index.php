<?php

/**
 * Kasir Toko Lily - Universal Root Index & Bootstrap Forwarder (With Active Debug Catch)
 */

// Aktifkan laporan error PHP untuk melihat penyebab 500 error di browser
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

try {
    // 1. Cari lokasi folder laravel_app
    $laravelAppPath = null;
    $possibleAppPaths = [
        __DIR__ . '/laravel_app',
        __DIR__ . '/../laravel_app',
        dirname(__DIR__) . '/laravel_app',
    ];

    foreach ($possibleAppPaths as $path) {
        if (file_exists($path . '/bootstrap/app.php')) {
            $laravelAppPath = realpath($path);
            break;
        }
    }

    if (!$laravelAppPath) {
        throw new \Exception("Folder laravel_app tidak ditemukan di server. Path saat ini: " . __DIR__);
    }

    // 2. Cek mode maintenance
    if (file_exists($maintenance = $laravelAppPath . '/storage/framework/maintenance.php')) {
        require $maintenance;
    }

    // 3. Cek autoloader composer (vendor)
    $autoloader = $laravelAppPath . '/vendor/autoload.php';
    if (!file_exists($autoloader)) {
        echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 700px; margin: 50px auto; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); background: #ffffff;'>";
        echo "<h2 style='color: #d9534f;'>⚠️ Vendor Autoloader Belum Terinstall</h2>";
        echo "<p>Folder <code>vendor</code> belum ada di: <code>" . htmlspecialchars($laravelAppPath) . "</code></p>";
        echo "<hr style='border: none; border-top: 1px solid #eee; margin: 15px 0;'>";
        echo "<h4>Solusi Cepat:</h4>";
        echo "<ol style='line-height: 1.6;'>";
        echo "<li>Buka <strong>File Manager Hostinger</strong> -> masuk ke folder <code>laravel_app/</code>.</li>";
        echo "<li>Upload folder <code>vendor</code> dari komputer lokal Anda (atau ekstrak file <code>vendor.zip</code> bawaan ke folder <code>laravel_app/</code>).</li>";
        echo "<li>Atau jika menggunakan SSH Hostinger, jalankan: <code>cd laravel_app && composer install --no-dev</code></li>";
        echo "</ol>";
        echo "</div>";
        exit;
    }

    require $autoloader;

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once $laravelAppPath . '/bootstrap/app.php';
    $app->handleRequest(\Illuminate\Http\Request::capture());

} catch (\Throwable $e) {
    echo "<div style='font-family: monospace; padding: 20px; background: #fff3f3; color: #b71c1c; border: 1px solid #ffcdd2; margin: 20px; border-radius: 6px;'>";
    echo "<h2 style='margin-top: 0;'>⚠️ Error Detail (Deployment Debug Mode)</h2>";
    echo "<p><strong>Pesan Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Baris: " . $e->getLine() . ")</p>";
    echo "<pre style='white-space: pre-wrap; background: #fff; padding: 10px; border: 1px solid #e0e0e0; max-height: 400px; overflow: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
