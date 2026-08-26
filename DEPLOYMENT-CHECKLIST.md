# DEPLOYMENT CHECKLIST — Portal PKL & Magang Diskominfo Provinsi Banten

Langkah deployment dari lingkungan pengembangan (XAMPP lokal) ke server produksi. Jalankan berurutan.

## 1. Persiapan Server

- [ ] PHP 8.0+ terpasang; ekstensi aktif: `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `zip`.
- [ ] MySQL/MariaDB aktif; database kosong `diskominfo_pkl` dibuat (utf8mb4).
- [ ] Apache 2.4+; `mod_rewrite`, `mod_headers`, `mod_deflate`, `mod_expires` aktif.
- [ ] `AllowOverride All` untuk direktori project (agar `.htaccess` berlaku).
- [ ] PHP `display_errors = Off`; `log_errors = On`; `upload_max_filesize` & `post_max_size` ≥ 8 MB; `max_file_uploads` ≥ 10.

## 2. Transfer File

- [ ] Salin seluruh project **kecuali**: `config/database.php`, `logs/`, `backup/`, konten `uploads/` (tidak ikut).
- [ ] Pastikan `config/database.php.example` ikut (acuan konfigurasi).
- [ ] Transfer via SFTP/SCP; jangan pakai FTP plaintext. Verifikasi checksum bila kritis.
- [ ] Set pemilik file sesuai pengguna web server (mis. `www-data`) dan permission: file `644`, folder `755`, `uploads/` `775`.

## 3. Konfigurasi

- [ ] Buat `config/database.php` dari `.example`; isi kredensial produksi (user DB khusus).
- [ ] `config/app.php`: `APP_BASE_URL = 'https://domain.resmi'`; pastikan `APP_DEBUG = false`.
- [ ] `robots.txt`: perbarui `Sitemap:` ke domain produksi.

## 4. Import / Migrasi Database

**Instalasi baru:**

```sh
# Opsi A — data minimal (user admin + placeholder):
mysql -u USER -p < database/schema.sql
mysql -u USER -p diskominfo_pkl < database/seeder.sql

# Opsi B — salinan penuh kondisi sumber (semua konten ikut):
mysql -u USER -p < database/database.sql
```

**Migrasi dari server lama / update:**

1. Backup DB lama dulu:
   ```sh
   mysqldump -u USER -p diskominfo_pkl > backup-lama.sql
   ```
2. Bandingkan struktur `database/schema.sql` dengan DB lama (tabel/kolom baru).
3. Jalankan pernyataan `ALTER TABLE`/`CREATE TABLE` tambahan bila ada, atau import dump baru:
   ```sh
   mysql -u USER -p diskominfo_pkl < database/database.sql
   ```
   (dump ini berisi `DROP DATABASE` — hanya jalankan bila data lama sudah dibackup.)
4. Salin folder `uploads/` dari server lama ke server baru (jaga permission).

## 5. Backup & Cron (Wajib)

- [ ] Uji `php scripts/backup.php /path/backup` di server.
- [ ] Cron (Linux):
  ```
  30 2 * * * php /var/www/diskominfo-pkl/scripts/backup.php /var/backup/diskominfo-pkl
  ```
  atau Task Scheduler (Windows) — contoh lengkap di README bagian 10.
- [ ] Folder backup di luar web root; salinan kedua di media lain.

## 6. HTTPS

- [ ] Sertifikat terpasang (Let's Encrypt/instansi).
- [ ] Redirect 301 HTTP→HTTPS.
- [ ] Verifikasi `https://domain/sitemap.php` → XML valid; halaman 200.

## 7. Verifikasi Pasca-Deploy

- [ ] `https://domain/` → 200; `https://domain/admin/login.php` → halaman login.
- [ ] `https://domain/config/` → 403; `https://domain/uploads/xxx.php` → 403; `https://domain/docs/` → 403.
- [ ] Header keamanan terpasang (curl -I): `X-Content-Type-Options: nosniff`, `Content-Security-Policy`, `X-Frame-Options`.
- [ ] `Content-Encoding: gzip` pada HTML/CSS.
- [ ] Login admin → ganti password segera.
- [ ] Uji alur: buka berita, agenda, galeri, dokumen, kirim pesan kontak (cek muncul di admin).
- [ ] Uji upload gambar & dokumen (ukuran kecil → besar).
- [ ] Error test: buka halaman tidak ada → 404; sesi tidak valid → redirect login.
- [ ] Cek `logs/app.log` setelah beberapa jam — tidak ada error mencurigakan.
- [ ] Lakukan **PRODUCTION-CHECKLIST.md** (konten, SEO, backup) sebelum go-live.

## 8. Go-Live

- [ ] Konten resmi terisi (checklist B/C produksi).
- [ ] Domain DNS mengarah ke server; SSL valid.
- [ ] Google Search Console: verifikasi + submit sitemap.
- [ ] Pengumuman resmi peluncuran (jika diperlukan).
- [ ] Simpan kredensial & dokumentasi di tempat aman; serah terima ke tim pengelola.