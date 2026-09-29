<?php
/**
 * Halaman Album — detail satu album dokumentasi beserta galeri foto
 * (lightbox + caption). URL: ?page=album&slug=...
 */

$slug = get_val('slug');
if ($slug === '') {
    redirect('?page=dokumentasi');
}

$album = q_one(
    'SELECT a.id, a.title, a.slug, a.description, a.event_date, a.location,
            a.participants, a.category_id, a.cover_image, c.name AS category_name
     FROM albums a
     LEFT JOIN album_categories c ON c.id = a.category_id
     WHERE a.status = \'published\' AND a.slug = ?',
    [$slug]
);

if (!$album) {
    $pageTitle = 'Album Tidak Ditemukan';
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    return;
}

$pageTitle = $album['title'];
$pageDescription = $album['description'] ?: ('Dokumentasi: ' . $album['title']);
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Dokumentasi', 'url' => base_url('?page=dokumentasi')],
    ['label' => $album['title']],
];

$canonicalUrl = base_url('?page=album&slug=' . urlencode($album['slug']));

/* Metadata SEO — gambar sampul album (dipakai header.php untuk Open Graph) */
if (upload_file_exists('albums', $album['cover_image'])) {
    $seoImage    = upload_url('albums/' . $album['cover_image']);
    $seoImageAlt = 'Sampul album: ' . $album['title'];
}

$photos = q_all(
    'SELECT id, image, thumbnail, caption, media_type
     FROM photos
     WHERE album_id = ?
     ORDER BY sort_order ASC, id ASC',
    [(int) $album['id']]
);

$totalPhotos = count(array_filter($photos, fn ($p) => ($p['media_type'] ?? 'image') === 'image'));
$totalVideos = count($photos) - $totalPhotos;

