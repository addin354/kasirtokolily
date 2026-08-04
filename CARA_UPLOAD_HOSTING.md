# 🚀 Panduan Lengkap Deploy Aplikasi Kasir Toko Lily ke Hosting

Dokumen ini berisi petunjuk langkah demi langkah untuk meng-upload dan mengonfigurasi aplikasi **Kasir Toko Lily** ke web hosting (**Hostinger via GitHub / Git Deployment**, cPanel, VPS, dll.).

---

## 🛑 Solusi Error 403 Forbidden (Deploy via GitHub)

Jika Anda mendeploy proyek ini menggunakan **Git / GitHub di Hostinger** dan menemui tampilan **403 Forbidden**, hal itu terjadi karena:
1. File `index.php` dan `.htaccess` di root proyek belum di-push ke GitHub repository Anda.
2. Web server (LiteSpeed/Apache) di Hostinger tidak menemukan file `index.php` utama di direktori root repository.
3. Folder `laravel_app/vendor` (Composer dependencies) belum terinstall di server.

### 📌 Langkah Penyelesaian Cepat:

#### 1. Push File Terbaru ke GitHub
Di VS Code / Terminal lokal Anda, jalankan perintah berikut untuk mengunggah file `index.php` dan `.htaccess` root ke GitHub:
```bash
git add .
git commit -m "Fix root index.php and htaccess for hosting deployment"
git push origin main
```

#### 2. Lakukan Re-Deploy / Auto Deploy di Hostinger
- Buka **Hostinger hPanel** -> **Git**.
- Klik tombol **Deploy** (atau tarik update dari branch `main`).

#### 3. Install Composer Dependencies di Hostinger (`vendor`)
Karena folder `vendor` di-ignore oleh Git demi keamanan, di Hostinger Anda perlu memastikan folder `vendor` tersedia dengan salah satu cara berikut:
- **Cara A (Jika ada SSH / Terminal Hostinger):**
  Masuk ke terminal Hostinger, lalu jalankan:
  ```bash
  cd public_html/laravel_app
  composer install --no-dev --optimize-autoloader
  ```
- **Cara B (Post-Deployment Command di Hostinger Git):**
  Di menu Git Deployment Hostinger, masukkan *Build / Post-Deployment Command*:
  `cd laravel_app && composer install --no-dev --optimize-autoloader`
- **Cara C (Upload Manual Folder Vendor):**
  Upload folder `laravel_app/vendor` (atau ekstrak `vendor.zip`) langsung ke `laravel_app/vendor` di File Manager Hostinger.

---

## 📁 Struktur Folder Siap Hosting

Aplikasi ini sudah dipisahkan menjadi dua bagian utama yang aman untuk Shared Hosting:
- `index.php` (di root) : Mengarahkan request server ke `public_html/index.php`.
- `.htaccess` (di root) : Mengatur `DirectoryIndex index.php` dan aturan rewrite URL.
- `laravel_app/` : Berisi seluruh kode sumber utama Laravel (diletakkan **di luar** folder publik demi keamanan).
- `public_html/` : Berisi file publik web (`index.php`, `.htaccess`, `build/`, `logo.png`, `storage_link.php`).
- `database_export.sql` : File export database MySQL/MariaDB yang siap di-import.

---

## 🗄️ Langkah Import Database di Hosting

1. Di cPanel / Hostinger hPanel, buka menu **MySQL Databases** (atau **Kelola Database**).
2. Buat database baru (contoh: `u827349422_kasir_lily`).
3. Buat user database baru dan tentukan password yang kuat.
4. Hubungkan User ke Database dengan memberikan **ALL PRIVILEGES**.
5. Buka **phpMyAdmin**.
6. Pilih database yang baru saja dibuat.
7. Klik tab **Import**, lalu pilih file `database_export.sql`.
8. Klik tombol **Go / Kirim** hingga import selesai.

---

## ⚙️ Pengaturan File `.env` di Hosting

1. Di File Manager Hostinger, masuk ke folder `laravel_app/`.
2. Jika file `.env` belum ada, salin file `.env.production.example` lalu ubah namanya menjadi `.env`.
3. Edit file `.env` dan sesuaikan parameter berikut:

```env
APP_NAME="Toko Lily Sembako"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kasirtokolily.id

# Sesuaikan dengan informasi database hosting Anda:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u827349422_kasir_lily
DB_USERNAME=u827349422_user_kasir
DB_PASSWORD=PasswordDatabaseHostingAnda
```

4. Simpan perubahan file `.env`.

---

## 🔗 Membuat Symbolic Link Storage

Agar foto/gambar produk dan aset ter-upload dapat diakses oleh browser:

1. Buka browser Anda dan akses URL helper yang sudah disediakan:
   `https://kasirtokolily.id/storage_link.php`
2. Halaman akan menampilkan pesan sukses:
   `✓ Berhasil membuat symbolic link dari ... ke ...`
3. Setelah sukses, Anda dapat menghapus file `public_html/storage_link.php` demi alasan keamanan.

---

## 🎯 Pengujian Aplikasi Live

1. Buka domain Anda: `https://kasirtokolily.id`
2. Pastikan halaman Login Toko Lily muncul dengan tampilan rapi (CSS & JS ter-load dengan benar).
3. Login menggunakan akun kasir/owner Anda.
