-- ============================================================
-- Portal PKL & Magang Diskominfo Provinsi Banten
-- Konten resmi: diisi dari website resmi Diskominfo Provinsi Banten
-- Sumber: https://diskominfo.bantenprov.go.id/ (halaman tercantum per baris)
-- Catatan: konten merupakan RINGKASAN (bukan salinan persis), fakta dipertahankan,
-- URL sumber disimpan pada kolom source_url. Jalankan setelah seeder.sql.
-- File ini IDEMPOTENT — aman dijalankan ulang.
-- ============================================================

USE diskominfo_pkl;

-- ------------------------------------------------------------
-- SETTINGS — sosial media & teks footer/home dari sumber resmi
-- ------------------------------------------------------------
UPDATE settings SET `value` = 'https://www.instagram.com/diskominfospprovbanten' WHERE `key` = 'social_instagram';
UPDATE settings SET `value` = 'https://www.facebook.com/ppiddiskominfobanten'          WHERE `key` = 'social_facebook';
UPDATE settings SET `value` = '#'                                                     WHERE `key` = 'social_twitter';
UPDATE settings SET `value` = 'https://www.youtube.com/channel/UCtCKKGCncMz0BoLEkNYfRLQ' WHERE `key` = 'social_youtube';

UPDATE settings SET `value` = 'Portal informasi dan layanan Praktik Kerja Lapangan (PKL) serta Magang di lingkungan Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten. Seluruh konten informatif bersumber dari website resmi Diskominfo Provinsi Banten dan dapat diperbarui oleh tim pengelola.' WHERE `key` = 'footer_about';

UPDATE settings SET `value` = 'Dinas Komunikasi, Informatika, Statistik dan Persandian (Diskominfo) Provinsi Banten dibentuk berdasarkan Peraturan Daerah Nomor 8 Tahun 2016 dan berperan sebagai leading sector penyelenggaraan pemerintahan berbasis elektronik (e-Government) di lingkungan Pemerintah Provinsi Banten. Portal ini menyediakan informasi resmi mengenai program Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo.' WHERE `key` = 'about_content';

-- ------------------------------------------------------------
-- PAGES — seksi halaman Profil & Layanan (update konten yang ada)
-- ------------------------------------------------------------
UPDATE pages SET
    title = 'Profil Program',
    content = '
<p>Dinas Komunikasi, Informatika, Statistik dan Persandian (Diskominfo) Provinsi Banten terbentuk berdasarkan Peraturan Daerah Nomor 8 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Provinsi Banten. Sebelumnya, urusan komunikasi dan informatika di Provinsi Banten dilaksanakan oleh Biro Humas dan Protokol Setda, Bagian Kominfo pada Dinas Perhubungan, Komunikasi dan Informatika (Dishubkominfo), serta Sekretariat Komisi Penyiaran Indonesia Daerah (KPID) Banten.</p>
<p>Sejak awal tahun 2017, Diskominfo Provinsi Banten menjadi organisasi perangkat daerah yang menangani bidang komunikasi, informatika, statistik, dan persandian, serta menjadi leading sector penyelenggaraan pemerintahan berbasis elektronik (e-Government) di lingkungan Pemerintah Provinsi Banten.</p>
<p>Portal ini merupakan media informasi resmi program Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten. Informasi mengenai mekanisme, persyaratan, dan alur pendaftaran akan diperbarui oleh tim pengelola setelah memperoleh konfirmasi resmi dari instansi.</p>',
    source_url = 'https://diskominfo.bantenprov.go.id/selayang-pandang'
WHERE slug = 'profil-program';

UPDATE pages SET
    title = 'Tujuan Program',
    content = '
<p>Informasi resmi mengenai tujuan program Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten akan ditampilkan di sini setelah diterima dan dikonfirmasi oleh tim pengelola.</p>
<p>Sebagai gambaran, Diskominfo Provinsi Banten mendukung terwujudnya pemerintahan berbasis elektronik dan pemanfaatan teknologi informasi dan komunikasi di Provinsi Banten. Informasi lebih lanjut dapat disampaikan melalui Help Desk 0821 1777 2226 atau email diskominfo@bantenprov.go.id.</p>',
    source_url = NULL
WHERE slug = 'tujuan';

UPDATE pages SET
    title = 'Visi & Misi Diskominfo',
    content = '
<h3>Visi</h3>
<p>&ldquo;BANTEN MAJU, ADIL MERATA TIDAK KORUPSI&rdquo;</p>
<h3>Misi</h3>
<ol>
    <li>Mewujudkan masyarakat bermoral Pancasila dan demokratis berlandaskan iman dan taqwa melalui reformasi birokrasi yang berintegritas, adaptif dan tangguh;</li>
    <li>Mendorong kemajuan ekonomi secara inklusif melalui penguatan sektor kreatif, unggulan dan potensial;</li>
    <li>Mewujudkan sumber daya manusia yang berintegritas, berdaya saing, berkualitas, inovatif dan tidak diskriminatif;</li>
    <li>Mewujudkan pemerataan pembangunan wilayah yang didukung infrastruktur berkualitas;</li>
    <li>Mewujudkan pembangunan berkelanjutan secara holistik dan resiliensi terhadap bencana.</li>
