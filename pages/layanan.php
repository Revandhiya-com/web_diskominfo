<?php
/**
 * Halaman Layanan â€” Informasi PKL, Informasi Magang, Persyaratan, Alur, Dokumen Terkait.
 * Konten dari tabel `pages` + `documents` (placeholder bila kosong).
 */

$pageTitle = 'Layanan';
$pageDescription = 'Informasi layanan Praktik Kerja Lapangan (PKL) dan Magang: persyaratan, alur pendaftaran, dan dokumen terkait.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Layanan'],
];

/* Konten seksi dari tabel pages (slug tetap) */
$pageSlugs = ['informasi-pkl', 'informasi-magang', 'persyaratan-pkl', 'persyaratan-magang', 'alur-pendaftaran'];
$pageRows = [];
foreach (q_all(
    'SELECT slug, title, content, source_url FROM pages WHERE slug IN (' . implode(',', array_fill(0, count($pageSlugs), '?')) . ')',
    $pageSlugs
) as $row) {
    $pageRows[$row['slug']] = $row;
}

$section = function (string $slug, string $fallbackTitle, string $fallbackContent): array {
    global $pageRows;
    $row = $pageRows[$slug] ?? null;
    return [
        'title'      => $row['title'] ?? $fallbackTitle,
        'content'    => $row['content'] ?? $fallbackContent,
        'source_url' => $row['source_url'] ?? null,
    ];
};

$infoPkl    = $section('informasi-pkl', 'Informasi PKL',
    '<p>Informasi resmi mengenai pelaksanaan Praktik Kerja Lapangan (PKL) akan ditampilkan di sini setelah data resmi diterima dari Diskominfo Provinsi Banten.</p>');
$infoMagang = $section('informasi-magang', 'Informasi Magang',
    '<p>Informasi resmi mengenai pelaksanaan Magang akan ditampilkan di sini setelah data resmi diterima dari Diskominfo Provinsi Banten.</p>');
$syaratPkl  = $section('persyaratan-pkl', 'Persyaratan PKL',
    '<p>Persyaratan resmi pengajuan PKL akan ditampilkan di sini setelah dikonfirmasi oleh tim pengelola.</p>');
$syaratMagang = $section('persyaratan-magang', 'Persyaratan Magang',
    '<p>Persyaratan resmi pengajuan Magang akan ditampilkan di sini setelah dikonfirmasi oleh tim pengelola.</p>');
$alur       = $section('alur-pendaftaran', 'Alur Pendaftaran',
    '<p>Alur pendaftaran resmi akan ditampilkan di sini setelah dikonfirmasi oleh tim pengelola.</p>');

/* Dokumen terkait â€” 6 terbaru dari tabel documents */
$relatedDocs = q_all(
    'SELECT d.id, d.name, d.file, d.size, d.mime, d.uploaded_at, c.name AS category_name
     FROM documents d
     LEFT JOIN document_categories c ON c.id = d.category_id
     ORDER BY d.uploaded_at DESC
     LIMIT 6'
);
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Informasi dan kebutuhan digital Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten.</p>
    </div>
</section>

<!-- ============ INFORMASI PKL & MAGANG ============ -->
<section class="dp-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <article class="dp-feature-card">
                    <div class="dp-feature-card-head">
                        <span class="dp-feature-icon" aria-hidden="true"><i class="bi bi-briefcase"></i></span>
                        <div>
                            <span class="dp-feature-kicker">Siswa SMK/sederajat</span>
                            <h2 class="dp-feature-title"><?= e($infoPkl['title']) ?></h2>
                        </div>
                    </div>
                    <div class="dp-feature-card-body">
                        <div class="dp-prose"><?= sanitize_admin_html($infoPkl['content']) ?></div>
                        <?php if (!empty($infoPkl['source_url'])): ?>
                            <p class="dp-source-note small mt-3 mb-0">
                                <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Sumber:
                                <a href="<?= e($infoPkl['source_url']) ?>" target="_blank" rel="noopener nofollow"><?= e($infoPkl['source_url']) ?></a>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
            <div class="col-lg-6">
                <article class="dp-feature-card">
                    <div class="dp-feature-card-head is-gold">
                        <span class="dp-feature-icon" aria-hidden="true"><i class="bi bi-mortarboard"></i></span>
                        <div>
                            <span class="dp-feature-kicker">Mahasiswa</span>
                            <h2 class="dp-feature-title"><?= e($infoMagang['title']) ?></h2>
                        </div>
                    </div>
                    <div class="dp-feature-card-body">
                        <div class="dp-prose"><?= sanitize_admin_html($infoMagang['content']) ?></div>
                        <?php if (!empty($infoMagang['source_url'])): ?>
                            <p class="dp-source-note small mt-3 mb-0">
                                <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Sumber:
                                <a href="<?= e($infoMagang['source_url']) ?>" target="_blank" rel="noopener nofollow"><?= e($infoMagang['source_url']) ?></a>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<!-- ============ PERSYARATAN ============ -->
