<?php
/**
 * Helper fungsi umum — dipakai di seluruh aplikasi.
 */

/**
 * URL dasar aplikasi (dengan path subfolder bila ada).
 * Deteksi otomatis dari server, kecuali APP_BASE_URL diisi manual.
 */
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        if (defined('APP_BASE_URL') && APP_BASE_URL) {
            $base = rtrim(APP_BASE_URL, '/');
        } else {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
            $scheme  = $https ? 'https' : 'http';
            $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
            /* Base URL dihitung dari lokasi aplikasi (BASE_PATH) relatif ke
               document root — BUKAN dari SCRIPT_NAME. Dengan begitu URL tetap
               benar baik dijalankan dari index.php (web root) maupun dari
               subfolder admin/ (mencegah path berganda: admin/admin/...). */
            $docRoot = str_replace('\\', '/', rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/'));
            $webRoot = str_replace('\\', '/', rtrim(BASE_PATH, '/'));
            $dir     = '';
            if ($docRoot !== '' && strpos($webRoot, $docRoot) === 0) {
                $dir = rtrim(substr($webRoot, strlen($docRoot)), '/');
            }
            $base = $scheme . '://' . $host . $dir;
        }
    }
    return $base . '/' . ltrim($path, '/');
}

/** URL aset statis (css, js, gambar). */
function asset_url(string $path = ''): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Logo resmi situs — otomatis dipakai jika file logo ditemukan di
 * assets/images/, dengan urutan prioritas nama file:
 * logo-diskominfo.{png|jpg|jpeg|webp|svg} → logo.{...} → image.{...}.
 * Mengembalikan array ['url','type','mime'] atau null (fallback ikon).
 */
function site_logo(): ?array
{
    static $cache = null;
    static $computed = false;
    if ($computed) {
        return $cache;
    }
    $computed = true;
    $cache = null;
    foreach (['logo-diskominfo', 'logo', 'image'] as $name) {
        foreach (['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'svg' => 'image/svg+xml'] as $ext => $mime) {
            $file = BASE_PATH . '/assets/images/' . $name . '.' . $ext;
            if (is_file($file)) {
                $cache = ['url' => asset_url('images/' . $name . '.' . $ext) . '?v=' . APP_ASSET_VERSION, 'ext' => $ext, 'mime' => $mime];
                break 2;
            }
        }
    }
    return $cache;
}

/** URL file di folder uploads. */
function upload_url(string $path = ''): string
{
    return base_url('uploads/' . ltrim($path, '/'));
}

/** Redirect ke URL internal aplikasi lalu berhenti. */
function redirect(string $path = ''): void
{
    header('Location: ' . base_url($path));
    exit;
}

/** Escape output untuk mencegah XSS. Gunakan UNTUK SEMUA output dinamis. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Catat error aplikasi ke log (tidak pernah ditampilkan ke pengguna). */
function log_error(string $message, ?Throwable $e = null): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($e !== null) {
        $line .= ' | ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine();
    }
    $dir = defined('LOGS_PATH') ? LOGS_PATH : (defined('BASE_PATH') ? BASE_PATH . '/logs' : sys_get_temp_dir() . '/logs');
    @file_put_contents($dir . '/app.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/** Sanitasi teks input sederhana (trim + buang tag HTML). */
function sanitize_text($value): string
{
    return trim(strip_tags((string) $value));
}

/** Ambil nilai $_POST dengan default. */
function post_val(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

/** Ambil nilai $_GET dengan default. */
function get_val(string $key, string $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $default));
}

/** True jika request saat ini adalah POST. */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Koneksi PDO tunggal (singleton) dengan prepared statements.
 * Semua query WAJIB memakai PDO prepared statement.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw new RuntimeException('Koneksi database gagal: ' . $e->getMessage());
            }
            throw new RuntimeException('Koneksi database gagal. Periksa konfigurasi server.');
        }
    }
    return $pdo;
}

/** Format tanggal ke Bahasa Indonesia. Contoh: 10 Agustus 2026 */
function format_date_id(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '-';
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return '-';
    }
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $out = sprintf('%d %s %d', (int) date('j', $ts), $months[(int) date('n', $ts)], (int) date('Y', $ts));
    if ($withTime) {
        $out .= ' ' . date('H:i', $ts) . ' WIB';
    }
    return $out;
}

/** Format bulan+tahun ke Bahasa Indonesia. Contoh: "Juli 2026" */
function format_month_year_id(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return '';
    }
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    return $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Format periode PKL/Magang. Contoh: "Juli 2026 – Agustus 2026" */
function format_period_id(?string $start, ?string $end): string
{
    $s = format_month_year_id($start);
    $e = format_month_year_id($end);
    if ($s !== '' && $e !== '') {
        return $s . ' – ' . $e;
    }
    return $s !== '' ? $s : ($e !== '' ? $e : '-');
}

/** Nama file thumbnail turunan hasil proses upload admin (jika ada). */
function thumb_of(string $file): string
{
    return pathinfo($file, PATHINFO_FILENAME) . '-thumb.' . pathinfo($file, PATHINFO_EXTENSION);
}

