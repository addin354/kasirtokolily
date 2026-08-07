<?php

/**
 * Kasir Toko Lily - Helper Auto-Update File .env di Hosting
 * Akses via browser: https://kasirtokolily.id/update_env.php
 */

$laravelAppPath = file_exists(__DIR__ . '/../laravel_app/bootstrap/app.php')
    ? __DIR__ . '/../laravel_app'
    : __DIR__ . '/laravel_app';

$envPath = $laravelAppPath . '/.env';

$message = '';
$status = '';

$defaultEnvContent = <<<ENV
APP_NAME="Toko Lily Sembako"
APP_ENV=production
APP_KEY=base64:LHjIzpkdIec5yJuToPwFYdt/pVOD26v8z1UP7xzuw48=
APP_DEBUG=false
APP_URL=https://kasirtokolily.id

# Batas bawah (inklusif) stok "menipis" (kuning). Stok 0 = merah, di atas batas = hijau.
STOK_MENIPIS_BATAS=10
STOK_NOTIFIKASI_MIN=10
STOK_ALERT_EMAIL_ENABLED=true
STOK_ALERT_EMAIL_TO=addinhusnannadhari354@gmail.com
STOK_ALERT_WA_ENABLED=true
STOK_ALERT_WA_DRIVER=fonnte
STOK_ALERT_WA_FONNTE_TOKEN=yqA4bNzWcGtVA14Gfmso
STOK_ALERT_WA_FONNTE_URL=https://api.fonnte.com/send
STOK_ALERT_WA_OWNER_TO=62812xxxxxxxxxx
STOK_ALERT_WA_TO=62812xxxxxxxxxx
SALDO_AWAL_KAS=0

# Admin toko: true = boleh lihat laporan penjualan & laba. Owner selalu punya akses penuh.
ADMIN_TOKO_LIHAT_LAPORAN=true

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# -------------------------------------------------------------------
# DATABASE HOSTING
# -------------------------------------------------------------------
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u827349422_kasir_lily
DB_USERNAME=u827349422_kasir_user
DB_PASSWORD="@~;1zQD@iD6"

# -------------------------------------------------------------------
# DRIVERS (FILE & SYNC)
# -------------------------------------------------------------------
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

# -------------------------------------------------------------------
# SMTP GMAIL CONFIGURATION (PORT 465 SSL)
# -------------------------------------------------------------------
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=addinhusnannadhari354@gmail.com
MAIL_PASSWORD="ysyuuevnttvkfgba"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=addinhusnannadhari354@gmail.com
MAIL_FROM_NAME="Toko Lily Sembako"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="\${APP_NAME}"
ENV;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['env_content'])) {
    $newContent = $_POST['env_content'];
    if (file_put_contents($envPath, $newContent) !== false) {
        // Clear bootstrap config cache if exists
        $cacheFile = $laravelAppPath . '/bootstrap/cache/config.php';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
        $status = 'success';
        $message = '✓ File .env berhasil diperbarui dan disimpan!';
    } else {
        $status = 'error';
        $message = '✗ Gagal menyimpan file .env. Periksa izin folder/file di server.';
    }
}

$currentContent = file_exists($envPath) ? file_get_contents($envPath) : $defaultEnvContent;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto-Update .env - Kasir Toko Lily</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .card { max-width: 850px; margin: 20px auto; background: #fff; border-radius: 10px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h2 { color: #1e3a8a; margin-top: 0; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .alert-error { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        textarea { width: 100%; height: 450px; font-family: 'Courier New', monospace; font-size: 13px; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; background: #fdfdfd; line-height: 1.5; }
        .btn { background-color: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; display: inline-block; }
        .btn:hover { background-color: #1d4ed8; }
        .btn-test { background-color: #10b981; margin-left: 10px; text-decoration: none; display: inline-block; padding: 12px 20px; border-radius: 6px; color: white; font-weight: 600; font-size: 15px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>⚙️ Auto-Update File .env (Kasir Toko Lily)</h2>
        <p>Gunakan halaman ini untuk memperbarui pengaturan <code>.env</code> di hosting tanpa perlu membuka Hostinger File Manager.</p>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label style="font-weight: bold; display: block; margin-bottom: 8px;">Isi Konfigurasi .env:</label>
            <textarea name="env_content"><?php echo htmlspecialchars($currentContent); ?></textarea>
            <div style="margin-top: 15px;">
                <button type="submit" class="btn">💾 Simpan .env Otomatis</button>
                <a href="test_email.php" class="btn-test" target="_blank">✉️ Tes Kirim Email (SMTP)</a>
            </div>
        </form>
    </div>
</body>
</html>
