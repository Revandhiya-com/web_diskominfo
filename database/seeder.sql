-- ============================================================
-- SEED DATA MINIMAL — placeholder pengembangan & instalasi.
--
-- PERHATIAN:
-- 1. Seluruh baris di file ini adalah PLACEHOLDER/Data CONTOH.
--    Belum ada data resmi dari Diskominfo Provinsi Banten.
-- 2. Tidak berisi konten dinamis (berita, agenda, pengumuman,
--    galeri, video, dokumen) — semua dikosongkan agar tidak ada
--    konten contoh yang terlihat seperti data resmi.
-- 3. Login admin (WAJIB GANTI password segera setelah install):
--    username "admin" / password "demo1234"
-- ============================================================

USE diskominfo_pkl;

-- ------------------------------------------------------------
-- USER ADMIN (password: demo1234 — WAJIB GANTI SEBELUM PUBLIKASI)
-- ------------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `is_active`, `remember_token`, `last_login_at`, `created_at`, `updated_at`) VALUES (1,'Admin Portal','admin','admin@example.invalid','$2y$10$pjNsXPHJ/45k.zafLr9OteNX9KqEsUwssAUiCQkUvxfmb21Rjunh6','admin',1,NULL,NULL,NOW(),NOW());

-- ------------------------------------------------------------
-- SETTINGS SITUS (placeholder — ganti dengan data resmi)
-- ------------------------------------------------------------
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (1,'site_tagline','Portal Informasi PKL & Magang');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (2,'hero_title','Ruang Tumbuh Talenta Digital');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (3,'hero_subtitle','Diskominfo Provinsi Banten - KP3B Serang');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (4,'hero_description','Satu ruang untuk belajar, berkarya, dan membangun layanan digital yang berdampak bagi Banten.');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (5,'hero_btn_primary','Mulai Eksplorasi');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (6,'hero_btn_primary_link','?page=layanan');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (7,'hero_btn_secondary','Informasi Magang');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (8,'hero_btn_secondary_link','?page=layanan');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (9,'about_title','Tentang Program');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (10,'about_content','Deskripsi resmi program PKL dan Magang di lingkungan Diskominfo Provinsi Banten akan ditampilkan di sini setelah data resmi diterima.');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (11,'about_btn','Pelajari Lebih Lanjut');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (12,'about_btn_link','?page=profil');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (13,'cta_title','Siap Memulai PKL atau Magang?');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (14,'cta_text','Pelajari persyaratan, alur pendaftaran, dan dokumen yang dibutuhkan sebelum mengajukan permohonan.');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (15,'cta_btn','Hubungi Kami');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (16,'cta_btn_link','?page=kontak');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (17,'contact_address','Alamat resmi Diskominfo Provinsi Banten akan ditampilkan setelah konfirmasi data resmi.');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (18,'contact_email','email.resmi@bantenprov.go.id');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (19,'contact_phone','(0254) 000-0000');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (20,'contact_map_link','https://maps.google.com');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (21,'social_facebook','#');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (22,'social_instagram','#');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (23,'social_twitter','#');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (24,'social_youtube','#');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (25,'footer_about','Portal informasi, layanan, berita, dan dokumentasi kegiatan Praktik Kerja Lapangan dan Magang di lingkungan Diskominfo Provinsi Banten.');
INSERT INTO `settings` (`id`, `key`, `value`) VALUES (26,'privacy_note','Kebijakan privasi akan ditampilkan setelah data resmi diterima.');

-- ------------------------------------------------------------
-- KATEGORI BERITA
-- ------------------------------------------------------------
INSERT INTO `news_categories` (`id`, `name`, `slug`) VALUES (1,'Berita','berita');
INSERT INTO `news_categories` (`id`, `name`, `slug`) VALUES (2,'Kegiatan','kegiatan');
INSERT INTO `news_categories` (`id`, `name`, `slug`) VALUES (3,'Artikel','artikel');
INSERT INTO `news_categories` (`id`, `name`, `slug`) VALUES (4,'Prestasi','prestasi');

-- ------------------------------------------------------------
-- KATEGORI ALBUM
-- ------------------------------------------------------------
INSERT INTO `album_categories` (`id`, `name`, `slug`) VALUES (1,'Workshop','workshop');
INSERT INTO `album_categories` (`id`, `name`, `slug`) VALUES (2,'Pelatihan','pelatihan');
INSERT INTO `album_categories` (`id`, `name`, `slug`) VALUES (3,'Seminar','seminar');
INSERT INTO `album_categories` (`id`, `name`, `slug`) VALUES (4,'Kegiatan PKL','kegiatan-pkl');
INSERT INTO `album_categories` (`id`, `name`, `slug`) VALUES (5,'Kegiatan Magang','kegiatan-magang');

