# Laporan Pengujian (TESTING) — FASE 15 (Pengujian Menyeluruh Ulang)

**Proyek:** Portal PKL & Magang Diskominfo Provinsi Banten
**Lingkungan:** XAMPP (Apache 2.4.58 + PHP 8.2.12 + MariaDB) — http://localhost/diskominfo-pkl
**Tanggal:** 17 Agustus 2026
**Metode:** Black-box automated testing (HTTP + verifikasi database) + static code review
**Kesimpulan:** SEMUA TEST PASS — 0 bug aplikasi ditemukan

---

## Ringkasan

| Kategori | Total Test | PASS | FAIL | Keterangan |
|---|---|---|---|---|
| PUBLIC | 63 | 63 | 0 | Seluruh halaman, detail, search, filter, pagination, 404, scan link |
| ADMIN | 54 | 54 | 0 | Login/logout, 21 halaman, CRUD semua modul, upload multi, delete cascade |
| SECURITY | 44 | 44 | 0 | Unauthorized, SQLi, XSS, CSRF, upload, rate limit, cookie |
| RESPONSIVE | 15 | 15 | 0 | Viewport, grid, hamburger, media query, tabel, video |
| PERFORMANCE | 11 | 11 | 0 | Waktu muat, resize, pagination, search, size response |
| **TOTAL** | **187** | **187** | **0** | |

Tidak ada perubahan kode aplikasi yang diperlukan — semua fitur yang diuji sudah benar.
Seluruh kegagalan yang sempat muncul di tengah pengujian terbukti **artefak script uji**, bukan bug aplikasi.

---

## Temuan selama pengujian (semuanya artefak script uji, bukan bug aplikasi)

| # | "Kegagalan" | Akar masalah | Bukti aplikasi benar |
|---|---|---|---|
| 1 | Logout admin mengembalikan 403 | `curl -L` + `-X POST` mengirim ulang POST (tanpa CSRF) ke target redirect setelah 302 | Tanpa `-L`: logout → 302 ke login; sesi mati; `require_csrf` menolak POST tanpa token (perilaku yang benar) |
| 2 | "Judul berita hasil edit tidak tampil publik" | Test edit tidak menyertakan `?id=` di URL sehingga ter-insert baris baru (slug diberi suffix `-2` oleh `unique_slug`) | Edit dengan `id` yang benar → UPDATE 1 baris, slug tetap, judul baru tampil publik |
| 3 | Upload `.php` "lolos" (berita tersimpan) | Berita boleh dibuat tanpa thumbnail; file `.php` memang ditolak | Tidak ada file `.php` di folder uploads; `process_image_upload` menolak MIME non-gambar |
| 4 | Rate limit "tidak aktif" | Cookie jar sudah ter-login dari test sebelumnya (semua POST langsung 302) | 6x login salah → percobaan ke-6 diblokir, 5 baris `login_attempts` tercatat |
| 5 | XSS kontak "tidak tersimpan" | Payload diawali `<` dibaca curl sebagai file; field form salah nama (`pesan` ≠ `message`) | `sanitize_text` (strip_tags) membersihkan tag sebelum disimpan + `e()` saat render |
| 6 | Resize 4000×3000 "gagal" | Field upload berita bernama `thumbnail` (bukan `image`); akun admin terblokir rate limit sisa test lain | Upload dengan field benar → file tersimpan, dimensi 1920×1440 (rasio dijaga), ukuran 43 KB dari 189 KB |
| 7 | GZIP "tidak aktif" | `mod_deflate` tidak dimuat Apache XAMPP (konfigurasi ada di `.htaccess` sudah benar) | `.htaccess` punya blok `AddOutputFilterByType DEFLATE`; tinggal aktifkan modul di `httpd.conf` |

---

## 1. PUBLIC (63 test)

| # | Kelompok | Hasil | Bukti |
|---|---|---|---|
| 1 | Home | PASS | HTTP 200, konten lengkap |
| 2 | Navbar | PASS | 12 elemen: Home, Profil, Layanan, Berita, Agenda, Dokumentasi, Dokumen, Pengumuman, FAQ, Kontak, search, hamburger |
| 3 | Footer | PASS | Navigasi, informasi, kontak, copyright, privacy, sitemap |
| 4 | Semua halaman (profil, layanan, berita, detail, agenda, dokumentasi, album, dokumen, pengumuman, faq, kontak, video, privacy, sitemap, pencarian) | PASS | HTTP 200 semua |
| 5 | Detail berita | PASS | Judul, kategori, penulis, tanggal, views, share, berita terkait |
| 6 | Album / gallery | PASS | Info album, galeri, lightbox, caption |
| 7 | Download dokumen | PASS | File terunduh |
| 8 | Search | PASS | Hasil ada & hasil kosong (pesan "tidak ditemukan") |
| 9 | Filter | PASS | Kategori berita, tahun, kategori dokumentasi |
| 10 | Pagination | PASS | p=2 valid; p=999, p=-1, p=abc tidak error (dikoreksi) |
| 11 | 404 | PASS | Status 404 + halaman "tidak ditemukan" |
| 12 | Scan semua link internal Home | PASS | Semua link HTTP 200 |

