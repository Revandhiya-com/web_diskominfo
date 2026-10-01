<?php
/**
 * Setup / seed admin user — HAPUS FILE INI setelah berhasil!
 * Akses: https://webdiskominfo.vercel.app/?page=setup&key=DiskoInfo2026
 */
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/helpers/functions.php';

// Kunci akses — ubah jika perlu, hapus file setelah setup selesai
$accessKey = 'DiskoInfo2026';
if (($_GET['key'] ?? '') !== $accessKey) {
    http_response_code(403);
    echo '<h2>403 - Akses ditolak</h2><p>Tambahkan <code>?page=setup&key=' . $accessKey . '</code> ke URL.</p>';
    exit;
}

$results = [];

// 1. Buat tabel sessions jika belum ada
try {
    db()->exec("CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(128) PRIMARY KEY,
        data TEXT NOT NULL DEFAULT '',
        last_activity BIGINT NOT NULL DEFAULT 0
    )");
    $results[] = ['ok', 'Tabel sessions: OK'];
} catch (Throwable $e) {
    $results[] = ['err', 'Tabel sessions: ' . $e->getMessage()];
}

// 2. Buat tabel login_attempts jika belum ada
try {
    db()->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id SERIAL PRIMARY KEY,
        username VARCHAR(150),
        ip VARCHAR(45),
        attempted_at TIMESTAMP DEFAULT NOW()
    )");
    $results[] = ['ok', 'Tabel login_attempts: OK'];
} catch (Throwable $e) {
    $results[] = ['err', 'Tabel login_attempts: ' . $e->getMessage()];
}

// 3. Buat tabel users jika belum ada
try {
    db()->exec("CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        username VARCHAR(80) NOT NULL UNIQUE,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'editor',
        is_active SMALLINT NOT NULL DEFAULT 1,
        remember_token VARCHAR(255),
        last_login_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    )");
    $results[] = ['ok', 'Tabel users: OK'];
} catch (Throwable $e) {
    $results[] = ['err', 'Tabel users: ' . $e->getMessage()];
}

// 4. Upsert admin user
$hash = password_hash('demo1234', PASSWORD_BCRYPT, ['cost' => 10]);
try {
    if (DB_DRIVER === 'pgsql') {
        db()->prepare("INSERT INTO users (name, username, email, password, role, is_active)
            VALUES (?, ?, ?, ?, 'admin', 1)
            ON CONFLICT (username) DO UPDATE SET password = EXCLUDED.password, is_active = 1, role = 'admin', updated_at = NOW()")
            ->execute(['Admin Portal PKL', 'admin', 'admin@diskominfo.local', $hash]);
    } else {
        db()->prepare("INSERT INTO users (name, username, email, password, role, is_active)
            VALUES (?, ?, ?, ?, 'admin', 1)
            ON DUPLICATE KEY UPDATE password = VALUES(password), is_active = 1, role = 'admin'")
            ->execute(['Admin Portal PKL', 'admin', 'admin@diskominfo.local', $hash]);
    }
    $results[] = ['ok', 'User admin: berhasil dibuat/diperbarui (username: admin / password: demo1234)'];
} catch (Throwable $e) {
    $results[] = ['err', 'User admin: ' . $e->getMessage()];
}

// Tampilkan hasil
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setup Database</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 60px auto; padding: 20px; }
        h1 { color: #1a365d; }
        .ok  { color: #22543d; background: #f0fff4; padding: 10px 14px; border-radius: 8px; margin: 8px 0; border-left: 4px solid #48bb78; }
        .err { color: #742a2a; background: #fff5f5; padding: 10px 14px; border-radius: 8px; margin: 8px 0; border-left: 4px solid #fc8181; }
        .box { background: #ebf8ff; border: 1px solid #90cdf4; border-radius: 10px; padding: 20px; margin-top: 30px; }
        code { background: #eee; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🔧 Setup Database Admin</h1>
    <?php foreach ($results as [$type, $msg]): ?>
        <div class="<?= $type ?>"><?= $type === 'ok' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>

    <div class="box">
        <h3>📋 Informasi Login Admin</h3>
        <p><strong>URL Panel:</strong> <a href="/panel/">https://webdiskominfo.vercel.app/panel/</a></p>
        <p><strong>Username:</strong> <code>admin</code></p>
        <p><strong>Password:</strong> <code>demo1234</code></p>
        <hr>
        <p style="color:#742a2a"><strong>⚠️ Hapus file ini setelah login berhasil!</strong><br>
        Hapus <code>pages/setup.php</code> dari project dan push ulang ke GitHub.</p>
    </div>
</body>
</html>
