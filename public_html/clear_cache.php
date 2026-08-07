<?php

/**
 * Kasir Toko Lily - Helper Clear Cache Web & Debug Mode Enabler
 * Akses via browser: https://kasirtokolily.id/clear_cache.php
 */

$laravelAppPath = file_exists(__DIR__ . '/../laravel_app/bootstrap/app.php')
    ? __DIR__ . '/../laravel_app'
    : __DIR__ . '/laravel_app';

$envFile = $laravelAppPath . '/.env';

// 1. Set APP_DEBUG=true di .env agar error terlihat detail
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (str_contains($envContent, 'APP_DEBUG=false')) {
        $envContent = str_replace('APP_DEBUG=false', 'APP_DEBUG=true', $envContent);
        @file_put_contents($envFile, $envContent);
    }
}

// 2. Hapus file cache di bootstrap/cache/
$cacheFiles = [
    $laravelAppPath . '/bootstrap/cache/config.php',
    $laravelAppPath . '/bootstrap/cache/routes-v7.php',
    $laravelAppPath . '/bootstrap/cache/services.php',
    $laravelAppPath . '/bootstrap/cache/packages.php',
];

$clearedFiles = 0;
foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (@unlink($file)) {
            $clearedFiles++;
        }
    }
}

// 3. Hapus compiled views
$viewDir = $laravelAppPath . '/storage/framework/views';
if (is_dir($viewDir)) {
    $files = glob($viewDir . '/*.php');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

// 4. Baca log error terakhir dari laravel.log
$logFile = $laravelAppPath . '/storage/logs/laravel.log';
$lastLogs = '';
if (file_exists($logFile)) {
    $lines = file($logFile);
    if ($lines !== false) {
        $lastLines = array_slice($lines, -80);
        $lastLogs = implode('', $lastLines);
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clear Cache & Log Inspector - Kasir Toko Lily</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; color: #333; }
        .card { max-width: 900px; margin: 20px auto; background: white; border-radius: 8px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .alert { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px; border-radius: 6px; font-weight: bold; margin-bottom: 20px; text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px; }
        .btn-success { background-color: #198754; }
        pre { background: #1e1e1e; color: #f8f8f2; padding: 15px; border-radius: 6px; overflow: auto; max-height: 450px; font-size: 12px; line-height: 1.4; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="card">
        <div class="alert">
            ✓ Cache & Views Berhasil Dibersihkan! (Debug Mode = TRUE Aktif)
        </div>
        
        <h3>🚀 Klik Tombol Dibawah untuk Tes Kembali:</h3>
        <p>
            <a href="/login" class="btn">🔑 Buka Halaman Login (Tampilkan Detail Error)</a>
            <a href="test_email.php" class="btn btn-success">✉️ Tes Kirim Email (SMTP)</a>
        </p>

        <?php if (!empty($lastLogs)): ?>
            <h4 style="margin-top: 30px; color: #b02a37;">📋 Log Error Terakhir di Server (storage/logs/laravel.log):</h4>
            <pre><?php echo htmlspecialchars($lastLogs); ?></pre>
        <?php else: ?>
            <p style="color: #666; font-style: italic;">Tidak ada catatan error di laravel.log.</p>
        <?php endif; ?>
    </div>
</body>
</html>
