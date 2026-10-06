<?php
/**
 * Halaman Beranda — 13 seksi.
 * Seluruh konten dinamis berasal dari database (placeholder bila kosong).
 */

$pageTitle = 'Beranda';
$pageDescription = 'Portal resmi PKL dan Magang Diskominfo Provinsi Banten: informasi layanan, berita, pengumuman, agenda, dokumentasi kegiatan, download dokumen, dan FAQ.';

/* ============================================================
   1. HERO — teks dari tabel settings
   ============================================================ */
$heroTitle    = setting('hero_title', 'Ruang Tumbuh Talenta Digital');
$heroSubtitle = setting('hero_subtitle', 'Diskominfo Provinsi Banten • KP3B Serang');
$heroDesc     = setting('hero_description', 'Satu ruang untuk belajar, berkarya, dan membangun layanan digital yang berdampak bagi Banten.');
/* Nilai seed lama tetap ditingkatkan secara aman; nilai yang telah disunting admin tidak disentuh. */
if ($heroTitle === 'Portal PKL & Magang') {
    $heroTitle = 'Ruang Tumbuh Talenta Digital';
}
if ($heroSubtitle === 'Diskominfo Provinsi Banten') {
    $heroSubtitle = 'Diskominfo Provinsi Banten • KP3B Serang';
}
if ($heroDesc === 'Pusat informasi, layanan, berita, dan dokumentasi kegiatan Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten.') {
    $heroDesc = 'Satu ruang untuk belajar, berkarya, dan membangun layanan digital yang berdampak bagi Banten.';
}
$heroBtn1     = [
    'label' => setting('hero_btn_primary', 'Mulai Eksplorasi'),
    'link'  => setting('hero_btn_primary_link', '?page=layanan'),
];
$heroBtn2     = [
    'label' => setting('hero_btn_secondary', 'Informasi Magang'),
    'link'  => setting('hero_btn_secondary_link', '?page=layanan'),
];
if ($heroBtn1['label'] === 'Informasi PKL') {
    $heroBtn1['label'] = 'Mulai Eksplorasi';
}

/* ============================================================
   4. PENGUMUMAN TERBARU — maks 5
   ============================================================ */
$announcements = q_all(
    "SELECT id, title, content, is_pinned, published_at
     FROM announcements
     WHERE status = 'published'
     ORDER BY is_pinned DESC, published_at DESC
     LIMIT 5"
);

/* ============================================================
   5. TENTANG PROGRAM — dari settings
   ============================================================ */
$aboutTitle = setting('about_title', 'Tentang Program');
$aboutText  = setting('about_content', '');
$aboutBtn   = [
    'label' => setting('about_btn', 'Pelajari Lebih Lanjut'),
    'link'  => setting('about_btn_link', '?page=profil'),
];

/* ============================================================
   6. STATISTIK — hitungan nyata dari database
   ============================================================ */
$statNews   = (int) (q_one("SELECT COUNT(*) AS total FROM news WHERE status = 'published'")['total'] ?? 0);
$statAlbums = (int) (q_one("SELECT COUNT(*) AS total FROM albums WHERE status = 'published'")['total'] ?? 0);
$statPhotos = (int) (q_one('SELECT COUNT(*) AS total FROM photos')['total'] ?? 0);
$statAgenda = (int) (q_one("SELECT COUNT(*) AS total FROM agendas WHERE status = 'published'")['total'] ?? 0);

/* ============================================================
   7. BERITA TERBARU — maks 3
   ============================================================ */
$latestNews = q_all(
    "SELECT n.id, n.title, n.slug, n.excerpt, n.thumbnail, n.published_at,
            c.name AS category_name
     FROM news n
     LEFT JOIN news_categories c ON c.id = n.category_id
     WHERE n.status = 'published'
     ORDER BY n.published_at DESC
     LIMIT 3"
);

/* ============================================================
   8. AGENDA TERDEKAT — 3 agenda mendatang
   ============================================================ */
$upcomingAgendas = q_all(
    "SELECT id, title, description, location, start_datetime, end_datetime
     FROM agendas
     WHERE status = 'published' AND start_datetime >= NOW()
     ORDER BY start_datetime ASC
     LIMIT 3"
);

/* ============================================================
   9. DOKUMENTASI TERBARU — album, maks 6
   ============================================================ */
$latestAlbums = q_all(
    "SELECT a.id, a.title, a.slug, a.description, a.event_date, a.location,
            a.cover_image, c.name AS category_name,
            (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id) AS photo_count
     FROM albums a
     LEFT JOIN album_categories c ON c.id = a.category_id
     WHERE a.status = 'published'
     ORDER BY a.created_at DESC
     LIMIT 6"
);

