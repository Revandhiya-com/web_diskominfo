# Laporan Pengujian (TESTING) — FASE 13

**Proyek:** Portal PKL & Magang Diskominfo Provinsi Banten
**Lingkungan:** XAMPP (Apache + PHP 8.x + MariaDB) — http://localhost/diskominfo-pkl
**Tanggal:** 14 Agustus 2026
**Metode:** Black-box automated testing (HTTP + verifikasi database) + static code review
**Kesimpulan:** SEMUA TEST PASS — 0 bug aplikasi tersisa; 1 bug nyata ditemukan & diperbaiki

---

## Ringkasan

| Kategori | Total Test | PASS | FAIL | Keterangan |
|---|---|---|---|---|
| PUBLIC (verifikasi konten) | 25 | 25 | 0 | Konten seed lengkap tampil benar |
| ADMIN CRUD (videos, users, pages CMS, messages, download, logout) | 62 | 62 | 0 | Seluruhnya diverifikasi di DB (bukan hanya status HTTP) |
| **TOTAL** | **87** | **87** | **0** | |

## Bug yang ditemukan & diperbaiki

1. **Bug: Halaman statis CMS tidak bisa diakses publik (404).**
   - `admin/page-form.php` mengklaim "URL publik: `?page=slug`", tapi router `index.php` hanya melayani file `pages/*.php` — halaman yang dibuat lewat CMS selalu 404.
   - **Perbaikan:** `index.php` ditambah fallback — jika file halaman tidak ada, cari slug di tabel `pages` berstatus `published`; jika ketemu render `pages/cms.php` (baru), jika tidak → 404. Draft tetap 404.
   - **Verifikasi:** create → `?page=slug` 200 + konten tampil; edit ke draft → 404; delete → 404; slug duplikat otomatis disuffix `-2`.

## 1. PUBLIC — konten seed (25 test)

Data uji: 9 berita (8 published + 1 draft) semuanya ber-thumbnail, 3 album (15 foto + thumbnail), 6 agenda, 5 pengumuman, 5 dokumen PDF asli, 2 video YouTube published.

| # | Item | Hasil |
|---|---|---|
| 1 | Home: hero title dari settings | PASS |
| 2 | Home: footer email dari settings | PASS |
| 3 | Home: seksi video tampil | PASS |
| 4 | Home: kartu berita dengan gambar | PASS |
| 5 | Home: statistik angka | PASS |
| 6 | Home: kartu album | PASS |
| 7 | Berita halaman 1 = 6 kartu (pagination 6/hal) | PASS |
| 8 | Berita halaman 2 = 2 kartu | PASS |
| 9 | Berita: link halaman 2 bekerja | PASS |
| 10 | Berita: draft tidak tampil publik | PASS |
| 11 | Berita: `p=99` dibatasi ke halaman akhir (bukan 404/error) | PASS |
| 12 | Berita: detail berisi konten lengkap | PASS |
| 13 | Berita: redirect slug lama → detail | PASS |
| 14 | Dokumentasi: album muncul | PASS |
| 15 | Album: foto + thumbnail galeri | PASS |
| 16 | Album: kategori muncul | PASS |
| 17 | Video: daftar video tampil | PASS |
| 18 | Video: embed iframe youtube-nocookie | PASS |
| 19 | Video: player detail | PASS |
| 20 | Dokumen: daftar PDF tampil | PASS |
| 21 | Dokumen: tombol unduh tampil | PASS |
| 22 | Agenda: item mendatang tampil | PASS |
| 23 | Pengumuman: baru tampil | PASS |
| 24 | Kontak: email dari settings | PASS |
| 25 | FAQ: isi tampil | PASS |

Smoke tambahan (semua HTTP 200/404 benar): 404, search (`?page=search&q=pkl`), privacy, sitemap, dokumentasi, video, profil, layanan.

## 2. ADMIN CRUD — fitur yang belum diuji di FASE 12 (62 test)

