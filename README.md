# Portal PKL & Magang Diskominfo Provinsi Banten

Portal web publik untuk informasi, layanan, berita, agenda, galeri, dan dokumen kegiatan **Praktik Kerja Lapangan (PKL) dan Magang** di lingkungan Diskominfo Provinsi Banten. Dilengkapi panel admin untuk pengelolaan konten.

> **Status: SIAP UJI PRODUKSI (Production Readiness Audit selesai).** Belum ada data resmi — seluruh konten dinamis masih kosong/draft dan WAJIB diisi tim pengelola sebelum publikasi.

---

## 1. Ringkasan

| Item | Nilai |
|---|---|
| Stack | PHP 8 (native, tanpa framework), MySQL/MariaDB, Apache |
| Frontend | Bootstrap 5 (lokal), Bootstrap Icons (lokal), CSS kustom |
| Bahasa | Indonesia (`lang="id"`) |
| Panel admin | `/admin/` dengan login + peran admin/editor |
| Mode debug | `APP_DEBUG = false` |
| Keamanan | Prepared statements, CSRF, session hardening, rate limit login, sanitasi XSS, header keamanan + CSP |

---

## 2. Persyaratan (Requirements)

- **PHP 8.0+** dengan ekstensi: `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `zip` (untuk backup), `json`
- **MySQL 5.7+ / MariaDB 10.3+**
- **Apache 2.4+** dengan `mod_rewrite`, `mod_headers`, `mod_deflate` (kompresi), `mod_expires` (cache)
- **HTTPS wajib** di produksi (keamanan sesi; lihat bagian 9)
- Server mendukung `.htaccess` (AllowOverride All) untuk proteksi direktori & header keamanan

Versi teruji: PHP 8.2, MariaDB 10.4 (XAMPP).

---

## 3. Struktur Project

```
diskominfo-pkl/
├── index.php              # Front controller publik (routing ?page=...)
├── .htaccess              # Keamanan root: blokir internal, header, cache
├── robots.txt             # Disallow internal + URL sitemap (WAJIB ganti di produksi)
├── sitemap.php            # XML sitemap dinamis (konten published)
├── config/
│   ├── app.php            # Konfigurasi umum (nama, base URL, debug, upload)
│   ├── database.php       # Kredensial DB (TIDAK di-commit)
│   └── database.php.example
├── helpers/               # functions.php, security.php, auth.php
├── includes/              # header.php, navbar.php, footer.php, breadcrumb.php
├── pages/                 # Halaman publik: home, berita, layanan, galeri, dll.
├── admin/                 # Panel admin (login + CRUD semua konten)
│   └── includes/          # bootstrap.php, layout.php, crud.php, upload.php
├── assets/                # CSS/JS/gambar/favicon + vendor Bootstrap lokal
├── uploads/               # Konten upload (news/, albums/, documents/, videos/, karya/)
│   └── .htaccess          # Blokir eksekusi script (PHP dll.) di folder upload
├── database/
│   ├── database.sql       # Dump penuh kondisi saat ini (import langsung)
│   ├── schema.sql         # Struktur murni (tanpa data)
│   └── seeder.sql         # Data minimal: user admin + placeholder wajib
├── scripts/
│   └── backup.php         # Backup DB + uploads (CLI, bisa dijadwalkan)
├── logs/                  # Log error aplikasi (diblokir akses web)
└── docs/                  # PRD & laporan pengujian (diblokir akses web)
```

---

## 4. Instalasi (Local Development)

1. Salin project ke web root (mis. `C:\xampp\htdocs\diskominfo-pkl`).
2. Buat database MySQL:
   ```sql
   CREATE DATABASE diskominfo_pkl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import skema + data awal:
   ```sh
   # Opsi A — data placeholder minimal:
   mysql -u USER -p diskominfo_pkl < database/schema.sql
   mysql -u USER -p diskominfo_pkl < database/seeder.sql
   # Opsi B — salinan kondisi saat ini (lebih lengkap):
   mysql -u USER -p diskominfo_pkl < database/database.sql
   ```
   Catatan: `schema.sql` berisi `CREATE DATABASE`; `seeder.sql` dan `database.sql` diawali `USE diskominfo_pkl` — pastikan DB sudah dibuat.
4. Buat kredensial DB (jangan pakai `root` tanpa password di produksi):
   ```sql
   CREATE USER 'diskominfo_pkl'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';
   GRANT ALL PRIVILEGES ON diskominfo_pkl.* TO 'diskominfo_pkl'@'localhost';
   FLUSH PRIVILEGES;
   ```
5. Salin `config/database.php.example` menjadi `config/database.php` lalu isi kredensial.
6. Pastikan folder `uploads/` dapat ditulis oleh proses PHP (lihat bagian 6).
7. Akses `http://localhost/diskominfo-pkl/`.

---

## 5. Pengaturan Awal Admin (Admin Setup)

