<?php
/**
 * Vercel entry point — front controller untuk Vercel.
 * Mendukung routing halaman publik, modul /admin, alias /panel, /login, /kelola, serta ?page=admin / ?page=login.
 */
define('BASE_PATH', dirname(__DIR__));

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// 1. Static file handling jika diakses lewat Vercel rewrite
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|pdf|txt|xml)$/i', $uri)) {
    $staticFile = BASE_PATH . $uri;
    if (is_file($staticFile)) {
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

// 2. Query parameter shortcut: ?page=admin / ?page=login / ?page=panel
if (isset($_GET['page']) && in_array(strtolower($_GET['page']), ['admin', 'login', 'panel', 'kelola'], true)) {
    $targetFile = BASE_PATH . '/admin/login.php';
    if (is_file($targetFile)) {
        require $targetFile;
        exit;
    }
}

// 3. Admin & Panel routing: mendukung /admin, /panel, /login, /kelola untuk menghindari blokir WAF /admin
$adminMappedUri = $uri;
if (strpos($uri, '/panel') === 0) {
    $sub = substr($uri, 6);
    $adminMappedUri = '/admin' . ($sub === '' || $sub === '/' ? '/login.php' : $sub);
} elseif ($uri === '/login' || $uri === '/login.php') {
    $adminMappedUri = '/admin/login.php';
} elseif (strpos($uri, '/kelola') === 0) {
    $sub = substr($uri, 7);
    $adminMappedUri = '/admin' . ($sub === '' || $sub === '/' ? '/login.php' : $sub);
}

if (strpos($adminMappedUri, '/admin') === 0) {
    $candidates = [
        BASE_PATH . $adminMappedUri,
        BASE_PATH . $adminMappedUri . '.php',
        rtrim(BASE_PATH, '/') . '/admin/index.php',
    ];

    if (is_dir(BASE_PATH . $adminMappedUri)) {
        array_unshift($candidates, rtrim(BASE_PATH . $adminMappedUri, '/') . '/index.php');
    }

    foreach ($candidates as $cand) {
        if (is_file($cand)) {
            require $cand;
            exit;
        }
    }
}

// 4. Frontend routing (Halaman Publik)
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

$pageFile = BASE_PATH . '/pages/' . $page . '.php';
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

/* Halaman men-set variabel $pageTitle, $pageDescription, $breadcrumbs, lalu echo kontennya */
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
