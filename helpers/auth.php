<?php
/**
 * Helper autentikasi & otorisasi admin.
 * Dipakai penuh mulai fase CMS; fungsi dasar sudah siap dari fase fondasi.
 */

/** True jika pengguna sedang login. */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Data pengguna yang login (array dari session), null jika belum login. */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/** Wajib login untuk mengakses halaman; redirect ke halaman login jika belum. */
function require_login(): void
{
    if (!is_logged_in()) {
        redirect('admin/login.php');
    }
}

/** Wajib peran tertentu; hentikan dengan 403 jika bukan. */
function require_role(string $role): void
{
    $user = current_user();
    if (!$user || ($user['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}

/** Simpan data user ke session setelah berhasil login. */
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user']    = $user;
}

/** Hapus session saat logout. */
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}