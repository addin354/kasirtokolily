<?php

/**
 * Kasir Toko Lily - Fix Owner Email Script
 * Akses via browser: https://kasirtokolily.id/fix_owner_email.php
 */

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

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

$newOwnerEmail = 'addinhusnannadhari354@gmail.com';

$updatedRows = DB::table('users')
    ->where('role', 'owner')
    ->orWhere('email', 'like', '%@pos.test%')
    ->orWhere('email', 'like', '%@example.%')
    ->update(['email' => $newOwnerEmail]);

echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 50px auto; border: 1px solid #c3e6cb; background-color: #d4edda; color: #155724; border-radius: 8px; text-align: center;'>";
echo "<h2>✓ Email Owner Database Berhasil Diperbarui!</h2>";
echo "<p>Semua email dummy (seperti <code>owner@pos.test</code>) di tabel <code>users</code> telah diganti menjadi: <strong>{$newOwnerEmail}</strong>.</p>";
echo "<p>Pesan error kembalian <em>Alamat tidak dapat ditemukan</em> tidak akan muncul lagi secara permanen!</p>";
echo "</div>";