/** Format ukuran file. Contoh: 1.5 MB */
function format_size(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    $size = (float) $bytes;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return ($i === 0 ? (string) (int) $size : rtrim(rtrim(number_format($size, 1, ',', ''), '0'), ',')) . ' ' . $units[$i];
}

/** Buat slug aman untuk URL dari teks. Contoh: "Workshop PKL 2026" -> "workshop-pkl-2026" */
function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $map = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ñ' => 'n', 'ç' => 'c',
    ];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'page';
}

/**
 * Eksekusi query SELECT dan kembalikan semua baris.
 * Gagal koneksi/query tidak membuat halaman error — mengembalikan array kosong.
 * Semua query WAJIB memakai prepared statement (parameter).
 */
function q_all(string $sql, array $params = []): array
{
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        log_error('DB q_all: ' . $sql, $e);
        return [];
    }
}

/** Eksekusi query SELECT dan kembalikan satu baris (atau null). */
function q_one(string $sql, array $params = []): ?array
{
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Throwable $e) {
        log_error('DB q_one: ' . $sql, $e);
        return null;
    }
}

/** Eksekusi query tulis (INSERT/UPDATE/DELETE) yang aman; true jika sukses. */
function q_exec(string $sql, array $params = []): bool
{
    try {
        db()->prepare($sql)->execute($params);
        return true;
    } catch (Throwable $e) {
        log_error('DB q_exec: ' . $sql, $e);
        return false;
    }
}

/** Eksekusi query tulis dan kembalikan jumlah baris terpengaruh; -1 jika gagal. */
function q_changes(string $sql, array $params = []): int
{
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (Throwable $e) {
        log_error('DB q_changes: ' . $sql, $e);
        return -1;
    }
}

/** Ambil nilai pengaturan dari tabel settings (dengan cache per-request). */
function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (q_all('SELECT `key`, `value` FROM settings') as $row) {
            $cache[$row['key']] = (string) $row['value'];
        }
    }
    return $cache[$key] ?? $default;
}

/** Cek halaman aktif untuk highlight menu navbar. */
function nav_active(string $name): string
{
    global $currentPage;
    return ($currentPage ?? 'home') === $name ? 'active' : '';
}

/* ------------------------------------------------------------
 * PAGINATION
 * ------------------------------------------------------------ */

/** Nomor halaman saat ini (param GET "p"), minimal 1. */
function pagination_current(): int
{
    $n = (int) ($_GET['p'] ?? 1);
    return max(1, $n);
}

/**
 * Info pagination lengkap untuk query + render.
 * Return: total, per_page, current, total_pages, offset.
 */
function pagination_info(int $total, int $perPage = 9): array
{
    $perPage = max(1, $perPage);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $current = pagination_current();
    if ($current > $totalPages) {
        $current = $totalPages;
    }
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $current,
        'total_pages' => $totalPages,
        'offset'      => ($current - 1) * $perPage,
    ];
}

/** URL halaman tertentu untuk pagination (mempertahankan query lain). */
function pagination_href(int $pageNum): string
{
    $params = $_GET;
    if ($pageNum <= 1) {
        unset($params['p']);
    } else {
        $params['p'] = $pageNum;
    }
    return base_url('?' . http_build_query($params));
}

/* ------------------------------------------------------------
 * UTILITAS KONTEN
 * ------------------------------------------------------------ */

/** Potong teks menjadi jumlah kata tertentu + "…". */
function trim_words(string $text, int $limit = 24): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }
    $words = preg_split('/\s+/u', $text);
    if (count($words) <= $limit) {
        return $text;
    }
    return implode(' ', array_slice($words, 0, $limit)) . '…';
}

/** Label tipe file dari nama/ekstensi (contoh: "PDF", "DOCX"). */
function file_type_label(string $file): string
{
    $ext = strtoupper(pathinfo($file, PATHINFO_EXTENSION));
    return $ext !== '' ? $ext : 'FILE';
}

/** True jika file benar-benar ada di folder uploads. */
function upload_file_exists(string $dir, ?string $file): bool
{
    return $file !== null
        && $file !== ''
        && strpos($file, '/') === false
        && strpos($file, '\\') === false
        && is_file(UPLOADS_PATH . '/' . $dir . '/' . $file);
}

/* ------------------------------------------------------------
 * SANITASI HTML KONTEN (anti stored-XSS)
 * Konten dari admin (berita/pages) ditampilkan sebagai HTML,
 * tapi tidak boleh memuat script, handler event, iframe, URL
 * berbahaya, maupun SVG — hanya tag teks/format yang aman.
 * ------------------------------------------------------------ */

/** Bersihkan nilai URL atribut (href/src); blokir skema berbahaya. */
function sanitize_html_url(string $url): string
{
    $t = trim($url);
    if ($t === '' || $t[0] === '#' || $t[0] === '/') {
        return $t;
    }
    if (preg_match('~^(javascript|vbscript|data|file):~i', $t)) {
        return '#';
    }
    if (preg_match('~^([a-z][a-z0-9+.\-]*):~i', $t, $m)) {
        $scheme = strtolower($m[1]);
        if (!in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) {
            return '#';
        }
    }
    return $t;
}

