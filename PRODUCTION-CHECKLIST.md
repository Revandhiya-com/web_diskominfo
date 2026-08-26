# PRODUCTION CHECKLIST — Portal PKL & Magang Diskominfo Provinsi Banten

Daftar periksa **sebelum situs go-live**. Centang hanya setelah diverifikasi oleh tim pengelola. Item yang memerlukan data resmi Diskominfo ditandai **[DATA RESMI]**.

## A. Keamanan & Konfigurasi

- [ ] `APP_DEBUG` bernilai `false` di `config/app.php` (sudah).
- [ ] `APP_BASE_URL` diisi domain produksi (mis. `https://pkl.diskominfo.bantenprov.go.id`) — **masih `null`**.
- [ ] Password admin **diganti** dari `demo1234` (jika instalasi memakai seeder) — gunakan password kuat (min. 12 karakter, kombinasi).
- [ ] `config/database.php` memakai akun DB khusus (bukan `root`), password kuat, hak terbatas pada `diskominfo_pkl.*`.
- [ ] Akun admin hanya yang diperlukan; akun editor dibuat per petugas dengan hak terbatas.
- [ ] HTTPS aktif + redirect HTTP→HTTPS (lihat README bagian 9).
- [ ] `robots.txt` berisi URL sitemap domain produksi — **masih `http://localhost/...`**.
- [ ] Folder `logs/`, `config/`, `helpers/`, `includes/`, `database/`, `docs/`, `pages/` tidak bisa diakses via web (sudah via `.htaccess` — verifikasi di server).
- [ ] `uploads/` tidak dapat mengeksekusi PHP (`.htaccess` sudah ada — verifikasi di server).

## B. Konten & Data [DATA RESMI]

- [ ] **Pengaturan Situs** lengkap & benar: tagline, hero, tentang, CTA, alamat, email, telepon, link peta. Kontak sudah terisi data resmi; **sosial media sudah terisi** (Instagram & Facebook & YouTube resmi dari website Diskominfo; Twitter/X = `#` karena profil resmi belum terverifikasi).
- [ ] **Halaman statis** (profil, tujuan, visi-misi, informasi PKL/Magang, persyaratan, alur, pembimbing, pengelola) — konten dari website resmi sudah diisi; bagian yang belum tersedia di sumber tetap placeholder jelas.
- [ ] **Halaman CMS mandiri** (Tugas & Fungsi, Unit Kerja, Sejarah Dinas, Informasi Pelayanan, PPID, Informasi Setiap Saat, Profil Pejabat) — sudah dibuat & terbit dengan link Sumber; verifikasi kembali isinya.
- [ ] **Berita/Artikel**: 5 berita resmi published (ringkasan + source_url); hapus/arsipkan draft demo bila dianggap perlu.
- [ ] **Agenda**: 2 agenda resmi published (dari laman Event); tambahkan agenda berjalan sesuai periode.
- [ ] **Pengumuman**: publish pengumuman resmi (saat ini 0 published).
- [ ] **Galeri**: upload dokumentasi resmi + publish album (saat ini 0 published; admin > Album).
- [ ] **Dokumen**: upload PDF resmi (panduan, formulir, template) via admin > Dokumen.
- [ ] **Video**: upload video resmi instansi (YouTube resmi) via admin > Video.
- [ ] **FAQ**: 3 jawaban resmi sudah diisi (tugas, alamat, kontak); sisanya (PKL/Magang) placeholder "menunggu konfirmasi data".
- [ ] **Pesan kontak masuk**: pastikan email admin dipantau; pengaturan notifikasi/pemrosesan disepakati tim pengelola.
- [ ] Nama/email pengguna admin bukan `admin@example.invalid`.

## C. Konten Organisasi [DATA RESMI]

- [ ] **Logo resmi Diskominfo**: file `assets/images/image.png` (logo yang diunggah pengelola) **sudah otomatis terpakai** di navbar, footer, dan favicon; untuk preferensi nama, taruh sebagai `logo-diskominfo.png` (prioritas pertama deteksi).
- [ ] Struktur organisasi / pengelola program (halaman "Pengelola Program" — sudah memuat unit kerja resmi; nama pengelola menunggu konfirmasi).
- [ ] Penanggung jawab & pembimbing resmi.
- [ ] Alamat resmi & jam layanan tercantum benar.
- [ ] Link sosial media resmi (Facebook, Instagram, Twitter/X, YouTube) — verifikasi URL sebelum diisi (Twitter/X masih `#`).

## D. SEO & Aksesibilitas

- [ ] `APP_BASE_URL` diisi (canonical, OG, sitemap otomatis benar).
- [ ] `robots.txt` sitemap URL benar.
- [ ] Sitemap `sitemap.php` mengembalikan XML valid di domain produksi.
- [ ] Google Search Console: daftarkan domain + submit sitemap.
- [ ] Uji halaman dengan validator aksesibilitas ringan (alt teks, label form, kontras) — struktur sudah disiapkan (skip link, alt, label).

## E. Performa & Stabilitas

- [ ] GZIP aktif (`mod_deflate`) — sudah diverifikasi di XAMPP; verifikasi di server produksi.
- [ ] Cache aset (`mod_expires`) aktif.
- [ ] Uji beban ringan: halaman home & berita < 2 detik di koneksi normal.
- [ ] Uji semua halaman 200 OK di domain produksi (termasuk 404 halaman salah).

## F. Backup & Operasional

- [x] `scripts/backup.php` dijalankan via Task Scheduler **"Backup Diskominfo PKL"** (harian 02:30) — wrapper `C:\backup\diskominfo-pkl\run-backup.bat`, log `backup-task.log`; **teruji: dump DB + zip uploads dibuat; restore teruji ke DB uji** (17 pages, 14 news, 26 settings). **Terverifikasi di sesi interaktif pengelola** (17-08-2026 20:13 — backup baru dibuat, exit code 0).
- [x] Backup disalin ke media di luar server (dikonfirmasi pengelola 17-08-2026) — folder sumber `C:\backup\diskominfo-pkl`.
- [ ] `logs/app.log` dipantau (min. mingguan) untuk error.
- [ ] Versi PHP & ekstensi (pdo_mysql, mbstring, gd, fileinfo, zip) sesuai di server.
- [ ] Jam server & zona waktu `Asia/Jakarta` benar (catatan: PHP CLI default `Europe/Berlin` — tidak memengaruhi aplikasi yang memakai `APP_TIMEZONE`).

## G. Dokumentasi & Serah Terima

- [ ] README.md + checklist ini diserahkan ke tim pengelola.
- [ ] **`docs/PANDUAN-PENGISIAN-KONTEN.md`** — panduan pengisian konten admin (pengumuman, album, dokumen, video, halaman, FAQ, pengaturan, backup) sudah disiapkan.
- [ ] Kredensial disimpan aman (password manager instansi), tidak di file.
- [ ] Daftar "hal yang perlu divalidasi Diskominfo" (bagian B/C) dikirim ke pengelola untuk pengisian konten.