<section class="dp-section dp-section-gray" id="persyaratan" aria-labelledby="persyaratan-heading">
    <div class="container">
        <div class="dp-section-head">
            <h2 class="dp-section-title" id="persyaratan-heading">Persyaratan</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <article class="dp-article-card h-100">
                    <div class="dp-article-card-head">
                        <span class="dp-article-card-icon" aria-hidden="true"><i class="bi bi-file-earmark-check"></i></span>
                        <h3 class="dp-article-card-title"><?= e($syaratPkl['title']) ?></h3>
                        <span class="dp-article-card-num" aria-hidden="true">01</span>
                    </div>
                    <div class="dp-article-card-body">
                        <div class="dp-prose"><?= sanitize_admin_html($syaratPkl['content']) ?></div>
                        <?php if (!empty($syaratPkl['source_url'])): ?>
                            <p class="dp-source-note small mt-3 mb-0">
                                <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Sumber:
                                <a href="<?= e($syaratPkl['source_url']) ?>" target="_blank" rel="noopener nofollow"><?= e($syaratPkl['source_url']) ?></a>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
            <div class="col-lg-6">
                <article class="dp-article-card h-100">
                    <div class="dp-article-card-head">
                        <span class="dp-article-card-icon" aria-hidden="true"><i class="bi bi-file-earmark-check"></i></span>
                        <h3 class="dp-article-card-title"><?= e($syaratMagang['title']) ?></h3>
                        <span class="dp-article-card-num" aria-hidden="true">02</span>
                    </div>
                    <div class="dp-article-card-body">
                        <div class="dp-prose"><?= sanitize_admin_html($syaratMagang['content']) ?></div>
                        <?php if (!empty($syaratMagang['source_url'])): ?>
                            <p class="dp-source-note small mt-3 mb-0">
                                <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Sumber:
                                <a href="<?= e($syaratMagang['source_url']) ?>" target="_blank" rel="noopener nofollow"><?= e($syaratMagang['source_url']) ?></a>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<!-- ============ ALUR ============ -->
<section class="dp-section" id="alur" aria-labelledby="alur-heading">
    <div class="container">
        <div class="dp-section-head">
            <h2 class="dp-section-title" id="alur-heading">Alur Pendaftaran</h2>
        </div>
        <div class="dp-card">
            <div class="dp-card-body">
                <ol class="dp-steps mb-0">
                    <?php if (($pageRows['alur-pendaftaran']['content'] ?? '') !== ''): ?>
                        <?= sanitize_admin_html($alur['content']) ?>
                    <?php else: ?>
                        <li><strong>Menunggu konfirmasi data resmi</strong> â€” alur pendaftaran akan ditampilkan setelah tim pengelola Diskominfo Provinsi Banten mengisi halaman ini.</li>
                    <?php endif; ?>
                </ol>
            </div>
        </div>
        <p class="dp-text-muted small mt-3 mb-0"><i class="bi bi-info-circle me-1" aria-hidden="true"></i> Alur di atas dikelola oleh administrator dan akan diperbarui sesuai ketentuan resmi instansi.</p>
    </div>
</section>

<!-- ============ DOKUMEN TERKAIT ============ -->
<section class="dp-section dp-section-gray" aria-labelledby="dokumen-heading">
    <div class="container">
        <div class="dp-section-head">
            <h2 class="dp-section-title" id="dokumen-heading">Dokumen Terkait</h2>
            <a class="dp-section-link" href="<?= e(base_url('?page=dokumen')) ?>">Semua Dokumen <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>

        <?php if (empty($relatedDocs)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Belum Ada Dokumen</p>
                <p class="dp-empty-text">Dokumen pendukung akan ditampilkan di sini setelah diunggah oleh tim pengelola.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($relatedDocs as $doc): ?>
                    <div class="col-md-6">
                        <article class="dp-doc-row">
                            <span class="dp-doc-icon dp-doc-<?= strtolower(e(file_type_label($doc['file']))) ?>" aria-hidden="true"><?= e(file_type_label($doc['file'])) ?></span>
                            <div class="flex-grow-1 min-w-0">
                                <h3 class="dp-doc-name mb-1"><?= e($doc['name']) ?></h3>
                                <div class="dp-card-meta mb-0">
                                    <span><i class="bi bi-calendar3" aria-hidden="true"></i><?= e(format_date_id($doc['uploaded_at'])) ?></span>
                                    <span><i class="bi bi-hdd" aria-hidden="true"></i><?= e(format_size((int) $doc['size'])) ?></span>
                                </div>
                            </div>
                            <a class="btn dp-btn-outline-primary btn-sm" href="<?= e(base_url('?page=dokumen&download=' . (int) $doc['id'])) ?>"><i class="bi bi-download" aria-hidden="true"></i> Unduh</a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>