<?php

return [

    /*
    | Batas bawah stok "menipis" (kuning): stok > 0 dan stok <= nilai ini.
    | Stok 0 = habis (merah). Stok > nilai ini = aman (hijau).
    */
    'stok_menipis_batas' => max(0, (int) env('STOK_MENIPIS_BATAS', 10)),

    /*
    | Notifikasi stok (Command check:stock + scheduler).
    | Peringatan bila stok < stok_notifikasi_min (produk aktif saja).
    | Email: jika to kosong, alamat diambil dari user role owner.
    | WhatsApp:
    | - simulated: append ke file log
    | - fonnte: kirim WA asli via API Fonnte
    | wa_owner_to: prioritas penerima owner (comma-separated), fallback ke wa_to.
    | wa_template: bisa pakai placeholder:
    | {app_name}, {datetime}, {min_stok}, {count_produk}, {produk_lines}, {dashboard_url}
    */
    'stock_notification' => [
        'min_stok' => max(0, (int) env('STOK_NOTIFIKASI_MIN', 10)),
        'email_enabled' => filter_var(
            env('STOK_ALERT_EMAIL_ENABLED', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) ?? true,
        'email_to' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('STOK_ALERT_EMAIL_TO', ''))
        ))),
        'wa_enabled' => filter_var(
            env('STOK_ALERT_WA_ENABLED', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) ?? true,
        'wa_driver' => env('STOK_ALERT_WA_DRIVER', 'simulated'),
        'wa_fonnte_token' => env('STOK_ALERT_WA_FONNTE_TOKEN', ''),
        'wa_fonnte_url' => env('STOK_ALERT_WA_FONNTE_URL', 'https://api.fonnte.com/send'),
        'wa_owner_to' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('STOK_ALERT_WA_OWNER_TO', ''))
        ))),
        'wa_to' => env('STOK_ALERT_WA_TO', '62812xxxxxxxxxx'),
        'wa_template' => (string) env(
            'STOK_ALERT_WA_TEMPLATE',
            "*{app_name}*%0A".
            "Peringatan stok menipis%0A".
            "Waktu: {datetime}%0A".
            "Batas minimum: {min_stok}%0A".
            "Jumlah produk: {count_produk}%0A%0A".
            "{produk_lines}%0A%0A".
            "Dashboard: {dashboard_url}"
        ),
    ],

    /*
    | Prediksi stok (rata-rata penjualan = total terjual ÷ hari jendela).
    | window_days: hari ke belakang untuk agregat penjualan.
    | cover_days: berapa hari stok "aman" dihitung saat rekomendasi restock.
    */
    'stock_prediction' => [
        'window_days' => max(1, (int) env('STOCK_PREDICTION_WINDOW_DAYS', 30)),
        'cover_days' => max(1, (int) env('STOCK_PREDICTION_COVER_DAYS', 14)),
    ],

    /*
    | Admin toko: boleh melihat laporan penjualan & laba rugi (bukan hanya produk).
    | Owner selalu punya akses. Set false untuk membatasi admin toko hanya ke master data.
    */
    'admin_toko' => [
        'can_view_laporan_finansial' => filter_var(
            env('ADMIN_TOKO_LIHAT_LAPORAN', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) ?? true,
    ],

    /*
    | Saldo awal kas toko.
    */
    'saldo_awal_kas' => floatval(env('SALDO_AWAL_KAS', 0)),

];
