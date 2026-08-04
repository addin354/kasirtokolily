<?php

/**
 * Kasir Toko Lily - Universal Root Index & Bootstrap Forwarder
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

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

// 2. Jika laravel_app ditemukan, jalankan Laravel secara langsung
if ($laravelAppPath) {
    // Cek mode maintenance
    if (file_exists($maintenance = $laravelAppPath . '/storage/framework/maintenance.php')) {
        require $maintenance;
    }

    // Cek autoloader composer (vendor)
    $autoloader = $laravelAppPath . '/vendor/autoload.php';
    if (!file_exists($autoloader)) {
        header("HTTP/1.1 500 Internal Server Error");
        echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 700px; margin: 50px auto; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>";
        echo "<h2 style='color: #d9534f;'>⚠️ Vendor Autoloader Belum Terinstall</h2>";
        echo "<p>Folder <code>vendor</code> belum ada di: <code>" . htmlspecialchars($laravelAppPath) . "</code></p>";
        echo "<hr style='border: none; border-top: 1px solid #eee; margin: 15px 0;'>";
        echo "<h4>Solusi Cepat:</h4>";
        echo "<ol>";
        echo "<li>Buka <strong>File Manager Hostinger</strong> -> masuk ke folder <code>laravel_app/</code>.</li>";
        echo "<li>Upload folder <code>vendor</code> dari komputer lokal Anda (atau ekstrak file <code>vendor.zip</code> bawaan ke folder <code>laravel_app/</code>).</li>";
        echo "<li>Atau jika menggunakan SSH Hostinger, jalankan: <code>cd laravel_app && composer install --no-dev</code></li>";
        echo "</ol>";
        echo "</div>";
        exit;
    }

    require $autoloader;

    /** @var Application $app */
    $app = require_once $laravelAppPath . '/bootstrap/app.php';
    $app->handleRequest(Request::capture());
    exit;
}

// 3. Fallback: Coba panggil file public_html/index.php
$possibleIndexPaths = [
    __DIR__ . '/public_html/index.php',
    __DIR__ . '/laravel_app/public/index.php',
    dirname(__DIR__) . '/public_html/index.php',
];

foreach ($possibleIndexPaths as $indexPath) {
    if (file_exists($indexPath)) {
        require_once $indexPath;
        exit;
    }
}

// 4. Tampilkan pesan debug jika direktori tidak ditemukan
header("HTTP/1.1 500 Internal Server Error");
echo "<h3>Error 500: Lokasi laravel_app / index.php tidak ditemukan di server.</h3>";
echo "<p><strong>Current Path (__DIR__):</strong> " . htmlspecialchars(__DIR__) . "</p>";
