<?php
/**
 * Halaman Alumni PKL & Magang — pencarian nama/sekolah/jurusan/tahun
 * dengan daftar kartu dan pagination.
 * URL detail: ?page=detail-alumni&slug=...
 */

$pageTitle = 'Alumni PKL & Magang';
$pageDescription = 'Cari dan kenali alumni yang pernah melaksanakan PKL dan Magang di Diskominfo Provinsi Banten.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Alumni'],
];

$perPage = 12;
$q = get_val('q');

$where  = ['status = "published"'];
$params = [];
if ($q !== '') {
    $where[] = '(name LIKE ? OR institution LIKE ? OR major LIKE ? OR CAST(year AS CHAR) LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = implode(' AND ', $where);

$total = (int) (q_one("SELECT COUNT(*) AS total FROM alumni WHERE $whereSql", $params)['total'] ?? 0);
$pagination = pagination_info($total, $perPage);
$alumni = q_all(
    "SELECT id, name, slug, photo, type, institution, major, start_date, end_date, year, division
     FROM alumni
     WHERE $whereSql
     ORDER BY COALESCE(year, YEAR(created_at)) DESC, name ASC
     LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
    $params
);

$photoSrc = function (?string $photo): string {
    if (upload_file_exists('alumni', $photo)) {
        return upload_url('alumni/' . $photo);
    }
    if (upload_file_exists('alumni', thumb_of((string) $photo))) {
        return upload_url('alumni/' . thumb_of((string) $photo));
    }
    return '';
};
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Alumni yang pernah melaksanakan Praktik Kerja Lapangan dan Magang di lingkungan Diskominfo Provinsi Banten.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <form class="dp-filter-bar row g-2 align-items-end mb-4" method="get" action="<?= e(base_url()) ?>" role="search">
            <input type="hidden" name="page" value="alumni">
            <div class="col-md-7">
                <label class="dp-form-label" for="alumni-q">Cari Nama Alumni</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="search" class="form-control dp-form-control" id="alumni-q" name="q" value="<?= e($q) ?>" placeholder="Cari nama, sekolah/kampus, jurusan, atau tahun...">
                </div>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn dp-btn-primary flex-fill"><i class="bi bi-search" aria-hidden="true"></i> Cari</button>
                <?php if ($q !== ''): ?>
                    <a href="<?= e(base_url('?page=alumni')) ?>" class="btn dp-btn-outline-primary" aria-label="Hapus pencarian"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($total > 0): ?>
            <p class="dp-text-muted small mb-3">
                <?= e(number_format($total)) ?> alumni ditemukan
                <?php if ($q !== ''): ?> untuk kata kunci "<strong><?= e($q) ?></strong>"<?php endif; ?>.
            </p>
        <?php endif; ?>

        <?php if (empty($alumni)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Data alumni tidak ditemukan.</p>
                <p class="dp-empty-text">Tidak ada alumni yang cocok dengan pencarian Anda. Coba kata kunci lain.</p>
                <a class="btn dp-btn-outline-primary" href="<?= e(base_url('?page=alumni')) ?>">Tampilkan Semua Alumni</a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($alumni as $a): ?>
                    <div class="col-md-4 col-lg-3">
                        <article class="dp-card">
                            <a class="dp-card-media dp-card-media-square d-block" href="<?= e(base_url('?page=detail-alumni&slug=' . urlencode($a['slug']))) ?>" tabindex="-1" aria-hidden="true">
                                <?php if ($photoSrc($a['photo']) !== ''): ?>
                                    <img src="<?= e($photoSrc($a['photo'])) ?>" alt="Foto alumni <?= e($a['name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="dp-media-placeholder"><i class="bi bi-person-fill" aria-hidden="true"></i></div>
                                <?php endif; ?>
                            </a>
                            <div class="dp-card-body">
                                <span class="dp-badge <?= $a['type'] === 'magang' ? 'dp-badge-gray' : 'dp-badge-blue' ?> mb-2"><?= e(strtoupper($a['type'])) ?></span>
                                <h2 class="dp-card-title">
                                    <a href="<?= e(base_url('?page=detail-alumni&slug=' . urlencode($a['slug']))) ?>"><?= e($a['name']) ?></a>
                                </h2>
                                <?php if ($a['institution']): ?>
                                    <p class="dp-card-text mb-1"><?= e($a['institution']) ?></p>
                                <?php endif; ?>
                                <?php if ($a['major']): ?>
                                    <p class="dp-card-text mb-1"><?= e($a['major']) ?></p>
                                <?php endif; ?>
                                <div class="dp-card-meta mt-2 mb-0">
                                    <span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_period_id($a['start_date'], $a['end_date'])) ?></span>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php $pagination = $pagination; require BASE_PATH . '/includes/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>