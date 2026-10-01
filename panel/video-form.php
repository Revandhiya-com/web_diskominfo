<?php
/**
 * CMS Video & Media — tambah/edit media (foto ATAU video unggahan langsung).
 * Jenis Media menentukan proses upload: gambar (resize+thumb) atau video (simpan apa adanya).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$id = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM videos WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'Media tidak ditemukan.', 'panel/videos.php');
}

$errors = [];
$title = $item['title'] ?? '';
$description = $item['description'] ?? '';
$mediaType = $item['media_type'] ?? 'video';
$status = $item['status'] ?? 'draft';
$fileSize = (int) ($item['file_size'] ?? 0);

if (is_post()) {
    require_csrf();
    $title = post_max('title', 200);
    $description = mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 5000);
    $mediaType = post_val('media_type') === 'image' ? 'image' : 'video';
    $status = post_val('status') === 'published' ? 'published' : 'draft';

    if ($title === '') {
        $errors[] = 'Judul wajib diisi.';
    }

    $newFile = null;
    $newThumb = null;
    $hasFile = isset($_FILES['file']) && $_FILES['file']['name'] !== '';

    if (!$item && !$hasFile && !$errors) {
        $errors[] = 'Pilih file yang akan diunggah.';
    }

    /* 1. Unggah file utama (foto → resize+thumb; video → simpan apa adanya). */
    if ($hasFile && !$errors) {
        try {
            if ($mediaType === 'image') {
                $up = process_image_upload($_FILES['file'], 'videos', true);
                $newFile = ['path' => $up['image'], 'mime' => $up['mime'], 'size' => $up['size'], 'thumb' => $up['thumb']];
            } else {
                $up = process_video_upload($_FILES['file']);
                $newFile = ['path' => $up['name'], 'mime' => $up['mime'], 'size' => $up['size'], 'thumb' => null];
            }
        } catch (UploadException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'Tipe file') === 0) {
                $msg = $mediaType === 'video'
                    ? 'Format video tidak didukung. Gunakan MP4 atau WebM.'
                    : 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.';
            } elseif (strpos($msg, 'Ukuran') === 0) {
                $msg = 'Ukuran melebihi batas maksimal ' . format_size(UPLOAD_VIDEO_MAX_SIZE) . '.';
            }
            $errors[] = $msg;
        }
    }

    /* 2. Thumbnail opsional (khusus video). */
    if (!$errors && $mediaType === 'video' && isset($_FILES['thumbnail']) && $_FILES['thumbnail']['name'] !== '') {
        try {
            $t = process_image_upload($_FILES['thumbnail'], 'videos', false);
            $newThumb = $t['image'];
        } catch (UploadException $e) {
            $errors[] = 'Thumbnail: ' . $e->getMessage();
        }
    }

    if (!$errors) {
        if ($item) {
            /* Edit: simpan dulu, baru hapus file lama yang tergantikan. */
            $ok = q_exec(
                'UPDATE videos SET title=?, description=?, media_type=?, file_path=?, mime_type=?, file_size=?, thumbnail=?, status=?, updated_at=NOW() WHERE id=?',
                [
                    $title,
                    $description !== '' ? $description : null,
                    $mediaType,
                    $newFile['path'] ?? $item['file_path'],
                    $newFile['mime'] ?? $item['mime_type'],
                    $newFile['size'] ?? (int) $item['file_size'],
                    $newThumb ?? ($newFile['thumb'] ?? $item['thumbnail']),
                    $status,
                    $id,
                ]
            );
            if ($ok) {
                if ($newFile && $newFile['path'] !== $item['file_path']) {
                    /* File baru sudah tersimpan aman → hapus file lama (beserta pasangan -thumb bila gambar). */
                    delete_uploaded('videos', $item['file_path']);
                    /* Ganti jenis video→gambar: thumbnail unggahan lama tidak lagi terpakai. */
                    if ($item['media_type'] === 'video' && $mediaType === 'image' && $item['thumbnail']) {
                        delete_uploaded('videos', $item['thumbnail']);
                    }
                }
                if ($newThumb !== null && $item['thumbnail'] && $newThumb !== $item['thumbnail']) {
                    delete_uploaded('videos', $item['thumbnail']);
                }
                log_activity('update', 'video', $id, 'Perbarui media: ' . $title);
                flash_redirect('success', 'Media disimpan.', 'panel/videos.php');
            }
            flash_redirect('danger', 'Gagal menyimpan media.', 'panel/videos.php');
        } else {
            $ok = q_exec(
                'INSERT INTO videos (title, description, media_type, file_path, mime_type, file_size, thumbnail, status, created_by) VALUES (?,?,?,?,?,?,?,?,?)',
                [$title, $description !== '' ? $description : null, $mediaType, $newFile['path'], $newFile['mime'], $newFile['size'], $newFile['thumb'] ?? $newThumb, $status, (int) current_user()['id']]
            );
            $newId = $ok ? (int) db()->lastInsertId() : 0;
            log_activity('create', 'video', $newId, 'Tambah media: ' . $title);
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'Media ditambahkan.' : 'Gagal menyimpan media.', 'panel/videos.php');
        }
    }

    /* Gagal validasi: bersihkan file yang sempat terunggah agar tak jadi sampah. */
    if ($errors && $newFile) {
        @unlink(UPLOADS_PATH . '/videos/' . $newFile['path']);
        if (!empty($newFile['thumb'])) {
            @unlink(UPLOADS_PATH . '/videos/' . $newFile['thumb']);
        }
    }
    if ($errors && $newThumb) {
        @unlink(UPLOADS_PATH . '/videos/' . $newThumb);
    }
}

