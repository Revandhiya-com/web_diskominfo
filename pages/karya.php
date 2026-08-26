<?php
/**
 * Halaman publik Karya — galeri hasil karya peserta PKL & Magang.
 * Kartu: cover gambar ATAU poster video (dengan tombol play); detail di
 * ?page=detail-karya&id=... Tempat video diputar langsung (HTML5).
 * Karya lama tetap tampil sebagai arsip mingguan (filter tim/minggu/jenis).
 */
$pageTitle = 'Karya Peserta';
$pageDescription = 'Galeri karya peserta PKL dan Magang Diskominfo Provinsi Banten: infografis, video edukasi, drama pendek, video kampanye, dan karya visual lainnya.';
$breadcrumbs = [
    ['label' => 'Home', 'url' => base_url()],
    ['label' => 'Karya'],
];

$jenisLabel = [
    'infografis' => 'Infografis',
    'video'      => 'Video',
    'foto'       => 'Foto',
    'lainnya'    => 'Lainnya',
];
$jenisIcon = [
    'infografis' => 'bi-bar-chart-fill',
    'video'      => 'bi-play-btn-fill',
    'foto'       => 'bi-camera-fill',
    'lainnya'    => 'bi-palette-fill',
];

$fJenis = get_val('jenis');
$fTim   = get_val('tim');
$fMinggu = (int) get_val('minggu', '0');

$where  = ["k.status = 'published'"];
$params = [];
if (isset($jenisLabel[$fJenis])) {
    $where[] = 'k.jenis = ?';
    $params[] = $fJenis;
}
if (in_array($fTim, ['Tim 1', 'Tim 2', 'Tim 3'], true)) {
    $where[] = 'k.team = ?';
    $params[] = $fTim;
}
if ($fMinggu > 0) {
    $where[] = 'k.week_number = ?';
    $params[] = $fMinggu;
}
/* Hanya karya yang medianya benar-benar ada di server. */
$where[] = "(k.media_type = 'video' AND v.file_path <> '' OR k.media_type = 'image')";
$whereSql = implode(' AND ', $where);

$total = (int) (q_one(
    "SELECT COUNT(*) n FROM karya k LEFT JOIN videos v ON v.id = k.video_id WHERE $whereSql",
    $params
)['n'] ?? 0);
$pagination = pagination_info($total, 9);

$karyaList = q_all(
    "SELECT k.id, k.title, k.description, k.jenis, k.media_type, k.thumbnail, k.team,
            k.week_number, k.published_at, k.created_at,
            v.file_path AS video_file, v.thumbnail AS video_thumb
     FROM karya k
     LEFT JOIN videos v ON v.id = k.video_id
     WHERE $whereSql
     ORDER BY k.week_number DESC, k.team ASC, COALESCE(k.published_at, k.created_at) DESC, k.id DESC
     LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
    $params
);

$hasFilter = ($fJenis !== '' && isset($jenisLabel[$fJenis])) || $fTim !== '' || $fMinggu > 0;

