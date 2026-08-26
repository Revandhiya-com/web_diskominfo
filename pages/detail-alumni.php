<?php
/**
 * Halaman Detail Alumni — foto, identitas publik, periode, dan pengalaman.
 * Hanya menampilkan data yang boleh diketahui publik (tanpa kontak/NIK/dll).
 */

$slug = get_val('slug');
if ($slug === '') {
    redirect('?page=alumni');
}

$alumni = q_one(
    'SELECT id, name, slug, photo, type, institution, major, start_date, end_date, year, division, description
     FROM alumni
     WHERE status = $$published$$ AND slug = ?',
    [$slug]
);

if (!$alumni) {
    $pageTitle = 'Alumni Tidak Ditemukan';
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    return;
}

$pageTitle = $alumni['name'];
$pageDescription = $alumni['institution']
    ? ($alumni['name'] . ' — Alumni ' . strtoupper($alumni['type']) . ' ' . $alumni['institution'])
    : ('Alumni ' . strtoupper($alumni['type']) . ': ' . $alumni['name']);
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Alumni', 'url' => base_url('?page=alumni')],
    ['label' => $alumni['name']],
];

$canonicalUrl = base_url('?page=detail-alumni&slug=' . urlencode($alumni['slug']));
$seoType = 'profile';
if (upload_file_exists('alumni', $alumni['photo'])) {
    $seoImage    = upload_url('alumni/' . $alumni['photo']);
    $seoImageAlt = 'Foto alumni ' . $alumni['name'];
}

$photoSrc = function (?string $photo): string {
    if (upload_file_exists('alumni', $photo)) {
        return upload_url('alumni/' . $photo);
    }
    if (upload_file_exists('alumni', thumb_of((string) $photo))) {
        return upload_url('alumni/' . thumb_of((string) $photo));
    }
    return '';
};

$infoRows = [
    ['label' => 'Jenis', 'value' => $alumni['type'] === 'magang' ? 'Magang' : 'PKL (Praktik Kerja Lapangan)'],
    ['label' => 'Asal Sekolah/Kampus', 'value' => $alumni['institution']],
    ['label' => 'Jurusan', 'value' => $alumni['major']],
    ['label' => 'Periode', 'value' => format_period_id($alumni['start_date'], $alumni['end_date'])],
    ['label' => 'Tahun', 'value' => $alumni['year'] ? (string) $alumni['year'] : ''],
    ['label' => 'Bidang/Divisi', 'value' => $alumni['division']],
];
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title" class="fs-2"><?= e($alumni['name']) ?></h1>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <article class="dp-card">
                    <div class="dp-card-body">
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                <div class="dp-alumni-photo mx-auto">
                                    <?php if ($photoSrc($alumni['photo']) !== ''): ?>
                                        <img src="<?= e($photoSrc($alumni['photo'])) ?>" alt="Foto alumni <?= e($alumni['name']) ?>">
                                    <?php else: ?>
                                        <span class="dp-alumni-photo-placeholder"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="dp-badge <?= $alumni['type'] === 'magang' ? 'dp-badge-gray' : 'dp-badge-blue' ?>"><?= e(strtoupper($alumni['type'])) ?></span>
                                    <?php if ($alumni['year']): ?>
                                        <span class="dp-badge dp-badge-gold"><i class="bi bi-calendar3" aria-hidden="true"></i>Tahun <?= e($alumni['year']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <h2 class="dp-card-title fs-4 mb-3"><?= e($alumni['name']) ?></h2>
                                <dl class="dp-alumni-info mb-0">
                                    <?php foreach ($infoRows as $row): ?>
                                        <?php if ($row['value'] !== '' && $row['value'] !== '-'): ?>
                                            <div class="dp-alumni-info-row">
                                                <dt><?= e($row['label']) ?></dt>
                                                <dd><?= e($row['value']) ?></dd>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </dl>
                            </div>
                        </div>

                        <?php if ($alumni['description']): ?>
                            <div class="mt-4">
                                <h3 class="dp-subsection-title">Pengalaman</h3>
                                <div class="dp-prose">
                                    <?= nl2br(e($alumni['description'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <a class="btn dp-btn-outline-primary" href="<?= e(base_url('?page=alumni')) ?>"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Daftar Alumni</a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>