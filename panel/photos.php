<?php
/**
 * Photo Manager — kelola foto satu album:
 * upload banyak sekaligus, preview, caption, urutan, pindah urutan, hapus.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$albumId = (int) get_val('album');
$album = q_one('SELECT id, title, slug, cover_image FROM albums WHERE id = ?', [$albumId]);
if (!$album) {
    flash_redirect('danger', 'Album tidak ditemukan.', 'panel/albums.php');
}

if (is_post()) {
    /* POST melebihi post_max_size server → PHP membuang seluruh body (POST/FILES kosong).
       Beri pesan jelas alih-alih error CSRF yang membingungkan. */
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        flash_redirect('danger', 'Ukuran total unggahan melebihi batas server (maks ' . format_size(UPLOAD_VIDEO_MAX_SIZE) . ' per file). Kompres video atau unggah beberapa bagian.', 'panel/photos.php?album=' . $albumId);
    }
    require_csrf();
    $action = post_val('action');

    if ($action === 'upload') {
        $uploaded = 0;
        $uploadedVideos = 0;
        $failed = [];
        $files = $_FILES['photos'] ?? [];
        $count = is_array($files['name'] ?? null) ? count($files['name']) : 0;

        if ($count === 0) {
            flash_redirect('warning', 'Pilih minimal satu file (foto: jpg/jpeg/png/webp atau video: mp4/webm/mov).', 'panel/photos.php?album=' . $albumId);
        }

        $nextOrder = (int) (q_one('SELECT COALESCE(MAX(sort_order), 0) + 1 AS n FROM photos WHERE album_id = ?', [$albumId])['n'] ?? 1);
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            try {
                $res = process_media_upload($file, 'albums', true);
                q_exec(
                    'INSERT INTO photos (album_id, image, thumbnail, caption, sort_order, media_type) VALUES (?,?,?,?,?,?)',
                    [$albumId, $res['image'], $res['thumb'], null, $nextOrder, $res['type']]
                );
                $nextOrder++;
                $uploaded++;
                if ($res['type'] === 'video') {
                    $uploadedVideos++;
                }
            } catch (UploadException $e) {
                $failed[] = ($file['name'] !== '' ? $file['name'] : 'File #' . ($i + 1)) . ': ' . $e->getMessage();
            }
        }

        log_activity('upload', 'media', $albumId, "Unggah $uploaded media ke album: " . $album['title']);
        $msg = "$uploaded media berhasil diunggah.";
        if ($failed) {
            $msg .= ' Gagal: ' . implode(' | ', $failed);
        }
        flash_redirect($failed && $uploaded === 0 ? 'danger' : ($failed ? 'warning' : 'success'), $msg, 'panel/photos.php?album=' . $albumId);
    }

    if ($action === 'reorder') {
        $order = $_POST['order'] ?? [];
        $ok = true;
        foreach ($order as $pid => $sort) {
            $ok = q_exec('UPDATE photos SET sort_order = ? WHERE id = ? AND album_id = ?', [(int) $sort, (int) $pid, $albumId]) && $ok;
        }
        log_activity('reorder', 'photo', $albumId, 'Perbarui urutan foto: ' . $album['title']);
        flash_redirect($ok ? 'success' : 'danger', $ok ? 'Urutan foto disimpan.' : 'Gagal menyimpan urutan.', 'panel/photos.php?album=' . $albumId);
    }

    if ($action === 'captions') {
        $captions = $_POST['caption'] ?? [];
        $ok = true;
        foreach ($captions as $pid => $cap) {
            $cap = mb_substr(trim((string) $cap), 0, 255);
            $ok = q_exec('UPDATE photos SET caption = ? WHERE id = ? AND album_id = ?', [$cap !== '' ? $cap : null, (int) $pid, $albumId]) && $ok;
        }
        log_activity('update', 'photo', $albumId, 'Perbarui caption foto: ' . $album['title']);
        flash_redirect($ok ? 'success' : 'danger', $ok ? 'Caption disimpan.' : 'Gagal menyimpan caption.', 'panel/photos.php?album=' . $albumId);
    }

    if ($action === 'move') {
        $pid = (int) post_val('pid');
        $dir = post_val('dir') === 'up' ? -1 : 1;
        $photos = q_all('SELECT id, sort_order FROM photos WHERE album_id = ? ORDER BY sort_order ASC, id ASC', [$albumId]);
        $idx = null;
        foreach ($photos as $i => $p) {
            if ((int) $p['id'] === $pid) {
                $idx = $i;
                break;
            }
        }
        if ($idx !== null) {
            $swap = $idx + $dir;
            if (isset($photos[$swap])) {
                q_exec('UPDATE photos SET sort_order = ? WHERE id = ?', [(int) ($photos[$swap]['sort_order'] ?? 0), $pid]);
                q_exec('UPDATE photos SET sort_order = ? WHERE id = ?', [(int) $photos[$idx]['sort_order'], (int) $photos[$swap]['id']]);
            }
        }
        redirect('panel/photos.php?album=' . $albumId);
    }

    if ($action === 'delete_photo') {
        $pid = (int) post_val('pid');
        $photo = q_one('SELECT id, image, thumbnail FROM photos WHERE id = ? AND album_id = ?', [$pid, $albumId]);
        if ($photo) {
            delete_uploaded('albums', $photo['image']);
            delete_uploaded('albums', $photo['thumbnail']);
            if (q_exec('DELETE FROM photos WHERE id = ?', [$pid])) {
                q_exec('UPDATE albums SET cover_image = NULL WHERE id = ? AND cover_image = ?', [$albumId, $photo['image']]);
            }
            log_activity('delete', 'photo', $albumId, 'Hapus foto dari album: ' . $album['title']);
            flash_redirect('success', 'Foto dihapus.', 'panel/photos.php?album=' . $albumId);
        }
        flash_redirect('danger', 'Foto tidak ditemukan.', 'panel/photos.php?album=' . $albumId);
    }
}

