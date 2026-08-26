<?php
/**
 * Halaman Sitemap — daftar tautan seluruh halaman publik, berita, dan album.
 * Versi XML untuk mesin pencari tersedia di sitemap.php (lihat robots.txt).
 */

$pageTitle = 'Sitemap';
$pageDescription = 'Daftar seluruh halaman portal PKL dan Magang Diskominfo Provinsi Banten: layanan, berita, pengumuman, agenda, dokumentasi, download, dan FAQ.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Sitemap'],
];

$news = q_all(
    'SELECT n.slug, n.title, n.published_at, c.name AS category_name
     FROM news n
     LEFT JOIN news_categories c ON c.id = n.category_id
     WHERE n.status = "published"
     ORDER BY n.published_at DESC
     LIMIT 100'
);

$albums = q_all(
    'SELECT a.slug, a.title, a.event_date
     FROM albums a
     WHERE a.status = "published"
     ORDER BY a.created_at DESC
     LIMIT 100'
);

/* Halaman CMS statis (pages) — halaman mandiri (bukan seksi profil/layanan). */
$staticSlugs = ['profil', 'layanan', 'berita', 'pengumuman', 'agenda', 'dokumentasi', 'dokumen', 'faq', 'kontak', 'privacy', 'search', 'sitemap'];
$sectionSlugs = ['profil-program', 'tujuan', 'visi-misi', 'informasi-pkl', 'informasi-magang', 'persyaratan-pkl', 'persyaratan-magang', 'alur-pendaftaran'];
$excludedSlugs = array_merge($staticSlugs, $sectionSlugs);
$cmsPages = q_all(
    'SELECT slug, title FROM pages
     WHERE status = "published" AND slug NOT IN (' . implode(',', array_fill(0, count($excludedSlugs), '?')) . ')
     ORDER BY title ASC',
    $excludedSlugs
);
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Seluruh halaman portal dalam satu daftar. Versi untuk mesin pencari: <a href="<?= e(base_url('sitemap.php')) ?>">sitemap.php</a>.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="dp-section-title mb-3">Halaman Utama</h2>
                <ul class="list-unstyled dp-side-links mb-4">
                    <li><a href="<?= e(base_url()) ?>">Beranda</a></li>
                    <li><a href="<?= e(base_url('?page=profil')) ?>">Profil Program</a></li>
                    <li><a href="<?= e(base_url('?page=layanan')) ?>">Layanan PKL &amp; Magang</a></li>
                    <li><a href="<?= e(base_url('?page=berita')) ?>">Berita</a></li>
                    <li><a href="<?= e(base_url('?page=pengumuman')) ?>">Pengumuman</a></li>
                    <li><a href="<?= e(base_url('?page=agenda')) ?>">Agenda</a></li>
                    <li><a href="<?= e(base_url('?page=dokumentasi')) ?>">Dokumentasi</a></li>
                    <li><a href="<?= e(base_url('?page=dokumen')) ?>">Download Dokumen</a></li>
                    <li><a href="<?= e(base_url('?page=faq')) ?>">FAQ</a></li>
                    <li><a href="<?= e(base_url('?page=kontak')) ?>">Kontak</a></li>
                    <li><a href="<?= e(base_url('?page=privacy')) ?>">Kebijakan Privasi</a></li>
                </ul>

                <h2 class="dp-section-title mb-3">Halaman Informasi</h2>
                <?php if (empty($cmsPages)): ?>
                    <p class="dp-text-muted small">Belum ada halaman informasi.</p>
                <?php else: ?>
                    <ul class="list-unstyled dp-side-links mb-0">
                        <?php foreach ($cmsPages as $p): ?>
                            <li><a href="<?= e(base_url('?page=' . urlencode($p['slug']))) ?>"><?= e($p['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="col-lg-6">
                <h2 class="dp-section-title mb-3">Berita</h2>
                <?php if (empty($news)): ?>
                    <p class="dp-text-muted small">Belum ada berita.</p>
                <?php else: ?>
                    <ul class="list-unstyled dp-side-links mb-4">
                        <?php foreach ($news as $n): ?>
                            <li>
                                <?php if ($n['category_name']): ?>
                                    <span class="dp-badge dp-badge-blue me-1"><?= e($n['category_name']) ?></span>
                                <?php endif; ?>
                                <a href="<?= e(base_url('?page=detail-berita&slug=' . urlencode($n['slug']))) ?>"><?= e($n['title']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <h2 class="dp-section-title mb-3">Dokumentasi</h2>
                <?php if (empty($albums)): ?>
                    <p class="dp-text-muted small">Belum ada album dokumentasi.</p>
                <?php else: ?>
                    <ul class="list-unstyled dp-side-links mb-0">
                        <?php foreach ($albums as $a): ?>
                            <li>
                                <?php if ($a['event_date']): ?>
                                    <span class="dp-card-meta me-2"><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($a['event_date'])) ?></span>
                                <?php endif; ?>
                                <a href="<?= e(base_url('?page=album&slug=' . urlencode($a['slug']))) ?>"><?= e($a['title']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>