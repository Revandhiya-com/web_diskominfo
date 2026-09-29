<?php
/**
 * Halaman Pengumuman — pengumuman terbaru (pinned dulu), urut terbaru.
 * Detail pengumuman dapat dibuka lewat ?page=pengumuman&id=...
 */

$pageTitle = 'Pengumuman';
$pageDescription = 'Pengumuman resmi terkait PKL dan Magang di lingkungan Diskominfo Provinsi Banten.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Pengumuman'],
];

/* Pengumuman yang dibuka (detail) */
$detail = null;
$detailId = (int) ($_GET['id'] ?? 0);
if ($detailId > 0) {
    $detail = q_one(
        'SELECT id, title, content, is_pinned, published_at
         FROM announcements
         WHERE status = \'published\' AND id = ?',
        [$detailId]
    );
}

/* Daftar lengkap — pinned dulu, lalu terbaru */
$announcements = q_all(
    'SELECT id, title, content, is_pinned, published_at
     FROM announcements
     WHERE status = \'published\'
     ORDER BY is_pinned DESC, published_at DESC'
);

$others = $detail
    ? array_values(array_filter($announcements, fn ($a) => (int) $a['id'] !== $detailId))
    : $announcements;

$renderAnn = function (array $ann, bool $expanded = false): void {
    $url = base_url('?page=pengumuman&id=' . (int) $ann['id']);
    ?>
    <article class="dp-news-item dp-announcement-item">
        <div class="dp-news-body flex-grow-1">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <?php if ((int) $ann['is_pinned'] === 1): ?>
                    <span class="dp-badge dp-badge-pinned"><i class="bi bi-pin-angle-fill" aria-hidden="true"></i> Penting</span>
                <?php endif; ?>
                <span class="dp-card-meta mb-0"><i class="bi bi-calendar3" aria-hidden="true"></i> <?= e(format_date_id($ann['published_at'], true)) ?></span>
            </div>
            <h2 class="dp-news-title mb-1">
                <?php if ($expanded): ?>
                    <?= e($ann['title']) ?>
                <?php else: ?>
                    <a href="<?= e($url) ?>"><?= e($ann['title']) ?></a>
                <?php endif; ?>
            </h2>
            <?php if ($expanded && $ann['content'] !== null && $ann['content'] !== ''): ?>
                <p class="dp-card-text mb-2"><?= e($ann['content']) ?></p>
                <a class="dp-section-link" href="<?= e(base_url('?page=pengumuman')) ?>"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali ke daftar pengumuman</a>
            <?php elseif (!$expanded && $ann['content'] !== null && $ann['content'] !== ''): ?>
                <p class="dp-card-text mb-0"><?= e(trim_words($ann['content'], 22)) ?></p>
            <?php endif; ?>
        </div>
    </article>
    <?php
};
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Informasi resmi dan pengumuman terbaru seputar kegiatan PKL dan Magang.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <?php if ($detail): ?>
            <h2 class="dp-section-title mb-3">Detail Pengumuman</h2>
            <?php $renderAnn($detail, true); ?>
            <div class="dp-section-head mt-5">
                <h2 class="dp-section-title">Pengumuman Lainnya</h2>
            </div>
        <?php endif; ?>

        <?php if (empty($announcements)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-megaphone" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Pengumuman</p>
                <p class="dp-empty-text">Pengumuman resmi dari tim pengelola akan ditampilkan di sini.</p>
            </div>
        <?php elseif (empty($others)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-megaphone" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Pengumuman Lainnya</p>
                <p class="dp-empty-text">Tidak ada pengumuman lain selain yang sedang Anda baca.</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($others as $ann): ?>
                    <?php $renderAnn($ann); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>