</ol>',
    source_url = 'https://diskominfo.bantenprov.go.id/visi-misi'
WHERE slug = 'visi-misi';

UPDATE pages SET
    title = 'Pengelola Program',
    content = '
<p>Struktur unit kerja Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten berdasarkan Peraturan Gubernur Nomor 48 Tahun 2025 tentang Perubahan atas Peraturan Gubernur Nomor 48 Tahun 2022, terdiri atas:</p>
<ul>
    <li>Sekretaris Dinas;</li>
    <li>Kepala Sub Bagian Umum dan Kepegawaian;</li>
    <li>Kepala Bidang Pengelolaan Informasi dan Kemitraan Komunikasi;</li>
    <li>Kepala Bidang Ekosistem Pemerintahan Digital;</li>
    <li>Kepala Bidang Statistik, Persandian dan Keamanan Informasi;</li>
    <li>Kelompok Jabatan Fungsional;</li>
    <li>Unit Pelaksana Teknis Daerah (UPTD).</li>
</ul>
<p>Daftar nama tim pengelola program PKL dan Magang akan ditampilkan setelah dikonfirmasi oleh Diskominfo Provinsi Banten.</p>',
    source_url = 'https://diskominfo.bantenprov.go.id/unit-kerja'
WHERE slug = 'pengelola';

UPDATE pages SET
    title = 'Pembimbing',
    content = '
<p>Daftar pembimbing program Praktik Kerja Lapangan (PKL) dan Magang akan ditampilkan di sini setelah data resmi diterima dan dikonfirmasi oleh tim pengelola Diskominfo Provinsi Banten.</p>
<p>Pertanyaan terkait pembimbing dapat diajukan melalui Help Desk 0821 1777 2226 atau email diskominfo@bantenprov.go.id.</p>',
    source_url = NULL
WHERE slug = 'pembimbing';

UPDATE pages SET
    title = 'Informasi PKL',
    content = '
<p>Informasi resmi mengenai pelaksanaan Praktik Kerja Lapangan (PKL) di lingkungan Diskominfo Provinsi Banten akan ditampilkan di sini setelah data resmi diterima dan dikonfirmasi oleh tim pengelola.</p>
<p>Untuk pertanyaan seputar PKL, silakan hubungi Help Desk 0821 1777 2226 atau email diskominfo@bantenprov.go.id.</p>',
    source_url = NULL
WHERE slug = 'informasi-pkl';

UPDATE pages SET
    title = 'Informasi Magang',
    content = '
<p>Informasi resmi mengenai pelaksanaan Magang di lingkungan Diskominfo Provinsi Banten akan ditampilkan di sini setelah data resmi diterima dan dikonfirmasi oleh tim pengelola.</p>
<p>Untuk pertanyaan seputar Magang, silakan hubungi Help Desk 0821 1777 2226 atau email diskominfo@bantenprov.go.id.</p>',
    source_url = NULL
WHERE slug = 'informasi-magang';

UPDATE pages SET
    title = 'Persyaratan PKL',
    content = '
<p>Persyaratan resmi pengajuan Praktik Kerja Lapangan (PKL) akan ditampilkan di sini setelah dikonfirmasi oleh tim pengelola Diskominfo Provinsi Banten.</p>
<p>Saat ini informasi persyaratan resmi belum tersedia untuk dipublikasikan. Mohon pantau halaman ini atau hubungi Help Desk 0821 1777 2226.</p>',
    source_url = NULL
WHERE slug = 'persyaratan-pkl';

UPDATE pages SET
    title = 'Persyaratan Magang',
    content = '
<p>Persyaratan resmi pengajuan Magang akan ditampilkan di sini setelah dikonfirmasi oleh tim pengelola Diskominfo Provinsi Banten.</p>
<p>Saat ini informasi persyaratan resmi belum tersedia untuk dipublikasikan. Mohon pantau halaman ini atau hubungi Help Desk 0821 1777 2226.</p>',
    source_url = NULL
WHERE slug = 'persyaratan-magang';

UPDATE pages SET
    title = 'Alur Pendaftaran',
    content = '
<li><strong>Menunggu konfirmasi data resmi</strong> — alur pendaftaran akan ditampilkan setelah tim pengelola Diskominfo Provinsi Banten mengisi halaman ini.</li>
<li>Untuk pertanyaan seputar pendaftaran, silakan hubungi Help Desk 0821 1777 2226 atau email diskominfo@bantenprov.go.id.</li>',
    source_url = NULL
