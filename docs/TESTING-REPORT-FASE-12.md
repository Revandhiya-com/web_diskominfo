# Laporan Pengujian (TESTING) — FASE 12

**Proyek:** Portal PKL & Magang Diskominfo Provinsi Banten
**Lingkungan:** XAMPP (Apache + PHP 8.x + MariaDB) — http://localhost/diskominfo-pkl
**Tanggal:** 14 Agustus 2026
**Metode:** Black-box automated testing (HTTP + verifikasi database) + static code review
**Kesimpulan:** SEMUA TEST PASS — 0 bug aplikasi ditemukan

---

## Ringkasan

| Kategori | Total Test | PASS | FAIL | Keterangan |
|---|---|---|---|---|
| PUBLIC | 60 | 60 | 0 | 3 "fail" awal = artefak script uji (bukan bug) |
| ADMIN | 37 | 37 | 0 | Diuji ulang deterministik dengan verifikasi DB |
| SECURITY | 21 | 21 | 0 | Termasuk rate limiting & stored XSS |
| RESPONSIVE | 15 | 15 | 0 | 2 "fail" awal = false positive (CSS handle) |
| PERFORMANCE | 15 | 15 | 0 | Resize, waktu respons, batas ukuran |
| **TOTAL** | **148** | **148** | **0** | |

Tidak ada perubahan kode yang dilakukan — semua fitur yang diuji sudah benar.

---

## 1. PUBLIC

| # | Item | Hasil | Bukti |
|---|---|---|---|
| 1 | Home | PASS | HTTP 200, konten lengkap |
| 2 | Navbar | PASS | 12 elemen: Home, Profil, Layanan, Berita, Agenda, Dokumentasi, Dokumen, Pengumuman, FAQ, Kontak, search box, hamburger toggle |
| 3 | Footer | PASS | Navigasi, informasi, kontak, copyright, privacy, sitemap |
| 4 | Profil | PASS | HTTP 200 |
| 5 | Layanan | PASS | HTTP 200 |
| 6 | Berita | PASS | List + jumlah berita tampil |
| 7 | Detail berita | PASS | Judul, kategori, penulis, tanggal, views, share, berita terkait |
| 8 | Agenda | PASS | HTTP 200 |
| 9 | Dokumentasi | PASS | Album tampil |
| 10 | Album (gallery) | PASS | Info album + galeri + lightbox + caption |
| 11 | Dokumen | PASS | List dokumen (tabel responsive) |
| 12 | Pengumuman | PASS | HTTP 200 |
| 13 | FAQ | PASS | HTTP 200 |
| 14 | Kontak | PASS | Form kirim + tersimpan + tampil di admin |
| 15 | Search | PASS | Kata kunci, filter jenis, kategori, tahun, bulan, tipe file |
| 16 | Filter | PASS | Filter kategori berita + search global |
| 17 | Pagination | PASS | p=999/-1/abc tidak error (dikoreksi ke halaman valid) |
| 18 | 404 page | PASS | Status 404 + teks "Halaman Tidak Ditemukan" |
| 19 | Scan semua link internal di Home | PASS | 30/30 link HTTP 200 |
| 20 | Sitemap XML, robots.txt, privacy, video | PASS | Semua HTTP 200 |

## 2. ADMIN

| # | Item | Hasil | Bukti |
|---|---|---|---|
| 1 | Login | PASS | admin/demo1234 → dashboard; password salah → error; CSRF salah → 403 |
| 2 | Logout | PASS | POST+CSRF → redirect login; sesi mati (index 302); SID diganti |
| 3 | Dashboard | PASS | HTTP 200 + log aktivitas login |
| 4 | CRUD Berita | PASS | create → tampil publik → edit (UPDATE, bukan insert) → toggle draft/published → delete → hilang dari publik |
| 5 | CRUD Album | PASS | create + cover → edit → delete (foto cascade) |
| 6 | Multiple photo upload | PASS | 2–3 foto sekaligus, urutan, thumbnail otomatis |
| 7 | Edit photo | PASS | Caption + reorder tersimpan di DB |
| 8 | Delete photo | PASS | Foto + thumbnail terhapus dari disk & DB |
| 9 | CRUD Agenda | PASS | create → tampil publik → delete |
| 10 | CRUD Pengumuman | PASS | create (pinned) → tampil publik → delete |
| 11 | CRUD Dokumen | PASS | Upload PDF → tampil publik → delete (file ikut terhapus) |
| 12 | CRUD FAQ | PASS | create → tampil publik → delete |
| 13 | Settings | PASS | Save + revert |
| 14 | 26 halaman admin | PASS | Semua HTTP 200 tanpa PHP error |
| 15 | Unauthorized access | PASS | 22 halaman admin redirect ke login tanpa sesi |

