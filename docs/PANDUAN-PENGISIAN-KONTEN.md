# Panduan Pengisian Konten — Admin Portal PKL & Magang Diskominfo

Panduan praktis untuk tim pengelola mengisi konten melalui panel admin.
Semua item di bawah hanya boleh diisi dengan **data resmi** dari Diskominfo Provinsi Banten.

## 0. Login

1. Buka `http://localhost/diskominfo-pkl/admin/` (di produksi: `https://domain/admin/`).
2. Masukkan username & password admin/editor.
3. Menu ada di sidebar kiri. Setiap halaman CMS memiliki tombol **Tambah** dan aksi **Edit/Hapus**.

> Konten tidak boleh berisi data fiktif (nama/NIP/persyaratan/kuota/jadwal yang tidak resmi).
> Bila data belum tersedia, biarkan placeholder yang sudah ada — jangan menghapusnya.

## 1. Pengumuman (admin > Pengumuman)

- **Tambah** → isi judul, isi pengumuman, tanggal terbit.
- Hanya terbitkan pengumuman resmi (contoh: pengumuman penerimaan PKL/Magang,
  jadwal seleksi, hasil seleksi).
- Belum ada pengumuman resmi → biarkan kosong (frontend menampilkan pesan kosong yang jelas).

## 2. Album Dokumentasi (admin > Album, lalu menu Foto)

- **Tambah Album** → judul, deskripsi singkat, tanggal kegiatan, foto sampul (cover).
- Setelah album dibuat, buka album → **Upload Foto** (bisa multi; otomatis di-resize dan
  dibuat thumbnail-nya). Isi keterangan (caption) per foto bila perlu.
- **Terbitkan album** (status published) agar tampil di halaman Dokumentasi publik.
- Foto harus dokumentasi resmi kegiatan Diskominfo; jangan gunakan foto orang lain.

## 3. Dokumen (admin > Dokumen)

- **Tambah** → judul, deskripsi, kategori, file PDF/Word/Excel/ZIP (maks 5 MB).
- Contoh: panduan PKL/Magang, formulir pendaftaran, template surat, laporan resmi.
- File yang diunggah tersedia di halaman Download publik (`?page=dokumen`).

## 4. Foto & Video Album (admin > Dokumentasi > pilih album > Media)

- **Unggah** → bisa campur foto (jpg/jpeg/png/webp, maks 5 MB) dan video (mp4/webm/ogg, maks 40 MB) sekaligus.
- Hanya dokumentasi resmi kegiatan PKL/Magang.
- Video tampil di galeri album publik dengan pemutar bawaan browser; foto tetap pakai lightbox.

## 5. Halaman (admin > Halaman)

Halaman statis CMS; seksi Profil/Layanan (slug `profil-program`, `tujuan`, `visi-misi`,
`informasi-pkl`, `informasi-magang`, `persyaratan-*`, `alur-pendaftaran`) ditampilkan
via `?page=profil` / `?page=layanan`.

Yang perlu diisi saat data resmi tersedia:

- **Informasi PKL / Informasi Magang** — ketentuan pelaksanaan, jadwal, kuota (bila resmi).
- **Persyaratan PKL / Persyaratan Magang** — daftar persyaratan resmi.
- **Alur Pendaftaran** (`alur-pendaftaran`) — langkah-langkah pendaftaran resmi.

Halaman CMS mandiri (Tugas & Fungsi, Unit Kerja, Sejarah Dinas, Informasi Pelayanan,
PPID, Informasi Setiap Saat, Profil Pejabat) sudah terisi dari website resmi —
cukup **verifikasi** isinya dan perbarui `source_url` bila sumber berubah.

## 6. FAQ (admin > FAQ)

- Kategori **"Tentang Diskominfo"** sudah berisi 3 jawaban resmi (tugas, alamat, kontak).
- Kategori **"Umum"** berisi placeholder PKL/Magang — ganti jawabannya hanya jika
  ketentuan resmi sudah dikonfirmasi (contoh: durasi, dokumen, sertifikat).

## 7. Pengaturan (admin > Pengaturan)

- `contact_address`, `contact_email`, `contact_phone` — sudah data resmi; jangan diubah tanpa konfirmasi.
- `social_facebook` / `social_youtube` — saat ini mengikuti footer website resmi
  Diskominfo; bila tidak diakui resmi, setel ke `#`.
- `social_instagram` — sudah URL resmi; jangan diubah.

## 8. Verifikasi Pejabat (halaman "Profil Pejabat")

- Daftar pejabat struktural diambil dari laman Profil Pegawai website resmi.
- Periksa berkala: nama, pangkat/golongan, dan jabatan; perbarui via admin > Halaman > `profil-pejabat`.

## 9. Backup (sudah otomatis & terverifikasi)

- Task Scheduler Windows: **"Backup Diskominfo PKL"** — harian **02:30** ke `C:\backup\diskominfo-pkl`
  (dump SQL + arsip `uploads/`, retensi 30 hari).
- Sudah terverifikasi: `schtasks /run /tn "Backup Diskominfo PKL"` berhasil (17-08-2026), restore dump teruji.
- Salinan off-site (Google Drive/HDD eksternal) dilakukan pengelola.
- Cek berkala: `C:\backup\diskominfo-pkl\backup-task.log`.