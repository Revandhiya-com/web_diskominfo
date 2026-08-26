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

/* Error tidak pernah bocor ke pengguna — dicatat ke logs/app.log saja. */
set_exception_handler(function (Throwable $e): void {
    log_error('Admin uncaught exception', $e);
    http_response_code(500);
    if (!headers_sent()) {
        header('Location: ' . base_url('admin/index.php'));
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
