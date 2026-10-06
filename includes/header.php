<?php
/**
 * Header halaman — bagian <head> + topbar + navbar.
 * Variabel yang dipakai (di-set oleh halaman / index.php):
 *   $pageTitle, $pageDescription
 */
if (!defined('BASE_PATH')) {
    exit('Akses langsung tidak diizinkan.');
}

$seoTitle = $pageTitle ?? APP_NAME;
if ($seoTitle !== APP_NAME) {
    $seoTitle = $seoTitle . ' | ' . APP_NAME;
}
$seoDescription = $pageDescription ?? 'Pusat informasi, layanan, berita, dan dokumentasi kegiatan Praktik Kerja Lapangan dan Magang di lingkungan Diskominfo Provinsi Banten.';
$seoNoindex     = $seoNoindex ?? false;
$canonicalUrl   = (isset($canonicalUrl) && (string) $canonicalUrl !== '') ? (string) $canonicalUrl : '';
$seoType        = $seoType ?? 'website';
$seoImage       = $seoImage ?? '';
$seoOgDesc      = mb_strimwidth(strip_tags($seoDescription), 0, 200, '…');
$siteName       = APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="<?= e($seoDescription) ?>">
    <?php if ($canonicalUrl !== ''): ?>
        <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <?php endif; ?>
    <?php if ($seoNoindex): ?>
        <meta name="robots" content="noindex, follow">
    <?php endif; ?>
    <?php $logo = site_logo(); ?>
    <?php if ($logo && $logo['ext'] !== 'svg'): ?>
        <link rel="icon" type="<?= e($logo['mime']) ?>" href="<?= e($logo['url']) ?>">
    <?php elseif ($logo): ?>
        <link rel="icon" type="image/svg+xml" href="<?= e($logo['url']) ?>">
    <?php else: ?>
        <link rel="icon" type="image/svg+xml" href="<?= asset_url('images/favicon.svg') ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://www.youtube.com" crossorigin>
    <link rel="preconnect" href="https://www.youtube-nocookie.com" crossorigin>

    <script>
    (function () {
        try {
            var stored = localStorage.getItem('dp-theme');
            var dark = stored === 'dark' || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            var root = document.documentElement;
            if (dark) {
                root.classList.add('dp-dark');
                root.setAttribute('data-bs-theme', 'dark');
            } else {
                root.classList.add('dp-light');
                root.setAttribute('data-bs-theme', 'light');
            }
        } catch (e) {}
    })();
    </script>

    <!-- Open Graph -->
    <meta property="og:type" content="<?= e($seoType) ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:locale" content="id_ID">
    <?php if ($canonicalUrl !== ''): ?>
        <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= e($seoTitle) ?>">
    <meta property="og:description" content="<?= e($seoOgDesc) ?>">
    <?php if ($seoImage !== ''): ?>
        <meta property="og:image" content="<?= e($seoImage) ?>">
        <meta property="og:image:alt" content="<?= e($seoImageAlt ?? $seoTitle) ?>">
    <?php endif; ?>

    <!-- Twitter / X -->
    <meta name="twitter:card" content="<?= $seoImage !== '' ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= e($seoTitle) ?>">
    <meta name="twitter:description" content="<?= e($seoOgDesc) ?>">
    <?php if ($seoImage !== ''): ?>
        <meta name="twitter:image" content="<?= e($seoImage) ?>">
    <?php endif; ?>

    <?php if (!empty($breadcrumbs)): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            <?php foreach ($breadcrumbs as $i => $crumb): ?>
            {
                "@type": "ListItem",
                "position": <?= (int) $i + 1 ?>,
                "name": <?= json_encode($crumb['label'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                "item": <?= json_encode($crumb['url'] ?? ($canonicalUrl !== '' ? $canonicalUrl : base_url()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
            }<?= $i < count($breadcrumbs) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ]
    }
    </script>
    <?php endif; ?>

    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap/bootstrap.min.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>?v=<?= APP_ASSET_VERSION ?>">
</head>
<body class="d-flex flex-column min-vh-100<?= (($currentPage ?? '') === 'home') ? ' banten-intro-active' : '' ?>">

<?php if (($currentPage ?? '') === 'home'): ?>
    <!-- Intro khusus beranda: visual kawasan KP3B, bukan video "city" generik. -->
    <noscript><style>#bantenIntro{display:none!important}.banten-intro-active{overflow:auto!important}</style></noscript>
    <section id="bantenIntro" class="banten-intro" aria-label="Pembuka Portal PKL dan Magang Diskominfo Provinsi Banten">
        <div class="banten-intro__visual" aria-hidden="true">
            <img src="<?= e(asset_url('images/kp3b-gedung-opd.jpg')) ?>" alt="" fetchpriority="high">
        </div>
        <div class="banten-intro__grain" aria-hidden="true"></div>
        <div class="banten-intro__content">
            <div class="banten-intro__topline">
                <span class="banten-intro__pulse" aria-hidden="true"></span>
                DISKOMINFO PROVINSI BANTEN
            </div>
            <p class="banten-intro__place"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> KP3B, Curug &middot; Kota Serang</p>
            <h1><span>Ruang Tumbuh</span>Talenta Digital Banten.</h1>
            <p class="banten-intro__copy">Belajar, berkarya, dan memberi dampak lewat program PKL &amp; Magang.</p>
            <button id="bantenIntroSkip" class="banten-intro__enter" type="button">
                Masuk ke portal <i class="bi bi-arrow-down-right" aria-hidden="true"></i>
            </button>
        </div>
        <div class="banten-intro__progress" aria-hidden="true"><span></span></div>
        <p class="banten-intro__note">Memuat pengalaman digital Banten</p>
    </section>
<?php endif; ?>

<a class="visually-hidden-focusable position-fixed top-0 start-0 z-3 p-2 bg-white text-primary shadow" href="#main-content">
    Langsung ke konten utama
</a>

<?php require BASE_PATH . '/includes/navbar.php'; ?>

<main id="main-content" class="flex-grow-1">
