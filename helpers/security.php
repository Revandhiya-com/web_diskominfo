<?php
/**
 * Helper keamanan: session hardening, CSRF, dan dasar-dasar proteksi.
 * Menggunakan database-backed session handler agar kompatibel dengan Vercel serverless.
 */

/** Deteksi HTTPS (server langsung maupun di belakang reverse proxy). */
function is_https_request(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
}

/**
 * Session handler berbasis database — kompatibel MySQL & PostgreSQL.
 * Diperlukan di Vercel serverless karena filesystem /tmp tidak persisten antar invokasi.
 */
class DbSessionHandler implements SessionHandlerInterface
{
    private bool $tableReady = false;

    private function getPdo(): ?PDO
    {
        try {
            return db();
        } catch (Throwable $e) {
            return null;
        }
    }

    public function open(string $savePath, string $sessionName): bool
    {
        if ($this->tableReady) return true;
        $pdo = $this->getPdo();
        if (!$pdo) return true;
        try {
            if (defined('DB_DRIVER') && DB_DRIVER === 'pgsql') {
                $pdo->exec(
                    "CREATE TABLE IF NOT EXISTS sessions (
                        id VARCHAR(128) PRIMARY KEY,
                        data TEXT NOT NULL DEFAULT '',
                        last_activity BIGINT NOT NULL DEFAULT 0
                    )"
                );
            } else {
                $pdo->exec(
                    "CREATE TABLE IF NOT EXISTS sessions (
                        id VARCHAR(128) PRIMARY KEY,
                        data TEXT NOT NULL DEFAULT '',
                        last_activity BIGINT NOT NULL DEFAULT 0
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
                );
            }
            $this->tableReady = true;
        } catch (Throwable $e) {
            error_log('Session table create error: ' . $e->getMessage());
        }
        return true;
    }

    public function close(): bool { return true; }

    public function read(string $id): string|false
    {
        $pdo = $this->getPdo();
        if (!$pdo) return '';
        try {
            $maxlife = (int) ini_get('session.gc_maxlifetime') ?: 1440;
            $cutoff  = time() - $maxlife;
            $stmt = $pdo->prepare('SELECT data FROM sessions WHERE id = ? AND last_activity > ?');
            $stmt->execute([$id, $cutoff]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (string) $row['data'] : '';
        } catch (Throwable $e) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        $pdo = $this->getPdo();
        if (!$pdo) return false;
        try {
            $now = time();
            if (defined('DB_DRIVER') && DB_DRIVER === 'pgsql') {
                $stmt = $pdo->prepare(
                    'INSERT INTO sessions (id, data, last_activity) VALUES (?, ?, ?)
                     ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data, last_activity = EXCLUDED.last_activity'
                );
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO sessions (id, data, last_activity) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE data = VALUES(data), last_activity = VALUES(last_activity)'
                );
            }
            $stmt->execute([$id, $data, $now]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        $pdo = $this->getPdo();
        if (!$pdo) return true;
        try {
            $stmt = $pdo->prepare('DELETE FROM sessions WHERE id = ?');
            $stmt->execute([$id]);
        } catch (Throwable $e) {}
        return true;
    }

    public function gc(int $maxlifetime): int|false
    {
        $pdo = $this->getPdo();
        if (!$pdo) return 0;
        try {
            $cutoff = time() - $maxlifetime;
            $stmt = $pdo->prepare('DELETE FROM sessions WHERE last_activity < ?');
            $stmt->execute([$cutoff]);
        } catch (Throwable $e) {}
        return 1;
    }
}

/** Mulai session dengan pengaturan aman + database-backed handler. */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Gunakan DB session handler jika constants DB sudah didefinisikan
    if (defined('DB_DRIVER') && defined('DB_HOST')) {
        $handler = new DbSessionHandler();
        session_set_save_handler($handler, true);
    }

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    @session_start();
}

/** Ambil (atau buat) token CSRF untuk session saat ini. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** HTML hidden input token CSRF untuk form. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Verifikasi token CSRF dari form (harus cocok dengan yang di session). */
function verify_csrf(?string $token): bool
{
    return !empty($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/** Verifikasi CSRF pada request POST, hentikan jika gagal. */
function require_csrf(): void
{
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Session tidak valid. Muat ulang halaman dan coba lagi.');
    }
}