<?php
/**
 * Front controller — pintu masuk seluruh halaman publik.
 * Route: index.php?page=nama -> pages/nama.php
 */
define('BASE_PATH', __DIR__);

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

/* Canonical URL — halaman bisa men-set $canonicalUrl sendiri (mis. detail berita);
   jika tidak, dibangun dari parameter penentu konten (kategori/slug/id).
   Halaman pencarian (search) dan 404 di-noindex sehingga tanpa canonical. */
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
