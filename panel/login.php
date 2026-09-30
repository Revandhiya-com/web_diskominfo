<?php
/**
 * Login admin — username/email + password, rate limiting, remember me aman.
 */
define('ADMIN_PUBLIC', true);
require __DIR__ . '/includes/bootstrap.php';

$error = '';
$maxAttempts = 5;
$windowSec = 900; // 15 menit

/* --- Auto-login via cookie "remember me" yang masih valid --- */
if (!is_logged_in() && !empty($_COOKIE['dp_remember'])) {
    $parts = explode('.', $_COOKIE['dp_remember'], 2);
    if (count($parts) === 2 && ctype_digit($parts[0]) && strlen($parts[1]) === 64) {
        $user = q_one('SELECT id, name, username, email, password, role, is_active FROM users WHERE id = ? AND is_active = 1', [(int) $parts[0]]);
        if ($user && hash_equals((string) $user['remember_token'], hash('sha256', $parts[1]))) {
            unset($user['password']);
            login_user($user);
            log_activity('login', 'auth', (int) $user['id'], 'Login otomatis (remember me)');
            redirect('panel/index.php');
        }
        setcookie('dp_remember', '', time() - 42000, '/', '', false, true);
    }
}

if (is_logged_in()) {
    redirect('panel/index.php');
}

if (is_post()) {
    require_csrf();
    $login    = post_max('login', 150);
    $password = (string) ($_POST['password'] ?? '');
    $remember = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';
    $ip       = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

    if ($login === '' || $password === '') {
        $error = 'Isi username/email dan password.';
    } else {
        $recent = (int) (q_one(
            'SELECT COUNT(*) AS n FROM login_attempts WHERE username = ? AND ip = ? AND attempted_at > (NOW() - (? * INTERVAL 1 SECOND))',
            [$login, $ip, $windowSec]
        )['n'] ?? 0);

        if ($recent >= $maxAttempts) {
            $error = 'Terlalu banyak percobaan login. Coba lagi ' . (int) ceil(($windowSec / 60)) . ' menit lagi.';
        } else {
            $user = q_one(
                'SELECT id, name, username, email, password, role, is_active, remember_token FROM users WHERE (username = ? OR email = ?) AND is_active = 1',
                [$login, $login]
            );

            if ($user && password_verify($password, $user['password'])) {
                q_exec('DELETE FROM login_attempts WHERE username = ? AND ip = ?', [$login, $ip]);
                q_exec('UPDATE users SET last_login_at = NOW() WHERE id = ?', [(int) $user['id']]);

                if ($remember) {
                    $raw = bin2hex(random_bytes(32));
                    q_exec('UPDATE users SET remember_token = ? WHERE id = ?', [hash('sha256', $raw), (int) $user['id']]);
                    setcookie('dp_remember', (int) $user['id'] . '.' . $raw, time() + 30 * 86400, '/', '', false, true);
                } else {
                    q_exec('UPDATE users SET remember_token = NULL WHERE id = ?', [(int) $user['id']]);
                }

                unset($user['password'], $user['remember_token']);
                login_user($user);
                log_activity('login', 'auth', (int) $user['id'], 'Login berhasil');
                redirect('panel/index.php');
            }

            q_exec('INSERT INTO login_attempts (username, ip) VALUES (?, ?)', [$login, $ip]);
            $error = 'Username/email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Login Admin — <?= e(APP_NAME_SHORT) ?></title>
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap/bootstrap.min.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <style>
        .login-wrap { min-height: 100vh; display: grid; place-items: center; background: linear-gradient(160deg, #0f1d33 0%, #1a2d4d 60%, #254a7a 100%); padding: 20px; }
        .login-card { width: 100%; max-width: 400px; background: #fff; border-radius: 14px; padding: 32px; box-shadow: 0 20px 50px rgba(0,0,0,.35); }
        .login-logo { font-size: 2.2rem; color: #d4a017; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="login-logo mb-2"><i class="bi bi-buildings"></i></div>
            <h1 class="h5 mb-1">Panel Admin</h1>
            <p class="text-muted small mb-0"><?= e(APP_NAME) ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(base_url('panel/login.php')) ?>" autocomplete="off">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label-admin" for="login">Username / Email</label>
                <input class="form-control" type="text" id="login" name="login" value="<?= e(post_val('login')) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label-admin" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me" value="1">
                <label class="form-check-label small" for="remember_me">Ingat saya (30 hari, cookie aman)</label>
            </div>
            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</button>
            <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
                <p class="text-muted small mt-3 mb-0 text-center">Demo: <code>admin</code> / <code>demo1234</code></p>
            <?php endif; ?>
        </form>
    </div>
</div>
</body>
</html>