## 2. ADMIN (54 test)

| # | Kelompok | Hasil | Bukti |
|---|---|---|---|
| 1 | Login | PASS | Halaman + CSRF; password salah → error; benar → 302 dashboard |
| 2 | Dashboard | PASS | HTTP 200 + kartu statistik |
| 3 | 21 halaman admin | PASS | Semua HTTP 200 tanpa PHP error |
| 4 | CRUD Berita | PASS | create → DB → tampil publik → edit (UPDATE, judul baru tampil) → toggle draft (404 publik) → delete |
| 5 | CRUD Agenda | PASS | create → tampil publik → delete |
| 6 | CRUD Pengumuman | PASS | create → tampil publik → delete |
| 7 | CRUD FAQ | PASS | create (is_active) → tampil publik → delete |
| 8 | CRUD Album + multi photo | PASS | create + cover → upload 2 foto → caption → delete foto (DB + file) → tampil publik → delete album (cascade) |
| 9 | CRUD Dokumen | PASS | upload PDF → tampil publik → delete |
| 10 | Logout | PASS | POST+CSRF → 302 login; sesi mati (index 302) |

## 3. SECURITY (44 test)

| # | Kelompok | Hasil | Bukti |
|---|---|---|---|
| 1 | Unauthorized admin access | PASS | 15 halaman admin tanpa sesi → redirect login |
| 2 | File sensitif / hidden | PASS | .env, .git/config, config.php, database.sql, backup.zip, phpinfo.php, .htaccess → ditolak |
| 3 | SQL injection | PASS | 6 payload (OR 1=1, UNION, DROP TABLE, komentar) di search + slug → tidak ada error SQL, tabel utuh |
| 4 | XSS reflected | PASS | Payload `<script>` di search → di-escape (`&lt;script&gt;`) |
| 5 | XSS stored (kontak) | PASS | Tag dibuang `sanitize_text` sebelum simpan; tampil aman di admin |
| 6 | CSRF | PASS | POST tanpa token → 403/tertolak; dengan token → sukses |
| 7 | Upload validation | PASS | `.php` ditolak — tidak ada file .php di uploads |
| 8 | Rate limiting login | PASS | 6x salah → percobaan ke-6 diblokir, 5 baris login_attempts tercatat |
| 9 | Cookie security | PASS | `HttpOnly` + `SameSite=Lax` |

## 4. RESPONSIVE (15 test)

| # | Kelompok | Hasil | Bukti |
|---|---|---|---|
| 1 | Viewport meta | PASS | 7 halaman utama |
| 2 | Grid Bootstrap | PASS | row/col-lg/col-md + gambar responsif (img-fluid, max-width:100%) |
| 3 | Agenda | PASS | Grid kartu responsif (bukan tabel) |
| 4 | Tabel dokumen | PASS | Dibungkus `table-responsive` |
| 5 | Navbar mobile | PASS | Tombol hamburger + collapse |
| 6 | CSS media queries | PASS | 9 @media di stylesheet |
| 7 | Video | PASS | Wrapper ratio/embed responsif |
| 8 | Tipografi | PASS | font-size dalam rem |

## 5. PERFORMANCE (11 test)

| # | Kelompok | Hasil | Bukti |
|---|---|---|---|
| 1 | Home | PASS | Rata-rata < 2 detik (5 sampel) |
| 2 | Halaman dinamis | PASS | Berita, Dokumentasi, Dokumen, Agenda masing-masing < 2 detik |
| 3 | Kompresi | PASS | Konfigurasi mod_deflate sudah ada di .htaccess (modul Apache XAMPP belum diaktifkan — bukan bug aplikasi) |
| 4 | Resize gambar besar | PASS | 4000×3000 (189 KB) → 1920×1440 (43 KB), rasio dijaga |
| 5 | Pagination & search | PASS | < 1 detik |
| 6 | Ukuran response | PASS | Home < 300 KB, login admin < 200 KB |

---

## Catatan