WHERE slug = 'alur-pendaftaran';

-- ------------------------------------------------------------
-- PAGES BARU — Tugas & Fungsi, Unit Kerja, Informasi Pelayanan, PPID, Profil Pejabat
-- ------------------------------------------------------------
INSERT INTO pages (title, slug, content, source_url, status) VALUES
('Tugas dan Fungsi', 'tugas-dan-fungsi', '
<p>Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten terbentuk berdasarkan Peraturan Daerah Nomor 8 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Provinsi Banten.</p>
<p>Dalam melaksanakan tugasnya, Dinas menyelenggarakan fungsi dan wewenang pengelolaan informasi dan komunikasi publik Pemerintah Daerah Provinsi, pengelolaan nama domain dan sub domain di lingkup Pemerintah Daerah provinsi, pengelolaan e-Government di lingkup Pemerintah Daerah provinsi, penyelenggaraan statistik sektoral, penetapan pola komunikasi sandi antar perangkat daerah provinsi, serta pelaksanaan tugas lain yang diberikan oleh Gubernur.</p>
<p>Berdasarkan Peraturan Gubernur Banten Nomor 48 Tahun 2022 pasal 38 ayat (2), tugas Dinas Kominfo antara lain:</p>
<ol>
    <li>Pengelolaan informasi dan komunikasi publik;</li>
    <li>Pengelolaan aplikasi informatika;</li>
    <li>Penyelenggaraan persandian untuk pengamanan informasi;</li>
    <li>Terselenggaranya statistik sektoral; dan</li>
    <li>Pelaksanaan fungsi lain yang diberikan oleh Gubernur terkait dengan tugas dan fungsinya sesuai peraturan perundang-undangan.</li>
</ol>
<p>Ketentuan terbaru mengenai kedudukan, susunan organisasi, tugas pokok dan fungsi diatur melalui Peraturan Gubernur Banten Nomor 48 Tahun 2025.</p>', 'https://diskominfo.bantenprov.go.id/tugas-dan-fungsi', 'published'),

('Unit Kerja', 'unit-kerja', '
<p>Berdasarkan Peraturan Gubernur Nomor 48 Tahun 2025 tentang Perubahan atas Peraturan Gubernur Nomor 48 Tahun 2022 tentang Kedudukan, Susunan Organisasi, Tugas Pokok dan Fungsi, Uraian Tugas, dan Tata Kerja Dinas Daerah, unit kerja Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten terdiri atas:</p>
<ol>
    <li>Sekretaris Dinas;</li>
    <li>Kepala Sub Bagian Umum dan Kepegawaian;</li>
    <li>Kepala Bidang Pengelolaan Informasi dan Kemitraan Komunikasi;</li>
    <li>Kepala Bidang Ekosistem Pemerintahan Digital;</li>
    <li>Kepala Bidang Statistik, Persandian dan Keamanan Informasi;</li>
    <li>Kelompok Jabatan Fungsional;</li>
    <li>Unit Pelaksana Teknis Daerah (UPTD).</li>
</ol>', 'https://diskominfo.bantenprov.go.id/unit-kerja', 'published'),

('Informasi Pelayanan', 'informasi-pelayanan', '
<p>Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten memiliki bidang dan sekretariat yang memberikan pelayanan terkait tugasnya masing-masing, yaitu:</p>
<ol>
    <li>Bidang Ekosistem Pemerintahan Digital;</li>
    <li>Bidang Pengelolaan Informasi dan Kemitraan Komunikasi;</li>
    <li>Bidang Statistik, Persandian dan Keamanan Informasi;</li>
    <li>Sekretariat.</li>
</ol>
<p>Daftar lengkap jenis layanan Diskominfo Provinsi Banten tersedia pada laman Informasi Pelayanan di website resmi Diskominfo Provinsi Banten (tautan pada bagian Sumber halaman ini).</p>', 'https://diskominfo.bantenprov.go.id/informasi-pelayanan', 'published'),

('PPID — Keterbukaan Informasi Publik', 'ppid', '
<p>Pejabat Pengelola Informasi dan Dokumentasi (PPID) Pelaksana Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten memberikan layanan informasi publik sesuai Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik dan peraturan pelaksanaannya.</p>
<h3>Kategori Informasi Berkala</h3>
<p>Informasi berkala yang disediakan antara lain meliputi: profil badan publik, laporan harta kekayaan pejabat negara (LHKPN), renja dan renstra, RKA dan DPA, informasi peraturan dan kebijakan, ringkasan laporan keuangan, ringkasan laporan akses informasi publik, LKIP, tata cara pengaduan (SP4N LAPOR!), serta layanan permohonan dan keberatan informasi publik.</p>
<h3>Alamat PPID Pelaksana</h3>
<p>Jl. Syech Nawawi Al-Bantani, KP3B (Kawasan Pusat Pemerintah Provinsi Banten), Area Gedung Baru OPD Provinsi Banten, Kecamatan Curug, Kota Serang, Provinsi Banten.</p>
<ul>
    <li>Email: diskominfo@bantenprov.go.id</li>
    <li>Telepon: (0254) 7824104</li>
</ul>
<p>Permohonan informasi juga dapat disampaikan melalui aplikasi SP4N LAPOR! (lapor.go.id). Detail dokumen informasi berkala tersedia pada laman Informasi Berkala di website resmi Diskominfo Provinsi Banten.</p>', 'https://diskominfo.bantenprov.go.id/informasi-berkala', 'published'),

('Profil Pejabat Diskominfo', 'profil-pejabat', '
<p>Berikut pejabat struktural Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten sebagaimana tercantum pada laman Profil Pegawai Diskominfo:</p>
<ol>
    <li><strong>Beni Ismail, S.STP., M.Si.</strong> — Kepala Dinas (Pembina Utama Muda, IV/C);</li>
    <li><strong>H. Karna Wijaya, SH., MH.</strong> — Sekretaris Dinas (Pembina Tk. I, IV/b);</li>
    <li><strong>Trenggono Wahyu Pratomo, SE., M.Si.</strong> — Kepala Sub Bagian Umum dan Kepegawaian (Pembina Tk. I, IV/b);</li>
    <li><strong>Sofyan Nuryaman, SH</strong> — Kepala Bidang Ekosistem Pemerintahan Digital (Penata Tk. I, III/d);</li>
    <li><strong>Akhmad Subhan Syafaat, SH.</strong> — Kepala Bidang Pengelolaan Informasi dan Kemitraan Komunikasi (Pembina Tk. I, IV/b);</li>
    <li><strong>Chobir Sabbaha, S.Si</strong> — Kepala Bidang Statistik, Persandian dan Keamanan Informasi (Penata Tk. I, III/d).</li>
</ol>
<p>Daftar lengkap pegawai (NIP, pangkat/golongan, dan jabatan fungsional) tersedia pada laman Profil Pegawai Diskominfo di website resmi Diskominfo Provinsi Banten (tautan pada bagian Sumber halaman ini).</p>', 'https://diskominfo.bantenprov.go.id/profil-pejabat-struktural', 'published')
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    content = VALUES(content),
    source_url = VALUES(source_url),
    status = VALUES(status);

-- ------------------------------------------------------------
-- NEWS — ringkasan berita resmi (fakta dipertahankan, bukan salinan)
-- ------------------------------------------------------------
INSERT INTO news (category_id, title, slug, excerpt, content, source_url, author_id, status, published_at) VALUES
(2, 'Kadis Kominfo Ikuti Presentasi Monev KIP', 'kadis-kominfo-ikuti-presentasi-monev-kip',
 'Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail mengikuti presentasi Monitoring dan Evaluasi (Monev) Keterbukaan Informasi Publik (KIP) yang dilaksanakan oleh Komisi Informasi Provinsi Banten.',
 '<p>Kota Serang &ndash; Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail mengikuti presentasi Monitoring dan Evaluasi (Monev) Keterbukaan Informasi Publik (KIP) yang dilaksanakan oleh Komisi Informasi Provinsi Banten, bertempat di KP3B, Kota Serang.</p>
<p>Kegiatan tersebut merupakan bagian dari evaluasi penyelenggaraan keterbukaan informasi publik di lingkungan Pemerintah Provinsi Banten.</p>',
 'https://diskominfo.bantenprov.go.id/berita/kadis-kominfo-ikuti-presentasi-monev-kip', 1, 'published', '2026-07-28 09:00:00'),

(2, 'Kadis Kominfo Hadiri Agenda Banten Tech Festival', 'kadis-kominfo-hadiri-agenda-banten-tech-festival',
 'Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail menghadiri agenda Banten Tech Festival yang diselenggarakan di Universitas Tangerang Raya.',
 '<p>Kota Serang &ndash; Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail menghadiri agenda Banten Tech Festival yang diselenggarakan di Universitas Tangerang Raya.</p>
<p>Kehadiran tersebut dalam rangka mendukung pengembangan ekosistem teknologi informasi dan komunikasi di Provinsi Banten.</p>',
 'https://diskominfo.bantenprov.go.id/berita/kadis-kominfo-hadiri-agenda-banten-tech-festival', 1, 'published', '2026-07-28 09:00:00'),

(2, 'Kadis Kominfo Hadiri Pelantikan Pemuda Muslimin', 'kadis-kominfo-hadiri-pelantikan-pemuda-muslimin',
 'Kepala Dinas Kominfo Provinsi Banten Beni Ismail mendampingi Wakil Gubernur Banten Achmad Dimiyati Natakusumah pada Pelantikan Pimpinan Wilayah Pemuda Muslimin Indonesia Provinsi Banten Periode 2026-2030.',
 '<p>Kota Serang &ndash; Kepala Dinas Kominfo Provinsi Banten Beni Ismail mendampingi Wakil Gubernur Banten Achmad Dimiyati Natakusumah dalam agenda Pelantikan Pimpinan Wilayah Pemuda Muslimin Indonesia Provinsi Banten Periode 2026-2030, bertempat di Lt. 3 Aula Kantor Dinas Kominfo Provinsi Banten, KP3B, Kota Serang, Sabtu (18/07/2026).</p>
<p>Pemuda Muslimin Indonesia merupakan organisasi kepemudaan yang didirikan pada tanggal 25 November 1928 di Yogyakarta.</p>',
 'https://diskominfo.bantenprov.go.id/berita/kadis-kominfo-hadiri-pelantikan-pemuda-muslimin', 1, 'published', '2026-07-28 09:00:00'),

(2, 'Senam Semangat Dinas Kominfo', 'senam-semangat-dinas-kominfo',
 'Kegiatan senam bersama di lingkungan Dinas Kominfo Provinsi Banten, KP3B, untuk menjaga kesehatan dan kebugaran pegawai.',
 '<p>Kota Serang &ndash; Senam bersama rutin dilaksanakan oleh pegawai Dinas Kominfo Provinsi Banten di area kantor Dinas Kominfo Provinsi Banten, KP3B, Kota Serang, pada Kamis (16/07/2026).</p>
<p>Kegiatan ini bertujuan menjaga kesehatan jasmani serta mempererat silaturahmi antar pegawai di lingkungan Diskominfo.</p>',
 'https://diskominfo.bantenprov.go.id/berita/senam-semangat-dinas-kominfo', 1, 'published', '2026-07-28 09:00:00'),

(3, 'Apa Itu Radikalisme dan Bahayanya?', 'apa-itu-radikalisme-dan-bahayanya',
 'Artikel edukasi tentang pengertian radikalisme, bahayanya bagi stabilitas sosial, regulasi terkait, serta kanal pengaduan konten radikal.',
 '<p>Radikalisme adalah paham atau ideologi yang menghendaki perubahan sosial, politik, atau sistem secara drastis atau ekstrem, seringkali menggunakan cara-cara kekerasan. Di era digital, paham ini kerap disebarkan melalui platform media sosial.</p>
<p>Bahaya radikalisme terletak pada potensinya merusak stabilitas sosial, memecah belah masyarakat, dan menimbulkan kerugian besar baik secara fisik maupun psikologis. Regulasi yang mengatur antara lain Undang-Undang Nomor 5 Tahun 2018 tentang Pemberantasan Tindak Pidana Terorisme, Peraturan BNPT Nomor 1 Tahun 2025 tentang Kontra Radikalisme, serta Undang-Undang Nomor 1 Tahun 2024 tentang Informasi dan Transaksi Elektronik.</p>
<p>Masyarakat dapat melaporkan konten yang mengarah pada radikalisme melalui kanal pengaduan seperti aduankonten.id dan lapor.go.id.</p>',
 'https://diskominfo.bantenprov.go.id/berita/apa-itu-radikalisme-dan-bahayanya', 1, 'published', '2025-11-17 09:00:00')
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id),
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    source_url = VALUES(source_url),
    author_id = VALUES(author_id),
    status = VALUES(status),
    published_at = VALUES(published_at);

-- ------------------------------------------------------------
-- AGENDAS — agenda resmi dari laman Event website Diskominfo
-- ------------------------------------------------------------
DELETE FROM agendas WHERE title IN (
    'Rapat Persiapan Kompetisi Inovasi Pelayanan Publik (KIPP) Tahun 2026',
    'Rapat Koordinasi Diskominfo Kab/Kota'
);

INSERT INTO agendas (title, description, location, source_url, start_datetime, end_datetime, status) VALUES
('Rapat Persiapan Kompetisi Inovasi Pelayanan Publik (KIPP) Tahun 2026',
 'Unsur Dinas Kominfo Statistik dan Persandian Provinsi Banten mengikuti rapat persiapan Kompetisi Inovasi Pelayanan Publik (KIPP) Tahun 2026.',
 'KP3B, Kota Serang',
 'https://diskominfo.bantenprov.go.id/event/diskominfo-ikuti-rapat-persiapan-kipp-tahun-2026',
 '2026-06-22 09:00:00', '2026-06-22 12:00:00', 'published'),

('Rapat Koordinasi Diskominfo Kab/Kota',
 'Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail membuka agenda Rapat Koordinasi Diskominfo Kab/Kota dalam rangka penyebarluasan informasi pembangunan.',
 'Aula Kantor Dinas Kominfo Provinsi Banten, KP3B',
 'https://diskominfo.bantenprov.go.id/event/rapat-koordinasi-diskominfo-kabkota',
 '2026-04-13 09:00:00', '2026-04-13 12:00:00', 'published');

-- ------------------------------------------------------------
-- PAGES — susunan organisasi terkini (Pergub 48/2025) termasuk Kepala Dinas
-- ------------------------------------------------------------
UPDATE pages SET
    title = 'Pengelola Program',
    content = '
<p>Struktur unit kerja Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten berdasarkan Peraturan Gubernur Nomor 48 Tahun 2025 tentang Perubahan atas Peraturan Gubernur Nomor 48 Tahun 2022, terdiri atas:</p>
<ol>
    <li>Kepala Dinas;</li>
    <li>Sekretaris Dinas;</li>
    <li>Kepala Sub Bagian Umum dan Kepegawaian;</li>
    <li>Kepala Bidang Pengelolaan Informasi dan Kemitraan Komunikasi;</li>
    <li>Kepala Bidang Ekosistem Pemerintahan Digital;</li>
    <li>Kepala Bidang Statistik, Persandian dan Keamanan Informasi;</li>
    <li>Kelompok Jabatan Fungsional;</li>
    <li>Unit Pelaksana Teknis Daerah (UPTD).</li>
</ol>
<p>Daftar nama tim pengelola program PKL dan Magang akan ditampilkan setelah dikonfirmasi oleh Diskominfo Provinsi Banten.</p>',
    source_url = 'https://diskominfo.bantenprov.go.id/unit-kerja'
WHERE slug = 'pengelola';

UPDATE pages SET
    title = 'Unit Kerja',
    content = '
<p>Berdasarkan Peraturan Gubernur Nomor 48 Tahun 2025 tentang Perubahan atas Peraturan Gubernur Nomor 48 Tahun 2022 tentang Kedudukan, Susunan Organisasi, Tugas Pokok dan Fungsi, Uraian Tugas, dan Tata Kerja Dinas Daerah, susunan organisasi Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten terdiri atas:</p>
<ol>
    <li>Kepala Dinas;</li>
    <li>Sekretaris Dinas;</li>
    <li>Kepala Sub Bagian Umum dan Kepegawaian;</li>
    <li>Kepala Bidang Pengelolaan Informasi dan Kemitraan Komunikasi;</li>
    <li>Kepala Bidang Ekosistem Pemerintahan Digital;</li>
    <li>Kepala Bidang Statistik, Persandian dan Keamanan Informasi;</li>
    <li>Kelompok Jabatan Fungsional;</li>
    <li>Unit Pelaksana Teknis Daerah (UPTD).</li>
</ol>',
    source_url = 'https://diskominfo.bantenprov.go.id/unit-kerja'
WHERE slug = 'unit-kerja';

-- ------------------------------------------------------------
-- PAGES BARU — Sejarah Dinas & Informasi Setiap Saat
-- ------------------------------------------------------------
INSERT INTO pages (title, slug, content, source_url, status) VALUES
('Sejarah Dinas', 'sejarah-dinas', '
<p>Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten terbentuk berdasarkan Peraturan Daerah Nomor 8 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Provinsi Banten. Sebelumnya, urusan komunikasi dan informatika di Provinsi Banten dilaksanakan oleh Biro Humas dan Protokol Setda serta Bagian Kominfo pada Dinas Perhubungan, Komunikasi dan Informatika (Dishubkominfo).</p>
<p>Dalam melaksanakan tugasnya, Dinas menyelenggarakan fungsi pengelolaan informasi dan komunikasi publik, pengelolaan nama domain yang ditetapkan Pemerintah Pusat dan sub domain di lingkup Pemerintah Daerah provinsi, pengelolaan e-Government, penyelenggaraan statistik sektoral, penetapan pola komunikasi sandi antar perangkat daerah provinsi, serta pelaksanaan tugas lain yang diberikan oleh Gubernur.</p>
<p>Struktur organisasi Diskominfo Provinsi Banten telah mengalami beberapa penyesuaian, antara lain melalui Peraturan Gubernur Nomor 83 Tahun 2016 (klasifikasi Dinas tipe B), Peraturan Gubernur Nomor 58 Tahun 2020 (perubahan nomenklatur), Peraturan Gubernur Nomor 48 Tahun 2022, dan terakhir Peraturan Gubernur Banten Nomor 48 Tahun 2025.</p>
<h3>Susunan Organisasi Terkini (Pergub Nomor 48 Tahun 2025)</h3>
<ol>
    <li>Kepala Dinas;</li>
    <li>Sekretaris Dinas;</li>
    <li>Kepala Sub Bagian Umum dan Kepegawaian;</li>
    <li>Kepala Bidang Pengelolaan Informasi dan Kemitraan Komunikasi;</li>
    <li>Kepala Bidang Ekosistem Pemerintahan Digital;</li>
    <li>Kepala Bidang Statistik, Persandian dan Keamanan Informasi;</li>
    <li>Kelompok Jabatan Fungsional;</li>
    <li>Unit Pelaksana Teknis Daerah (UPTD).</li>
</ol>
<h3>Kepala Dinas Kominfo dari Masa ke Masa</h3>
<ol>
    <li>Komari, S.Pd., M.M. (Januari 2017 &ndash; 2 Maret 2020);</li>
    <li>Ir. Hj. Eneng Nurcahyati (3 Maret 2020 &ndash; 31 Agustus 2022);</li>
    <li>Plt. Didi Hadiyatna, SE., M.M. (6 September 2022 &ndash; 2 Mei 2023);</li>
    <li>Plt. Nana Suryana, ST., M.Si. (2 Mei 2023 &ndash; 19 Februari 2025);</li>
    <li>Plt. Arif Agus Rakhman, S.IP., M.Si. (19 Februari 2025 &ndash; 3 November 2025);</li>
    <li>Beni Ismail, S.STP., M.Si. (3 November 2025 &ndash; saat ini).</li>
</ol>', 'https://diskominfo.bantenprov.go.id/sejarah-dinas', 'published'),

('Informasi Setiap Saat', 'informasi-setiap-saat', '
<p>Pejabat Pengelola Informasi dan Dokumentasi (PPID) Pelaksana Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten menyediakan informasi yang wajib disediakan dan diumumkan setiap saat sesuai Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik.</p>
<h3>Program Prioritas dan Maklumat Pelayanan</h3>
<ul>
    <li><a href="https://drive.google.com/file/d/1rhOGwMncsvZ4ct_ba3FAXFHsj0ofg1mT/view?usp=sharing" target="_blank" rel="noopener">Program Prioritas Pemerintah Provinsi Banten 2024-2025</a></li>
    <li><a href="https://drive.google.com/file/d/1dBKTov6_S5MMLaTOI_mN1ju9eJZJvj9h/view?usp=sharing" target="_blank" rel="noopener">Maklumat Pengaduan Pelayanan Publik</a></li>
</ul>
<h3>Daftar Informasi Publik</h3>
<p>Daftar Informasi Publik (DIP) Diskominfo dikelola oleh Sekretariat, tersedia dalam bentuk online di Kantor Diskominfo Lt. 1, KP3B, dengan ringkasan sebagai berikut:</p>
<table>
    <thead>
        <tr><th>No</th><th>Ringkasan Informasi</th><th>Jangka Waktu Retensi</th></tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Profil badan publik</td><td>5 tahun</td></tr>
        <tr><td>2</td><td>Ringkasan laporan keuangan</td><td>10 tahun</td></tr>
        <tr><td>3</td><td>Ringkasan program dan kegiatan</td><td>1 tahun</td></tr>
        <tr><td>4</td><td>Peraturan dan kebijakan terkait badan publik Diskominfo</td><td>5 tahun</td></tr>
        <tr><td>5</td><td>Informasi jumlah SDM ASN dan Non ASN badan publik</td><td>5 tahun</td></tr>
        <tr><td>6</td><td>Informasi peraturan organisasi badan publik</td><td>5 tahun</td></tr>
        <tr><td>7</td><td>Dokumen LKIP</td><td>5 tahun</td></tr>
        <tr><td>8</td><td>Dokumen rencana kerja badan publik</td><td>1 tahun</td></tr>
        <tr><td>9</td><td>Dokumen rencana strategis badan publik</td><td>5 tahun</td></tr>
        <tr><td>10</td><td>Profil pegawai Diskominfo</td><td>5 tahun</td></tr>
        <tr><td>11</td><td>Sejarah badan publik</td><td>2 tahun</td></tr>
        <tr><td>12</td><td>Tugas dan fungsi badan publik</td><td>5 tahun</td></tr>
        <tr><td>13</td><td>Informasi kepegawaian badan publik</td><td>2 tahun</td></tr>
        <tr><td>14</td><td>Ringkasan RKA dan DPA badan publik</td><td>1 tahun</td></tr>
        <tr><td>15</td><td>Informasi terkait data statistik</td><td>1 tahun</td></tr>
        <tr><td>16</td><td>LHKPN pejabat struktural</td><td>1 tahun</td></tr>
        <tr><td>17</td><td>Surat keputusan kegiatan di Diskominfo</td><td>2 tahun</td></tr>
        <tr><td>18</td><td>Informasi kanal-kanal resmi informasi di Provinsi Banten</td><td>2 tahun</td></tr>
        <tr><td>19</td><td>SAKIP</td><td>1 tahun</td></tr>
        <tr><td>20</td><td>Informasi layanan pembuatan tanda tangan elektronik</td><td>1 tahun</td></tr>
    </tbody>
</table>
<ul>
    <li><a href="https://drive.google.com/file/d/1a9GmBbEsp9qUG4bKNcZUuSpsNoLfBGuU/view?usp=drive_link" target="_blank" rel="noopener">SK Daftar Informasi Publik (DIP) 2025</a></li>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/20/sk-dip.pdf" target="_blank" rel="noopener">SK Daftar Informasi Publik (DIP) 2024</a></li>
</ul>
<h3>Kelembagaan dan Alur PPID</h3>
<ul>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/20/struktur-tim-kerja-ppid-pembantu-dinas-komunikasi-informatika-statistik-dan-persandaian-rev.png" target="_blank" rel="noopener">Visi Misi PPID Pelaksana Diskominfo</a></li>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/20/22_2.png" target="_blank" rel="noopener">Struktur Tim PPID Pelaksana Diskominfo</a></li>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/47/info-grapis-ppid.jpeg" target="_blank" rel="noopener">Infografis Alur Informasi Publik</a></li>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/53/2021-form-permintaan-inf-publik.pdf" target="_blank" rel="noopener">Form Permohonan Informasi Publik Diskominfo</a></li>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/20/tanda-terima-ppid.pdf" target="_blank" rel="noopener">Tanda Terima Permohonan Informasi Publik Diskominfo</a></li>
</ul>
<h3>Regulasi Terkait Keterbukaan Informasi</h3>
<ul>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/65/uu-nomor-14-tahun-2008-uu-nomor-14-tahun-2008.pdf" target="_blank" rel="noopener">UU Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik</a></li>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/66/permendagri-3-2017-ttg-ppid.pdf" target="_blank" rel="noopener">Permendagri Nomor 3 Tahun 2017 tentang PPID</a></li>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/67/perpres-2018-95-sistem-pemerintahan-berbasis-elektronik-spbe.pdf" target="_blank" rel="noopener">Perpres Nomor 95 Tahun 2018 tentang Sistem Pemerintahan Berbasis Elektronik</a></li>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/69/24perda-no-6-tahun-2018-1.pdf" target="_blank" rel="noopener">Perda Nomor 6 Tahun 2018 tentang Penyelenggaraan Komunikasi dan Informatika</a></li>
    <li><a href="https://dmsppid.bantenprov.go.id/upload/dms/65/1482381374-uu_19_tahun_2016_publish.pdf" target="_blank" rel="noopener">UU Nomor 19 Tahun 2016 tentang Informasi dan Transaksi Elektronik</a></li>
    <li><a href="https://drive.google.com/file/d/10dHfbrOuqpqh5Rbd7vnWReJts79HiH4t/view?usp=sharing" target="_blank" rel="noopener">UU Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi</a></li>
</ul>
<p>Permohonan informasi dapat disampaikan melalui aplikasi <a href="https://www.lapor.go.id/" target="_blank" rel="noopener">SP4N LAPOR! (lapor.go.id)</a>. Daftar lengkap dokumen tersedia pada laman Informasi Setiap Saat di website resmi Diskominfo Provinsi Banten.</p>', 'https://diskominfo.bantenprov.go.id/informasi-setiap-saat', 'published')
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    content = VALUES(content),
    source_url = VALUES(source_url),
    status = VALUES(status);

-- ------------------------------------------------------------
-- FAQ — jawaban yang didukung sumber resmi (idempotent)
-- ------------------------------------------------------------
DELETE FROM faqs WHERE question IN (
    'Apa tugas Diskominfo Provinsi Banten?',
    'Di mana alamat Diskominfo Provinsi Banten?',
    'Bagaimana cara menghubungi Diskominfo Provinsi Banten?'
);

INSERT INTO faqs (question, answer, category, sort_order, is_active) VALUES
('Apa tugas Diskominfo Provinsi Banten?',
 'Dinas Komunikasi, Informatika, Statistik dan Persandian (Diskominfo) Provinsi Banten melaksanakan urusan pemerintahan di bidang komunikasi, informatika, statistik dan persandian sesuai tugas dan fungsinya.
Informasi selengkapnya tersedia pada halaman Tugas dan Fungsi.',
 'Tentang Diskominfo', 1, 1),
('Di mana alamat Diskominfo Provinsi Banten?',
 'Jl. Syech Nawawi Al-Bantani, KP3B (Kawasan Pusat Pemerintahan Provinsi Banten), Area Gedung Baru OPD Pemprov Banten, Kota Serang, Provinsi Banten.',
 'Tentang Diskominfo', 2, 1),
('Bagaimana cara menghubungi Diskominfo Provinsi Banten?',
 'Email: diskominfo@bantenprov.go.id
Help Desk: 0821 1777 2226',
 'Tentang Diskominfo', 3, 1);
