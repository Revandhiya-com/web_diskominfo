<?php
/**
 * Halaman CMS — render halaman statis yang dibuat admin melalui CMS
 * (rute publik: ?page=<slug>). Slug dipastikan terbit oleh index.php.
 */

$pageTitle = $cmsPage['title'] ?? 'Halaman';
$pageDescription = 'Halaman informasi ' . $pageTitle;
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => $pageTitle],
];
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Informasi resmi dari tim pengelola Portal PKL dan Magang Diskominfo Provinsi Banten.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <div class="dp-card">
            <div class="dp-card-body">
                <div class="dp-prose">
                    <?= sanitize_admin_html($cmsPage['content']) ?>
                </div>
                <?php if (!empty($cmsPage['source_url'])): ?>
                    <p class="dp-source-note small mt-3 mb-0">
                        <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Sumber:
                        <a href="<?= e($cmsPage['source_url']) ?>" target="_blank" rel="noopener nofollow"><?= e($cmsPage['source_url']) ?></a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>