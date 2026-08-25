<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Naskah & Panduan Demo Aplikasi - Sidang Skripsi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; line-height: 1.4; margin: 15px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; border-bottom: 2px solid #0d6efd; padding-bottom: 5px; }
        .header-table td { border: none; padding: 0; }
        .shop-name { font-size: 16px; font-weight: bold; color: #0d6efd; }
        .shop-address { font-size: 8.5px; color: #555; }
        .report-title { font-size: 12px; font-weight: bold; text-align: right; color: #111; text-transform: uppercase; }
        .report-subtitle { font-size: 8.5px; text-align: right; color: #555; margin-top: 2px; }
        
        h2 { font-size: 11px; color: #0d6efd; border-bottom: 1px solid #0d6efd; padding-bottom: 3px; margin-top: 15px; margin-bottom: 8px; text-transform: uppercase; }
        h3 { font-size: 10px; color: #111; margin-top: 10px; margin-bottom: 4px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ccc; padding: 5px; text-align: left; font-size: 9px; }
        table.data-table th { background: #e9ecef; font-weight: bold; color: #111; }
        
        .box-quote { background: #f8f9fa; border-left: 3px solid #0d6efd; padding: 8px; margin: 6px 0; font-style: italic; font-size: 9.5px; color: #333; }
        .box-qa { background: #f1f3f5; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px; margin-bottom: 8px; }
        .qa-q { font-weight: bold; color: #d63384; margin-bottom: 3px; font-size: 9.5px; }
        .qa-a { color: #212529; font-size: 9px; }
        
        .badge { display: inline-block; padding: 2px 5px; background: #0d6efd; color: #fff; font-size: 8px; font-weight: bold; border-radius: 2px; }
        .page-break { page-break-after: always; }
        .footer { font-size: 8px; color: #777; text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="shop-name">TOKO LILY SEMBAKO</div>
                <div class="shop-address">Sistem Informasi Kasir & Manajemen Persediaan Berbasis Web | URL: https://kasirtokolily.id</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="report-title">Naskah & Panduan Demo Sidang Skripsi</div>
                <div class="report-subtitle">Tanggal: {{ now()->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Table Duration -->
    <h2>1. Rencana Alur & Durasi Demo (Total: 7 - 10 Menit)</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Tahapan Demo</th>
                <th style="width: 15%;">Durasi</th>
                <th style="width: 45%;">Highlight Fitur Utama</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><b>Pembukaan & Intro</b></td>
                <td>1 Menit</td>
                <td>Pengenalan judul skripsi & penegasan sistem ter-deploy live di domain kasirtokolily.id</td>
            </tr>
            <tr>
                <td>2</td>
                <td><b>Kasir & Barcode Camera</b></td>
                <td>2.5 Menit</td>
                <td>Scan Barcode 1D Kamera (Zoom 1.35x + Red Laser Overlay), Multi Harga, Payment Cash/Transfer/QRIS, Cetak Struk</td>
            </tr>
            <tr>
                <td>3</td>
                <td><b>Smart Reorder Recommendation</b></td>
                <td>2 Menit</td>
                <td>Rekomendasi Jumlah Order Restok cerdas (Sales Velocity 30 Hari) + 1-Click Tambah Pembelian</td>
            </tr>
            <tr>
                <td>4</td>
                <td><b>Inventory & Stock Opname</b></td>
                <td>1.5 Menit</td>
                <td>Stock Opname audit fisik, Penyesuaian Stok (Tambah/Kurang), dan Audit Log perubahan stok</td>
            </tr>
            <tr>
                <td>5</td>
                <td><b>Finansial & Laba Rugi</b></td>
                <td>2 Menit</td>
                <td>Laba Kotor, Laba Bersih 100% Hijau (Untung), Saldo Kas Toko & Eksport PDF/Excel Laporan Finansial</td>
            </tr>
            <tr>
                <td>6</td>
                <td><b>Penutup</b></td>
                <td>1 Menit</td>
                <td>Kesimpulan & Kesiapan Tanya Jawab Dosen Penguji</td>
            </tr>
        </tbody>
    </table>

    <!-- Step by Step Script -->
    <h2>2. Naskah Presentasi Step-by-Step</h2>

    <h3>TAHAP 1: PEMBUKAAN (1 Menit)</h3>
    <div class="box-quote">
        "Assalamu’alaikum Warahmatullahi Wabarakatuh, Yang terhormat Bapak/Ibu Dosen Penguji dan Dosen Pembimbing.<br>
        Perkenankan saya <b>[Nama Anda]</b> untuk mendemokan aplikasi <b>Sistem Informasi Kasir & Manajemen Toko Lily Sembako</b> yang berjalan secara live pada domain <code>kasirtokolily.id</code>.<br>
        Sistem ini dirancang untuk mengatasi kendala pencatatan manual, efisiensi kasir, akurasi stok barang, rekomendasi pembelian barang, hingga kalkulasi laba bersih secara otomatis."
    </div>

    <h3>TAHAP 2: SKENARIO KASIR & SCAN BARCODE KAMERA (2.5 Menit)</h3>
    <div class="box-quote">
        "Pertama, saya mendemokan antarmuka Kasir (Point of Sale). Halaman ini dirancang cepat, responsif, dan mendukung pemindaian barcode barang menggunakan kamera bawaan HP/Laptop tanpa perlu hardware scanner tambahan. Kamera dilengkapi fitur zoom 1.35x, panduan garis merah laser, dan filter khusus kode barcode 1D. Kasir dapat memilih tingkat harga eceran/grosir/partai, serta memilih metode pembayaran Cash, Transfer Bank, atau QRIS."
    </div>

    <h3>TAHAP 3: SMART REORDER RECOMMENDATION (2 Menit)</h3>
    <div class="box-quote">
        "Selanjutnya adalah fitur unggulan skripsi ini: <b>Smart Reorder Recommendation</b>. Sistem menganalisis laju penjualan harian (Sales Velocity) 30 hari terakhir dan secara otomatis menghitung berapa jumlah pasti barang yang harus di-order ke supplier agar toko terhindar dari kondisi kehabisan stok maupun kelebihan stok."
    </div>

    <div class="page-break"></div>

    <h3>TAHAP 4: INVENTORY & STOCK OPNAME (1.5 Menit)</h3>
    <div class="box-quote">
        "Pada modul Inventory & Tata Kelola Persediaan, sistem menyediakan kontrol penuh terhadap audit stok fisik (Stock Opname) serta penyesuaian barang hilang/rusak (Stock Adjustment) lengkap dengan audit log perubahannya."
    </div>

    <h3>TAHAP 5: FINANSIAL, LABA RUGI & SALDO KAS (2 Menit)</h3>
    <div class="box-quote">
        "Modul penting bagi Pemilik Toko (Owner) adalah Laporan Laba Rugi Real-Time. Sistem secara otomatis mengkalkulasi Pendapatan Penjualan, Harga Pokok Penjualan (HPP), Laba Kotor, Pengeluaran Toko, hingga Laba Bersih dan Saldo Kas Toko, serta menyediakan opsi cetak PDF/Excel."
    </div>

    <h3>TAHAP 6: PENUTUP (1 Menit)</h3>
    <div class="box-quote">
        "Demikian demonstrasi Sistem Informasi Kasir & Manajemen Toko Lily Sembako. Aplikasi ini telah lulus 73 unit automated testing dengan 311 assertion. Terima kasih atas perhatian Bapak/Ibu Dosen Penguji, waktu dan tempat saya persilakan untuk sesi tanya jawab."
    </div>

    <!-- Q&A Preparation -->
    <h2>3. Jawaban Kunci Potensi Pertanyaan Dosen Penguji (Q&A)</h2>

    <div class="box-qa">
        <div class="qa-q">Q1: Bagaimana cara sistem menghitung Rekomendasi Order Restok Barang?</div>
        <div class="qa-a">
            <b>Jawaban:</b> Sistem menggunakan kalkulasi Sales Velocity 30 Hari:<br>
            • Rata-rata Harian = Total Terjual 30 Hari / 30<br>
            • Proyeksi Kebutuhan = Rata-rata Harian x 14 Hari Buffer<br>
            • Rekomendasi Order = MAX(Proyeksi Kebutuhan, Stok Minimum x 2) - Stok Saat Ini.
        </div>
    </div>

    <div class="box-qa">
        <div class="qa-q">Q2: Bagaimana sistem menjaga akurasi Laba Bersih jika harga modal berubah?</div>
        <div class="qa-a">
            <b>Jawaban:</b> Sistem menggunakan HPP berbasis transaksi. Saat Nota Pembelian baru dibuat, `harga_beli` produk ter-update. Saat transaksi kasir terjadi, HPP dihitung langsung berdasarkan modal produk yang berlaku saat itu, sehingga Laba Kotor (Omzet - HPP) dan Laba Bersih (Laba Kotor - Pengeluaran) selalu presisi.
        </div>
    </div>

    <div class="box-qa">
        <div class="qa-q">Q3: Bagaimana pemisahan Hak Akses (RBAC / Security) antar role?</div>
        <div class="qa-a">
            <b>Jawaban:</b> Menggunakan Role-Based Access Control (RBAC) via Laravel Policy & Gate. Role Kasir hanya mengakses POS & Retur Kasir. Role Admin mengelola barang & pembelian. Role Owner menguasai Laporan Finansial, Laba Rugi, dan Persetujuan Kasir.
        </div>
    </div>

    <div class="box-qa">
        <div class="qa-q">Q4: Spesifikasi Teknologi Aplikasi</div>
        <div class="qa-a">
            • Backend: PHP 8.2 & Framework Laravel 11<br>
            • Database: MySQL / MariaDB<br>
            • Frontend: Blade Template, Vanilla CSS Modern Design, Bootstrap 5, Chart.js<br>
            • Testing: PHPUnit (73 Automated Feature Tests PASS)
        </div>
    </div>

    <div class="footer">
        Naskah & Panduan Demo Sidang Skripsi - Toko Lily Sembako | Live System: https://kasirtokolily.id
    </div>

</body>
</html>
