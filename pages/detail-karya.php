<?php
/**
 * Halaman Detail Karya — media diputar/tampil penuh + meta (jenis, tim,
 * minggu, tanggal) dan deskripsi. Video memakai HTML5 player.
 * URL: ?page=detail-karya&id=...
 */
$id = (int) get_val('id', '0');

$karya = q_one(
    "SELECT k.*, v.file_path AS video_file, v.mime_type AS video_mime,
            v.thumbnail AS video_thumb, v.title AS video_title
     FROM karya k
     LEFT JOIN videos v ON v.id = k.video_id
     WHERE k.id = ? AND k.status = 'published'",
    [$id]
);

if (!$karya || (
    $karya['media_type'] === 'video'
        ? !($karya['video_file'] && upload_file_exists('videos', $karya['video_file']))
        : !upload_file_exists('karya', $karya['file_path'])
)) {
    redirect('?page=karya');
}

$jenisLabel = [
    'infografis' => 'Infografis',
    'video'      => 'Video',
    'foto'       => 'Foto',
    'lainnya'    => 'Lainnya',
];
$jenis = $karya['jenis'];
$isVideo = ($karya['media_type'] ?? 'image') === 'video';

$poster = null;
if ($karya['thumbnail'] && upload_file_exists('karya', $karya['thumbnail'])) {
    $poster = upload_url('karya/' . $karya['thumbnail']);
} elseif ($isVideo && $karya['video_thumb'] && upload_file_exists('videos', $karya['video_thumb'])) {
    $poster = upload_url('videos/' . $karya['video_thumb']);
}

$pageTitle       = $karya['title'];
$pageDescription = trim_words((string) $karya['description'], 40);
$pageImage       = $poster ?: ($isVideo ? null : upload_url('karya/' . $karya['file_path']));
$breadcrumbs = [
    ['label' => 'Home', 'url' => base_url()],
    ['label' => 'Karya', 'url' => base_url('?page=karya')],
    ['label' => $karya['title']],
];
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb dp-breadcrumb mb-2">
                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <?php if (isset($crumb['url']) && $i < count($breadcrumbs) - 1): ?>
                        <li class="breadcrumb-item"><a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= e(trim_words($crumb['label'], 8)) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <div class="d-flex flex-wrap gap-1 mb-2">
            <span class="dp-badge dp-badge-blue"><?= e($jenisLabel[$jenis]) ?></span>
            <?php if ($karya['team']): ?><span class="dp-badge dp-badge-gold"><i class="bi bi-people-fill me-1" aria-hidden="true"></i><?= e($karya['team']) ?></span><?php endif; ?>
            <?php if ($karya['week_number']): ?><span class="dp-badge dp-badge-gold"><i class="bi bi-calendar-week me-1" aria-hidden="true"></i>Minggu ke-<?= (int) $karya['week_number'] ?></span><?php endif; ?>
            <?php if ((int) $karya['is_edukasi'] === 1 && $isVideo): ?><span class="dp-badge dp-badge-blue"><i class="bi bi-mortarboard me-1" aria-hidden="true"></i>Konten Edukasi</span><?php endif; ?>
        </div>
        <h1 id="page-header-title" class="mb-2"><?= e($karya['title']) ?></h1>
        <p class="dp-text-muted mb-0">
            <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>Dipublikasikan <?= e(format_date_id($karya['published_at'] ?: $karya['created_at'])) ?>
        </p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if ($isVideo): ?>
                    <video controls preload="metadata" playsinline class="dp-video-frame"
                        src="<?= e(upload_url('videos/' . $karya['video_file'])) ?>"
                        <?php if ($poster): ?>poster="<?= e($poster) ?>"<?php endif; ?>>
                        <source src="<?= e(upload_url('videos/' . $karya['video_file'])) ?>" type="<?= e($karya['video_mime'] ?: 'video/mp4') ?>">
                        Browser Anda tidak mendukung pemutaran video. Silakan gunakan browser versi terbaru.
                    </video>
                <?php else: ?>
                    <img class="img-fluid rounded w-100" src="<?= e(upload_url('karya/' . $karya['file_path'])) ?>" alt="<?= e($karya['title']) ?>">
                <?php endif; ?>

                <?php if ($karya['description']): ?>
                    <div class="dp-prose mt-4">
                        <?= nl2br(e($karya['description'])) ?>
                    </div>
                <?php endif; ?>

                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <a class="btn dp-btn-outline-primary" href="<?= e(base_url('?page=karya')) ?>"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Galeri Karya</a>
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="dp-card h-100">
                    <div class="dp-card-body">
                        <h2 class="dp-subsection-title">Informasi Karya</h2>
                        <ul class="list-unstyled dp-karya-meta mb-0">
                            <li><strong><i class="bi bi-tags me-1" aria-hidden="true"></i>Jenis:</strong> <?= e($jenisLabel[$jenis]) ?></li>
                            <li><strong><i class="bi bi-people me-1" aria-hidden="true"></i>Tim:</strong> <?= e($karya['team'] ?: '—') ?></li>
                            <li><strong><i class="bi bi-calendar-week me-1" aria-hidden="true"></i>Periode:</strong> <?= $karya['week_number'] ? 'Minggu ke-' . (int) $karya['week_number'] : '—' ?></li>
                            <li><strong><i class="bi bi-calendar3 me-1" aria-hidden="true"></i>Tanggal:</strong> <?= e(format_date_id($karya['published_at'] ?: $karya['created_at'])) ?></li>
                            <?php if ($isVideo): ?>
                                <li><strong><i class="bi bi-collection-play me-1" aria-hidden="true"></i>Konten Edukasi:</strong> <?= (int) $karya['is_edukasi'] === 1 ? 'Ya — juga tampil di halaman Konten Edukasi' : 'Tidak' ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>