$photos = q_all(
    'SELECT id, image, thumbnail, caption, sort_order, media_type
     FROM photos WHERE album_id = ?
     ORDER BY sort_order ASC, id ASC',
    [$albumId]
);
$photoCount = count($photos);

admin_layout_start('Media — ' . $album['title'], 'albums');
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="bi bi-camera me-1"></i>Media: <?= e($album['title']) ?></h1>
        <p class="text-muted small mb-0"><?= e(number_format($photoCount)) ?> media · simpan dulu album bila baru dibuat.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn-admin-sm" href="<?= e(base_url('panel/album-form.php?id=' . (int) $album['id'])) ?>"><i class="bi bi-pencil"></i>Edit Album</a>
        <a class="btn-admin-sm" href="<?= e(base_url('panel/albums.php')) ?>">&larr; Daftar Album</a>
    </div>
</div>

<div class="admin-card mb-3">
    <div class="admin-card-head"><h2>Unggah Foto &amp; Video (banyak sekaligus)</h2></div>
    <div class="admin-card-body">
        <form method="post" enctype="multipart/form-data" data-uploading>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="upload">
            <label class="admin-upload-zone d-block" for="photosInput">
                <i class="bi bi-cloud-arrow-up d-block mb-2"></i>
                <strong>Klik untuk memilih foto atau video</strong>
                <div class="admin-help">foto: jpg / jpeg / png / webp (maks <?= e(format_size(UPLOAD_MAX_SIZE)) ?>) · video: mp4 / webm / mov (maks <?= e(format_size(UPLOAD_VIDEO_MAX_SIZE)) ?>) · gambar besar otomatis di-resize</div>
                <input class="d-none" type="file" id="photosInput" name="photos[]" accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,.ogg,image/jpeg,image/png,image/webp,video/mp4,video/webm,video/ogg" multiple data-upload-preview="#photoPreview">
            </label>
            <div id="photoPreview" class="admin-upload-preview"></div>
            <button class="btn btn-primary mt-3" type="submit"><i class="bi bi-upload me-1"></i>Unggah Foto</button>
        </form>
    </div>
