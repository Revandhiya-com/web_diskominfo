<?php
/**
 * Halaman Detail Berita — judul, kategori, penulis, tanggal, gambar, isi,
 * berita terkait, dan tombol bagikan / salin tautan.
 */

$slug = get_val('slug');

if ($slug === '') {
    redirect('?page=berita');
}

$news = q_one(
    'SELECT n.id, n.title, n.slug, n.excerpt, n.content, n.thumbnail, n.published_at, n.views, n.source_url,
            c.name AS category_name, c.id AS category_id,
            u.name AS author_name
     FROM news n
     LEFT JOIN news_categories c ON c.id = n.category_id
     LEFT JOIN users u ON u.id = n.author_id
     WHERE n.status = $$published$$ AND n.slug = ?',
    [$slug]
);

if (!$news) {
    $pageTitle = 'Berita Tidak Ditemukan';
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    return;
}

/* Hitungan kunjungan */
q_exec('UPDATE news SET views = views + 1 WHERE id = ?', [(int) $news['id']]);
$news['views'] = (int) $news['views'] + 1;

$pageTitle = $news['title'];
$pageDescription = $news['excerpt'] ?: ('Berita: ' . $news['title']);
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Berita', 'url' => base_url('?page=berita')],
    ['label' => $news['title']],
];

$canonicalUrl = base_url('?page=detail-berita&slug=' . urlencode($news['slug']));
$shareText = $news['title'] . ' — Portal PKL & Magang Diskominfo Provinsi Banten';

/* Metadata SEO — gambar & tipe konten (dipakai header.php untuk Open Graph) */
$seoType = 'article';
if (upload_file_exists('news', $news['thumbnail'])) {
    $seoImage    = upload_url('news/' . $news['thumbnail']);
    $seoImageAlt = 'Gambar berita: ' . $news['title'];
}

