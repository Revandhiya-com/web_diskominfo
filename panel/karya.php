<?php
/**
 * CMS Karya Peserta — daftar karya: pratinjau, judul, jenis, tim, minggu,
 * flag edukasi, status (terbit/draf), lihat, edit, hapus.
 * Hapus selalu membersihkan file miliknya; video pustaka tidak ikut terhapus.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

const KARYA_JENIS_LABEL = [
    'infografis' => 'Infografis',
    'video'      => 'Video',
    'foto'       => 'Foto',
    'lainnya'    => 'Lainnya',
];

/** True bila videos row masih direferensikan karya lain (selain $except). */
function karya_video_shared(int $videoId, int $exceptKaryaId): bool
{
    return $videoId > 0 && (int) (q_one(
        'SELECT COUNT(*) n FROM karya WHERE video_id = ? AND id != ?',
        [$videoId, $exceptKaryaId]
    )['n'] ?? 0) > 0;
}

if (is_post()) {
    require_csrf();
    if (post_val('action') === 'toggle') {
        $id = (int) post_val('id');
        $k = q_one('SELECT id, title, status, video_id, is_edukasi FROM karya WHERE id = ?', [$id]);
        if ($k) {
            $new = $k['status'] === 'published' ? 'draft' : 'published';
            $pub = ($new === 'published')
                ? 'published_at = COALESCE(published_at, NOW()),'
                : 'published_at = published_at,';
            q_exec(
                "UPDATE karya SET status = ?, $pub updated_at = NOW() WHERE id = ?",
                [$new, $id]
            );
            /* Sinkron status video buatan karya agar Konten Edukasi konsisten. */
            if ($k['video_id']) {
                $vStatus = ((int) $k['is_edukasi'] === 1 && $new === 'published') ? 'published' : 'draft';
                q_exec("UPDATE videos SET status = ?, updated_at = NOW() WHERE id = ? AND source = 'karya'", [$vStatus, (int) $k['video_id']]);
            }
            log_activity('update', 'karya', $id, ($new === 'published' ? 'Terbitkan' : 'Tarik ke draf') . ' karya: ' . $k['title']);
            flash_redirect('success', 'Status karya diubah.', 'panel/karya.php');
        }
        flash_redirect('danger', 'Karya tidak ditemukan.', 'panel/karya.php');
    }
    if (post_val('action') === 'delete') {
        $id = (int) post_val('id');
        $k = q_one('SELECT * FROM karya WHERE id = ?', [$id]);
        if ($k) {
            /* 1. Media gambar milik karya. */
            if ($k['media_type'] === 'image' && $k['file_path']) {
                delete_uploaded('karya', $k['file_path']);
            }
            /* 2. Cover/thumbnail milik karya. */
            if ($k['thumbnail']) {
                delete_uploaded('karya', $k['thumbnail']);
            }
            /* 3. Video buatan karya (source='karya') → hapus baris + filenya,
                  TAPI hanya bila tidak dipakai karya lain (satu file dipakai bersama).
                  Video pustaka (source='library') dipakai bersama → JANGAN disentuh. */
            if ($k['video_id'] && !karya_video_shared((int) $k['video_id'], $id)) {
                $v = q_one("SELECT id, file_path, thumbnail FROM videos WHERE id = ? AND source = 'karya'", [(int) $k['video_id']]);
                if ($v) {
                    delete_uploaded('videos', $v['file_path']);
                    if ($v['thumbnail']) {
                        delete_uploaded('videos', $v['thumbnail']);
                    }
                    q_exec('DELETE FROM videos WHERE id = ?', [(int) $v['id']]);
                }
            }
            q_exec('DELETE FROM karya WHERE id = ?', [$id]);
            log_activity('delete', 'karya', $id, 'Hapus karya: ' . $k['title']);
            flash_redirect('success', 'Karya dan file miliknya dihapus.', 'panel/karya.php');
        }
        flash_redirect('danger', 'Karya tidak ditemukan.', 'panel/karya.php');
    }
}

$q      = post_max('q', 100, get_val('q'));
$fJenis = get_val('jenis');
$fTim   = get_val('tim');

$parts  = [];
$params = [];
if ($q !== '') {
    $parts[] = '(k.title LIKE ? OR k.description LIKE ? OR k.team LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%");
}
if (in_array($fJenis, ['infografis', 'video', 'foto', 'lainnya'], true)) {
    $parts[] = 'k.jenis = ?';
    $params[] = $fJenis;
}
if (in_array($fTim, ['Tim 1', 'Tim 2', 'Tim 3'], true)) {
    $parts[] = 'k.team = ?';
    $params[] = $fTim;
}
$where = $parts ? 'WHERE ' . implode(' AND ', $parts) : '';

$total = (int) (q_one("SELECT COUNT(*) n FROM karya k $where", $params)['n'] ?? 0);
$pageInfo = pagination_info($total, 10);
$items = q_all(
    "SELECT k.*, v.title AS video_title, v.status AS video_status, v.source AS video_source
     FROM karya k
     LEFT JOIN videos v ON v.id = k.video_id
     $where
     ORDER BY k.created_at DESC, k.id DESC
     LIMIT {$pageInfo['per_page']} OFFSET {$pageInfo['offset']}",
    $params
);

