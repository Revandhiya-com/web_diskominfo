<?php
/**
 * CMS Video & Media — daftar media: pratinjau/play, judul, tipe, ukuran,
 * tanggal, status (terbit/draf), lihat di situs, edit, hapus.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (is_post()) {
    require_csrf();
    if (post_val('action') === 'toggle') {
        toggle_status('videos', 'title', 'media');
    }
    if (post_val('action') === 'delete') {
        $id = (int) post_val('id');
        $v = q_one('SELECT id, title, file_path, thumbnail FROM videos WHERE id = ?', [$id]);
        if ($v) {
            /* Hapus file fisik dulu, baru record DB — tidak menyisakan file yatim. */
            delete_uploaded('videos', $v['file_path']);
            if ($v['thumbnail']) {
                delete_uploaded('videos', $v['thumbnail']);
            }
            q_exec('DELETE FROM videos WHERE id = ?', [$id]);
            log_activity('delete', 'video', $id, 'Hapus media: ' . $v['title']);
            flash_redirect('success', 'Media dan filenya dihapus.', 'admin/videos.php');
        }
        flash_redirect('danger', 'Media tidak ditemukan.', 'admin/videos.php');
    }
}

$q = post_max('q', 100, get_val('q'));
$parts = [];
$params = [];
if ($q !== '') {
    $parts[] = '(title LIKE ? OR description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';
$total = (int) (q_one("SELECT COUNT(*) n FROM videos $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 10);
$items = q_all("SELECT * FROM videos $where ORDER BY created_at DESC, id DESC LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}", $params);

admin_layout_start('Video & Media', 'videos');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head"><h2>Video &amp; Media</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('admin/video-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Media</a></div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-6"><label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Judul / deskripsi…"></div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/videos.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-admin mb-0 align-middle">
                <thead><tr><th>Pratinjau</th><th>Judul</th><th>Tipe</th><th>Ukuran</th><th>Tanggal</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    <?php if (!$items): ?>
                        <tr><td colspan="7" class="empty-muted">Belum ada media. Klik "Tambah Media" untuk mengunggah video atau foto.</td></tr>
                    <?php else: foreach ($items as $m): $isVideo = ($m['media_type'] ?? '') === 'video'; ?>
                        <tr>
                            <td style="width:200px">
                                <?php if ($isVideo): ?>
                                    <video controls preload="metadata" playsinline
                                           style="width:180px;height:auto;border-radius:.4rem;background:#000"
                                           src="<?= e(upload_url('videos/' . $m['file_path'])) ?>"
                                        <?php if ($m['thumbnail'] && upload_file_exists('videos', $m['thumbnail'])): ?>
                                            poster="<?= e(upload_url('videos/' . $m['thumbnail'])) ?>"
                                        <?php endif; ?>>
                                        Browser Anda tidak mendukung pemutaran video. Silakan gunakan browser versi terbaru.
                                    </video>
                                <?php elseif (upload_file_exists('videos', $m['file_path'])): ?>
                                    <a href="<?= e(upload_url('videos/' . $m['file_path'])) ?>" target="_blank" rel="noopener">
                                        <img src="<?= e(upload_url('videos/' . ($m['thumbnail'] ?: $m['file_path']))) ?>" alt=""
                                             style="width:180px;height:auto;max-height:110px;object-fit:cover;border-radius:.4rem">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">File hilang</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e($m['title']) ?></strong>
                                <div class="small text-muted"><?= e(trim_words($m['description'] ?? '', 14)) ?></div></td>
                            <td><?= $isVideo
                                ? '<span class="dp-badge dp-badge-blue"><i class="bi bi-film"></i> Video</span>'
                                : '<span class="dp-badge dp-badge-gold"><i class="bi bi-camera"></i> Foto</span>' ?></td>
                            <td class="text-nowrap"><?= e(format_size((int) $m['file_size'])) ?></td>
                            <td class="text-nowrap"><?= e(format_date_id($m['created_at'])) ?></td>
                            <td><?= status_badge($m['status']) ?>
                                <form method="post" class="d-inline ms-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                    <button class="btn btn-sm btn-link p-0 align-baseline" type="submit"
                                            title="<?= $m['status'] === 'published' ? 'Tarik ke draf' : 'Terbitkan' ?>">
                                        <i class="bi <?= $m['status'] === 'published' ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end"><div class="admin-actions justify-content-end">
                                <?php if ($m['status'] === 'published'): ?>
                                    <a class="btn-admin-sm" href="<?= e(base_url('?page=video#v' . (int) $m['id'])) ?>" target="_blank" rel="noopener" title="Lihat di situs publik"><i class="bi bi-box-arrow-up-right"></i>Lihat</a>
                                <?php endif; ?>
                                <a class="btn-admin-sm" href="<?= e(base_url('admin/video-form.php?id=' . (int) $m['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                                <form method="post" class="d-inline" data-confirm="Hapus media ini beserta filenya dari server?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                    <button class="btn-admin-sm danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                </form>
                            </div></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
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
<?php
admin_layout_end();