1. Login di `http://localhost/diskominfo-pkl/admin/login.php`.
2. **Segera ganti password admin** (Pengaturan → Pengguna).
3. Isi data yang masih placeholder lewat menu **Pengaturan Situs** (kontak, alamat, peta, sosial media, hero, CTA). Data kosong/kurang valid diberi penanda jelas di halaman admin.
4. Publish konten resmi: berita, agenda, pengumuman, galeri, dokumen, video, FAQ.

Login awal dari `seeder.sql`: username `admin`, password `demo1234` — **hanya untuk development, WAJIB diganti**.

---

## 6. Konfigurasi Upload

- Folder: `uploads/` dengan subfolder `news/`, `albums/`, `documents/`, `videos/`, `karya/`.
- **Permission (Linux):** `chmod -R 775 uploads` dan pastikan pemilik sesuai pengguna web server (mis. `www-data`). Jangan pernah `chmod 777`.
- **Windows (XAMPP):** tidak perlu set permission; pastikan folder tidak read-only.
- Batas ukuran: 5 MB/file untuk gambar & 100 MB/file untuk video (lihat `UPLOAD_MAX_SIZE` dan `UPLOAD_VIDEO_MAX_SIZE` di `config/app.php`).
- **php.ini yang diperlukan untuk video 100 MB (XAMPP: `C:\xampp\php\php.ini`, lalu restart Apache):**

  ```ini
  upload_max_filesize = 100M
  post_max_size = 110M          ; ≥ upload_max_filesize (ruang untuk field form + CSRF)
  max_execution_time = 300
  max_input_time = 300
  ```

- Ekstensi diizinkan: gambar `jpg/jpeg/png/webp`; dokumen `pdf/doc/docx/xls/xlsx/zip`; video `mp4/webm/ogg/mov`. Video divalidasi via MIME asli (`finfo`), bukan sekadar ekstensi.
- File upload disimpan dengan nama acak; gambar otomatis di-resize (maks 1920px) + thumbnail. `uploads/.htaccess` memblokir eksekusi script.

### 6.1 Modul Karya Peserta

Galeri hasil karya peserta (infografis, video, foto, lainnya) dengan tim & periode mingguan.

- **Admin:** menu **Karya** → Tambah/Edit (judul, deskripsi, jenis, tim Tim 1–3, Minggu ke-N, thumbnail/cover, status).
- **Jenis media:**
  - `Infografis`/`Foto` → upload gambar ke `uploads/karya/` (resize + thumb otomatis).
  - `Video` → pilih dari **Pustaka Video** ATAU upload baru. File video SELALU disimpan sekali di `uploads/videos/` dan tercatat pada tabel `videos` (`source='karya'`) — tidak pernah ada salinan kedua.
  - `Lainnya` → gambar atau video (dideteksi dari MIME asli file).
- **Konten Edukasi:** centang flag tersebut; saat karya terbit, video yang sama juga tampil di halaman publik `?page=video` (satu record, dua halaman). Menarik karya ke draf menyembunyikannya dari Konten Edukasi.
- **Publik:** galeri `?page=karya` (filter jenis/tim/periode + pagination), detail & player HTML5 di `?page=detail-karya&id=...`.
- **Hapus aman:** menghapus karya ikut menghapus file miliknya; video pustaka yang dipakai bersama tidak tersentuh.

---

## 7. Konfigurasi Aplikasi (Environment/Config)

Semua konfigurasi non-rahasia ada di `config/app.php`:

| Konstanta | Keterangan |
|---|---|
| `APP_BASE_URL` | `null` = deteksi otomatis. **Produksi: isi manual** `https://domain.anda` |
| `APP_DEBUG` | Wajib `false` di produksi (sudah `false`) |
| `APP_TIMEZONE` | `Asia/Jakarta` |
| `APP_ASSET_VERSION` | Naikkan saat CSS/JS berubah (cache busting) |
| `UPLOAD_MAX_SIZE` | Batas ukuran upload gambar/dokumen (byte) |
| `UPLOAD_VIDEO_MAX_SIZE` | Batas ukuran upload video (byte, default 100 MB) |

Kredensial database ada di `config/database.php` (tidak di-commit; gunakan `.example` sebagai acuan).

---

## 8. Deployment ke Produksi (ringkas)

1. Jalankan langkah Instalasi (bagian 4) di server produksi.
2. Isi `APP_BASE_URL` dengan domain produksi (HTTPS).
3. Buat akun DB khusus + password kuat; update `config/database.php`.
4. Aktifkan HTTPS (bagian 9) dan periksa `robots.txt` (URL sitemap).
5. Upload konten resmi; verifikasi semua halaman.
6. Jalankan checklist lengkap: lihat **`PRODUCTION-CHECKLIST.md`** dan **`DEPLOYMENT-CHECKLIST.md`**.

---

## 9. HTTPS (Wajib di Produksi)

