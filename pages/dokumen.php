<?php
/**
 * Halaman Dokumen — unduhan dokumen pendukung.
 * Fitur: filter kategori, pencarian, pagination, dan handler download
 * (?page=dokumen&download=ID) yang menambah penghitung unduhan.
 */

/* ---------- Handler download ---------- */
if (!empty($_GET['download'])) {
    $docId = (int) $_GET['download'];
    $doc = $docId > 0
        ? q_one('SELECT id, name, file, size, mime FROM documents WHERE id = ?', [$docId])
        : null;

    if (!$doc || !upload_file_exists('documents', $doc['file'])) {
        http_response_code(404);
        exit('Dokumen tidak ditemukan.');
    }

    q_exec('UPDATE documents SET downloads = downloads + 1 WHERE id = ?', [$docId]);

    $filePath = UPLOADS_PATH . '/documents/' . $doc['file'];
    $mime = $doc['mime'] ?: 'application/octet-stream';
    $downloadName = preg_replace('/["\r\n]+/', '', $doc['name']) . '.' . pathinfo($doc['file'], PATHINFO_EXTENSION);

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string) filesize($filePath));
    header('Content-Disposition: attachment; filename="' . $downloadName . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    readfile($filePath);
    exit;
}

/* ---------- Halaman daftar ---------- */
$pageTitle = 'Dokumen';
$pageDescription = 'Unduhan dokumen pendukung PKL dan Magang: panduan, formulir, surat, template, dan laporan.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Dokumen'],
];

$perPage = 10;
$q        = get_val('q');
$catSlug  = get_val('kategori');

$categories = q_all('SELECT id, name, slug FROM document_categories ORDER BY name ASC');

$where  = [];
$params = [];
if ($q !== '') {
    $where[] = 'd.name LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($catSlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $catSlug;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = (int) (q_one(
    "SELECT COUNT(*) AS total
     FROM documents d
     LEFT JOIN document_categories c ON c.id = d.category_id
     $whereSql",
    $params
)['total'] ?? 0);

$pagination = pagination_info($total, $perPage);

$docs = q_all(
    "SELECT d.id, d.name, d.file, d.size, d.mime, d.downloads, d.uploaded_at,
            c.name AS category_name
     FROM documents d
     LEFT JOIN document_categories c ON c.id = d.category_id
     $whereSql
     ORDER BY d.uploaded_at DESC
     LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
    $params
);
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Dokumen resmi dan formulir pendukung kegiatan PKL dan Magang yang dapat diunduh.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <!-- Filter -->
        <form class="dp-filter-bar row g-2 align-items-end mb-4" method="get" action="<?= e(base_url()) ?>" role="search">
            <input type="hidden" name="page" value="dokumen">
            <div class="col-md-6">
                <label class="dp-form-label" for="doc-file-q">Cari Dokumen</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="search" class="form-control dp-form-control" id="doc-file-q" name="q" value="<?= e($q) ?>" placeholder="Nama dokumen...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="dp-form-label" for="doc-file-cat">Kategori</label>
                <select class="form-select dp-form-select" id="doc-file-cat" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>" <?= $catSlug === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn dp-btn-primary flex-fill"><i class="bi bi-funnel" aria-hidden="true"></i> Filter</button>
                <?php if ($q !== '' || $catSlug !== ''): ?>
                    <a href="<?= e(base_url('?page=dokumen')) ?>" class="btn dp-btn-outline-primary" aria-label="Hapus filter"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($docs)): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Dokumen Tidak Ditemukan</p>
                <p class="dp-empty-text">Belum ada dokumen pada kategori tersebut atau tidak cocok dengan kata kunci Anda.</p>
            </div>
        <?php else: ?>
            <div class="dp-card">
                <div class="table-responsive">
                    <table class="table dp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Nama Dokumen</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Format</th>
                                <th scope="col">Ukuran</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col" class="text-end">Unduh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($docs as $doc): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="dp-doc-icon dp-doc-<?= strtolower(e(file_type_label($doc['file']))) ?>" aria-hidden="true"><?= e(file_type_label($doc['file'])) ?></span>
                                            <div class="min-w-0">
                                                <div class="fw-semibold"><?= e($doc['name']) ?></div>
                                                <small class="dp-text-muted"><i class="bi bi-download me-1" aria-hidden="true"></i><?= e(number_format((int) $doc['downloads'])) ?>x diunduh</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $doc['category_name'] ? e($doc['category_name']) : '<span class="dp-text-muted">-</span>' ?></td>
                                    <td>
                                        <span class="dp-badge dp-badge-gray"><?= e(file_type_label($doc['file'])) ?></span>
                                    </td>
                                    <td><?= e(format_size((int) $doc['size'])) ?></td>
                                    <td><?= e(format_date_id($doc['uploaded_at'])) ?></td>
                                    <td class="text-end">
                                        <a class="btn dp-btn-primary btn-sm" href="<?= e(base_url('?page=dokumen&download=' . (int) $doc['id'])) ?>">
                                            <i class="bi bi-download" aria-hidden="true"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php $pagination = $pagination; require BASE_PATH . '/includes/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>