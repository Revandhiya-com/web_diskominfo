<?php
/**
 * Halaman Dokumentasi — daftar album dokumentasi kegiatan.
 * Fitur: pencarian, filter kategori, tahun, bulan, pagination, terbaru dulu.
 */

$pageTitle = 'Dokumentasi';
$pageDescription = 'Arsip dokumentasi kegiatan PKL dan Magang di lingkungan Diskominfo Provinsi Banten.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Dokumentasi'],
];

$perPage = 9;
$q        = get_val('q');
$catSlug  = get_val('kategori');
$tahun    = get_val('tahun');
$bulan    = get_val('bulan'); // 1-12

/* Kategori album (dari database) */
$categories = q_all('SELECT id, name, slug FROM album_categories ORDER BY name ASC');

/* Tahun yang tersedia (dari data album) */
$years = q_all(
    'SELECT DISTINCT EXTRACT(YEAR FROM event_date) AS tahun
     FROM albums
     WHERE status = \'published\' AND event_date IS NOT NULL
     ORDER BY tahun DESC'
);

$bulanList = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

/* Bangun filter */
$where  = ['a.status = \'published\''];
$params = [];
if ($q !== '') {
    $where[] = '(a.title LIKE ? OR a.description LIKE ? OR a.location LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($catSlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $catSlug;
}
if ($tahun !== '' && ctype_digit($tahun)) {
    $where[] = 'EXTRACT(YEAR FROM a.event_date) = ?';
    $params[] = (int) $tahun;
}
if ($bulan !== '' && ctype_digit($bulan) && (int) $bulan >= 1 && (int) $bulan <= 12) {
    $where[] = 'EXTRACT(MONTH FROM a.event_date) = ?';
    $params[] = (int) $bulan;
}
$whereSql = implode(' AND ', $where);

$total = (int) (q_one(
    "SELECT COUNT(*) AS total
     FROM albums a
     LEFT JOIN album_categories c ON c.id = a.category_id
     WHERE $whereSql",
    $params
)['total'] ?? 0);

$pagination = pagination_info($total, $perPage);

$albums = q_all(
    "SELECT a.id, a.title, a.slug, a.description, a.event_date, a.location, a.cover_image,
            c.name AS category_name,
            (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id AND p.media_type = 'image') AS photo_count,
            (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id AND p.media_type = 'video') AS video_count
     FROM albums a
     LEFT JOIN album_categories c ON c.id = a.category_id
     WHERE $whereSql
     ORDER BY a.event_date IS NULL, a.event_date DESC, a.created_at DESC
     LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
    $params
);

$hasFilter = $q !== '' || $catSlug !== '' || $tahun !== '' || $bulan !== '';
$hasCover = function (?string $file): bool {
    return upload_file_exists('albums', $file);
};
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Arsip digital kegiatan PKL dan Magang — dokumentasi terbaru tampil otomatis setelah admin mengunggah album.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <!-- Filter -->
        <form class="dp-filter-bar row g-2 align-items-end mb-4" method="get" action="<?= e(base_url()) ?>" role="search">
            <input type="hidden" name="page" value="dokumentasi">
            <div class="col-md-4">
                <label class="dp-form-label" for="doc-q">Cari Dokumentasi</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="search" class="form-control dp-form-control" id="doc-q" name="q" value="<?= e($q) ?>" placeholder="Judul kegiatan, lokasi, deskripsi...">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="dp-form-label" for="doc-cat">Kategori</label>
                <select class="form-select dp-form-select" id="doc-cat" name="kategori">
                    <option value="">Semua</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>" <?= $catSlug === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="dp-form-label" for="doc-year">Tahun</label>
                <select class="form-select dp-form-select" id="doc-year" name="tahun">
                    <option value="">Semua</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?= (int) $y['tahun'] ?>" <?= $tahun === (string) $y['tahun'] ? 'selected' : '' ?>><?= (int) $y['tahun'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="dp-form-label" for="doc-month">Bulan</label>
                <select class="form-select dp-form-select" id="doc-month" name="bulan">
                    <option value="">Semua</option>
                    <?php foreach ($bulanList as $num => $name): ?>
                        <option value="<?= $num ?>" <?= $bulan === (string) $num ? 'selected' : '' ?>><?= e($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn dp-btn-primary flex-fill"><i class="bi bi-funnel" aria-hidden="true"></i> Filter</button>
                <?php if ($hasFilter): ?>
                    <a href="<?= e(base_url('?page=dokumentasi')) ?>" class="btn dp-btn-outline-primary" aria-label="Hapus filter"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($total > 0): ?>
            <p class="dp-text-muted small mb-3"><?= e(number_format($total)) ?> album ditemukan.</p>
        <?php endif; ?>

        <?php if (empty($albums)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-images" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Dokumentasi Tidak Ditemukan</p>
                <p class="dp-empty-text">Tidak ada dokumentasi yang cocok dengan pencarian atau filter Anda.</p>
                <a class="btn dp-btn-outline-primary" href="<?= e(base_url('?page=dokumentasi')) ?>">Tampilkan Semua</a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($albums as $album): ?>
                    <div class="col-sm-6 col-lg-4">
                        <article class="dp-card">
                            <a class="dp-card-media d-block" href="<?= e(base_url('?page=album&slug=' . urlencode($album['slug']))) ?>" tabindex="-1" aria-hidden="true">
                                <?php if ($hasCover($album['cover_image'])): ?>
                                    <img src="<?= e(upload_url('albums/' . $album['cover_image'])) ?>" alt="<?= e($album['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="dp-media-placeholder"><i class="bi bi-image" aria-hidden="true"></i></div>
                                <?php endif; ?>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="dp-badge dp-badge-gold"><i class="bi bi-camera" aria-hidden="true"></i> <?= (int) $album['photo_count'] ?> Foto<?php if ((int) $album['video_count'] > 0): ?> · <?= (int) $album['video_count'] ?> Video<?php endif; ?></span>
                                </div>
                            </a>
                            <div class="dp-card-body">
                                <h2 class="dp-card-title">
                                    <a href="<?= e(base_url('?page=album&slug=' . urlencode($album['slug']))) ?>"><?= e($album['title']) ?></a>
                                </h2>
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

            <?php $pagination = $pagination; require BASE_PATH . '/includes/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>