<?php
/**
 * CMS Agenda — list + tambah/edit + hapus + publish/unpublish.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    if (post_val('action') === 'toggle') {
        toggle_status('agendas', 'title', 'agenda');
    }
    if (post_val('action') === 'delete') {
        $id = (int) post_val('id');
        $a = q_one('SELECT id, title FROM agendas WHERE id = ?', [$id]);
        if ($a) {
            q_exec('DELETE FROM agendas WHERE id = ?', [$id]);
            log_activity('delete', 'agenda', $id, 'Hapus agenda: ' . $a['title']);
            flash_redirect('success', 'Agenda dihapus.', 'admin/agendas.php');
        }
        flash_redirect('danger', 'Agenda tidak ditemukan.', 'admin/agendas.php');
    }
}

$q = post_max('q', 100, get_val('q'));
$parts = [];
$params = [];
if ($q !== '') {
    $parts[] = '(title LIKE ? OR location LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';
$total = (int) (q_one("SELECT COUNT(*) n FROM agendas $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 10);
$items = q_all("SELECT * FROM agendas $where ORDER BY start_datetime DESC LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}", $params);

admin_layout_start('Agenda', 'agendas');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head"><h2>Agenda</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('admin/agenda-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Agenda</a></div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-6"><label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Judul / lokasi…"></div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/agendas.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="table table-admin mb-0">
            <thead><tr><th>Agenda</th><th>Mulai</th><th>Selesai</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                <?php if (!$items): ?><tr><td colspan="5" class="empty-muted">Belum ada agenda.</td></tr>
                <?php else: foreach ($items as $a): ?>
                    <tr>
                        <td><strong><?= e($a['title']) ?></strong><div class="small text-muted"><?= e($a['location'] ?: '-') ?></div></td>
                        <td><?= e(format_date_id($a['start_datetime'], true)) ?></td>
                        <td><?= e($a['end_datetime'] ? format_date_id($a['end_datetime'], true) : '-') ?></td>
                        <td><?= status_badge($a['status']) ?>
                            <form method="post" class="d-inline ms-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                <button class="btn btn-sm btn-link p-0 align-baseline" type="submit"
                                        title="<?= $a['status'] === 'published' ? 'Tarik ke draf' : 'Terbitkan' ?>">
                                    <i class="bi <?= $a['status'] === 'published' ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-end"><div class="admin-actions justify-content-end">
                            <a class="btn-admin-sm" href="<?= e(base_url('admin/agenda-form.php?id=' . (int) $a['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                            <form method="post" class="d-inline" data-confirm="Hapus agenda ini?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
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