### 2.1 Video (18 test)
- Login admin → dashboard (302/200).
- Buat video (POST + CSRF) → 302; tersimpan di DB (status awal draft).
- Form edit termuat dengan data lama; edit → 302; judul & status ter-update di DB (UPDATE, bukan insert baru).
- URL non-YouTube ditolak dengan pesan "URL YouTube tidak dikenali" dan **tidak** tersimpan.
- Toggle draft ↔ published via `?action=toggle` (302 + status benar di DB).
- Draft tidak tampil di `?page=video`; published tampil.
- Hapus → 302; hilang dari DB.

### 2.2 Users (14 test)
- Buat user editor → 302; tersimpan; username duplikat ditolak (200 + pesan).
- Login editor sukses (302), tetapi dilarang akses `users.php` & `user-form.php` (302), boleh `video-form.php` (200).
- Nonaktifkan diri sendiri ditolak ("Tidak bisa menonaktifkan akun sendiri").
- Nonaktifkan editor lain → 302, `is_active=0` di DB; editor nonaktif tak bisa login ("Username/email atau password salah").
- Hapus editor → 302, hilang dari DB; hapus akun sendiri ditolak (flash).
- Hapus id tak ada → flash "Pengguna tidak ditemukan".

### 2.3 Pages CMS + rute publik (12 test)
- Buat halaman → 302; `?page=slug` → 200 berisi konten (**bug CMS route fixed**).
- Slug duplikat disuffix `-2` (302 + DB).
- Halaman duplikat tampil publik.
- Edit ke draft → 302; `?page=slug` → 404.
- Hapus → 302; hilang → 404; hapus id tak ada → flash.
- Bersih: halaman asli dihapus → 404.

### 2.4 Messages (12 test)
- Kirim via form kontak publik (honeypot kosong + CSRF) → 302; tersimpan di DB.
- Tampil di daftar admin; tandai dibaca (is_read=1) & belum dibaca (is_read=0).
- Pencarian `?q=...` menemukan pesan.
- Hapus → 302; hilang dari DB.

### 2.5 Download counter (6 test)
- Tombol unduh ada di `?page=dokumen`.
- Unduh → HTTP 200, isi `%PDF` valid, header `Content-Disposition: attachment`.
- `downloads` naik tepat 1.
- Id tak ada → 404.

### 2.6 Logout (4 test)
- GET logout.php → 405 (metode tidak diizinkan).
- POST + CSRF → 302 ke login; sesi mati (index admin → 302).

## 3. Perbaikan & perubahan lain

1. **`config/app.php`**: `APP_DEBUG` diubah `true` → `false` (rekomendasi FASE 12 terlaksana; detail error tidak lagi bocor; seluruh halaman tetap 200).
2. **`database/schema.sql`**: tabel `pages` disinkronkan dengan DB asli — tambah kolom `status ENUM('draft','published') NOT NULL DEFAULT 'published'` + index `idx_pages_status`.
3. **`database/database.sql` & `seeder.sql`**: seed video demo diperbarui ke 2 video YouTube asli yang valid (sebelumnya placeholder `watch?v=placeholder` status draft); INSERT halaman statis kini menyebut kolom `status` secara eksplisit.
4. **Konten seed lengkap** (dijalankan via script seed idempotent): settings 26 key terisi, 9 berita + thumbnail GD asli (bukan 1x1 px), 3 album (3 cover + 15 foto + 15 thumbnail), 5 dokumen PDF asli, 6 agenda, 5 pengumuman, 2 video. Album uji 1x1 px dan berita tanpa thumbnail dihapus.

## Catatan

1. Semua data uji (video uji, editor uji, halaman uji, pesan uji, login_attempts) sudah dibersihkan. DB kembali ke kondisi akhir: 2 video, 1 admin, 9 halaman, 1 pesan demo, 9 berita, 3 album, 15 foto, 6 agenda, 5 pengumuman, 5 dokumen, 0 login_attempts.
2. Kegagalan di iterasi awal (FASE 13) seluruhnya artefak script uji PowerShell (konflik variabel `$args`/`$HOME`, escaping PHP, URL `?page=` terpotong karena parsing variabel, asersi yang salah), bukan bug aplikasi — setelah script dikoreksi, semua test deterministik.
3. Rekomendasi tersisa untuk rilis (di luar scope): isi `APP_BASE_URL` produksi, ganti password admin demo & kredensial DB, isi data resmi Diskominfo (settings, dokumen, halaman).

