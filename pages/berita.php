<?php
/**
 * Halaman Berita — daftar berita dengan pencarian, filter kategori, dan pagination.
 * URL detail: ?page=detail-berita&slug=...
 */

/* Link lama (homepage) memakai ?page=berita&slug=... → alihkan ke detail. */
if (!empty($_GET['slug'])) {
    redirect('?page=detail-berita&slug=' . urlencode((string) $_GET['slug']));
}

$pageTitle = 'Berita';
$pageDescription = 'Berita, kegiatan, dan karya terkait program PKL dan Magang Diskominfo Provinsi Banten.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Berita'],
];

$perPage = 6;
$q       = get_val('q');
$kategori = get_val('kategori'); // slug kategori

/* Kategori untuk filter (dari database) */
$categories = q_all('SELECT id, name, slug FROM news_categories ORDER BY name ASC');

$where  = ['n.status = $$published$$'];
$params = [];
if ($q !== '') {
    $where[] = '(n.title LIKE ? OR n.excerpt LIKE ? OR n.content LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($kategori !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $kategori;
}
$whereSql = implode(' AND ', $where);

$total = (int) (q_one(
    "SELECT COUNT(*) AS total
     FROM news n
     LEFT JOIN news_categories c ON c.id = n.category_id
     WHERE $whereSql",
    $params
)['total'] ?? 0);

$pagination = pagination_info($total, $perPage);

$newsList = q_all(
    "SELECT n.id, n.title, n.slug, n.excerpt, n.thumbnail, n.published_at,
            c.name AS category_name, c.slug AS category_slug
     FROM news n
     LEFT JOIN news_categories c ON c.id = n.category_id
     WHERE $whereSql
     ORDER BY n.published_at DESC
     LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
    $params
);

$hasThumb = function (string $dir, ?string $file): bool {
    return upload_file_exists($dir, $file);
};
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Berita dan informasi terbaru seputar program PKL dan Magang di lingkungan Diskominfo Provinsi Banten.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <!-- Filter: pencarian + kategori -->
        <form class="dp-filter-bar row g-2 align-items-end mb-4" method="get" action="<?= e(base_url()) ?>" role="search">
            <input type="hidden" name="page" value="berita">
            <div class="col-md-5">
                <label class="dp-form-label" for="news-q">Cari Berita</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="search" class="form-control dp-form-control" id="news-q" name="q" value="<?= e($q) ?>" placeholder="Kata kunci judul, ringkasan, isi...">
                </div>
            </div>
            <div class="col-md-4">
                <label class="dp-form-label" for="news-cat">Kategori</label>
                <select class="form-select dp-form-select" id="news-cat" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>" <?= $kategori === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn dp-btn-primary flex-fill"><i class="bi bi-funnel" aria-hidden="true"></i> Terapkan</button>
                <?php if ($q !== '' || $kategori !== ''): ?>
                    <a href="<?= e(base_url('?page=berita')) ?>" class="btn dp-btn-outline-primary" aria-label="Hapus filter"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($total > 0): ?>
            <p class="dp-text-muted small mb-3">
                <?= e(number_format($total)) ?> berita ditemukan
                <?php if ($q !== ''): ?> untuk kata kunci "<strong><?= e($q) ?></strong>"<?php endif; ?>
                <?php if ($kategori !== ''): ?> pada kategori "<strong><?= e($kategori) ?></strong>"<?php endif; ?>.
            </p>
        <?php endif; ?>

        <?php if (empty($newsList)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-newspaper" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Berita Tidak Ditemukan</p>
                <p class="dp-empty-text">Tidak ada berita yang cocok dengan pencarian atau filter Anda. Coba kata kunci lain atau hapus filter.</p>
                <a class="btn dp-btn-outline-primary" href="<?= e(base_url('?page=berita')) ?>">Tampilkan Semua Berita</a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($newsList as $news): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="dp-card">
                            <a class="dp-card-media d-block" href="<?= e(base_url('?page=detail-berita&slug=' . urlencode($news['slug']))) ?>" tabindex="-1" aria-hidden="true">
                                <?php if ($hasThumb('news', $news['thumbnail'])): ?>
                                    <img src="<?= e(upload_url('news/' . $news['thumbnail'])) ?>" alt="<?= e($news['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="dp-media-placeholder"><i class="bi bi-newspaper" aria-hidden="true"></i></div>
                                <?php endif; ?>
                            </a>
                            <div class="dp-card-body">
                                <?php if ($news['category_name']): ?>
                                    <span class="dp-badge dp-badge-blue mb-2"><?= e($news['category_name']) ?></span>
                                <?php endif; ?>
                                <h2 class="dp-card-title">
                                    <a href="<?= e(base_url('?page=detail-berita&slug=' . urlencode($news['slug']))) ?>"><?= e($news['title']) ?></a>
                                </h2>
                                <div class="dp-card-meta">
                                    <span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($news['published_at'])) ?></span>
                                </div>
                                <?php if ($news['excerpt']): ?>
                                    <p class="dp-card-text"><?= e($news['excerpt']) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php $pagination = $pagination; require BASE_PATH . '/includes/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>