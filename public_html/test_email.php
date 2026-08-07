<?php

/**
 * Kasir Toko Lily - Helper Test Pengiriman Email (SMTP Test)
 * Akses via browser: https://kasirtokolily.id/test_email.php
 */

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Mail;

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

echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 30px auto; border: 1px solid #ddd; border-radius: 8px;'>";
echo "<h2>✉️ Kasir Toko Lily - Uji Coba Pengiriman Email (SMTP Test)</h2>";

$mailer = config('mail.default');
$host = config('mail.mailers.smtp.host');
$port = config('mail.mailers.smtp.port');
$username = config('mail.mailers.smtp.username');
$encryption = config('mail.mailers.smtp.encryption');
$fromAddress = config('mail.from.address');
$targetEmail = env('STOK_ALERT_EMAIL_TO', 'addinhusnannadhari354@gmail.com');

echo "<table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;'>";
echo "<tr style='border-bottom: 1px solid #eee;'><td style='padding: 6px; font-weight: bold;'>Mailer Driver:</td><td>{$mailer}</td></tr>";
echo "<tr style='border-bottom: 1px solid #eee;'><td style='padding: 6px; font-weight: bold;'>SMTP Host:</td><td>{$host}</td></tr>";
echo "<tr style='border-bottom: 1px solid #eee;'><td style='padding: 6px; font-weight: bold;'>SMTP Port:</td><td>{$port}</td></tr>";
echo "<tr style='border-bottom: 1px solid #eee;'><td style='padding: 6px; font-weight: bold;'>SMTP Encryption:</td><td>{$encryption}</td></tr>";
echo "<tr style='border-bottom: 1px solid #eee;'><td style='padding: 6px; font-weight: bold;'>SMTP Username:</td><td>{$username}</td></tr>";
echo "<tr style='border-bottom: 1px solid #eee;'><td style='padding: 6px; font-weight: bold;'>Sender (From):</td><td>{$fromAddress}</td></tr>";
echo "<tr style='border-bottom: 1px solid #eee;'><td style='padding: 6px; font-weight: bold;'>Penerima Tes:</td><td>{$targetEmail}</td></tr>";
echo "</table>";

try {
    Mail::raw("Halo! Ini adalah email tes otomatis dari Kasir Toko Lily pada " . date('d M Y H:i:s') . " WIB.", function ($message) use ($targetEmail) {
        $message->to($targetEmail)
            ->subject("Uji Coba Pengiriman Email Kasir Toko Lily (" . date('H:i') . ")");
    });

    echo "<h3 style='color: green; background: #e8f8ed; padding: 12px; border-radius: 6px;'>✓ SUKSES: Email tes berhasil terkirim ke {$targetEmail}!</h3>";
    echo "<p>Silakan periksa Inbox atau folder Spam Gmail Anda.</p>";
} catch (\Throwable $e) {
    echo "<h3 style='color: red; background: #fde8e8; padding: 12px; border-radius: 6px;'>✗ GAGAL: Terjadi kesalahan saat mengirim email.</h3>";
    echo "<p><strong>Detail Error SMTP:</strong></p>";
    echo "<pre style='background: #333; color: #fff; padding: 15px; border-radius: 6px; overflow: auto; white-space: pre-wrap;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<h4>Saran Perbaikan:</h4>";
    echo "<ul>";
    echo "<li>Pastikan Google App Password (Sandi Aplikasi) diisi **TANPA SPASI**: <code>ysyuuevnttvkfgba</code></li>";
    echo "<li>Jika menggunakan Port 587 TLS gagal di Hostinger, coba gunakan Port 465 SSL di file <code>.env</code>:</li>";
    echo "<pre style='background: #f4f4f4; padding: 10px;'>MAIL_PORT=465\nMAIL_ENCRYPTION=ssl</pre>";
    echo "</ul>";
}

echo "</div>";