/* Berita terkait — kategori sama, kecuali berita ini, maks 3 */
$relatedNews = q_all(
    'SELECT n.id, n.title, n.slug, n.excerpt, n.thumbnail, n.published_at,
            c.name AS category_name
     FROM news n
     LEFT JOIN news_categories c ON c.id = n.category_id
     WHERE n.status = $$published$$ AND n.id != ?
       AND (n.category_id = ? OR ? IS NULL)
     ORDER BY n.published_at DESC
     LIMIT 3',
    [(int) $news['id'], $news['category_id'], $news['category_id']]
);
if (count($relatedNews) < 3) {
    $extra = q_all(
        'SELECT n.id, n.title, n.slug, n.excerpt, n.thumbnail, n.published_at,
                c.name AS category_name
         FROM news n
         LEFT JOIN news_categories c ON c.id = n.category_id
         WHERE n.status = $$published$$ AND n.id != ? AND (n.category_id IS NULL OR n.category_id != ?)
         ORDER BY n.published_at DESC
         LIMIT ?',
        [(int) $news['id'], $news['category_id'], 3 - count($relatedNews)]
    );
    $relatedNews = array_merge($relatedNews, $extra);
}
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title" class="fs-2"><?= e($news['title']) ?></h1>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <div class="row g-4">
            <!-- Artikel -->
            <div class="col-lg-8">
                <article class="dp-card">
                    <div class="dp-card-body">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <?php if ($news['category_name']): ?>
                                <span class="dp-badge dp-badge-blue"><?= e($news['category_name']) ?></span>
                            <?php endif; ?>
                            <span class="dp-card-meta mb-0">
                                <span><i class="bi bi-person" aria-hidden="true"></i><?= e($news['author_name'] ?: 'Tim Pengelola') ?></span>
                                <span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($news['published_at'], true)) ?></span>
                                <span><i class="bi bi-eye" aria-hidden="true"></i><?= e(number_format($news['views'])) ?> kali dilihat</span>
                            </span>
                        </div>

                        <?php if (upload_file_exists('news', $news['thumbnail'])): ?>
                            <?php
                            $imgPath = UPLOADS_PATH . '/news/' . $news['thumbnail'];
                            $imgSize = @getimagesize($imgPath);
                            ?>
                            <figure class="mb-4">
                                <img src="<?= e(upload_url('news/' . $news['thumbnail'])) ?>" alt="<?= e($news['title']) ?>" class="img-fluid rounded-2 w-100"<?= $imgSize ? ' width="' . (int) $imgSize[0] . '" height="' . (int) $imgSize[1] . '"' : '' ?>>
                            </figure>
                        <?php endif; ?>

                        <div class="dp-prose dp-prose-article">
                            <?= sanitize_admin_html($news['content']) ?>
                        </div>

                        <?php
                        /* Dokumentasi Kegiatan — galeri foto & video berita, dikelompokkan per jenis lomba */
                        $newsPhotos = q_all(
                            'SELECT id, group_name, caption, image, thumbnail, media_type
                             FROM news_photos
                             WHERE news_id = ?
                             ORDER BY sort_order, id',
                            [(int) $news['id']]
                        );
                        $photoGroups = [];
                        foreach ($newsPhotos as $np) {
                            if (!upload_file_exists('news', $np['image'])) continue;
                            $g = trim((string) ($np['group_name'] ?? ''));
                            if ($g === '') $g = 'Dokumentasi Kegiatan';
                            $photoGroups[$g][] = $np;
                        }
                        ?>
                        <?php if (!empty($photoGroups)): ?>
                            <h2 class="dp-section-title mt-4">Dokumentasi Kegiatan</h2>
                            <?php foreach ($photoGroups as $groupName => $photos): ?>
                                <h3 class="dp-subsection-title"><?= e($groupName) ?></h3>
                                <div class="dp-gallery-grid" role="list" aria-label="<?= e($groupName) ?>">
                                    <?php foreach ($photos as $np): ?>
                                        <?php if (($np['media_type'] ?? 'image') === 'video'): ?>
                                            <figure class="dp-gallery-item dp-gallery-video" role="listitem">
                                                <video controls preload="metadata" playsinline class="dp-video-frame"
                                                    src="<?= e(upload_url('news/' . $np['image'])) ?>"
                                                    <?php if ($np['thumbnail'] && upload_file_exists('news', $np['thumbnail'])): ?>poster="<?= e(upload_url('news/' . $np['thumbnail'])) ?>"<?php endif; ?>>
                                                    Browser Anda tidak mendukung pemutaran video. Silakan gunakan browser versi terbaru.
                                                </video>
                                                <?php if ($np['caption']): ?>
                                                    <figcaption class="small text-muted mt-1"><?= e($np['caption']) ?></figcaption>
                                                <?php endif; ?>
                                            </figure>
                                        <?php else: ?>
                                            <figure class="dp-gallery-item" role="listitem">
                                                <a href="#" class="dp-gallery-link" data-lightbox data-full="<?= e(upload_url('news/' . $np['image'])) ?>" data-caption="<?= e($np['caption'] ?: $groupName) ?>" aria-label="Perbesar foto: <?= e($np['caption'] ?: $groupName) ?>">
                                                    <?php if ($np['thumbnail'] && upload_file_exists('news', $np['thumbnail'])): ?>
                                                        <img src="<?= e(upload_url('news/' . $np['thumbnail'])) ?>" alt="<?= e($np['caption'] ?: $groupName) ?>" loading="lazy">
                                                    <?php else: ?>
                                                        <img src="<?= e(upload_url('news/' . $np['image'])) ?>" alt="<?= e($np['caption'] ?: $groupName) ?>" loading="lazy">
                                                    <?php endif; ?>
                                                    <span class="dp-gallery-zoom" aria-hidden="true"><i class="bi bi-zoom-in"></i></span>
                                                </a>
                                            </figure>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                            <p class="dp-text-muted small mt-3 mb-0"><i class="bi bi-lightbulb me-1" aria-hidden="true"></i>Klik foto untuk memperbesar. Gunakan panah kiri/kanan pada keyboard untuk berpindah foto.</p>
                        <?php endif; ?>

                        <?php if ($news['source_url']): ?>
                            <p class="dp-source-note small mt-3 mb-0">
                                <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Sumber:
                                <a href="<?= e($news['source_url']) ?>" target="_blank" rel="noopener nofollow"><?= e($news['source_url']) ?></a>
                            </p>
                        <?php endif; ?>

                        <!-- Bagikan -->
                        <div class="dp-share mt-4 pt-3 border-top">
                            <span class="dp-share-label">Bagikan:</span>
                            <a class="dp-share-btn dp-share-wa" href="https://wa.me/?text=<?= e(rawurlencode($shareText . ' ' . $canonicalUrl)) ?>" target="_blank" rel="noopener" aria-label="Bagikan ke WhatsApp"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
                            <a class="dp-share-btn dp-share-fb" href="https://www.facebook.com/sharer/sharer.php?u=<?= e(rawurlencode($canonicalUrl)) ?>" target="_blank" rel="noopener" aria-label="Bagikan ke Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
                            <a class="dp-share-btn dp-share-x" href="https://twitter.com/intent/tweet?url=<?= e(rawurlencode($canonicalUrl)) ?>&text=<?= e(rawurlencode($shareText)) ?>" target="_blank" rel="noopener" aria-label="Bagikan ke X (Twitter)"><i class="bi bi-twitter-x" aria-hidden="true"></i></a>
                            <button type="button" class="dp-share-btn dp-share-copy" data-copy-link="<?= e($canonicalUrl) ?>" aria-label="Salin tautan berita">
                                <span class="dp-copy-label"><i class="bi bi-link-45deg" aria-hidden="true"></i> Salin Link</span>
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <aside class="col-lg-4" aria-label="Berita terkait">
                <div class="dp-side-block mb-4">
                    <h2 class="dp-side-block-title">Berita Terkait</h2>
                    <?php if (empty($relatedNews)): ?>
                        <p class="dp-text-muted small mb-0">Belum ada berita lain pada kategori ini.</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($relatedNews as $rel): ?>
                                <article class="dp-related-item">
                                    <?php if (upload_file_exists('news', $rel['thumbnail'])): ?>
                                        <a class="dp-related-thumb" href="<?= e(base_url('?page=detail-berita&slug=' . urlencode($rel['slug']))) ?>" tabindex="-1" aria-hidden="true">
                                            <img src="<?= e(upload_url('news/' . $rel['thumbnail'])) ?>" alt="" loading="lazy">
                                        </a>
                                    <?php endif; ?>
                                    <div class="flex-grow-1 min-w-0">
                                        <h3 class="dp-related-title"><a href="<?= e(base_url('?page=detail-berita&slug=' . urlencode($rel['slug']))) ?>"><?= e($rel['title']) ?></a></h3>
                                        <div class="dp-card-meta mb-0">
                                            <?php if ($rel['category_name']): ?>
                                                <span><?= e($rel['category_name']) ?></span>
                                            <?php endif; ?>
                                            <span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($rel['published_at'])) ?></span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="dp-side-block">
                    <h2 class="dp-side-block-title">Lihat Juga</h2>
                    <ul class="list-unstyled dp-side-links mb-0">
                        <li><a href="<?= e(base_url('?page=berita')) ?>"><i class="bi bi-newspaper" aria-hidden="true"></i> Semua Berita</a></li>
                        <li><a href="<?= e(base_url('?page=pengumuman')) ?>"><i class="bi bi-megaphone" aria-hidden="true"></i> Pengumuman</a></li>
                        <li><a href="<?= e(base_url('?page=dokumentasi')) ?>"><i class="bi bi-images" aria-hidden="true"></i> Dokumentasi Kegiatan</a></li>
                        <li><a href="<?= e(base_url('?page=faq')) ?>"><i class="bi bi-question-circle" aria-hidden="true"></i> FAQ</a></li>
                    </ul>
                </div>
            </aside>
        </div>
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