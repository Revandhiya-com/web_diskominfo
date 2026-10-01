<?php
/**
 * Setup diagnostik — cek env vars & koneksi DB, lalu seed admin
 * Akses: https://webdiskominfo.vercel.app/?page=setup&key=DiskoInfo2026
 * HAPUS FILE INI setelah selesai!
 */

// Tampilkan semua error PHP
ini_set('display_errors', '1');
error_reporting(E_ALL);

$accessKey = 'DiskoInfo2026';
if (($_GET['key'] ?? '') !== $accessKey) {
    http_response_code(403);
    echo '<h2>403 - Akses ditolak. Tambahkan ?page=setup&key=' . $accessKey . '</h2>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Setup Diagnostik</title>
<style>
body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:20px;background:#f7fafc}
h2{color:#1a365d}
.ok{background:#f0fff4;border-left:4px solid #48bb78;padding:10px 14px;margin:6px 0;border-radius:6px;color:#22543d}
.err{background:#fff5f5;border-left:4px solid #fc8181;padding:10px 14px;margin:6px 0;border-radius:6px;color:#742a2a}
.warn{background:#fffbeb;border-left:4px solid #f6c90e;padding:10px 14px;margin:6px 0;border-radius:6px}
code{background:#eee;padding:2px 6px;border-radius:4px}
pre{background:#1a202c;color:#e2e8f0;padding:16px;border-radius:8px;overflow:auto;font-size:13px}
</style>
</head>
<body>
<h2>🔧 Setup & Diagnostik Database</h2>

<?php
// ============================================================
// 1. CEK ENVIRONMENT VARIABLES
// ============================================================
echo '<h3>1. Environment Variables</h3>';
$vars = ['DB_DRIVER','DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASS'];
$allSet = true;
foreach ($vars as $v) {
    $val = getenv($v);
    if ($val !== false && $val !== '') {
        $display = ($v === 'DB_PASS') ? str_repeat('*', strlen($val)) : htmlspecialchars($val);
        echo "<div class='ok'>✅ <strong>$v</strong> = <code>$display</code></div>";
    } else {
        echo "<div class='err'>❌ <strong>$v</strong> — tidak ditemukan (empty/not set)</div>";
        $allSet = false;
    }
}

if (!$allSet) {
    echo "<div class='err'><strong>⚠️ ENV VARS BELUM DIISI di Vercel!</strong><br>
    Buka: <a href='https://vercel.com/dashboard' target='_blank'>vercel.com/dashboard</a> → 
    Project → Settings → Environment Variables → tambahkan semua variabel di atas.</div>";
}

// ============================================================
// 2. CEK KONEKSI DATABASE
// ============================================================
echo '<h3>2. Koneksi Database</h3>';

$driver  = getenv('DB_DRIVER') ?: 'pgsql';
$host    = getenv('DB_HOST')   ?: '';
$port    = getenv('DB_PORT')   ?: '5432';
$dbname  = getenv('DB_NAME')   ?: '';
$user    = getenv('DB_USER')   ?: '';
$pass    = getenv('DB_PASS')   ?: '';

if (!$host || !$dbname || !$user) {
    echo "<div class='err'>❌ Koneksi dibatalkan — ENV VARS tidak lengkap.</div>";
} else {
    try {
        if ($driver === 'pgsql') {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
        } else {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        }
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
        ]);
        echo "<div class='ok'>✅ Koneksi ke database berhasil! Driver: <code>$driver</code></div>";

        // ============================================================
        // 3. BUAT TABEL & SEED
        // ============================================================
        echo '<h3>3. Setup Tabel & Seed Admin</h3>';

        // Tabel sessions
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(128) PRIMARY KEY,
                data TEXT NOT NULL DEFAULT '',
                last_activity BIGINT NOT NULL DEFAULT 0
            )");
            echo "<div class='ok'>✅ Tabel <code>sessions</code>: siap</div>";
        } catch (Throwable $e) {
            echo "<div class='err'>❌ Tabel sessions: " . htmlspecialchars($e->getMessage()) . "</div>";
        }

        // Tabel login_attempts
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
                id SERIAL PRIMARY KEY,
                username VARCHAR(150),
                ip VARCHAR(45),
                attempted_at TIMESTAMP DEFAULT NOW()
            )");
            echo "<div class='ok'>✅ Tabel <code>login_attempts</code>: siap</div>";
        } catch (Throwable $e) {
            echo "<div class='err'>❌ Tabel login_attempts: " . htmlspecialchars($e->getMessage()) . "</div>";
        }

        // Tabel users
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
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
            echo "<div class='ok'>✅ Tabel <code>users</code>: siap</div>";
        } catch (Throwable $e) {
            echo "<div class='err'>❌ Tabel users: " . htmlspecialchars($e->getMessage()) . "</div>";
        }

        // Seed / upsert admin user
        $hash = password_hash('demo1234', PASSWORD_BCRYPT, ['cost' => 10]);
        try {
            if ($driver === 'pgsql') {
                $pdo->prepare("INSERT INTO users (name, username, email, password, role, is_active)
                    VALUES (?, ?, ?, ?, 'admin', 1)
                    ON CONFLICT (username) DO UPDATE SET password = EXCLUDED.password, is_active = 1, role = 'admin', updated_at = NOW()")
                    ->execute(['Admin Portal PKL', 'admin', 'admin@diskominfo.local', $hash]);
            } else {
                $pdo->prepare("INSERT INTO users (name, username, email, password, role, is_active)
                    VALUES (?, ?, ?, ?, 'admin', 1)
                    ON DUPLICATE KEY UPDATE password = VALUES(password), is_active = 1, role = 'admin'")
                    ->execute(['Admin Portal PKL', 'admin', 'admin@diskominfo.local', $hash]);
            }
            echo "<div class='ok'>✅ User <code>admin</code> berhasil dibuat/diperbarui</div>";
        } catch (Throwable $e) {
            echo "<div class='err'>❌ Seed admin: " . htmlspecialchars($e->getMessage()) . "</div>";
        }

        // Verifikasi
        $row = $pdo->prepare('SELECT id, username, role, is_active FROM users WHERE username = ?');
        $row->execute(['admin']);
        $u = $row->fetch();
        if ($u) {
            echo "<div class='ok'>✅ Verifikasi: user ditemukan — ID=" . $u['id'] . ", role=" . $u['role'] . ", aktif=" . $u['is_active'] . "</div>";
        } else {
            echo "<div class='err'>❌ Verifikasi: user admin tidak ditemukan setelah insert</div>";
        }

        echo '<div style="background:#ebf8ff;border:1px solid #90cdf4;border-radius:10px;padding:20px;margin-top:20px">
            <h3>🎉 Setup Selesai! Silakan Login:</h3>
            <p><strong>URL:</strong> <a href="/panel/">https://webdiskominfo.vercel.app/panel/</a></p>
            <p><strong>Username:</strong> <code>admin</code></p>
            <p><strong>Password:</strong> <code>demo1234</code></p>
            <p style="color:red"><strong>⚠️ Hapus pages/setup.php setelah berhasil login!</strong></p>
        </div>';

    } catch (PDOException $e) {
        echo "<div class='err'>❌ Koneksi GAGAL: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='warn'>💡 Pastikan ENV VARS di Vercel sudah benar dan Supabase mengizinkan koneksi dari Vercel.</div>";
    }
}
?>
</body>
</html>
