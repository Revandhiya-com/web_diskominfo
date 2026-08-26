<?php
/**
 * Pesan Masuk — daftar, baca/tandai dibaca, hapus.
 * Khusus admin (isi pesan pengunjung tidak untuk editor).
 */
define('ADMIN_ONLY', true);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    $action = post_val('action');
    $id = (int) post_val('id');

    if ($action === 'read' && $id > 0) {
        q_exec('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [$id]);
        redirect('admin/messages.php?view=' . $id);
    }
    if ($action === 'unread' && $id > 0) {
        q_exec('UPDATE contact_messages SET is_read = 0 WHERE id = ?', [$id]);
        flash_redirect('success', 'Pesan ditandai belum dibaca.', 'admin/messages.php');
    }
    if ($action === 'delete' && $id > 0) {
        $m = q_one('SELECT id, name FROM contact_messages WHERE id = ?', [$id]);
        if ($m) {
            q_exec('DELETE FROM contact_messages WHERE id = ?', [$id]);
            log_activity('delete', 'message', $id, 'Hapus pesan dari: ' . $m['name']);
            flash_redirect('success', 'Pesan dihapus.', 'admin/messages.php');
        }
        flash_redirect('danger', 'Pesan tidak ditemukan.', 'admin/messages.php');
    }
}

$viewId = (int) get_val('view');
$q = post_max('q', 100, get_val('q'));
$parts = [];
$params = [];
if ($q !== '') {
    $parts[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
    $params = ["%$q%", "%$q%", "%$q%", "%$q%"];
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';
$total = (int) (q_one("SELECT COUNT(*) n FROM contact_messages $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 15);
$messages = q_all("SELECT * FROM contact_messages $where ORDER BY created_at DESC LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}", $params);
$current = $viewId > 0 ? q_one('SELECT * FROM contact_messages WHERE id = ?', [$viewId]) : ($messages[0] ?? null);

admin_layout_start('Pesan Masuk', 'messages');
?>
<div class="row g-3">
    <div class="<?= $current ? 'col-lg-5' : 'col-12' ?>">
        <div class="admin-card">
            <div class="admin-card-head"><h2>Pesan Masuk</h2></div>
            <div class="admin-card-body pb-2">
                <form method="get" class="row g-2">
                    <div class="col-8"><input class="form-control form-control-sm" type="text" name="q" value="<?= e($q) ?>" placeholder="Cari nama/email/pesan…"></div>
                    <div class="col-4"><button class="btn btn-outline-primary btn-sm w-100" type="submit"><i class="bi bi-search"></i></button></div>
                </form>
            </div>
            <div class="admin-card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (!$messages): ?>
                        <div class="admin-card-body empty-muted">Belum ada pesan masuk.</div>
                    <?php else: foreach ($messages as $m): ?>
                        <?php if ($q === ''): ?>
                            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-start gap-2 <?= $current && (int) $current['id'] === (int) $m['id'] ? 'active' : '' ?>"
                               href="<?= e(base_url('admin/messages.php?view=' . (int) $m['id'])) ?>">
                                <div class="min-w-0" style="min-width:0">
                                    <div class="small fw-bold <?= (int) $m['is_read'] ? 'text-body' : 'text-primary' ?>"><?= (int) $m['is_read'] ? '' : '● ' ?><?= e($m['name']) ?></div>
                                    <div class="small text-truncate"><?= e($m['subject'] ?: '(tanpa subjek)') ?></div>
                                    <div class="small text-muted"><?= e(format_date_id($m['created_at'], true)) ?></div>
                                </div>
                            </a>
                        <?php else: ?>
                            <div class="list-group-item">
                                <a href="<?= e(base_url('admin/messages.php?view=' . (int) $m['id'])) ?>"><strong><?= e($m['name']) ?></strong></a>
                                <div class="small text-muted"><?= e($m['subject'] ?: '-') ?> · <?= e(format_date_id($m['created_at'], true)) ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <?php if ($pageInfo['total_pages'] > 1): ?>
                <div class="admin-card-body border-top"><nav aria-label="Paginasi"><ul class="pagination pagination-sm mb-0">
                    <?php for ($i = 1; $i <= $pageInfo['total_pages']; $i++): ?>
                        <li class="page-item <?= $i === $pageInfo['current'] ? 'active' : '' ?>"><a class="page-link" href="<?= e(pagination_href($i)) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                </ul></nav></div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($current): ?>
        <div class="col-lg-7">
            <div class="admin-card">
                <div class="admin-card-head">
                    <h2>Detail Pesan</h2>
                    <div class="d-flex gap-2">
                        <?php if ((int) $current['is_read']): ?>
                            <form method="post" class="d-inline"><?= csrf_field() ?>
                                <input type="hidden" name="action" value="unread">
                                <input type="hidden" name="id" value="<?= (int) $current['id'] ?>">
                                <button class="btn-admin-sm" type="submit"><i class="bi bi-envelope"></i>Tandai belum dibaca</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" class="d-inline" data-confirm="Hapus pesan ini?"><?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $current['id'] ?>">
                            <button class="btn-admin-sm danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                        </form>
                    </div>
                </div>
                <div class="admin-card-body">
                    <h3 class="h5"><?= e($current['subject'] ?: '(tanpa subjek)') ?></h3>
                    <p class="text-muted small mb-3">
                        Dari: <strong><?= e($current['name']) ?></strong> &lt;<?= e($current['email']) ?>&gt;<br>
                        Diterima: <?= e(format_date_id($current['created_at'], true)) ?>
                        <?php if ($current['ip']): ?> · IP: <?= e($current['ip']) ?><?php endif; ?>
                    </p>
                    <div class="p-3 bg-light rounded"><div style="white-space:pre-line"><?= e($current['message']) ?></div></div>
                    <div class="mt-3">
                        <a class="btn btn-sm btn-outline-primary" href="mailto:<?= e($current['email']) ?>?subject=Re: <?= e($current['subject'] ?: 'Pesan Anda') ?>"><i class="bi bi-reply me-1"></i>Balas via Email</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php
admin_layout_end();