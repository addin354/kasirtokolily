<?php

// Aktifkan laporan error PHP untuk melihat penyebab 500 error di browser
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

try {
    $laravelAppPath = file_exists(__DIR__ . '/laravel_app/bootstrap/app.php')
        ? __DIR__ . '/laravel_app'
        : __DIR__ . '/../laravel_app';

    if (!file_exists($laravelAppPath . '/bootstrap/app.php')) {
        throw new \Exception("Folder laravel_app tidak ditemukan dari public_html. Path saat ini: " . __DIR__);
    }

    if (file_exists($maintenance = $laravelAppPath . '/storage/framework/maintenance.php')) {
        require $maintenance;
    }

    $autoloader = $laravelAppPath . '/vendor/autoload.php';
    if (!file_exists($autoloader)) {
        die("<h3 style='color:red;'>Folder vendor belum ada di " . htmlspecialchars($laravelAppPath) . ". Silakan upload folder vendor.</h3>");
    }

    require $autoloader;

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once $laravelAppPath . '/bootstrap/app.php';
    $app->handleRequest(\Illuminate\Http\Request::capture());

} catch (\Throwable $e) {
    echo "<div style='font-family: monospace; padding: 20px; background: #fff3f3; color: #b71c1c; border: 1px solid #ffcdd2; margin: 20px; border-radius: 6px;'>";
    echo "<h2 style='margin-top: 0;'>⚠️ Error Detail (public_html)</h2>";
    echo "<p><strong>Pesan Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Baris: " . $e->getLine() . ")</p>";
    echo "<pre style='white-space: pre-wrap; background: #fff; padding: 10px; border: 1px solid #e0e0e0; max-height: 400px; overflow: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}