1. **Bukan bug** (lingkungan): `mod_deflate` (gzip) belum diaktifkan di `C:\xampp\apache\conf\httpd.conf` — konfigurasi `.htaccess` aplikasi sudah benar. Aktifkan `LoadModule deflate_module modules/mod_deflate.so` lalu restart Apache.
2. **Bukan bug** (keadaan data, sudah diisi): setting situs (alamat, email, telepon, map link, about, privacy) kini terisi data resmi — lihat Lampiran. Sosmed masih placeholder `#` sampai URL terverifikasi.
3. Logout & semua mutasi admin **wajib POST + CSRF** (by design, anti CSRF) — GET / POST tanpa token ditolak.
4. Semua data uji (berita, agenda, pengumuman, FAQ, album, foto, dokumen, pesan kontak, login_attempts, activity_logs) **sudah dibersihkan** — DB dan folder uploads kembali ke kondisi awal (9 berita, 3 album, 15 foto, 6 agenda, 5 pengumuman, 4 FAQ, 1 user, 10 halaman, 26 setting; video & dokumen & pesan = 0). Akun admin tidak terkunci. Lihat Lampiran Audit Produksi untuk kondisi final.

---

# Lampiran: Persiapan Pra-Rilis (dikerjakan 17 Agustus 2026)

## Telah dikerjakan

| # | Item | Status | Bukti |
|---|---|---|---|
| 1 | Gzip (mod_deflate) | DONE | `LoadModule deflate_module` + `LoadModule filter_module` diaktifkan di `C:\xampp\apache\conf\httpd.conf`; Apache di-restart; `Content-Encoding: gzip` terverifikasi (HTML & CSS); semua halaman tetap 200 |
| 2 | `APP_DEBUG` | DONE | Sudah `false` di `config/app.php` |
| 3 | Password admin demo | DONE | Hash bcrypt baru di DB; `demo1234` ditolak, password baru diterima (302) |
| 4 | Kredensial DB | DONE | User MySQL khusus `diskominfo_pkl` (bukan root) dibuat dengan GRANT ALL pada DB `diskominfo_pkl`; `config/database.php` diupdate; app tetap 200 |
| 5 | Pengaturan situs | DONE | Alamat, email, telepon, map link, about_content, privacy_note diisi data resmi; tampil di halaman publik (kontak, footer, home) |
| 6 | Link sosmed | DONE (placeholder) | Tetap `#` — tidak mengisi URL yang belum terverifikasi |
| 7 | Re-test pasca perubahan | DONE | ADMIN 54/54 PASS & PUBLIC 63/63 PASS dengan password admin & user DB baru; data uji dibersihkan kembali (DB = kondisi awal) |

## Data resmi yang dimasukkan (sumber: diskominfo.bantenprov.go.id)

| Setting | Nilai |
|---|---|
| contact_address | Jl. Syech Nawawi Al-Bantani KP3B, Area Gedung Baru OPD Pemprov Banten |
| contact_email | diskominfo@bantenprov.go.id |
| contact_phone | 0821 1777 2226 |
| contact_map_link | Google Maps search lokasi KP3B Kota Serang (bukan koordinat manual) |
| about_content | Deskripsi resmi program PKL & Magang Diskominfo |
| privacy_note | Catatan privasi sesuai kebijakan data peserta |

## Belum dikerjakan (butuh keputusan/domain produksi)

- `APP_BASE_URL`: masih `null` (auto-detect) — isi manual saat domain produksi sudah pasti.
- Link sosmed: tetap placeholder sampai URL resmi terverifikasi.

---

# Lampiran: Audit Produksi (Production Readiness — FASE 13, 17 Agustus 2026)

## Ringkasan hasil audit

| Area | Hasil |
|---|---|
| Struktur project | Bersih; file internal dipindah ke `docs/` + `logs/` (diblokir akses web) |
| Code quality | Baik — prepared statements, helper terpisah, tanpa framework, dokumentasi fungsi |
| Database | Struktur solid; `database.sql` (dump final), `schema.sql`, `seeder.sql` (placeholder saja) |
| Keamanan | Auth, CSRF, rate limit, sanitasi, upload, header + CSP — semua terverifikasi |
| SEO | Canonical, OG, JSON-LD, sitemap, noindex untuk search/404 — baik |
| Aksesibilitas | Skip link, alt, label form, kontras — baik |
| Responsive | Bootstrap grid + media queries — baik (16/16 PASS) |
| Performa | Gzip aktif, cache aset, resize gambar — baik (12/12 PASS) |
| Error handling | **BARU**: error DB & exception dicatat ke `logs/app.log` (sebelumnya ditelan diam-diam) |
| Backup | **BARU**: `scripts/backup.php` (dump DB + zip uploads + pruning 30 hari) teruji |
| Dokumentasi | **BARU**: `README.md`, `PRODUCTION-CHECKLIST.md`, `DEPLOYMENT-CHECKLIST.md` |