admin_layout_start($item ? 'Edit Media' : 'Tambah Video/Media', 'videos');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit Media' : 'Tambah Video/Media' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('panel/videos.php')) ?>">&larr; Kembali</a>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger"><strong>Perbaiki hal berikut:</strong>
        <ul class="mb-0 mt-1"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-card" data-uploading id="mediaForm">
    <?= csrf_field() ?>
    <div class="admin-card-body">
        <div class="admin-form-grid">
            <div class="admin-form-full">
                <label class="form-label-admin">Judul <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="title" maxlength="200" value="<?= e($title) ?>" required>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Deskripsi</label>
                <textarea class="form-control" name="description" rows="3" maxlength="5000"><?= e($description) ?></textarea>
            </div>
            <div>
                <label class="form-label-admin">Jenis Media</label>
                <select class="form-select" name="media_type" id="mediaType">
                    <option value="video" <?= $mediaType === 'video' ? 'selected' : '' ?>>Video</option>
                    <option value="image" <?= $mediaType === 'image' ? 'selected' : '' ?>>Foto/Gambar</option>
                </select>
            </div>
            <div>
                <label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draf (tidak tampil publik)</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Terbit</option>
                </select>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">File <span class="required-star">*</span></label>
                <?php if ($item): ?>
                    <div class="border rounded p-2 mb-2 d-flex align-items-center gap-3 flex-wrap">
                        <?php if ($mediaType === 'video'): ?>
                            <video controls preload="metadata" playsinline style="max-width:280px;width:100%;height:auto;border-radius:.5rem;background:#000"
                                   src="<?= e(upload_url('videos/' . $item['file_path'])) ?>"
                                <?php if ($item['thumbnail'] && upload_file_exists('videos', $item['thumbnail'])): ?>
                                    poster="<?= e(upload_url('videos/' . $item['thumbnail'])) ?>"
                                <?php endif; ?>>
                                Browser Anda tidak mendukung pemutaran video.
                            </video>
                        <?php elseif (upload_file_exists('videos', $item['file_path'])): ?>
                            <img src="<?= e(upload_url('videos/' . ($item['thumbnail'] ?: $item['file_path']))) ?>" alt="" style="max-width:220px;height:auto;border-radius:.5rem">
                        <?php endif; ?>
                        <small class="text-muted">File saat ini: <?= e(pathinfo($item['file_path'], PATHINFO_BASENAME)) ?> · <?= e(format_size($fileSize)) ?></small>
                    </div>
                <?php endif; ?>
                <input class="form-control" type="file" name="file" id="mediaFile" data-video-preview="#mediaPreview"
                       accept="<?= $mediaType === 'video'
                           ? '.mp4,.webm,.mov,video/mp4,video/webm,video/quicktime'
                           : '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp' ?>">
                <div class="admin-help" id="mediaHelp"></div>
                <div id="mediaPreview" class="mt-2"></div>
            </div>
            <div class="admin-form-full" id="thumbWrap" style="display:none">
                <label class="form-label-admin">Thumbnail Video (jpg/jpeg/png/webp, opsional)</label>
                <?php if ($item && $item['thumbnail'] && upload_file_exists('videos', $item['thumbnail'])): ?>
                    <div class="mb-2"><img src="<?= e(upload_url('videos/' . $item['thumbnail'])) ?>" alt="" style="max-width:180px;height:auto;border-radius:.5rem"></div>
                <?php endif; ?>
                <input class="form-control" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <div class="admin-help">Gambar kecil yang tampil sebelum video diputar. Kosongkan untuk mempertahankan yang ada.</div>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Media</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('panel/videos.php')) ?>">Batal</a>
    </div>
</form>

<script>
(function () {
    'use strict';
    var typeSel = document.getElementById('mediaType');
    var fileInput = document.getElementById('mediaFile');
    var help = document.getElementById('mediaHelp');
    var preview = document.getElementById('mediaPreview');
    var thumbWrap = document.getElementById('thumbWrap');

    function syncType() {
        var isVideo = typeSel.value === 'video';
        fileInput.accept = isVideo
            ? '.mp4,.webm,.mov,video/mp4,video/webm,video/quicktime'
            : '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp';
        help.textContent = isVideo
            ? 'Format yang didukung: MP4 dan WebM. Maksimal ukuran <?= e(format_size(UPLOAD_VIDEO_MAX_SIZE)) ?>.'
            : 'Format yang didukung: JPG, JPEG, PNG, WebP. Maksimal ukuran <?= e(format_size(UPLOAD_MAX_SIZE)) ?>.';
        thumbWrap.style.display = isVideo ? '' : 'none';
        clearPreview();
    }

    function clearPreview() {
        if (preview.dataset.url) {
            URL.revokeObjectURL(preview.dataset.url);
            delete preview.dataset.url;
        }
        preview.innerHTML = '';
    }

    fileInput.addEventListener('change', function () {
        clearPreview();
        var file = fileInput.files && fileInput.files[0];
        if (!file) return;
        var url = URL.createObjectURL(file);
        preview.dataset.url = url;
        if (typeSel.value === 'video') {
            var v = document.createElement('video');
            v.controls = true;
            v.preload = 'metadata';
            v.playsInline = true;
            v.src = url;
            v.style.cssText = 'max-width:320px;width:100%;height:auto;border-radius:.5rem;background:#000';
            preview.appendChild(v);
        } else {
            var img = document.createElement('img');
            img.src = url;
            img.alt = 'Pratinjau';
            img.style.cssText = 'max-width:260px;height:auto;border-radius:.5rem';
            preview.appendChild(img);
        }
    });

    typeSel.addEventListener('change', syncType);
    syncType();
})();
</script>
<?php
admin_layout_end();
