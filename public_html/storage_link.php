<?php
/**
 * Helper Script: Create Storage Link for Shared Hosting (cPanel / Hostinger)
 * Jalankan file ini sekali via browser (contoh: https://kasirtokolily.id/storage_link.php)
 * setelah aplikasi di-upload ke hosting.
 */

$target = file_exists(__DIR__ . '/../laravel_app/storage/app/public')
    ? __DIR__ . '/../laravel_app/storage/app/public'
    : __DIR__ . '/laravel_app/storage/app/public';

$link = __DIR__ . '/storage';

if (file_exists($link)) {
    echo "<h3 style='color: green;'>✓ Symbolic link / folder 'storage' sudah ada di: " . htmlspecialchars($link) . "</h3>";
    exit;
}

if (!file_exists($target)) {
    echo "<h3 style='color: red;'>✗ Direktori target tidak ditemukan: " . htmlspecialchars($target) . "</h3>";
    exit;
}

if (@symlink($target, $link)) {
    echo "<h3 style='color: green;'>✓ Berhasil membuat symbolic link dari '$target' ke '$link'!</h3>";
    echo "<p>Sekarang file/gambar ter-upload dapat diakses oleh publik.</p>";
} else {
    echo "<h3 style='color: orange;'>⚠️ Gagal membuat symbolic link secara otomatis via PHP function.</h3>";
    echo "<p>Silakan buat symbolic link secara manual via Terminal cPanel/SSH: <code>ln -s " . htmlspecialchars($target) . " " . htmlspecialchars($link) . "</code></p>";
}
