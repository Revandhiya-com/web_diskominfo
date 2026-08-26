<?php
/**
 * Banner judul halaman (dipakai setelah breadcrumb di halaman publik).
 * Halaman meng-set $pageTitle dan opsional $pageSubtitle.
 */
if (!defined('BASE_PATH')) {
    exit('Akses langsung tidak diizinkan.');
}

$pageSubtitle = $pageSubtitle ?? '';
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle ?? 'Halaman') ?></h1>
        <?php if ($pageSubtitle !== ''): ?>
            <p class="mb-0"><?= e($pageSubtitle) ?></p>
        <?php endif; ?>
    </div>
</section>