<?php
/**
 * Konfigurasi utama aplikasi (non-rahasia).
 * Ubah nilai di file ini sesuai kebutuhan, tanpa menyentuh kode lain.
 */

// Nama aplikasi / portal
define('APP_NAME', 'Portal PKL & Magang Diskominfo Provinsi Banten');
define('APP_NAME_SHORT', 'PKL & Magang');

// Base URL: biarkan null untuk deteksi otomatis dari server.
define('APP_BASE_URL', null);

// Zona waktu aplikasi (semua timestamp disimpan & ditampilkan dalam zona ini)
define('APP_TIMEZONE', 'Asia/Jakarta');

// Mode debug: true = tampilkan detail error.
define('APP_DEBUG', getenv('APP_DEBUG') ? (getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1') : false);

// Versi aset untuk cache busting (ubah saat memperbarui CSS/JS)
define('APP_ASSET_VERSION', '1.10.0');

// Nama session aplikasi
define('SESSION_NAME', 'diskominfo_pkl_session');

// Lokasi folder upload: gunakan sys_get_temp_dir() di Vercel/serverless karena BASE_PATH read-only
if (getenv('VERCEL') || !@is_writable(BASE_PATH)) {
    $tmpDir = sys_get_temp_dir() . '/uploads';
    if (!is_dir($tmpDir)) {
        @mkdir($tmpDir, 0777, true);
    }
    define('UPLOADS_PATH', $tmpDir);
} else {
    define('UPLOADS_PATH', BASE_PATH . '/uploads');
}

// Supabase Storage — dipakai di Vercel agar file upload permanen (tidak hilang saat restart).
// Set SUPABASE_URL dan SUPABASE_KEY di Vercel Project Settings → Environment Variables.
// SUPABASE_URL  : https://[project-ref].supabase.co
// SUPABASE_KEY  : service_role key (atau anon key jika bucket public)
// SUPABASE_BUCKET: nama bucket (default: "uploads")
define('SUPABASE_URL',    rtrim((string) getenv('SUPABASE_URL'), '/'));
define('SUPABASE_KEY',    (string) getenv('SUPABASE_KEY'));
define('SUPABASE_BUCKET', getenv('SUPABASE_BUCKET') ?: 'uploads');
define('USE_SUPABASE_STORAGE', SUPABASE_URL !== '' && SUPABASE_KEY !== '');

// Lokasi folder log
define('LOGS_PATH', BASE_PATH . '/logs');

// Batas ukuran file upload (dalam byte) — 5 MB per file
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);

// Batas ukuran file video upload (dalam byte) — 100 MB per file.
define('UPLOAD_VIDEO_MAX_SIZE', 100 * 1024 * 1024);

// Ekstensi file yang diizinkan
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_DOCUMENT_EXT', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip']);
define('ALLOWED_VIDEO_EXT', ['mp4', 'webm', 'ogg', 'mov']);

// Halaman default
define('DEFAULT_PAGE', 'home');
