<?php
/**
 * Logout admin — hanya via POST + token CSRF (mencegah logout CSRF).
 */
require __DIR__ . '/includes/bootstrap.php';

if (!is_post()) {
    http_response_code(405);
    exit('Metode tidak diizinkan. Gunakan tombol Keluar di panel admin.');
}
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Session tidak valid. Muat ulang halaman dan coba lagi.');
}

log_activity('logout', 'auth', (int) current_user()['id'], 'Logout');
logout_user();
setcookie('dp_remember', '', time() - 42000, '/', '', false, true);
redirect('admin/login.php');