<?php
/**
 * Halaman publik Video / Konten Edukasi — media berstatus Terbit
 * DITAMBAAH karya peserta bertipe video yang ditandai "Konten Edukasi"
 * (satu file & satu record videos yang sama — tanpa duplikasi).
 * Video diputar langsung dengan HTML5 player (responsive + fallback).
 */
$pageTitle = 'Konten Edukasi';
$pageDescription = 'Video dan konten edukasi kegiatan Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten.';
$breadcrumbs = [
    ['label' => 'Home', 'url' => base_url()],
    ['label' => 'Dokumentasi', 'url' => base_url('?page=dokumentasi')],
    ['label' => 'Video'],
];

/* Union: pustaka video/foto + karya edukasi bertipe video — kolom diseragamkan.
   Cek keberadaan file dilakukan di PHP (thumbnail karya di uploads/karya/,
   thumbnail pustaka di uploads/videos/). */
$unionSql = "
    SELECT v.id AS media_id, v.title, v.description, v.media_type, v.file_path,
           v.mime_type, v.file_size, v.thumbnail,
           COALESCE(v.updated_at, v.created_at) AS sort_date, v.created_at,
           NULL AS karya_id, NULL AS team, NULL AS week_number,
           NULL AS karya_thumb
    FROM videos v
    WHERE v.status = 'published'
    UNION ALL
    SELECT vv.id, k.title,
           COALESCE(NULLIF(TRIM(k.description), ''), vv.description),
           vv.media_type, vv.file_path, vv.mime_type, vv.file_size,
           vv.thumbnail AS thumbnail,
           COALESCE(k.published_at, k.created_at) AS sort_date, k.created_at,
           k.id AS karya_id, k.team, k.week_number,
           k.thumbnail AS karya_thumb
    FROM karya k
    JOIN videos vv ON vv.id = k.video_id
    WHERE k.status = 'published' AND k.is_edukasi = 1
      AND k.media_type = 'video' AND vv.file_path <> ''
";

$total = (int) (q_one("SELECT COUNT(*) n FROM ($unionSql) t")['n'] ?? 0);
$pageInfo = pagination_info($total, 9);
$rows = q_all(
    "SELECT * FROM ($unionSql) t ORDER BY t.sort_date DESC, t.media_id DESC
     LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}"
);

/* Normalisasi baris union → struktur kartu + URL poster yang benar. */
$videos = [];
foreach ($rows as $r) {
    $poster = null;
    if (!empty($r['karya_thumb']) && upload_file_exists('karya', $r['karya_thumb'])) {
        $poster = upload_url('karya/' . $r['karya_thumb']);
    } elseif (!empty($r['thumbnail']) && upload_file_exists('videos', $r['thumbnail'])) {
        $poster = upload_url('videos/' . $r['thumbnail']);
    }
    if (($r['media_type'] ?? '') !== 'video' && !upload_file_exists('videos', $r['file_path'])) {
        continue;
    }
    $r['_poster'] = $poster;
    $videos[] = $r;
}

$fallbackMsg = 'Browser Anda tidak mendukung pemutaran video. Silakan gunakan browser versi terbaru.';
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Tonton langsung video kegiatan PKL &amp; Magang Diskominfo Provinsi Banten.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <?php if ($total > 0): ?>
            <p class="dp-text-muted small mb-3"><?= e(number_format($total)) ?> media dipublikasikan.</p>
        <?php endif; ?>

        <?php if (empty($videos)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-camera-video" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Video</p>
                <p class="dp-empty-text">Video kegiatan akan segera hadir. Nantikan pembaruan dari kami.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($videos as $v): $isVideo = ($v['media_type'] ?? '') === 'video'; ?>
                    <div class="col-md-6 col-lg-4" id="<?= $v['karya_id'] ? 'k' . (int) $v['karya_id'] : 'v' . (int) $v['media_id'] ?>">
                        <article class="dp-card h-100 dp-media-card">
                            <?php if ($isVideo): ?>
                                <video controls preload="metadata" playsinline class="dp-video-frame"<?php if (!empty($v['_poster'])): ?> poster="<?= e($v['_poster']) ?>"<?php endif; ?>>
                                    <source src="<?= e(upload_url('videos/' . $v['file_path'])) ?>" type="<?= e($v['mime_type'] ?: 'video/mp4') ?>">
                                    <?= e($fallbackMsg) ?>
                                </video>
                            <?php elseif (upload_file_exists('videos', $v['file_path'])): ?>
                                <img class="dp-video-frame" src="<?= e(upload_url('videos/' . $v['file_path'])) ?>" alt="<?= e($v['title']) ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="dp-card-body">
                                <h2 class="dp-card-title">
                                    <?php if ($v['karya_id']): ?>
                                        <a href="<?= e(base_url('?page=detail-karya&id=' . (int) $v['karya_id'])) ?>"><?= e($v['title']) ?></a>
                                    <?php else: ?>
                                        <?= e($v['title']) ?>
                                    <?php endif; ?>
                                </h2>
                                <?php if ($v['karya_id']): ?>
                                    <div class="mb-2">
                                        <span class="dp-badge dp-badge-gold"><i class="bi bi-palette-fill me-1" aria-hidden="true"></i>Karya Peserta</span>
                                        <?php if ($v['team']): ?><span class="dp-badge dp-badge-blue"><?= e($v['team']) ?></span><?php endif; ?>
                                        <?php if ($v['week_number']): ?><span class="dp-badge dp-badge-gold">Minggu ke-<?= (int) $v['week_number'] ?></span><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="dp-card-meta">
                                    <span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($v['created_at'])) ?></span>
                                    <?php if (!$v['karya_id']): ?>
                                        <span><i class="bi <?= $isVideo ? 'bi-film' : 'bi-camera' ?>" aria-hidden="true"></i><?= $isVideo ? 'Video' : 'Foto' ?> · <?= e(format_size((int) $v['file_size'])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($v['description']): ?>
                                    <p class="dp-card-text mb-0"><?= nl2br(e($v['description'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php $pagination = $pageInfo; require BASE_PATH . '/includes/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>