/* ============================================================
   10. FAQ SINGKAT — maks 4
   ============================================================ */
$shortFaqs = q_all(
    "SELECT id, question, answer
     FROM faqs
     WHERE is_active = 1
     ORDER BY sort_order ASC, id ASC
     LIMIT 4"
);

/* ============================================================
   12. CTA — dari settings
   ============================================================ */
$ctaTitle = setting('cta_title', 'Siap Memulai PKL atau Magang?');
$ctaText  = setting('cta_text', 'Pelajari persyaratan, alur pendaftaran, dan dokumen yang dibutuhkan.');
$ctaBtn   = [
    'label' => setting('cta_btn', 'Hubungi Kami'),
    'link'  => setting('cta_btn_link', '?page=kontak'),
];

/* Helper placeholder gambar (mendukung Supabase Storage & local uploads) */
$hasThumb = function (string $dir, ?string $file): bool {
    return upload_file_exists($dir, $file);
};
?>
<!-- ============ 2. HERO ============ -->
<section class="dp-hero dp-hero--banten" aria-labelledby="hero-title">
    <div class="dp-hero-visual" aria-hidden="true">
        <img src="<?= e(asset_url('images/kp3b-gedung-opd.jpg')) ?>" alt="" fetchpriority="high">
    </div>
    <div class="dp-hero-orb dp-hero-orb--one" aria-hidden="true"></div>
    <div class="dp-hero-orb dp-hero-orb--two" aria-hidden="true"></div>
    <div class="container">
        <div class="row align-items-center justify-content-center text-center py-3">
            <div class="col-lg-10">
                <span class="dp-hero-eyebrow mb-3 d-inline-block"><i class="bi bi-stars" aria-hidden="true"></i> Portal PKL &amp; Magang &middot; Banten</span>
                <h1 id="hero-title"><?= e($heroTitle) ?></h1>
                <p class="dp-hero-subtitle"><?= e($heroSubtitle) ?></p>
                <p class="dp-hero-desc mx-auto" style="max-width: 760px;"><?= e($heroDesc) ?></p>
                <div class="dp-hero-actions d-flex flex-wrap justify-content-center gap-3">
                    <a class="btn dp-btn-gold btn-lg" href="<?= e(base_url($heroBtn1['link'])) ?>"><i class="bi bi-briefcase" aria-hidden="true"></i> <?= e($heroBtn1['label']) ?></a>
                    <a class="btn dp-btn-light-outline btn-lg" href="<?= e(base_url($heroBtn2['link'])) ?>"><i class="bi bi-mortarboard" aria-hidden="true"></i> <?= e($heroBtn2['label']) ?></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ 3. QUICK ACCESS ============ -->
<section class="dp-section" aria-label="Akses cepat">
    <div class="container">
        <div class="row g-3">
            <?php
            $quickItems = [
                ['bi-file-earmark-text', 'Persyaratan', '?page=layanan#persyaratan'],
                ['bi-pencil-square', 'Pendaftaran', '?page=layanan#alur'],
                ['bi-megaphone', 'Pengumuman', '?page=pengumuman'],
                ['bi-folder2-open', 'Dokumen', '?page=dokumen'],
                ['bi-images', 'Dokumentasi', '?page=dokumentasi'],
                ['bi-question-circle', 'FAQ', '?page=faq'],
            ];
            foreach ($quickItems as $item): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a class="dp-quick-card" href="<?= e(base_url($item[2])) ?>">
                        <i class="bi <?= e($item[0]) ?>" aria-hidden="true"></i>
                        <span><?= e($item[1]) ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ 4. PENGUMUMAN TERBARU ============ -->
