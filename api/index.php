<?php
/**
 * Vercel entry point — front controller untuk Vercel.
 * Folder admin/ sudah direname menjadi panel/ untuk menghindari blokir Vercel WAF.
 */
define('BASE_PATH', dirname(__DIR__));

function resolve_app_file(string $relativePath): ?string {
    $bases = [
        BASE_PATH,
        '/var/task/user',
        '/var/task',
        dirname(BASE_PATH),
        $_SERVER['DOCUMENT_ROOT'] ?? '',
    ];
    $cleanPath = '/' . ltrim($relativePath, '/');
    foreach ($bases as $b) {
        if (!$b) continue;
        $file = rtrim(str_replace('\\', '/', $b), '/') . $cleanPath;
        if (is_file($file)) {
            return $file;
        }
    }
    return null;
}

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// 0. Handle POST login auth — ?_auth=1 — sebelum routing apapun agar WAF tidak memblokir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['_auth'])) {
    $f = resolve_app_file('/panel/login.php');
    if ($f) { require $f; exit; }
}

// 1. Static file handling
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|pdf|txt|xml)$/i', $uri)) {
    $staticFile = resolve_app_file($uri);
    if ($staticFile) {
        $mimeTypes = [
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'ico'   => 'image/x-icon',
            'svg'   => 'image/svg+xml',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'pdf'   => 'application/pdf',
            'txt'   => 'text/plain',
            'xml'   => 'application/xml',
        ];
        $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        readfile($staticFile);
        exit;
    }
}

// 2. Panel routing — semua request yang diawali /panel/ dilayani dari folder panel/
if (strpos($uri, '/panel') === 0) {
    $sub = substr($uri, 6); // strip '/panel'
    if ($sub === '' || $sub === '/' || $sub === '/index.php') {
        // Root /panel/ selalu ke halaman login (menangani GET form & POST submit)
        $panelFile = resolve_app_file('/panel/login.php');
    } else {
        $candidates = [
            '/panel' . $sub,
            '/panel' . $sub . '.php',
            rtrim('/panel' . $sub, '/') . '/index.php',
        ];
        $panelFile = null;
        foreach ($candidates as $cand) {
            $f = resolve_app_file($cand);
            if ($f) { $panelFile = $f; break; }
        }
    }
    if ($panelFile) {
        require $panelFile;
        exit;
    }
}

// 3. /login shortcut
if ($uri === '/login' || $uri === '/login.php') {
    $f = resolve_app_file('/panel/login.php');
    if ($f) { require $f; exit; }
}

// 4. Query parameter shortcut: ?page=admin / ?page=panel / ?page=login / ?page=kelola
if (isset($_GET['page']) && in_array(strtolower($_GET['page']), ['admin', 'login', 'panel', 'kelola'], true)) {
    $f = resolve_app_file('/panel/login.php');
    if ($f) { require $f; exit; }
}

// 5. Frontend routing (Halaman Publik)
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

set_exception_handler(function (Throwable $e): void {
    log_error('Uncaught exception', $e);
    http_response_code(500);
    if (!headers_sent()) {
        header('Location: ' . base_url('?page=404'));
    }
    exit;
});

start_secure_session();

$page = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($_GET['page'] ?? DEFAULT_PAGE)));
if ($page === '') {
    $page = DEFAULT_PAGE;
}

"$pageFile = BASE_PATH . '/pages/' . $page . '.php';

if ($page === 'setup' && is_file($pageFile)) {
    require $pageFile;
    exit;
}
$cmsPage = null;
if (!is_file($pageFile)) {
    $row = q_one('SELECT slug, title, content, source_url FROM pages WHERE slug = ? AND status = \'published\'', [$page]);
    if ($row) {
        $cmsPage = $row;
        $pageFile = BASE_PATH . '/pages/cms.php';
    } else {
        $page = '404';
        $pageFile = BASE_PATH . '/pages/404.php';
    }
}

$currentPage = $page;

ob_start();
require $pageFile;
$pageContent = ob_get_clean();

$pageTitle       = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription ?? 'Pusat informasi, layanan, berita, dan dokumentasi kegiatan Praktik Kerja Lapangan dan Magang.';
$breadcrumbs     = $breadcrumbs ?? [];

$seoNoindex = ($page === 'search' || $page === '404');
if (!$seoNoindex && !isset($canonicalUrl)) {
    $canonParams = [];
    foreach (['kategori', 'slug', 'id'] as $k) {
        $v = get_val($k);
        if ($v !== '') {
            $canonParams[$k] = $v;
        }
    }
    if ($page !== DEFAULT_PAGE) {
        $canonParams = ['page' => $page] + $canonParams;
    }
    $canonicalUrl = base_url($canonParams === [] ? '' : '?' . http_build_query($canonParams));
}
$seoType     = $seoType ?? 'website';
$seoImage    = $seoImage ?? '';
$seoOgDesc   = mb_strimwidth(strip_tags($pageDescription), 0, 200, '…');

require BASE_PATH . '/includes/header.php';

if (!empty($breadcrumbs)) {
    require BASE_PATH . '/includes/breadcrumb.php';
}

echo $pageContent;

require BASE_PATH . '/includes/footer.php';
