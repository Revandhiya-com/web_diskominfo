<?php
/**
 * Breadcrumb reusable.
 * Set $breadcrumbs = [['label' => 'Beranda', 'url' => base_url()], ['label' => 'Halaman Ini']]
 * pada halaman, lalu include file ini.
 */
if (!defined('BASE_PATH')) {
    exit('Akses langsung tidak diizinkan.');
}

$breadcrumbs = $breadcrumbs ?? [];
if (empty($breadcrumbs)) {
    return;
}
?>
<nav class="dp-breadcrumb" aria-label="Breadcrumb">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php $count = count($breadcrumbs); $index = 0; ?>
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php $index++; $isLast = ($index === $count); ?>
                <?php if ($isLast || empty($crumb['url'])): ?>
                    <li class="breadcrumb-item active" aria-current="page"><?= e($crumb['label']) ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
