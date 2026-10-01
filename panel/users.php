<?php
/**
 * CMS Pengguna — list + search + pagination + hapus.
 * Khusus admin (role editor dialihkan).
 */
define('ADMIN_ONLY', true);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    if (post_val('action') === 'delete') {
        $id = (int) post_val('id');
        if ($id === (int) current_user()['id']) {
            flash_redirect('danger', 'Tidak bisa menghapus akun sendiri.', 'panel/users.php');
        }
        $u = q_one('SELECT id, name FROM users WHERE id = ?', [$id]);
        if ($u) {
            q_exec('DELETE FROM users WHERE id = ?', [$id]);
            log_activity('delete', 'user', $id, 'Hapus pengguna: ' . $u['name']);
            flash_redirect('success', 'Pengguna dihapus.', 'panel/users.php');
        }
        flash_redirect('danger', 'Pengguna tidak ditemukan.', 'panel/users.php');
    }
}

$q = post_max('q', 100, get_val('q'));
$parts = [];
$params = [];
if ($q !== '') {
    $parts[] = '(name LIKE ? OR username LIKE ? OR email LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';
$total = (int) (q_one("SELECT COUNT(*) n FROM users $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 10);
$items = q_all(
    "SELECT id, name, username, email, role, is_active, last_login_at, created_at
     FROM users $where
     ORDER BY role ASC, name ASC
     LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}",
    $params
);
$me = (int) current_user()['id'];

admin_layout_start('Pengguna', 'users');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head">
        <h2>Pengguna</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('panel/user-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Pengguna</a>
    </div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Nama / username / email…">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('panel/users.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="table table-admin mb-0">
            <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Peran</th><th>Status</th><th>Terakhir Login</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="7" class="empty-muted">Belum ada pengguna.</td></tr>
                <?php else: foreach ($items as $u): ?>
                    <tr>
                        <td><strong><?= e($u['name']) ?></strong><?= (int) $u['id'] === $me ? ' <span class="dp-badge dp-badge-gold">Anda</span>' : '' ?></td>
                        <td><code><?= e($u['username']) ?></code></td>
                        <td><?= e($u['email'] ?: '-') ?></td>
                        <td>
                            <span class="dp-badge <?= $u['role'] === 'admin' ? 'dp-badge-gold' : 'dp-badge-gray' ?>">
                                <?= $u['role'] === 'admin' ? 'Admin' : 'Editor' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ((int) $u['is_active']): ?>
                                <span class="dp-badge dp-badge-gold">Aktif</span>
                            <?php else: ?>
                                <span class="dp-badge dp-badge-gray">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($u['last_login_at'] ? format_date_id($u['last_login_at'], true) : '-') ?></td>
                        <td class="text-end">
                            <div class="admin-actions justify-content-end">
                                <a class="btn-admin-sm" href="<?= e(base_url('panel/user-form.php?id=' . (int) $u['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                                <?php if ((int) $u['id'] !== $me): ?>
                                    <form method="post" class="d-inline" data-confirm="Hapus pengguna ini?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                        <button class="btn-admin-sm danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
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