/** True jika URL aman untuk src/poster video: harus menuju folder uploads lokal. */
function is_local_uploads_url(string $url): bool
{
    $t = trim($url);
    if ($t === '' || preg_match('~^(javascript|vbscript|data|file|blob):~i', $t)) {
        return false;
    }
    /* Relatif terhadap root aplikasi: uploads/... */
    if (stripos($t, 'uploads/') === 0) {
        return true;
    }
    $path = (string) (parse_url($t, PHP_URL_PATH) ?? '');
    /* Path absolut atau URL penuh: wajib mengandung segmen /uploads/ */
    return $path !== '' && stripos($path, '/uploads/') !== false;
}

/** Hapus tag berbahaya & atribut berbahaya dari HTML yang berasal dari admin. */
function sanitize_admin_html(string $html): string
{
    if ($html === '') {
        return '';
    }
    if (!class_exists('DOMDocument')) {
        return strip_tags($html);
    }
    static $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'a',
        'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote',
        'pre', 'code', 'img', 'figure', 'figcaption', 'hr',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'span', 'div', 'dl', 'dt', 'dd', 'video', 'source',
    ];
    static $allowedAttrs = [
        'href', 'title', 'alt', 'src', 'width', 'height', 'target', 'rel',
        'name', 'id', 'colspan', 'rowspan', 'datetime', 'cite',
        'controls', 'preload', 'playsinline', 'muted', 'loop', 'poster', 'type',
    ];

    $prev = libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<!DOCTYPE html><html><body>' . $html . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $body = $doc->getElementsByTagName('body')->item(0);
    if (!$body) {
        return strip_tags($html);
    }

    /* Hapus tag berbahaya beserta isinya (termasuk SVG agar tak ada XSS via SVG). */
    foreach (['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'option', 'link', 'meta', 'base', 'svg', 'math', 'audio', 'track', 'template', 'noscript', 'applet'] as $tag) {
        while ($el = $doc->getElementsByTagName($tag)->item(0)) {
            if ($el->parentNode) {
                $el->parentNode->removeChild($el);
            }
        }
    }

    /* Bersihkan tag & atribut elemen yang tersisa (html/head/body dipertahankan). */
    $all = $doc->getElementsByTagName('*');
    $toUnwrap = [];
    foreach ($all as $el) {
        $tag = strtolower($el->nodeName);
        if ($tag === 'html' || $tag === 'head' || $tag === 'body') {
            continue;
        }
        if (!in_array($tag, $allowedTags, true)) {
            $toUnwrap[] = $el;
            continue;
        }
        $attrs = [];
        foreach ($el->attributes as $attr) {
            $name  = strtolower($attr->nodeName);
            $value = $attr->nodeValue;
            if ($name === 'style' || $name === 'class' || $name === 'onerror' || strpos($name, 'on') === 0) {
                continue;
            }
            if (!in_array($name, $allowedAttrs, true)) {
                continue;
            }
            if ($name === 'href' || $name === 'src') {
                $value = sanitize_html_url($value);
            }

            /* Video: src/poster wajib file di folder uploads lokal; nilai enum dibatasi. */
            if ($tag === 'video' || $tag === 'source') {
                if (in_array($name, ['src', 'poster'], true) && !is_local_uploads_url($value)) {
                    continue;
                }
                if ($name === 'preload' && !in_array($value, ['auto', 'metadata', 'none'], true)) {
                    continue;
                }
                if ($name === 'type' && !preg_match('~^video/(mp4|webm|ogg|quicktime)$~i', $value)) {
                    continue;
                }
            }
            if (($name === 'width' || $name === 'height' || $name === 'colspan' || $name === 'rowspan') && !ctype_digit($value)) {
                continue;
            }
            if ($name === 'target' && $value !== '_blank') {
                continue;
            }
            if (($name === 'id' || $name === 'name') && !preg_match('~^[a-zA-Z][a-zA-Z0-9:_\-.]*$~', $value)) {
                continue;
            }
            if ($name === 'rel' && !preg_match('~^(noopener|noreferrer|noopener noreferrer|noreferrer noopener)$~', $value)) {
                $value = 'noopener';
            }
            $attrs[$name] = $value;
        }
        if ($tag === 'a' && isset($attrs['target'])) {
            $attrs['rel'] = isset($attrs['rel']) && $attrs['rel'] !== 'noopener' ? 'noopener noreferrer' : 'noopener';
        }
        while ($el->attributes->length > 0) {
            $el->removeAttribute($el->attributes->item(0)->nodeName);
        }
        foreach ($attrs as $name => $value) {
            $el->setAttribute($name, $value);
        }
    }
    foreach ($toUnwrap as $el) {
        if ($el->parentNode) {
            while ($el->firstChild) {
                $el->parentNode->insertBefore($el->firstChild, $el);
            }
            $el->parentNode->removeChild($el);
        }
    }

    $out = '';
    foreach ($body->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    return $out;
}