## File terkait perubahan kode

- `index.php` — fallback rute CMS (query `pages` → `pages/cms.php`).
- `pages/cms.php` — baru: render halaman statis CMS publik.
- `config/app.php` — `APP_DEBUG=false`.
- `database/schema.sql`, `database/database.sql`, `database/seeder.sql` — sinkronisasi.

---

# Lampiran: Penyelesaian Akhir FASE 13 (audit & cleanup pasca-test)

**Tanggal:** 14 Agustus 2026 — audit akhir dilakukan setelah seluruh test hijau.

## Audit yang dilakukan (read-only)

1. **Konsistensi uploads ↔ database:** semua file di `uploads/` dirujuk oleh DB (9 thumbnail berita, 15 foto + 15 thumbnail + 3 cover album, 5 PDF dokumen). Tidak ada file yatim.
2. **Status konten:** 9 berita (8 published + 1 draft sengaja), 3 album published, 6 agenda published, 5 pengumuman published, 2 video published, 5 dokumen, 4 FAQ, settings 26/26, login_attempts 0.
3. **Temuan:** halaman statis `pengelola` (id 4) **hilang** dari tabel `pages` — sisa test FASE 12 ("Tarik ke draf halaman: Pengelola Program" lalu terhapus). Seksi "Pengelola" di `?page=profil` jatuh ke fallback placeholder.

## Perbaikan akhir

1. **Halaman `pengelola` di-restore** (slug `pengelola`, status `published`, konten placeholder seed). Verifikasi: `?page=profil` menampilkan konten CMS seksi Pengelola, `?page=pengelola` → 200.
2. **`activity_logs` dikosongkan** (`TRUNCATE`) — 348 baris noise test (login/logout/create/delete uji dari FASE 12–13) dihapus; log mulai bersih untuk penggunaan nyata. `login_attempts` tetap 0.
3. **`database/database.sql` & `seeder.sql` disinkronkan penuh dengan kondisi akhir live** (bukan lagi seed placeholder minimal):
   - Dump data aktual (mysqldump, kolom eksplisit) untuk: users (1), settings (26), kategori (4+5+5), pages (10 — termasuk `pengelola`), news (9 ber-thumbnail), albums (3) + photos (15), videos (2), agendas (6), announcements (5), documents (5), faqs (4), contact_messages (1).
   - **Verifikasi:** file di-import ke DB uji `diskominfo_pkl_test` → jumlah baris 17 tabel **100% cocok** dengan live → DB uji di-drop. Fresh install sekarang menghasilkan situs identik (file upload sudah tersedia di `uploads/`).
4. **Password admin tetap `demo1234`** (keputusan: fase pengembangan).

## Smoke test akhir (setelah APP_DEBUG=false)

- Publik: `verify_publik_fase13.ps1` → **25/25 PASS**.
- Admin: login `admin`/`demo1234` → 302; dashboard, news, messages, pages, settings, albums, videos → **200**.

## Kondisi akhir DB (reference)

| Tabel | Jumlah |
|---|---|
| users | 1 (admin) |
| pages | 10 (9 + `pengelola` dipulihkan) |
| news | 9 (8 published + 1 draft) |
| albums / photos | 3 / 15 |
| videos | 2 |
| agendas / announcements / documents / faqs | 6 / 5 / 5 / 4 |
| settings | 26 |
| contact_messages | 1 (demo "Peserta Demo") |
| login_attempts / activity_logs | 0 / 0 |

## Rekomendasi rilis (di luar scope)

Isi `APP_BASE_URL` produksi (null = auto-detect, aman untuk localhost), ganti password admin demo & kredensial DB, isi data resmi Diskominfo (settings, halaman, dokumen, kontak), daftarkan domain + HTTPS.
