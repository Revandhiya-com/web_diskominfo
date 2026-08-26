<?php
/**
 * CMS Halaman Statis — list (profil, layanan, dll.) + edit + publish/unpublish.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    if (post_val('action') === 'toggle') {
        toggle_status('pages', 'title', 'halaman');
    }
    if (post_val('action') === 'delete') {
        $id = (int) post_val('id');
        $p = q_one('SELECT id, title FROM pages WHERE id = ?', [$id]);
        if ($p) {
            q_exec('DELETE FROM pages WHERE id = ?', [$id]);
            log_activity('delete', 'page', $id, 'Hapus halaman: ' . $p['title']);
            flash_redirect('success', 'Halaman dihapus.', 'admin/pages.php');
        }
        flash_redirect('danger', 'Halaman tidak ditemukan.', 'admin/pages.php');
    }
}

$q = post_max('q', 100, get_val('q'));
$st = get_val('status', '');
$parts = [];
$params = [];
if ($q !== '') {
    $parts[] = '(title LIKE ? OR slug LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($st === 'published' || $st === 'draft') {
    $parts[] = 'status = ?';
    $params[] = $st;
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';
$total = (int) (q_one("SELECT COUNT(*) n FROM pages $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 15);
$items = q_all("SELECT id, title, slug, status, updated_at FROM pages $where ORDER BY title ASC LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}", $params);

admin_layout_start('Halaman Statis', 'pages');
?>
<div class="alert alert-info small py-2"><i class="bi bi-info-circle me-1"></i>Halaman ini dipakai publik, contoh: <code>?page=profil</code>, <code>?page=layanan</code>. Slug menentukan URL-nya.</div>
<div class="admin-card mb-3">
    <div class="admin-card-head"><h2>Halaman Statis</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('admin/page-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Halaman</a></div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4"><label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Judul / slug…"></div>
            <div class="col-md-3"><label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua status</option>
                    <option value="published" <?= $st === 'published' ? 'selected' : '' ?>>Terbit</option>
                    <option value="draft" <?= $st === 'draft' ? 'selected' : '' ?>>Draf</option>
                </select></div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/pages.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="table table-admin mb-0">
            <thead><tr><th>Judul</th><th>Slug / URL</th><th>Diperbarui</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                <?php if (!$items): ?><tr><td colspan="5" class="empty-muted">Belum ada halaman.</td></tr>
                <?php else: foreach ($items as $p): ?>
                    <tr>
                        <td><strong><?= e($p['title']) ?></strong></td>
                        <td><code>?page=<?= e($p['slug']) ?></code></td>
                        <td><?= e($p['updated_at'] ? format_date_id($p['updated_at']) : '-') ?></td>
                        <td><?= status_badge($p['status']) ?>
                            <form method="post" class="d-inline ms-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button class="btn btn-sm btn-link p-0 align-baseline" type="submit"
                                        title="<?= $p['status'] === 'published' ? 'Tarik ke draf' : 'Terbitkan' ?>">
                                    <i class="bi <?= $p['status'] === 'published' ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-end"><div class="admin-actions justify-content-end">
                            <a class="btn-admin-sm" href="<?= e(base_url('?page=' . urlencode($p['slug']))) ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i>Lihat</a>
                            <a class="btn-admin-sm" href="<?= e(base_url('admin/page-form.php?id=' . (int) $p['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                            <form method="post" class="d-inline" data-confirm="Hapus halaman ini?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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