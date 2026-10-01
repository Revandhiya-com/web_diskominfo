<?php
/**
 * CMS FAQ — list + tambah/edit + hapus + aktif/nonaktif.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    if (post_val('action') === 'toggle') {
        toggle_active('faqs', 'question', 'FAQ');
    }
    if (post_val('action') === 'delete') {
        $id = (int) post_val('id');
        $f = q_one('SELECT id, question FROM faqs WHERE id = ?', [$id]);
        if ($f) {
            q_exec('DELETE FROM faqs WHERE id = ?', [$id]);
            log_activity('delete', 'faq', $id, 'Hapus FAQ: ' . $f['question']);
            flash_redirect('success', 'FAQ dihapus.', 'panel/faqs.php');
        }
        flash_redirect('danger', 'FAQ tidak ditemukan.', 'panel/faqs.php');
    }
}

$q = post_max('q', 100, get_val('q'));
$parts = [];
$params = [];
if ($q !== '') {
    $parts[] = '(question LIKE ? OR answer LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';
$total = (int) (q_one("SELECT COUNT(*) n FROM faqs $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 15);
$items = q_all("SELECT * FROM faqs $where ORDER BY sort_order ASC, id ASC LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}", $params);

admin_layout_start('FAQ', 'faqs');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head"><h2>FAQ</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('panel/faq-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah FAQ</a></div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-6"><label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Pertanyaan / jawaban…"></div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('panel/faqs.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="table table-admin mb-0">
            <thead><tr><th>Urutan</th><th>Pertanyaan</th><th>Kategori</th><th>Aktif</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                <?php if (!$items): ?><tr><td colspan="5" class="empty-muted">Belum ada FAQ.</td></tr>
                <?php else: foreach ($items as $f): ?>
                    <tr>
                        <td><?= (int) $f['sort_order'] ?></td>
                        <td><strong><?= e($f['question']) ?></strong><div class="small text-muted"><?= e(trim_words($f['answer'], 16)) ?></div></td>
                        <td><?= e($f['category'] ?: '-') ?></td>
                        <td><?= (int) $f['is_active'] ? '<span class="dp-badge dp-badge-gold">Aktif</span>' : '<span class="dp-badge dp-badge-gray">Nonaktif</span>' ?>
                            <form method="post" class="d-inline ms-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                                <button class="btn btn-sm btn-link p-0 align-baseline" type="submit"
                                        title="<?= (int) $f['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                    <i class="bi <?= (int) $f['is_active'] ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-end"><div class="admin-actions justify-content-end">
                            <a class="btn-admin-sm" href="<?= e(base_url('panel/faq-form.php?id=' . (int) $f['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                            <form method="post" class="d-inline" data-confirm="Hapus FAQ ini?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
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
