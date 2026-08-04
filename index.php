<?php

/**
 * Kasir Toko Lily - Root Index Forwarder
 * Memastikan web server (Hostinger / cPanel / LiteSpeed / Apache)
 * dapat langsung menjalankan aplikasi tanpa error 403 Forbidden.
 */

if (file_exists(__DIR__ . '/public_html/index.php')) {
    require_once __DIR__ . '/public_html/index.php';
} elseif (file_exists(__DIR__ . '/laravel_app/public/index.php')) {
    require_once __DIR__ . '/laravel_app/public/index.php';
} else {
    header("HTTP/1.1 500 Internal Server Error");
    echo "<h3>Error: File index.php tidak ditemukan di public_html maupun laravel_app/public.</h3>";
}