1. Pasang sertifikat (Let's Encrypt, atau sertifikat instansi).
2. Redirect HTTP → HTTPS di VirtualHost Apache:
   ```apache
   <VirtualHost *:80>
       ServerName pkl.diskominfo.bantenprov.go.id
       Redirect permanent / https://pkl.diskominfo.bantenprov.go.id/
   </VirtualHost>
   <VirtualHost *:443>
       ServerName pkl.diskominfo.bantenprov.go.id
       DocumentRoot "C:/path/diskominfo-pkl"
       SSLEngine on
       SSLCertificateFile      "path/cert.pem"
       SSLCertificateKeyFile   "path/key.pem"
       <Directory "C:/path/diskominfo-pkl">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
3. Header keamanan & CSP sudah diatur di `.htaccess`; `base_url()` otomatis memakai `https` bila `APP_BASE_URL` diisi `https://...`.
4. Jangan menonaktifkan `SameSite`/cookie security — sudah dikonfigurasi di `helpers/security.php`.

---

## 10. Backup (Wajib Berkala)

Script: `scripts/backup.php` — menghasilkan dump database + arsip zip folder `uploads/`, dengan retensi otomatis 30 hari.

```sh
php scripts/backup.php /path/ke/folder/backup
```

Penjadwalan:

- **Linux (crontab):**
  ```
  30 2 * * * php /var/www/diskominfo-pkl/scripts/backup.php /var/backup/diskominfo-pkl
  ```
- **Windows (Task Scheduler):**
  ```
  schtasks /create /tn "Backup Diskominfo PKL" /tr "php C:\xampp\htdocs\diskominfo-pkl\scripts\backup.php C:\backup\diskominfo-pkl" /sc daily /st 02:30
  ```

Penting:
- Folder backup **jangan** berada di dalam web root.
- Ekstensi PHP `zip` wajib aktif agar folder upload ikut terarsip.
- Salin backup ke media lain (server lain, penyimpanan cloud instansi) secara berkala.

Restore:
```sh
mysql -u USER -p diskominfo_pkl < db-YYYYMMDD-HHMMSS.sql
# lalu ekstrak uploads-YYYYMMDD-HHMMSS.zip ke folder project
```

---

## 11. Keamanan (Ringkasan)

- **SQL injection:** 100% prepared statements (`q_*` helpers).
- **XSS:** semua output melewati `e()` (htmlspecialchars); konten admin disanitasi; CSP `script-src 'self' 'unsafe-inline'` + `object-src 'none'`.
- **CSRF:** token wajib di semua form POST admin (`csr_field()` / `require_csrf()`).
- **Sesi:** cookie `HttpOnly`, `Secure` (saat HTTPS), `SameSite=Lax`, nama khusus.
- **Login:** rate limit (10 gagal/15 menit) + lockout; password disimpan `password_hash()` bcrypt; remember-me 30 hari via token acak.
- **Upload:** ekstensi + MIME (finfo) + ukuran divalidasi; nama acak; `uploads/.htaccess` blokir eksekusi; folder upload tidak dieksekusi.
- **Direktori internal** (`config`, `helpers`, `includes`, `database`, `logs`, `docs`, `pages`) diblokir dari akses web; file `.sql/.md/.log/.json/.ini/.example` ditolak.
- **Error tidak bocor:** `APP_DEBUG=false`; semua error DB/exception dicatat ke `logs/app.log`, pengguna dialihkan ke halaman 404/panel.
- **Header:** X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy.
- **Backup DB** tidak pernah disimpan di web root (lihat bagian 10).

---

## 12. Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| Halaman putih / DB error | Kredensial `config/database.php` salah | Cek `logs/app.log`; verifikasi user & hak akses DB |
| 403 saat buka `/admin/` dll. | `AllowOverride` nonaktif | Aktifkan `AllowOverride All` di VirtualHost/Directory |
| Upload gagal | Ukuran file > batas | Naikkan `upload_max_filesize` & `post_max_size` di php.ini; cek permission folder `uploads/` |
| CSS/JS lama di browser | Cache aset | Naikkan `APP_ASSET_VERSION` di `config/app.php` |
| Login terkunci | Terlalu banyak percobaan gagal | Tunggu ±15 menit atau hapus baris `login_attempts` di DB |
| Sitemap URL salah | `robots.txt` masih `localhost` | Isi `APP_BASE_URL` + perbarui `robots.txt` |
| Backup tidak menghasilkan zip | Ekstensi `zip` nonaktif | Aktifkan `extension=zip` di php.ini |
| Error berbahasa kode (exception) | Bug aplikasi | Buka `logs/app.log`, laporkan dengan isi log |

---

## Dokumentasi Lainnya

- `PRODUCTION-CHECKLIST.md` — daftar periksa sebelum go-live
- `DEPLOYMENT-CHECKLIST.md` — langkah deployment server + migrasi database
- `docs/PRD.md` — Product Requirement Document
- `docs/TESTING-REPORT-FASE-15.md` — hasil pengujian (187/187 PASS) + audit pra-rilis
