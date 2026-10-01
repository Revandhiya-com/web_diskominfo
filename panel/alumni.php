<?php
/**
 * CMS Alumni — list + cari + publish/unpublish + hapus alumni PKL/Magang.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    if (post_val('action') === 'toggle') {
        toggle_status('alumni', 'name', 'alumni');
    }
    if (post_val('action') === 'delete') {
        $id = (int) post_val('id');
        $a = q_one('SELECT id, name, photo FROM alumni WHERE id = ?', [$id]);
        if ($a) {
            delete_uploaded('alumni', $a['photo']);
            q_exec('DELETE FROM alumni WHERE id = ?', [$id]);
            log_activity('delete', 'alumni', $id, 'Hapus alumni: ' . $a['name']);
            flash_redirect('success', 'Alumni dihapus.', 'panel/alumni.php');
        }
        flash_redirect('danger', 'Alumni tidak ditemukan.', 'panel/alumni.php');
    }
}

$q = post_max('q', 100, get_val('q'));
$parts = [];
$params = [];
if ($q !== '') {
    $parts[] = '(name LIKE ? OR institution LIKE ? OR major LIKE ? OR CAST(year AS CHAR) LIKE ?)';
    $like = "%$q%";
    array_push($params, $like, $like, $like, $like);
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';
$total = (int) (q_one("SELECT COUNT(*) n FROM alumni $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 10);
$items = q_all("SELECT * FROM alumni $where ORDER BY COALESCE(year, EXTRACT(YEAR FROM created_at)) DESC, name ASC LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}", $params);

admin_layout_start('Alumni', 'alumni');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head"><h2>Alumni PKL &amp; Magang</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('panel/alumni-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Alumni</a></div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-6"><label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Nama, sekolah/kampus, jurusan, tahun…"></div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('panel/alumni.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="table table-admin mb-0">
            <thead><tr><th>Alumni</th><th>Jenis</th><th>Asal</th><th>Periode</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                <?php if (!$items): ?><tr><td colspan="6" class="empty-muted">Belum ada data alumni.</td></tr>
                <?php else: foreach ($items as $a): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (upload_file_exists('alumni', $a['photo'])): ?>
                                    <img src="<?= e(upload_url('alumni/' . $a['photo'])) ?>" alt="" class="rounded" style="width:38px;height:38px;object-fit:cover">
                                <?php else: ?>
                                    <span class="d-inline-flex align-items-center justify-content-center bg-secondary-subtle rounded" style="width:38px;height:38px"><i class="bi bi-person text-secondary"></i></span>
                                <?php endif; ?>
                                <strong><?= e($a['name']) ?></strong>
                            </div>
                        </td>
                        <td><span class="badge text-bg-<?= $a['type'] === 'magang' ? 'info' : 'primary' ?>"><?= e(strtoupper($a['type'])) ?></span></td>
                        <td><span class="small"><?= e($a['institution'] ?: '-') ?></span></td>
                        <td class="small text-muted"><?= e(format_period_id($a['start_date'], $a['end_date'])) ?><?= $a['year'] ? ' · ' . e($a['year']) : '' ?></td>
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
                            <a class="btn-admin-sm" href="<?= e(base_url('?page=detail-alumni&slug=' . urlencode($a['slug']))) ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i>Lihat</a>
                            <a class="btn-admin-sm" href="<?= e(base_url('panel/alumni-form.php?id=' . (int) $a['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                            <form method="post" class="d-inline" data-confirm="Hapus alumni ini?">
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