/** Cover kartu: file gambar / poster video. Return [src|null, isVideo]. */
$karyaCover = function (array $k): array {
    if (($k['media_type'] ?? '') === 'video') {
        /* thumbnail karya ada di uploads/karya/, poster videos di uploads/videos/ */
        if ($k['thumbnail'] && upload_file_exists('karya', $k['thumbnail'])) {
            return ['karya/' . $k['thumbnail'], true];
        }
        if ($k['video_thumb'] && upload_file_exists('videos', $k['video_thumb'])) {
            return ['videos/' . $k['video_thumb'], true];
        }
        return [null, true];
    }
    if ($k['thumbnail'] && upload_file_exists('karya', $k['thumbnail'])) {
        return ['karya/' . $k['thumbnail'], false];
    }
    if ($k['file_path'] && upload_file_exists('karya', $k['file_path'])) {
        return ['karya/' . $k['file_path'], false];
    }
    return [null, false];
};
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Hasil karya terbaik peserta PKL &amp; Magang Diskominfo Provinsi Banten — infografis, video edukasi, drama pendek, hingga video kampanye.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <!-- Filter: jenis, tim, periode -->
        <form class="dp-filter-bar row g-2 align-items-end mb-4" method="get" action="<?= e(base_url()) ?>">
            <input type="hidden" name="page" value="karya">
            <div class="col-md-3">
                <label class="dp-form-label" for="karya-jenis">Jenis Karya</label>
                <select class="form-select dp-form-select" id="karya-jenis" name="jenis">
                    <option value="">Semua Jenis</option>
                    <?php foreach ($jenisLabel as $j => $l): ?>
                        <option value="<?= e($j) ?>" <?= $fJenis === $j ? 'selected' : '' ?>><?= e($l) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="dp-form-label" for="karya-tim">Tim</label>
                <select class="form-select dp-form-select" id="karya-tim" name="tim">
                    <option value="">Semua Tim</option>
                    <?php foreach (['Tim 1', 'Tim 2', 'Tim 3'] as $t): ?>
                        <option value="<?= e($t) ?>" <?= $fTim === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="dp-form-label" for="karya-minggu">Periode</label>
                <select class="form-select dp-form-select" id="karya-minggu" name="minggu">
                    <option value="">Semua Periode</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $fMinggu === $i ? 'selected' : '' ?>>Minggu ke-<?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn dp-btn-primary flex-fill"><i class="bi bi-funnel" aria-hidden="true"></i> Terapkan</button>
                <?php if ($hasFilter): ?>
                    <a href="<?= e(base_url('?page=karya')) ?>" class="btn dp-btn-outline-primary" aria-label="Hapus filter"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($total > 0): ?>
            <p class="dp-text-muted small mb-3"><?= e(number_format($total)) ?> karya ditemukan.</p>
        <?php endif; ?>

        <?php if (empty($karyaList)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-palette" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Karya</p>
                <p class="dp-empty-text">Karya peserta akan segera hadir di galeri ini. Nantikan pembaruan dari kami<?php if ($hasFilter): ?>, atau coba ubah filter<?php endif; ?>.</p>
                <?php if ($hasFilter): ?>
                    <a class="btn dp-btn-outline-primary" href="<?= e(base_url('?page=karya')) ?>">Tampilkan Semua Karya</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($karyaList as $k):
                    [$coverSrc, $isVideo] = $karyaCover($k);
                    $detailUrl = base_url('?page=detail-karya&id=' . (int) $k['id']);
                ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="dp-card h-100 dp-media-card">
                            <a class="dp-card-media d-block dp-karya-cover" href="<?= e($detailUrl) ?>" tabindex="-1" aria-hidden="true">
                                <?php if ($coverSrc): ?>
                                    <img src="<?= e(upload_url($coverSrc)) ?>" alt="<?= e($k['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <span class="dp-media-placeholder"><i class="bi <?= e($jenisIcon[$k['jenis']]) ?>" aria-hidden="true"></i></span>
                                <?php endif; ?>
                                <?php if ($isVideo): ?>
                                    <span class="dp-karya-play" aria-hidden="true"><i class="bi bi-play-circle-fill"></i></span>
                                <?php endif; ?>
                            </a>
                            <div class="dp-card-body">
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="dp-badge dp-badge-blue"><i class="bi <?= e($jenisIcon[$k['jenis']]) ?>" aria-hidden="true"></i> <?= e($jenisLabel[$k['jenis']]) ?></span>
                                    <?php if ($k['week_number']): ?>
                                        <span class="dp-badge dp-badge-gold"><i class="bi bi-calendar-week" aria-hidden="true"></i> Minggu ke-<?= (int) $k['week_number'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <h2 class="dp-card-title"><a href="<?= e($detailUrl) ?>"><?= e($k['title']) ?></a></h2>
                                <div class="dp-card-meta">
                                    <span><i class="bi bi-people-fill" aria-hidden="true"></i><?= e($k['team'] ?: 'Tanpa tim') ?></span>
                                    <span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($k['published_at'] ?: $k['created_at'])) ?></span>
                                </div>
                                <?php if ($k['description']): ?>
                                    <p class="dp-card-text"><?= nl2br(e(trim_words($k['description'], 22))) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php require BASE_PATH . '/includes/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>