<section class="dp-section pt-0" aria-labelledby="pengumuman-heading">
    <div class="container">
        <div class="dp-section-head">
            <h2 class="dp-section-title" id="pengumuman-heading">Pengumuman Terbaru</h2>
            <a class="dp-section-link" href="<?= e(base_url('?page=pengumuman')) ?>">Semua Pengumuman <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>

        <?php if (empty($announcements)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-megaphone" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Pengumuman</p>
                <p class="dp-empty-text">Pengumuman resmi dari tim pengelola akan ditampilkan di sini.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($announcements as $index => $ann): ?>
                    <div class="col-12">
                        <article class="dp-news-item">
                            <div class="dp-news-body flex-grow-1">
                                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                    <?php if ((int) $ann['is_pinned'] === 1): ?>
                                        <span class="dp-badge dp-badge-pinned"><i class="bi bi-pin-angle-fill" aria-hidden="true"></i> Penting</span>
                                    <?php endif; ?>
                                    <span class="dp-card-meta mb-0"><i class="bi bi-calendar3" aria-hidden="true"></i> <?= e(format_date_id($ann['published_at'], true)) ?></span>
                                </div>
                                <h3 class="dp-news-title mb-1">
                                    <a href="<?= e(base_url('?page=pengumuman&id=' . (int) $ann['id'])) ?>"><?= e($ann['title']) ?></a>
                                </h3>
                                <?php if ($ann['content'] !== null && $ann['content'] !== ''): ?>
                                    <p class="dp-card-text mb-0"><?= e($ann['content']) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ 5. TENTANG PROGRAM ============ -->
<section class="dp-section dp-section-gray" aria-labelledby="tentang-heading">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <div class="dp-about-visual rounded-3">
                    <img src="<?= e(asset_url('images/kp3b-gedung-opd.jpg')) ?>" alt="Gedung SKPD Terpadu di kawasan KP3B, Curug, Kota Serang">
                    <span class="dp-about-visual-label"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> KP3B &middot; Kota Serang</span>
                </div>
            </div>
            <div class="col-lg-7">
                <span class="dp-badge dp-badge-gold mb-2">Tentang Kami</span>
                <h2 class="dp-section-title mb-3" id="tentang-heading"><?= e($aboutTitle) ?></h2>
                <p class="text-secondary mb-4"><?= e($aboutText) ?></p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="d-flex gap-2"><i class="bi bi-check-circle-fill text-primary" aria-hidden="true"></i><span class="small">Pusat informasi resmi kegiatan PKL dan Magang</span></div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2"><i class="bi bi-check-circle-fill text-primary" aria-hidden="true"></i><span class="small">Dokumentasi kegiatan secara terstruktur</span></div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2"><i class="bi bi-check-circle-fill text-primary" aria-hidden="true"></i><span class="small">Dokumen pendukung yang dapat diunduh</span></div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2"><i class="bi bi-check-circle-fill text-primary" aria-hidden="true"></i><span class="small">Pengumuman dan agenda terbaru</span></div>
                    </div>
                </div>
                <a class="btn dp-btn-primary" href="<?= e(base_url($aboutBtn['link'])) ?>"><?= e($aboutBtn['label']) ?> <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- ============ 6. STATISTIK ============ -->
<section class="dp-stats" aria-label="Statistik situs">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-lg-3">
                <div class="dp-stat">
                    <div class="dp-stat-value"><?= e(number_format($statAlbums)) ?></div>
                    <div class="dp-stat-label">Album Dokumentasi</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="dp-stat">
                    <div class="dp-stat-value"><?= e(number_format($statPhotos)) ?></div>
                    <div class="dp-stat-label">Foto Terunggah</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="dp-stat">
                    <div class="dp-stat-value"><?= e(number_format($statNews)) ?></div>
                    <div class="dp-stat-label">Berita</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="dp-stat">
                    <div class="dp-stat-value"><?= e(number_format($statAgenda)) ?></div>
                    <div class="dp-stat-label">Agenda</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ 7. BERITA TERBARU ============ -->
<section class="dp-section" aria-labelledby="berita-heading">
    <div class="container">
        <div class="dp-section-head">
            <h2 class="dp-section-title" id="berita-heading">Berita Terbaru</h2>
            <a class="dp-section-link" href="<?= e(base_url('?page=berita')) ?>">Semua Berita <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>

        <?php if (empty($latestNews)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-newspaper" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Berita</p>
                <p class="dp-empty-text">Berita dan kegiatan terbaru akan ditampilkan di sini.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($latestNews as $news): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="dp-card">
                            <div class="dp-card-media">
                                <?php if ($hasThumb('news', $news['thumbnail'])): ?>
                                    <img src="<?= e(upload_url('news/' . $news['thumbnail'])) ?>" alt="<?= e($news['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="dp-media-placeholder"><i class="bi bi-newspaper" aria-hidden="true"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="dp-card-body">
                                <?php if ($news['category_name']): ?>
                                    <span class="dp-badge dp-badge-blue mb-2"><?= e($news['category_name']) ?></span>
                                <?php endif; ?>
                                <h3 class="dp-card-title"><a href="<?= e(base_url('?page=berita&slug=' . urlencode($news['slug']))) ?>"><?= e($news['title']) ?></a></h3>
                                <div class="dp-card-meta"><span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($news['published_at'])) ?></span></div>
                                <?php if ($news['excerpt']): ?>
                                    <p class="dp-card-text"><?= e($news['excerpt']) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ 8. AGENDA TERDEKAT ============ -->
<section class="dp-section dp-section-gray" aria-labelledby="agenda-heading">
    <div class="container">
        <div class="dp-section-head">
            <h2 class="dp-section-title" id="agenda-heading">Agenda Terdekat</h2>
            <a class="dp-section-link" href="<?= e(base_url('?page=agenda')) ?>">Semua Agenda <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>

        <?php if (empty($upcomingAgendas)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Agenda</p>
                <p class="dp-empty-text">Agenda kegiatan resmi akan diumumkan melalui halaman ini.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($upcomingAgendas as $agenda):
                    $ts    = strtotime($agenda['start_datetime']);
                    $day   = date('j', $ts);
                    $month = date('M', $ts);
                    $date  = format_date_id($agenda['start_datetime'], true);
                    $time  = date('H:i', $ts);
                ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="dp-agenda-item">
                            <div class="dp-agenda-date" aria-hidden="true">
                                <span class="day"><?= e($day) ?></span>
                                <span class="month"><?= e($month) ?></span>
                            </div>
                            <div class="dp-agenda-info">
                                <h3 class="dp-agenda-title"><?= e($agenda['title']) ?></h3>
                                <div class="dp-agenda-meta">
                                    <span><i class="bi bi-clock" aria-hidden="true"></i> <?= e($date) ?> — <?= e($time) ?></span>
                                    <?php if ($agenda['location']): ?>
                                        <span><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e($agenda['location']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ 9. DOKUMENTASI TERBARU ============ -->
<section class="dp-section" aria-labelledby="dokumentasi-heading">
    <div class="container">
        <div class="dp-section-head">
            <h2 class="dp-section-title" id="dokumentasi-heading">Dokumentasi Terbaru</h2>
            <a class="dp-section-link" href="<?= e(base_url('?page=dokumentasi')) ?>">Semua Dokumentasi <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>

        <?php if (empty($latestAlbums)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-images" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Dokumentasi</p>
                <p class="dp-empty-text">Dokumentasi kegiatan akan tampil otomatis di sini setelah admin mengunggah album baru.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($latestAlbums as $album): ?>
                    <div class="col-sm-6 col-lg-4">
                        <article class="dp-card">
                            <div class="dp-card-media">
                                <?php if ($hasThumb('albums', $album['cover_image'])): ?>
                                    <img src="<?= e(upload_url('albums/' . $album['cover_image'])) ?>" alt="<?= e($album['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="dp-media-placeholder"><i class="bi bi-image" aria-hidden="true"></i></div>
                                <?php endif; ?>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="dp-badge dp-badge-gold"><i class="bi bi-camera" aria-hidden="true"></i> <?= (int) $album['photo_count'] ?> Foto</span>
                                </div>
                            </div>
                            <div class="dp-card-body">
                                <h3 class="dp-card-title"><a href="<?= e(base_url('?page=album&slug=' . urlencode($album['slug']))) ?>"><?= e($album['title']) ?></a></h3>
                                <div class="dp-card-meta">
                                    <?php if ($album['event_date']): ?>
                                        <span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($album['event_date'])) ?></span>
                                    <?php endif; ?>
                                    <?php if ($album['location']): ?>
                                        <span><i class="bi bi-geo-alt" aria-hidden="true"></i><?= e($album['location']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($album['category_name']): ?>
                                    <span class="dp-badge dp-badge-blue"><?= e($album['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ 10. FAQ SINGKAT ============ -->
<?php if (!empty($shortFaqs)): ?>
    <section class="dp-section" aria-labelledby="faq-heading">
        <div class="container">
            <div class="dp-section-head">
                <h2 class="dp-section-title" id="faq-heading">Pertanyaan yang Sering Diajukan</h2>
                <a class="dp-section-link" href="<?= e(base_url('?page=faq')) ?>">Semua FAQ <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="row g-3">
                <?php foreach ($shortFaqs as $faq): ?>
                    <div class="col-md-6">
                        <div class="dp-card">
                            <div class="dp-card-body">
                                <h3 class="dp-card-title"><i class="bi bi-question-circle-fill text-primary me-1" aria-hidden="true"></i><?= e($faq['question']) ?></h3>
                                <p class="dp-card-text mb-0"><?= e($faq['answer']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ============ 12. CTA ============ -->
<section class="dp-cta" aria-labelledby="cta-heading">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <h2 id="cta-heading"><?= e($ctaTitle) ?></h2>
                <p><?= e($ctaText) ?></p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="btn dp-btn-gold btn-lg" href="<?= e(base_url($ctaBtn['link'])) ?>"><i class="bi bi-envelope" aria-hidden="true"></i> <?= e($ctaBtn['label']) ?></a>
            </div>
        </div>
    </div>
</section>