$relatedBase = 'SELECT a.id, a.title, a.slug, a.event_date, a.location, a.cover_image, c.name AS category_name,
            (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id AND p.media_type = \'image\') AS photo_count,
            (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id AND p.media_type = \'video\') AS video_count
     FROM albums a
     LEFT JOIN album_categories c ON c.id = a.category_id
     WHERE a.status = \'published\' AND a.id <> ?';
$relatedWhere = empty($album['category_id'])
    ? (int) $album['id']
    : [(int) $album['id'], (int) $album['category_id']];
$relatedSql = empty($album['category_id'])
    ? $relatedBase . ' ORDER BY a.created_at DESC LIMIT 4'
    : $relatedBase . ' AND a.category_id = ? ORDER BY a.created_at DESC LIMIT 4';
$relatedAlbums = is_array($relatedWhere) ? q_all($relatedSql, $relatedWhere) : q_all($relatedSql, [$relatedWhere]);
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title" class="fs-2"><?= e($album['title']) ?></h1>
        <p class="mb-0">Dokumentasi kegiatan PKL dan Magang — Diskominfo Provinsi Banten.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <!-- Info album -->
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="dp-card h-100">
                    <div class="dp-card-media">
                        <?php if (upload_file_exists('albums', $album['cover_image'])): ?>
                            <img src="<?= e(upload_url('albums/' . $album['cover_image'])) ?>" alt="Sampul album: <?= e($album['title']) ?>">
                        <?php else: ?>
                            <div class="dp-media-placeholder"><i class="bi bi-image" aria-hidden="true"></i></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="dp-card h-100">
                    <div class="dp-card-body">
                        <h2 class="dp-card-title fs-4 mb-3">Informasi Album</h2>
                        <ul class="list-unstyled dp-album-info mb-0">
                            <?php if ($album['category_name']): ?>
                                <li><i class="bi bi-tag" aria-hidden="true"></i><span><strong>Kategori:</strong> <?= e($album['category_name']) ?></span></li>
                            <?php endif; ?>
                            <?php if ($album['event_date']): ?>
                                <li><i class="bi bi-calendar3" aria-hidden="true"></i><span><strong>Tanggal:</strong> <?= e(format_date_id($album['event_date'])) ?></span></li>
                            <?php endif; ?>
                            <?php if ($album['location']): ?>
                                <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span><strong>Lokasi:</strong> <?= e($album['location']) ?></span></li>
                            <?php endif; ?>
                            <?php if ($album['participants']): ?>
                                <li><i class="bi bi-people" aria-hidden="true"></i><span><strong>Peserta:</strong> <?= e($album['participants']) ?></span></li>
                            <?php endif; ?>
                            <li><i class="bi bi-camera" aria-hidden="true"></i><span><strong>Jumlah Foto:</strong> <?= e(number_format($totalPhotos)) ?></span></li>
                            <li><i class="bi bi-film" aria-hidden="true"></i><span><strong>Jumlah Video:</strong> <?= e(number_format($totalVideos)) ?></span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($album['description']): ?>
            <div class="dp-alert dp-alert-info mb-4" role="note">
                <strong>Deskripsi Kegiatan:</strong><br>
                <?= e($album['description']) ?>
            </div>
        <?php endif; ?>

        <!-- Galeri -->
        <h2 class="dp-section-title mb-3">Galeri Foto &amp; Video</h2>
        <?php if (empty($photos)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-camera" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Foto/Video</p>
                <p class="dp-empty-text">Dokumentasi album ini akan ditambahkan oleh tim pengelola.</p>
            </div>
        <?php else: ?>
            <div class="dp-gallery-grid" role="list" aria-label="Galeri foto dan video <?= e($album['title']) ?>">
                <?php foreach ($photos as $photo): ?>
                    <?php if (($photo['media_type'] ?? 'image') === 'video'): ?>
                        <?php if (upload_file_exists('albums', $photo['image'])): ?>
                            <figure class="dp-gallery-item" role="listitem">
                                <video src="<?= e(upload_url('albums/' . $photo['image'])) ?>" controls preload="metadata" playsinline style="width:100%;height:auto;display:block;border-radius:.5rem;background:#000"></video>
                                <?php if ($photo['caption']): ?>
                                    <figcaption class="dp-gallery-caption"><?= e($photo['caption']) ?></figcaption>
                                <?php endif; ?>
                            </figure>
                        <?php endif; ?>
                    <?php elseif (upload_file_exists('albums', $photo['image'])): ?>
                        <figure class="dp-gallery-item" role="listitem">
                            <a href="#" class="dp-gallery-link" data-lightbox data-full="<?= e(upload_url('albums/' . $photo['image'])) ?>" data-caption="<?= e($photo['caption'] ?: $album['title']) ?>" aria-label="Perbesar foto: <?= e($photo['caption'] ?: $album['title']) ?>">
                                <?php if ($photo['thumbnail'] && upload_file_exists('albums', $photo['thumbnail'])): ?>
                                    <img src="<?= e(upload_url('albums/' . $photo['thumbnail'])) ?>" alt="<?= e($photo['caption'] ?: $album['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <img src="<?= e(upload_url('albums/' . $photo['image'])) ?>" alt="<?= e($photo['caption'] ?: $album['title']) ?>" loading="lazy">
                                <?php endif; ?>
                                <span class="dp-gallery-zoom" aria-hidden="true"><i class="bi bi-zoom-in"></i></span>
                            </a>
                            <?php if ($photo['caption']): ?>
                                <figcaption class="dp-gallery-caption"><?= e($photo['caption']) ?></figcaption>
                            <?php endif; ?>
                        </figure>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <p class="dp-text-muted small mt-3 mb-0"><i class="bi bi-lightbulb me-1" aria-hidden="true"></i>Klik foto untuk memperbesar. Gunakan panah kiri/kanan pada keyboard untuk berpindah foto.</p>
        <?php endif; ?>

        <!-- Dokumentasi lainnya (related) -->
        <?php if (!empty($relatedAlbums)): ?>
            <h2 class="dp-section-title mt-5 mb-3">Dokumentasi Lainnya</h2>
            <div class="row g-3">
                <?php foreach ($relatedAlbums as $ra): ?>
                    <div class="col-sm-6 col-lg-3">
                        <article class="dp-card">
                            <div class="dp-card-media">
                                <?php if (upload_file_exists('albums', $ra['cover_image'])): ?>
                                    <img src="<?= e(upload_url('albums/' . $ra['cover_image'])) ?>" alt="<?= e($ra['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="dp-media-placeholder"><i class="bi bi-image" aria-hidden="true"></i></div>
                                <?php endif; ?>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="dp-badge dp-badge-gold"><i class="bi bi-camera" aria-hidden="true"></i> <?= (int) $ra['photo_count'] ?> Foto<?php if ((int) $ra['video_count'] > 0): ?> · <?= (int) $ra['video_count'] ?> Video<?php endif; ?></span>
                                </div>
                            </div>
                            <div class="dp-card-body">
                                <h3 class="dp-card-title fs-6"><a href="<?= e(base_url('?page=album&slug=' . urlencode($ra['slug']))) ?>"><?= e($ra['title']) ?></a></h3>
                                <div class="dp-card-meta">
                                    <?php if ($ra['event_date']): ?>
                                        <span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($ra['event_date'])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($ra['category_name']): ?>
                                    <span class="dp-badge dp-badge-blue"><?= e($ra['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox (dibuka lewat JavaScript di main.js) -->
<div class="dp-lightbox" id="dpLightbox" role="dialog" aria-modal="true" aria-hidden="true" aria-label="Pratinjau foto">
    <div class="dp-lightbox-backdrop" data-lightbox-close></div>
    <div class="dp-lightbox-content">
        <button type="button" class="dp-lightbox-close" data-lightbox-close aria-label="Tutup pratinjau"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        <button type="button" class="dp-lightbox-nav dp-lightbox-prev" aria-label="Foto sebelumnya"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
        <img class="dp-lightbox-img" src="" alt="">
        <button type="button" class="dp-lightbox-nav dp-lightbox-next" aria-label="Foto berikutnya"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
        <div class="dp-lightbox-meta">
            <div class="dp-lightbox-caption" aria-live="polite"></div>
            <div class="dp-lightbox-count"></div>
        </div>
    </div>
</div>