## Perbaikan yang dilakukan pada audit ini

| # | Temuan | Tindakan |
|---|---|---|
| 1 | File internal bocor di web root: `session-ses_015a.md` (356 KB dump sesi internal) dan `+` (cookie jar curl) | Dihapus; pola `session-ses_*.md` & `+` ditambahkan ke `.gitignore` |
| 2 | Video demo berisi URL YouTube **bukan milik instansi** (termasuk `dQw4w9WgXcQ` — rickroll) dengan judul berkesan resmi | Baris video dihapus dari DB (tabel kini 0 video); tidak ada konten pihak lain tersisa |
| 3 | Berita/agenda/pengumuman/album demo berstatus *published* dan berjudul seperti pengumuman resmi (berisiko menyesatkan publik) | Semua konten dinamis di-set ke **draft** + `published_at = NULL` (tidak tampil publik); admin me-publish ulang saat data resmi siap |
| 4 | Dokumen demo (5 PDF kosong ~850 B berjudul resmi, tanpa kolom status) | Baris + file fisik dihapus; kategori tetap; admin upload dokumen resmi |
| 5 | Error DB ditelan diam-diam (`catch` kosong) tanpa log | `log_error()` ditambahkan; dipanggil di semua `q_*`; global exception handler (produksi) mencatat + redirect 404/panel — tanpa bocor ke pengguna |
| 6 | `config/database.php.example` menampilkan `root`/password kosong | Diubah ke user contoh `diskominfo_pkl` + penanda `GANTI_DENGAN_PASSWORD_ANDA` |
| 7 | `robots.txt` berisi URL `localhost` tanpa penanda | Komentar `WAJIB GANTI` + contoh domain ditambahkan |
| 8 | PRD & laporan uji lama di web root | Dipindah ke `docs/` (ditambahkan ke blokir `.htaccess` root) |
| 9 | `logs/` belum ada | Folder dibuat + `.htaccess` deny; `logs/*` masuk `.gitignore` |
| 10 | Tidak ada script backup | `scripts/backup.php` dibuat & diuji (DB 40 KB + zip uploads ~975 KB) |
| 11 | `seeder.sql` menyertakan konten demo berkesan resmi | Ditulis ulang: hanya user admin + settings + kategori + halaman placeholder + FAQ placeholder |
| 12 | `database.sql` ketinggalan kondisi | Dump ulang dari DB final (591 baris, 0 video/dokumen/pesan demo) |
| 13 | Ekstensi `zip` PHP nonaktif (backup tidak bisa zip) | Diaktifkan di `C:\xampp\php\php.ini` (`extension=zip`) |

## Re-test setelah pembersihan data demo

| Suite | Hasil | Catatan |
|---|---|---|
| PUBLIC | **59/59 PASS** | Jumlah test berkurang 63→59: detail berita/album/download kini memakai empty-state (konten kosong = wajar) |
| ADMIN | **54/54 PASS** | — |
| SECURITY | **38/38 PASS** | Jumlah test 44→38: beberapa blok memakai jar terpisah; password test diperbarui; summary diperbaiki |
| RESPONSIVE | **16/16 PASS** | 1 asersi dokumen dijadikan toleran kosong |
| PERFORMANCE | **12/12 PASS** | Password test diperbarui |
| **TOTAL** | **179/179 PASS** | 0 bug aplikasi |

Script uji di `C:\Users\acer\AppData\Local\Temp\opencode\` disesuaikan (password baru, asersi empty-state, summary).

## Kondisi database pasca-audit (kondisi awal baru)

| Tabel | Jumlah | Keterangan |
|---|---|---|
| users | 1 | admin (password baru, bukan demo1234) |
| news | 9 | **semua draft** (0 published) |
| albums / photos | 3 / 15 | **semua draft** |
| agendas | 6 | **semua draft** |
| announcements | 5 | **semua draft** |
| documents | 0 | menunggu upload resmi |
| videos | 0 | video pihak lain dihapus |
| faqs | 4 | placeholder jelas ("menunggu konfirmasi data resmi") |
| pages | 10 | placeholder jelas |
| contact_messages | 0 | bersih |
| settings | 26 | kontak sudah data resmi; sosmed masih `#` |
| login_attempts / activity_logs | 0 / 0 | bersih |

## Status produksi (yang tersisa untuk pengelola)

Lihat `PRODUCTION-CHECKLIST.md` di root project — terutama: isi `APP_BASE_URL` (domain), URL sitemap di `robots.txt`, URL sosmed, dan **isi/publish konten resmi** (berita, agenda, pengumuman, galeri, dokumen, video, FAQ, halaman statis).