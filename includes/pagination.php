<?php
/**
 * Pagination reusable (Bootstrap).
 * Halaman meng-set $pagination = pagination_info($total, $perPage),
 * lalu require file ini di akhir daftar konten.
 */
if (!defined('BASE_PATH')) {
    exit('Akses langsung tidak diizinkan.');
}

$pagination = $pagination ?? null;
if (!$pagination || (int) $pagination['total_pages'] <= 1) {
    return;
}

$current = (int) $pagination['current'];
$totalPages = (int) $pagination['total_pages'];

/* Jendela halaman di sekitar halaman aktif */
$window = [];
$start = max(2, $current - 2);
$end = min($totalPages - 1, $current + 2);
for ($i = $start; $i <= $end; $i++) {
    $window[] = $i;
}
?>
<nav class="mt-4" aria-label="Navigasi halaman">
    <ul class="pagination dp-pagination justify-content-center flex-wrap mb-0">
        <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(pagination_href($current - 1)) ?>" aria-label="Halaman sebelumnya">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </a>
        </li>

        <li class="page-item <?= $current === 1 ? 'active' : '' ?>">
            <a class="page-link" href="<?= e(pagination_href(1)) ?>">1</a>
        </li>

        <?php if ($start > 2): ?>
            <li class="page-item disabled"><span class="page-link">…</span></li>
        <?php endif; ?>

        <?php foreach ($window as $i): ?>
            <li class="page-item <?= $i === $current ? 'active' : '' ?>">
                <a class="page-link" href="<?= e(pagination_href($i)) ?>"><?= (int) $i ?></a>
            </li>
        <?php endforeach; ?>

        <?php if ($end < $totalPages - 1): ?>
            <li class="page-item disabled"><span class="page-link">…</span></li>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <li class="page-item <?= $current === $totalPages ? 'active' : '' ?>">
                <a class="page-link" href="<?= e(pagination_href($totalPages)) ?>"><?= (int) $totalPages ?></a>
            </li>
        <?php endif; ?>

        <li class="page-item <?= $current >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(pagination_href($current + 1)) ?>" aria-label="Halaman berikutnya">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </a>
        </li>
    </ul>
</nav>