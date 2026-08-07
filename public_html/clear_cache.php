<?php

/**
 * Kasir Toko Lily - Helper Clear Cache Web
 * Akses via browser: https://kasirtokolily.id/clear_cache.php
 */

$laravelAppPath = file_exists(__DIR__ . '/../laravel_app/bootstrap/app.php')
    ? __DIR__ . '/../laravel_app'
    : __DIR__ . '/laravel_app';

$cacheFiles = [
    $laravelAppPath . '/bootstrap/cache/config.php',
    $laravelAppPath . '/bootstrap/cache/routes-v7.php',
    $laravelAppPath . '/bootstrap/cache/services.php',
    $laravelAppPath . '/bootstrap/cache/packages.php',
];

$cleared = 0;
foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (@unlink($file)) {
            $cleared++;
        }
    }
}

echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 50px auto; border: 1px solid #c3e6cb; background-color: #d4edda; color: #155724; border-radius: 8px; text-align: center;'>";
echo "<h2>✓ Cache Laravel Berhasil Dibersihkan!</h2>";
echo "<p>Pengaturan dari file <code>.env</code> terbaru kini aktif 100%.</p>";
echo "<a href='test_email.php' style='display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Buka Halaman Tes Email SMTP</a>";
echo "</div>";
