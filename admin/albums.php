<?php
/**
 * CMS Dokumentasi — daftar album + search/filter/pagination + hapus.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    $action = post_val('action');
    if ($action === 'toggle') {
        toggle_status('albums', 'title', 'album');
    }
    if ($action === 'delete') {
        $id = (int) post_val('id');
        $album = q_one('SELECT * FROM albums WHERE id = ?', [$id]);
        if ($album) {
            $photos = q_all('SELECT image, thumbnail FROM photos WHERE album_id = ?', [$id]);
            foreach ($photos as $p) {
                delete_uploaded('albums', $p['image']);
                delete_uploaded('albums', $p['thumbnail']);
            }
            q_exec('DELETE FROM albums WHERE id = ?', [$id]);
            delete_uploaded('albums', $album['cover_image']);
            log_activity('delete', 'album', $id, 'Hapus album: ' . $album['title']);
            flash_redirect('success', 'Album beserta seluruh fotonya telah dihapus.', 'admin/albums.php');
        }
        flash_redirect('danger', 'Album tidak ditemukan.', 'admin/albums.php');
    }
}

$q     = post_max('q', 100, get_val('q'));
$catId = (int) get_val('category', '0');
$st    = get_val('status', '');
$parts = [];
$params = [];

if ($q !== '') {
    $parts[] = '(a.title LIKE ? OR a.location LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($catId > 0) {
    $parts[] = 'a.category_id = ?';
    $params[] = $catId;
}
if ($st === 'published' || $st === 'draft') {
    $parts[] = 'a.status = ?';
    $params[] = $st;
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';

$total = (int) (q_one("SELECT COUNT(*) n FROM albums a $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 10);

$albums = q_all(
    "SELECT a.*, c.name AS category_name,
            (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id) AS photo_count
     FROM albums a
     LEFT JOIN album_categories c ON c.id = a.category_id
     $where
     ORDER BY a.created_at DESC
     LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}",
    $params
);
$categories = q_all('SELECT id, name FROM album_categories ORDER BY name ASC');

admin_layout_start('Dokumentasi', 'albums');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head">
        <h2>Album Dokumentasi</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('admin/album-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Album</a>
    </div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Judul / lokasi…">
            </div>
            <div class="col-md-3">
                <label class="form-label-admin">Kategori</label>
                <select class="form-select" name="category">
                    <option value="0">Semua kategori</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $catId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua status</option>
                    <option value="published" <?= $st === 'published' ? 'selected' : '' ?>>Terbit</option>
                    <option value="draft" <?= $st === 'draft' ? 'selected' : '' ?>>Draf</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/albums.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="table table-admin mb-0">
            <thead>
                <tr>
                    <th>Sampul</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tanggal Acara</th>
                    <th>Foto</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$albums): ?>
                    <tr><td colspan="7" class="empty-muted">
                        <p class="mb-1"><i class="bi bi-images fs-4 d-block mb-2"></i>Belum ada album dokumentasi.</p>
                        <a class="btn-admin-sm" href="<?= e(base_url('admin/album-form.php')) ?>">Tambah album pertama</a>
                    </td></tr>
                <?php else: foreach ($albums as $a): ?>
                    <tr>
                        <td class="thumb-cell">
                            <?php if (upload_file_exists('albums', $a['cover_image'])): ?>
                                <img src="<?= e(upload_url('albums/' . $a['cover_image'])) ?>" alt="">
                            <?php else: ?>
                                <span class="thumb-img d-inline-grid place-items-center bg-secondary-subtle rounded rounded-1"><i class="bi bi-image text-muted"></i></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($a['title']) ?></strong>
                            <div class="small text-muted"><?= e($a['location'] ?: '-') ?></div>
                        </td>
                        <td><?= e($a['category_name'] ?: '-') ?></td>
                        <td><?= e($a['event_date'] ? format_date_id($a['event_date']) : '-') ?></td>
                        <td><?= e(number_format($a['photo_count'])) ?></td>
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
                        <td class="text-end">
                            <div class="admin-actions justify-content-end">
                                <a class="btn-admin-sm" href="<?= e(base_url('admin/photos.php?album=' . (int) $a['id'])) ?>"><i class="bi bi-camera"></i>Foto</a>
                                <a class="btn-admin-sm" href="<?= e(base_url('admin/album-form.php?id=' . (int) $a['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                                <form method="post" class="d-inline" onsubmit="return confirm('Hapus album ini beserta seluruh fotonya?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
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
            <nav aria-label="Paginasi album">
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($i = 1; $i <= $pageInfo['total_pages']; $i++): ?>
                        <li class="page-item <?= $i === $pageInfo['current'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= e(pagination_href($i)) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
<?php
admin_layout_end();