## 3. SECURITY

| # | Item | Hasil | Bukti |
|---|---|---|---|
| 1 | Unauthorized admin access | PASS | Semua halaman admin → redirect login (302) |
| 2 | SQL injection | PASS | 11 payload (UNION, OR 1=1, DROP TABLE) di public & admin — semua ditolak, tabel utuh, tidak ada error SQL |
| 3 | XSS | PASS | Stored XSS via konten berita: script, onerror, javascript:, iframe, svg, onclick **semua dicabut** `sanitize_admin_html`; HTML normal (p, a, img) tetap tampil; XSS di form kontak & nama user juga escaped |
| 4 | CSRF | PASS | POST tanpa token → 403; login CSRF salah → 403; logout wajib CSRF |
| 5 | Upload validation | PASS | `.php` ditolak (MIME); PHP dibalut jpg ditolak (finfo); >5MB ditolak; nama file random (bin2hex); PHP tidak bisa dieksekusi di uploads (403) |
| 6 | Session handling | PASS | SID regenerasi saat login & logout (anti fixation), cookie HttpOnly + SameSite=Lax, secure saat HTTPS |
| 7 | Rate limiting login | PASS | Diblokir pada percobaan salah ke-6 (5 percobaan / 15 menit), login_attempts tercatat |
| 8 | Security headers | PASS | nosniff, X-Frame-Options, CSP, Referrer-Policy, Permissions-Policy |
| 9 | Proteksi folder | PASS | /config/, /helpers/, /database/ → 403; database.sql tidak bisa diakses |

## 4. RESPONSIVE

| # | Item | Hasil | Bukti |
|---|---|---|---|
| 1 | Desktop | PASS | Grid col-lg-*, breakpoint 991.98px |
| 2 | Tablet | PASS | Breakpoint 767.98px |
| 3 | Mobile | PASS | Breakpoint 575.98px, viewport meta, hamburger toggle |
| 4 | Gambar | PASS | CSS `width:100%` / `max-width:100%` (`.dp-card-media img`) |
| 5 | Tabel | PASS | Semua tabel dibungkus `table-responsive` |
| 6 | Form | PASS | form-control + grid responsive |

## 5. PERFORMANCE

| # | Item | Hasil | Bukti |
|---|---|---|---|
| 1 | Large image | PASS | 4000x3000 (336 KB) → di-resize 1920x1440 (104 KB, quality 85) |
| 2 | Multiple photos | PASS | 2–3 foto besar sekaligus: resize + thumbnail 480px otomatis, tanpa error |
| 3 | Pagination | PASS | Halaman berita 86–100 ms |
| 4 | Search | PASS | 83 ms |
| 5 | Waktu respons semua halaman | PASS | 79–292 ms (rata-rata 130 ms) |
| 6 | Batas ukuran upload | PASS | >5MB ditolak dengan pesan jelas |

---

## Catatan

1. **Bukan bug** (keadaan data, bukan kode): setting situs (alamat, email, telepon, sosmed, teks hero) masih kosong di DB sehingga footer menampilkan `-`. Diisi via **Admin → Pengaturan Situs**.
2. **Bukan bug**: foto seed `tes-foto-1.png` / `tes-foto-2.png` berukuran 1x1 px tanpa thumbnail (file contoh). Galeri tetap menampilkannya dengan fallback ke gambar asli.
3. **Bukan bug**: berita seed tidak punya thumbnail → placeholder ikon tampil (dirancang).
4. Folder `uploads/news` dan `uploads/documents` kosong — wajar, hanya berisi saat admin mengunggah.
5. Logout **hanya via POST + CSRF** (by design, anti logout-CSRF). GET ke logout.php → 405.
6. Halaman `?page=album` memakai parameter **slug**, bukan id (sesuai dokumentasi kode).
7. Semua data uji otomatis (berita, album, foto, agenda, pengumuman, FAQ, dokumen, pesan kontak, login_attempts) **sudah dibersihkan** — DB & folder uploads kembali ke kondisi awal. Akun admin tidak terkunci (rate limit dibersihkan).

## Rekomendasi sebelum rilis (di luar scope FASE 12)

- Set `APP_DEBUG` ke `false` di `config/app.php` (saat ini `true` — menampilkan detail error).
- Isi pengaturan situs (kontak, sosmed, teks hero) via panel admin.
- Isi `APP_BASE_URL` manual untuk produksi.
- Ganti password admin demo (`demo1234`) dan kredensial DB.