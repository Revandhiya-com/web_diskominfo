<?php
/**
 * CMS Berita — list + search + filter + pagination + hapus.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    if (post_val('action') === 'toggle') {
        toggle_status('news', 'title', 'berita');
    }
    if (post_val('action') === 'delete') {
        $id = (int) post_val('id');
        $news = q_one('SELECT id, title, thumbnail FROM news WHERE id = ?', [$id]);
        if ($news) {
            delete_uploaded('news', $news['thumbnail']);
            q_exec('DELETE FROM news WHERE id = ?', [$id]);
            log_activity('delete', 'news', $id, 'Hapus berita: ' . $news['title']);
            flash_redirect('success', 'Berita dihapus.', 'admin/news.php');
        }
        flash_redirect('danger', 'Berita tidak ditemukan.', 'admin/news.php');
    }
}

$q   = post_max('q', 100, get_val('q'));
$cat = (int) get_val('category', '0');
$st  = get_val('status', '');
$parts = [];
$params = [];
if ($q !== '') {
    $parts[] = '(n.title LIKE ? OR n.excerpt LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($cat > 0) {
    $parts[] = 'n.category_id = ?';
    $params[] = $cat;
}
if ($st === 'published' || $st === 'draft') {
    $parts[] = 'n.status = ?';
    $params[] = $st;
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';

$total = (int) (q_one("SELECT COUNT(*) n FROM news n $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 10);
$items = q_all(
    "SELECT n.id, n.title, n.slug, n.status, n.views, n.published_at, n.thumbnail, c.name AS category_name
     FROM news n
     LEFT JOIN news_categories c ON c.id = n.category_id
     $where
     ORDER BY COALESCE(n.published_at, n.created_at) DESC
     LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}",
    $params
);
$categories = q_all('SELECT id, name FROM news_categories ORDER BY name ASC');

admin_layout_start('Berita', 'news');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head">
        <h2>Berita</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('admin/news-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Berita</a>
    </div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4"><label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Judul / ringkasan…"></div>
            <div class="col-md-3"><label class="form-label-admin">Kategori</label>
                <select class="form-select" name="category">
                    <option value="0">Semua kategori</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $cat === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="col-md-3"><label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua status</option>
                    <option value="published" <?= $st === 'published' ? 'selected' : '' ?>>Terbit</option>
                    <option value="draft" <?= $st === 'draft' ? 'selected' : '' ?>>Draf</option>
                </select></div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/news.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="table table-admin mb-0">
            <thead><tr><th>Judul</th><th>Kategori</th><th>Diterbitkan</th><th>Dilihat</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="6" class="empty-muted">Belum ada berita.</td></tr>
                <?php else: foreach ($items as $n): ?>
                    <tr>
                        <td><strong><?= e($n['title']) ?></strong><div class="small text-muted">/<?= e($n['slug']) ?></div></td>
                        <td><?= e($n['category_name'] ?: '-') ?></td>
                        <td><?= e($n['published_at'] ? format_date_id($n['published_at']) : '-') ?></td>
                        <td><?= e(number_format($n['views'])) ?></td>
                        <td><?= status_badge($n['status']) ?>
                            <form method="post" class="d-inline ms-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                                <button class="btn btn-sm btn-link p-0 align-baseline" type="submit"
                                        title="<?= $n['status'] === 'published' ? 'Tarik ke draf' : 'Terbitkan' ?>">
                                    <i class="bi <?= $n['status'] === 'published' ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            <div class="admin-actions justify-content-end">
                                <a class="btn-admin-sm" href="<?= e(base_url('?page=detail-berita&slug=' . urlencode($n['slug']))) ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i>Lihat</a>
                                <a class="btn-admin-sm" href="<?= e(base_url('admin/news-form.php?id=' . (int) $n['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                                <form method="post" class="d-inline" data-confirm="Hapus berita ini?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                                    <button class="btn-admin-sm danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pageInfo['total_pages'] > 1): ?>
        <div class="admin-card-body border-top">
            <nav aria-label="Paginasi"><ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $pageInfo['total_pages']; $i++): ?>
                    <li class="page-item <?= $i === $pageInfo['current'] ? 'active' : '' ?>"><a class="page-link" href="<?= e(pagination_href($i)) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>
<?php
admin_layout_end();