-- ------------------------------------------------------------
-- KATEGORI DOKUMEN
-- ------------------------------------------------------------
INSERT INTO `document_categories` (`id`, `name`, `slug`) VALUES (1,'Panduan','panduan');
INSERT INTO `document_categories` (`id`, `name`, `slug`) VALUES (2,'Formulir','formulir');
INSERT INTO `document_categories` (`id`, `name`, `slug`) VALUES (3,'Surat','surat');
INSERT INTO `document_categories` (`id`, `name`, `slug`) VALUES (4,'Template','template');
INSERT INTO `document_categories` (`id`, `name`, `slug`) VALUES (5,'Laporan','laporan');

-- ------------------------------------------------------------
-- HALAMAN STATIS (placeholder — isi konten resmi lewat CMS)
-- ------------------------------------------------------------
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (1,'Profil Program PKL & Magang','profil-program','<p>Portal ini merupakan pusat informasi resmi kegiatan Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten.</p><p>Deskripsi resmi program akan ditampilkan di sini setelah data diterima dan dikonfirmasi oleh tim pengelola.</p>','published',NULL,NOW());
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (2,'Tujuan Program','tujuan','<ul><li>Menyediakan informasi terpusat mengenai PKL dan Magang bagi calon peserta, peserta aktif, alumni, dan masyarakat.</li><li>Menyediakan dokumentasi kegiatan secara terstruktur.</li><li>Menjadi media publikasi dan arsip digital kegiatan.</li></ul><p>Daftar tujuan resmi akan disempurnakan setelah konfirmasi data dari Diskominfo Provinsi Banten.</p>','published',NULL,NOW());
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (3,'Visi & Misi','visi-misi','<p>Visi dan misi program akan ditampilkan di sini setelah data resmi diterima dari Diskominfo Provinsi Banten.</p>','published',NULL,NOW());
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (5,'Pembimbing','pembimbing','<p>Daftar pembimbing program akan ditampilkan di sini setelah data resmi diterima dari Diskominfo Provinsi Banten.</p>','published',NULL,NOW());
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (6,'Informasi PKL','informasi-pkl','<p>Praktik Kerja Lapangan (PKL) merupakan kegiatan pembelajaran bagi siswa/mahasiswa untuk mengenal lingkungan kerja secara langsung.</p><p>Informasi lengkap mengenai mekanisme resmi pelaksanaan PKL akan ditampilkan setelah konfirmasi data dari Diskominfo Provinsi Banten.</p>','published',NULL,NOW());
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (7,'Informasi Magang','informasi-magang','<p>Magang merupakan kegiatan pengembangan kompetensi melalui praktik kerja nyata di lingkungan instansi.</p><p>Informasi lengkap mengenai mekanisme resmi pelaksanaan Magang akan ditampilkan setelah konfirmasi data dari Diskominfo Provinsi Banten.</p>','published',NULL,NOW());
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (8,'Persyaratan PKL','persyaratan-pkl','<p>Persyaratan resmi pengajuan PKL akan ditampilkan di sini setelah dikonfirmasi oleh tim pengelola Diskominfo Provinsi Banten.</p>','published',NULL,NOW());
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (9,'Persyaratan Magang','persyaratan-magang','<p>Persyaratan resmi pengajuan Magang akan ditampilkan di sini setelah dikonfirmasi oleh tim pengelola Diskominfo Provinsi Banten.</p>','published',NULL,NOW());
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (10,'Alur Pendaftaran','alur-pendaftaran','<ol><li>Pengajuan permohonan oleh calon peserta.</li><li>Verifikasi berkas oleh tim pengelola.</li><li>Persetujuan dan penempatan.</li><li>Pelaksanaan kegiatan.</li><li>Evaluasi dan penyelesaian.</li></ol><p>Urutan dan ketentuan resmi alur pendaftaran akan dikonfirmasi oleh tim pengelola sebelum dipublikasikan.</p>','published',NULL,NOW());
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `updated_at`, `created_at`) VALUES (36,'Pengelola Program','pengelola','<p>Struktur dan nama tim pengelola program akan ditampilkan di sini setelah data resmi diterima dari Diskominfo Provinsi Banten.</p>','published',NULL,NOW());

-- ------------------------------------------------------------
-- FAQ (placeholder — jawaban menunggu konfirmasi data resmi)
-- ------------------------------------------------------------
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `is_active`, `created_at`) VALUES (1,'Bagaimana cara mengajukan permohonan PKL?','Tata cara resmi pengajuan PKL akan ditampilkan setelah konfirmasi data dari Diskominfo Provinsi Banten.','Umum',1,1,NOW());
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `is_active`, `created_at`) VALUES (2,'Dokumen apa saja yang perlu disiapkan?','Daftar dokumen resmi akan ditampilkan setelah konfirmasi data dari Diskominfo Provinsi Banten.','Umum',2,1,NOW());
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `is_active`, `created_at`) VALUES (3,'Berapa lama pelaksanaan PKL/Magang?','Durasi pelaksanaan mengikuti ketentuan resmi instansi dan akan diumumkan kemudian.','Umum',3,1,NOW());
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `is_active`, `created_at`) VALUES (4,'Apakah peserta memperoleh sertifikat?','Informasi mengenai sertifikat akan ditampilkan setelah konfirmasi data resmi.','Umum',4,1,NOW());