</div>

<?php if (!$photos): ?>
    <div class="admin-card">
        <div class="admin-card-body empty-muted">
            <i class="bi bi-camera fs-4 d-block mb-2"></i>
            Belum ada foto/video di album ini. Unggah dokumentasi kegiatan di atas.
        </div>
    </div>
<?php else: ?>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="reorder">
        <div class="admin-photo-grid">
            <?php foreach ($photos as $p): ?>
                <div class="admin-photo-card">
                    <div class="photo-img-wrap">
                        <?php if (($p['media_type'] ?? 'image') === 'video'): ?>
                            <video src="<?= e(upload_url('albums/' . $p['image'])) ?>" muted preload="metadata" playsinline></video>
                            <span class="position-absolute top-0 start-0 m-1"><span class="dp-badge dp-badge-blue"><i class="bi bi-film"></i> Video</span></span>
                        <?php elseif (upload_file_exists('albums', $p['thumbnail'] ?? '')): ?>
                            <img src="<?= e(upload_url('albums/' . $p['thumbnail'])) ?>" alt="<?= e($p['caption'] ?: 'Foto') ?>" loading="lazy">
                        <?php else: ?>
                            <img src="<?= e(upload_url('albums/' . $p['image'])) ?>" alt="<?= e($p['caption'] ?: 'Foto') ?>" loading="lazy">
                        <?php endif; ?>
                        <span class="photo-order">Urutan <?= (int) $p['sort_order'] ?></span>
                        <span class="photo-actions">
                            <a href="<?= e(base_url('panel/photos.php?album=' . $albumId . '&')) ?>" class="d-none" aria-hidden="true"></a>
                            <form method="post" class="d-inline-flex">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="move">
                                <input type="hidden" name="pid" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="dir" value="up">
                                <button type="submit" title="Pindah ke atas" aria-label="Pindah ke atas"><i class="bi bi-chevron-up"></i></button>
                            </form>
                            <form method="post" class="d-inline-flex">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="move">
                                <input type="hidden" name="pid" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="dir" value="down">
                                <button type="submit" title="Pindah ke bawah" aria-label="Pindah ke bawah"><i class="bi bi-chevron-down"></i></button>
                            </form>
                            <form method="post" class="d-inline-flex" data-confirm="Hapus foto ini?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_photo">
                                <input type="hidden" name="pid" value="<?= (int) $p['id'] ?>">
                                <button class="del" type="submit" title="Hapus foto" aria-label="Hapus foto"><i class="bi bi-trash"></i></button>
                            </form>
                        </span>
                    </div>
                    <div class="p-2 d-flex gap-2 align-items-center">
                        <input class="form-control form-control-sm" type="number" min="1" name="order[<?= (int) $p['id'] ?>]" value="<?= (int) $p['sort_order'] ?>" title="Urutan" aria-label="Urutan angka">
                        <small class="text-muted" style="font-size:.7rem">Urutan</small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-primary mt-3" type="submit"><i class="bi bi-sort-numeric-down-alt me-1"></i>Simpan Urutan</button>
    </form>

    <form method="post" class="admin-card mt-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="captions">
        <div class="admin-card-head"><h2>Caption Media</h2></div>
        <div class="admin-card-body">
            <div class="row g-2">
                <?php foreach ($photos as $p): ?>
                    <div class="col-md-6">
                        <label class="form-label-admin"><?= e(number_format($p['sort_order'])) ?>. <?= e(pathinfo($p['image'], PATHINFO_FILENAME)) ?></label>
                        <input class="form-control form-control-sm" type="text" name="caption[<?= (int) $p['id'] ?>]" maxlength="255"
                               value="<?= e($p['caption'] ?? '') ?>" placeholder="Keterangan foto…">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-outline-primary mt-3" type="submit"><i class="bi bi-save me-1"></i>Simpan Caption</button>
        </div>
    </form>
<?php endif; ?>
<?php
admin_layout_end();
