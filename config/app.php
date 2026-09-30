<?php
/**
 * Konfigurasi utama aplikasi (non-rahasia).
 * Ubah nilai di file ini sesuai kebutuhan, tanpa menyentuh kode lain.
 */

// Nama aplikasi / portal
define('APP_NAME', 'Portal PKL & Magang Diskominfo Provinsi Banten');
define('APP_NAME_SHORT', 'PKL & Magang');

// Base URL: biarkan null untuk deteksi otomatis dari server.
// UNTUK GO-LIVE: isi manual dengan domain resmi, contoh:
// 'https://pkl.diskominfo.bantenprov.go.id'
// CATATAN: bila diisi, seluruh tautan memakai domain ini — preview di
// localhost akan ikut mengarah ke domain tersebut sampai domain aktif.
define('APP_BASE_URL', null);

// Zona waktu aplikasi (semua timestamp disimpan & ditampilkan dalam zona ini)
define('APP_TIMEZONE', 'Asia/Jakarta');

// Mode debug: true = tampilkan detail error.
define('APP_DEBUG', getenv('APP_DEBUG') ? (getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1') : false);

// Versi aset untuk cache busting (ubah saat memperbarui CSS/JS)
define('APP_ASSET_VERSION', '1.8.0');

// Nama session aplikasi
define('SESSION_NAME', 'diskominfo_pkl_session');

// Lokasi folder upload (jalur absolut di dalam project)
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// Lokasi folder log (jalur absolut di dalam project)
define('LOGS_PATH', BASE_PATH . '/logs');

// Batas ukuran file upload (dalam byte) — 5 MB per file
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);

// Batas ukuran file video upload (dalam byte) — 100 MB per file.
// CATATAN SERVER: upload_max_filesize & post_max_size di php.ini harus >= nilai ini
// (lihat README bagian konfigurasi upload).
define('UPLOAD_VIDEO_MAX_SIZE', 100 * 1024 * 1024);

// Ekstensi file yang diizinkan
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_DOCUMENT_EXT', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip']);
define('ALLOWED_VIDEO_EXT', ['mp4', 'webm', 'ogg', 'mov']);

// Halaman default
define('DEFAULT_PAGE', 'home');