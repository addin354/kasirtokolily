<?php

/**
 * Kasir Toko Lily - Clean Dummy Owner Script
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

// Hapus akun dummy (owner@pos.test atau @example.com) dari tabel users agar tidak terjadi error duplikat
$deletedRows = DB::table('users')
    ->where('email', 'owner@pos.test')
    ->orWhere('email', 'like', '%@pos.test%')
    ->orWhere('email', 'like', '%@example.%')
    ->delete();

echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 50px auto; border: 1px solid #c3e6cb; background-color: #d4edda; color: #155724; border-radius: 8px; text-align: center;'>";
echo "<h2>✓ Akun Dummy Berhasil Dibersihkan!</h2>";
echo "<p>Akun dummy (<code>owner@pos.test</code>) di database telah dibersihkan sebanyak <strong>{$deletedRows} baris</strong>.</p>";
echo "<p>Email resmi Owner Anda (<code>addinhusnannadhari354@gmail.com</code>) kini sudah aktif sebagai satu-satunya penerima laporan resmi!</p>";
echo "</div>";
