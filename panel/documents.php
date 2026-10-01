<?php
/**
 * CMS Dokumen — list + filter + tambah/edit + hapus (file ikut dihapus).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    if (post_val('action') === 'delete') {
        $id = (int) post_val('id');
        $d = q_one('SELECT id, name, file FROM documents WHERE id = ?', [$id]);
        if ($d) {
            delete_uploaded('documents', $d['file']);
            q_exec('DELETE FROM documents WHERE id = ?', [$id]);
            log_activity('delete', 'document', $id, 'Hapus dokumen: ' . $d['name']);
            flash_redirect('success', 'Dokumen dihapus.', 'panel/documents.php');
        }
        flash_redirect('danger', 'Dokumen tidak ditemukan.', 'panel/documents.php');
    }
}

$q = post_max('q', 100, get_val('q'));
$cat = (int) get_val('category', '0');
$parts = [];
$params = [];
if ($q !== '') {
    $parts[] = 'd.name LIKE ?';
    $params[] = "%$q%";
}
if ($cat > 0) {
    $parts[] = 'd.category_id = ?';
    $params[] = $cat;
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';
$total = (int) (q_one("SELECT COUNT(*) n FROM documents d $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 10);
$items = q_all(
    "SELECT d.*, c.name AS category_name
     FROM documents d
     LEFT JOIN document_categories c ON c.id = d.category_id
     $where
     ORDER BY d.uploaded_at DESC
     LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}",
    $params
);
$categories = q_all('SELECT id, name FROM document_categories ORDER BY name ASC');

admin_layout_start('Dokumen', 'documents');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head"><h2>Dokumen Unduhan</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('panel/document-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Dokumen</a></div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4"><label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Nama dokumen…"></div>
            <div class="col-md-4"><label class="form-label-admin">Kategori</label>
                <select class="form-select" name="category">
                    <option value="0">Semua kategori</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $cat === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('panel/documents.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="table table-admin mb-0">
            <thead><tr><th>Nama</th><th>Kategori</th><th>Ukuran</th><th>Unduhan</th><th>Diunggah</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                <?php if (!$items): ?><tr><td colspan="6" class="empty-muted">Belum ada dokumen.</td></tr>
                <?php else: foreach ($items as $d): ?>
                    <tr>
                        <td><strong><?= e($d['name']) ?></strong><div class="small text-muted"><?= e($d['file']) ?></div></td>
                        <td><?= e($d['category_name'] ?: '-') ?></td>
                        <td><?= e(format_size((int) $d['size'])) ?></td>
                        <td><?= e(number_format($d['downloads'])) ?></td>
                        <td><?= e(format_date_id($d['uploaded_at'])) ?></td>
                        <td class="text-end"><div class="admin-actions justify-content-end">
                            <a class="btn-admin-sm" href="<?= e(base_url('?page=dokumen&download=' . (int) $d['id'])) ?>" target="_blank" rel="noopener"><i class="bi bi-download"></i>Unduh</a>
                            <a class="btn-admin-sm" href="<?= e(base_url('panel/document-form.php?id=' . (int) $d['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                            <form method="post" class="d-inline" data-confirm="Hapus dokumen ini beserta filenya?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                                <button class="btn-admin-sm danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pageInfo['total_pages'] > 1): ?>
        <div class="admin-card-body border-top"><nav aria-label="Paginasi"><ul class="pagination pagination-sm mb-0">
            <?php for ($i = 1; $i <= $pageInfo['total_pages']; $i++): ?>
                <li class="page-item <?= $i === $pageInfo['current'] ? 'active' : '' ?>"><a class="page-link" href="<?= e(pagination_href($i)) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul></nav></div>
    <?php endif; ?>
</div>
<?php
admin_layout_end();
