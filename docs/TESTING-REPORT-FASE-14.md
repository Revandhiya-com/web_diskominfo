# Laporan Pengujian (TESTING) — FASE 14

**Proyek:** Portal PKL & Magang Diskominfo Provinsi Banten
**Lingkungan:** XAMPP (Apache + PHP 8.x + MariaDB) — http://localhost/diskominfo-pkl
**Tanggal:** 14 Agustus 2026
**Metode:** Black-box smoke testing (HTTP) + verifikasi header respons + static review CSS/JS
**Fokus:** UI/UX (dark mode) & performa (vendor lokal, caching, anti-CLS)

**Kesimpulan:** SEMUA TEST PASS — tidak ada regresi dari FASE 13 (25/25 publik tetap hijau).

---

## Ringkasan

| Kategori | Hasil |
|---|---|
| Regression publik (FASE 13) | 25/25 PASS |
| Smoke publik FASE 14 (15 halaman) | 15/15 HTTP 200, tanpa error PHP |
| Smoke admin FASE 14 (9 halaman) | login 302 + 8/8 HTTP 200 |
| Header cache (CSS/JS/img/PHP) | sesuai target |
| Referensi CDN tersisa di kode | 0 |

## 1. Dark mode (fitur baru)

- **Mekanisme:** tiga lapis —
  1. Inline script anti-FOUC di `<head>` membaca `localStorage['dp-theme']` + `prefers-color-scheme`, lalu set class `dp-dark`/`dp-light` + `data-bs-theme` di `<html>` **sebelum** CSS dirender (tidak ada kedipan).
  2. CSS: blok `html.dp-dark` (manual) + `@media (prefers-color-scheme: dark) { html:not(.dp-light) }` (otomatis) — keduanya override variabel `--dp-*`.
  3. Bootstrap 5.3 native dark via `data-bs-theme="dark"` (form, dropdown, pagination ikut gelap otomatis).
- **Toggle:** tombol ☀/🌙 di topbar navbar (semua halaman publik) — `main.js` `initTheme()`: simpan pilihan ke localStorage, ikon berubah, `aria-pressed`/`aria-label` diperbarui.
- **Kontras disiapkan:** heading pakai variabel semantik baru `--dp-heading`, link `--dp-link`/`--dp-link-hover` (light = nilai primary lama, dark = biru terang); topbar/footer tetap navy gelap; badge/alert/dokumen hardcoded (gold/success/danger/pdf/docx/xlsx/rar) di-override dengan versi gelap.
- **Reduced motion:** blok dark menghormati `prefers-reduced-motion` (transisi dimatikan).
- **Cakupan:** publik saja (admin tetap terang — keputusan desain).

## 2. Bootstrap & Bootstrap Icons → lokal

- File baru di `assets/vendor/`:
  - `bootstrap/bootstrap.min.css` (232 KB), `bootstrap/bootstrap.bundle.min.js` (80 KB)
  - `bootstrap-icons/bootstrap-icons.min.css` (85 KB) + `fonts/bootstrap-icons.woff2` (130 KB)
- `includes/header.php`, `includes/footer.php`, `admin/includes/layout.php`, `admin/login.php` beralih dari CDN jsdelivr ke path lokal + `?v=APP_ASSET_VERSION`.
- CSP di `.htaccess` disederhanakan: `script-src 'self' 'unsafe-inline'`, `style-src 'self' 'unsafe-inline'`, `font-src 'self' data:` — CDN jsdelivr **dihapus** dari whitelist. Verifikasi: 0 referensi `cdn.jsdelivr` tersisa di kode; ikon font termuat (HTTP 200).
- Efek: tidak ada ketergantungan internet untuk render; aset di-cache browser 1 tahun (immutable).

## 3. Performa

1. **Cache-Control / Expires** (`.htaccess`):
   - PHP: `no-store, no-cache, must-revalidate` (konten dinamis).
   - CSS/JS: `public, max-age=31536000, immutable` (versioned via `?v=`).
   - Gambar/PDF: `public, max-age=2592000`.
   - Terverifikasi via `curl -I` — semua header keluar sesuai.
   - Catatan: `mod_deflate`/`mod_expires` belum aktif di XAMPP lokal (hanya `mod_headers`); aturan tetap berlaku penuh di hosting produksi.
2. **Anti-CLS:** `aspect-ratio` sudah ada pada `.dp-card-media` (16/9), `.dp-gallery-link` (4/3); thumbnails berita & related memakai ukuran tetap; gambar detail berita menyertakan `width`/`height` asli.
3. **Lazy-load:** semua `<img>` publik (kecuali LCP: cover album & gambar detail berita) sudah `loading="lazy"` — audit 15 tag img.
4. **Preconnect:** `<link rel="preconnect">` ke `www.youtube.com` + `youtube-nocookie.com` di header (untuk embed video).
5. `APP_ASSET_VERSION` 1.1.0 → 1.2.0.

## 4. Smoke test detail

**Publik (semua 200, tanpa Fatal/Warning/Notice/Deprecated):** home, profil, layanan, berita, agenda, dokumentasi, album (3 slug), video, pengumuman, kontak, faq, search `q=pkl`, privacy, sitemap, `pengelola`; redirect lama berita (`?page=berita&slug=...` → 302).

**Admin:** login `admin`/`demo1234` (302) → dashboard, news, pages, settings, videos, albums, messages, users semua 200.

**Regression:** `verify_publik_fase13.ps1` → 25/25 PASS (konten seed & halaman publik tidak berubah).

## 5. Perubahan file

| File | Perubahan |
|---|---|
| `assets/css/style.css` | variabel semantik `--dp-link/-hover/--dp-heading` + blok dark mode + `color-scheme` |
| `assets/js/main.js` | `initTheme()` (toggle + localStorage + ikon) |
| `assets/vendor/*` | baru — Bootstrap 5.3.3 & Bootstrap Icons 1.11.3 lokal |
| `includes/header.php` | asset lokal, preconnect YouTube, inline theme script |
| `includes/footer.php` | bootstrap bundle lokal |
| `includes/navbar.php` | tombol toggle tema |
| `admin/includes/layout.php`, `admin/login.php` | asset lokal |
| `.htaccess` | CSP tanpa CDN + cache headers + Expires |
| `config/app.php` | `APP_ASSET_VERSION=1.2.0` |

## 6. Catatan

1. Dark mode perlu pengecekan visual manual singkat di browser (kontras akhir) — verifikasi otomatis hanya memastikan tidak ada error/regresi; skema warna diuji melalui inspeksi CSS.
2. Rekomendasi untuk rilis tetap: isi `APP_BASE_URL` produksi, ganti password admin, data resmi Diskominfo, aktifkan HTTPS + pastikan `mod_deflate`/`mod_expires` aktif di server.