admin_layout_start('Karya Peserta', 'karya');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head"><h2>Karya Peserta</h2>
        <a class="btn btn-sm btn-primary" href="<?= e(base_url('panel/karya-form.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Karya</a></div>
    <div class="admin-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-5"><label class="form-label-admin">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Judul / deskripsi / tim…"></div>
            <div class="col-md-3"><label class="form-label-admin">Jenis</label>
                <select class="form-select" name="jenis">
                    <option value="">Semua jenis</option>
                    <?php foreach (KARYA_JENIS_LABEL as $kj => $kl): ?>
                        <option value="<?= e($kj) ?>" <?= $fJenis === $kj ? 'selected' : '' ?>><?= e($kl) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="col-md-2"><label class="form-label-admin">Tim</label>
                <select class="form-select" name="tim">
                    <option value="">Semua tim</option>
                    <?php foreach (['Tim 1', 'Tim 2', 'Tim 3'] as $t): ?>
                        <option value="<?= e($t) ?>" <?= $fTim === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= e(base_url('panel/karya.php')) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-admin mb-0 align-middle">
                <thead><tr><th>Pratinjau</th><th>Judul</th><th>Jenis</th><th>Tim</th><th>Minggu</th><th>Edukasi</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    <?php if (!$items): ?>
                        <tr><td colspan="8" class="empty-muted">Belum ada karya. Klik "Tambah Karya" untuk mengunggah infografis, foto, atau video peserta.</td></tr>
                    <?php else: foreach ($items as $k):
                        $isVideo = ($k['media_type'] ?? '') === 'video';
                        $coverOk = $k['thumbnail'] && upload_file_exists('karya', $k['thumbnail']);
                    ?>
                        <tr>
                            <td style="width:170px">
                                <?php if ($isVideo): ?>
                                    <?php if ($coverOk): ?>
                                        <span class="d-inline-block position-relative">
                                            <img src="<?= e(upload_url('karya/' . $k['thumbnail'])) ?>" alt=""
                                                 style="width:150px;height:auto;max-height:90px;object-fit:cover;border-radius:.4rem;background:#000">
                                            <i class="bi bi-play-circle-fill position-absolute text-white" style="font-size:1.6rem;top:50%;left:50%;transform:translate(-50%,-50%)"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="dp-badge dp-badge-blue"><i class="bi bi-film"></i> Video #<?= (int) $k['video_id'] ?></span>
                                    <?php endif; ?>
                                <?php elseif (upload_file_exists('karya', $k['file_path'])): ?>
                                    <img src="<?= e(upload_url('karya/' . ($k['thumbnail'] ?: $k['file_path']))) ?>" alt=""
                                         style="width:150px;height:auto;max-height:90px;object-fit:cover;border-radius:.4rem">
                                <?php else: ?>
                                    <span class="text-muted small">File hilang</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e($k['title']) ?></strong>
                                <div class="small text-muted"><?= e(trim_words($k['description'] ?? '', 12)) ?></div></td>
                            <td><?php $jb = ['infografis' => 'dp-badge-gold', 'video' => 'dp-badge-blue', 'foto' => '', 'lainnya' => ''];
                                echo '<span class="dp-badge ' . $jb[$k['jenis']] . '">' . e(KARYA_JENIS_LABEL[$k['jenis']]) . '</span>'; ?></td>
                            <td><?= e($k['team'] ?: '-') ?></td>
                            <td><?= $k['week_number'] ? 'Minggu ke-' . (int) $k['week_number'] : '-' ?></td>
                            <td><?= $k['is_edukasi'] ? '<span class="dp-badge dp-badge-gold"><i class="bi bi-mortarboard"></i> Edukasi</span>' : '<span class="text-muted">—</span>' ?></td>
                            <td><?= status_badge($k['status']) ?>
                                <form method="post" class="d-inline ms-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= (int) $k['id'] ?>">
                                    <button class="btn btn-sm btn-link p-0 align-baseline" type="submit"
                                            title="<?= $k['status'] === 'published' ? 'Tarik ke draf' : 'Terbitkan' ?>">
                                        <i class="bi <?= $k['status'] === 'published' ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end"><div class="admin-actions justify-content-end">
                                <?php if ($k['status'] === 'published'): ?>
                                    <a class="btn-admin-sm" href="<?= e(base_url('?page=detail-karya&id=' . (int) $k['id'])) ?>" target="_blank" rel="noopener" title="Lihat di situs publik"><i class="bi bi-box-arrow-up-right"></i>Lihat</a>
                                <?php endif; ?>
                                <a class="btn-admin-sm" href="<?= e(base_url('panel/karya-form.php?id=' . (int) $k['id'])) ?>"><i class="bi bi-pencil"></i>Edit</a>
                                <form method="post" class="d-inline" data-confirm="Hapus karya ini? File milik karya ikut dihapus; video pustaka yang dibagikan tidak terhapus.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $k['id'] ?>">
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
