<?php
/**
 * Bootstrap area admin — wajib include di setiap halaman admin.
 * Auth publik (login.php) mendefinisikan ADMIN_PUBLIC sebelum include.
 */

define('BASE_PATH', dirname(__DIR__, 2));

require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/helpers/functions.php';
require BASE_PATH . '/helpers/security.php';
require BASE_PATH . '/helpers/auth.php';

date_default_timezone_set(APP_TIMEZONE);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

/* Error tidak pernah bocor ke pengguna kecuali pesan umum/debug. */
set_exception_handler(function (Throwable $e): void {
    log_error('Admin uncaught exception', $e);
    http_response_code(500);
    if (!headers_sent()) {
        echo '<div style="font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.1);">';
        echo '<h2 style="color:#d9534f;margin-top:0;">Terjadi Kesalahan Server (Admin)</h2>';
        echo '<p style="color:#333;font-weight:bold;">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p style="font-size:0.9rem;color:#666;">Jika Anda baru memasang di Vercel, pastikan <strong>Environment Variables</strong> database (<code>DB_DRIVER</code>, <code>DB_HOST</code>, <code>DB_PORT</code>, <code>DB_NAME</code>, <code>DB_USER</code>, <code>DB_PASS</code>) sudah diisi di Vercel Project Settings.</p>';
        echo '<hr style="border:0;border-top:1px solid #eee;margin:20px 0;">';
        echo '<a href="' . htmlspecialchars(base_url('admin/login.php'), ENT_QUOTES, 'UTF-8') . '" style="color:#0070f3;text-decoration:none;">&laquo; Kembali ke Halaman Login</a>';
        echo '</div>';
    }
    exit;
});

start_secure_session();

require BASE_PATH . '/admin/includes/crud.php';
require BASE_PATH . '/admin/includes/upload.php';

if (!defined('ADMIN_PUBLIC')) {
    require_login();
    if ((current_user()['is_active'] ?? 1) != 1) {
        logout_user();
        redirect('admin/login.php');
    }
    if (defined('ADMIN_ONLY')) {
        require_admin();
    }
}
