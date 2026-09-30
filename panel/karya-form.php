<?php
/**
 * CMS Karya Peserta — tambah/edit karya (infografis, video, foto, lainnya).
 *
 * Aturan media (tanpa duplikasi file):
 * - Infografis/foto        : gambar di-upload ke uploads/karya/ (resize + thumb).
 * - Video                  : file video SELALU milik tabel `videos` (satu file,
 *   satu record). Admin memilih dari Pustaka Video ATAU meng-upload baru —
 *   upload baru otomatis membuat baris videos source='karya'.
 * - Lainnya                : boleh gambar atau video (dideteksi dari MIME asli).
 * - Flag "Konten Edukasi"  : saat karya terbit, video ikut tampil di halaman
 *   Konten Edukasi (?page=video) tanpa upload kedua.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

const KARYA_JENIS = ['infografis', 'video', 'foto', 'lainnya'];
const KARYA_TIM   = ['Tim 1', 'Tim 2', 'Tim 3'];

$id   = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM karya WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'Karya tidak ditemukan.', 'admin/karya.php');
}

$errors       = [];
$title        = $item['title'] ?? '';
$description  = $item['description'] ?? '';
$jenis        = $item['jenis'] ?? 'video';
$status       = $item['status'] ?? 'draft';
$team         = $item['team'] ?? '';
$week         = $item['week_number'] ?? '';
$isEdukasi    = (int) ($item['is_edukasi'] ?? 0);
$mediaType    = $item['media_type'] ?? 'image';
$fileSize     = (int) ($item['file_size'] ?? 0);
$videoId      = (int) ($item['video_id'] ?? 0);
$currentVideo = $videoId > 0 ? q_one('SELECT id, title, status, source FROM videos WHERE id = ?', [$videoId]) : null;

/* Pustaka video (hanya yang ber-type video) untuk opsi "pakai yang sudah ada". */
$library = q_all("SELECT id, title, status FROM videos WHERE media_type = 'video' ORDER BY created_at DESC, id DESC LIMIT 200");

/** Sinkron baris videos buatan karya (source='karya'): judul/deskripsi/status. */
function karya_sync_video(int $videoId, string $title, string $description, bool $isEdukasi, string $karyaStatus): void
{
    $vStatus = ($isEdukasi && $karyaStatus === 'published') ? 'published' : 'draft';
    q_exec(
        "UPDATE videos SET title = ?, description = ?, status = ?, updated_at = NOW()
         WHERE id = ? AND source = 'karya'",
        [$title, $description !== '' ? $description : null, $vStatus, $videoId]
    );
}

/** Deteksi MIME asli file upload → 'video' | 'image'. */
function karya_sniff_type(array $file): string
{
    if (is_string($file['tmp_name'] ?? null) && $file['tmp_name'] !== '' && is_file($file['tmp_name'])) {
        $mime = strtolower((string) (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']));
        if (isset(VIDEO_MIME_EXT[$mime])) {
            return 'video';
        }
    }
    return 'image';
}

/** Buat baris videos baru dari file upload karya → kembalikan id-nya. */
function karya_create_video_row(array $file, string $title, string $description, bool $isEdukasi, string $status): array
{
    $up = process_video_upload($file);
    $vStatus = ($isEdukasi && $status === 'published') ? 'published' : 'draft';
    $ok = q_exec(
        'INSERT INTO videos (title, description, media_type, file_path, mime_type, file_size, status, source, created_by)
         VALUES (?,?,?,?,?,?,?,?,?)',
        [
            $title,
            $description !== '' ? $description : null,
            'video',
            $up['name'],
            $up['mime'],
            $up['size'],
            $vStatus,
            'karya',
            (int) current_user()['id'],
        ]
    );
    if (!$ok) {
        @unlink(UPLOADS_PATH . '/videos/' . $up['name']);
        throw new UploadException('Gagal menyimpan data video.');
    }
    return ['id' => (int) db()->lastInsertId(), 'name' => $up['name']];
}

if (is_post()) {
    require_csrf();
    $title     = post_max('title', 200);
    $description = mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 5000);
    $jenisIn   = post_val('jenis');
    $jenis     = in_array($jenisIn, KARYA_JENIS, true) ? $jenisIn : 'lainnya';
    $status    = post_val('status') === 'published' ? 'published' : 'draft';
    $teamIn    = post_val('team');
    $team      = in_array($teamIn, KARYA_TIM, true) ? $teamIn : '';
    $week      = max(0, min(52, (int) post_val('week_number', '0')));
    $isEdukasi = post_val('is_edukasi') === '1' ? 1 : 0;

    if ($title === '') {
        $errors[] = 'Judul karya wajib diisi.';
    }

    /* Media lama (mode edit) */
    $oldFile   = $item['file_path'] ?? '';
    $oldThumb  = $item['thumbnail'] ?? '';
    $oldMedia  = $mediaType;
    $oldVidId  = $videoId;

    $newFile    = null;  // ['path'=>..,'mime'=>..,'size'=>..] gambar di karya/
    $newThumb   = null;  // cover karya (untuk video)
    $newVideoId = 0;     // videos.id bila media video
    $newMedia   = '';    // 'image' | 'video' hasil keputusan branch

    $hasFile = isset($_FILES['file']) && $_FILES['file']['name'] !== '';
    $videoSource = post_val('video_source') === 'library' ? 'library' : 'upload';
    $pickedLibrary = (int) post_val('video_library_id', '0');

    try {
        if ($jenis === 'video' || ($jenis === 'lainnya' && ((!$item && $hasFile && karya_sniff_type($_FILES['file']) === 'video') || ($item && $oldMedia === 'video' && !$hasFile)))) {
            /* ---------- BRANCH VIDEO ---------- */
            $newMedia = 'video';
            if ($hasFile) {
                $row = karya_create_video_row($_FILES['file'], $title, $description, (bool) $isEdukasi, $status);
                $newVideoId = $row['id'];
            } elseif ($videoSource === 'library' && $pickedLibrary > 0) {
                $lib = q_one("SELECT id FROM videos WHERE id = ? AND media_type = 'video'", [$pickedLibrary]);
                if (!$lib) {
                    throw new UploadException('Video pustaka tidak ditemukan.');
                }
                $newVideoId = $pickedLibrary;
            } elseif ($item && $oldMedia === 'video' && $oldVidId > 0) {
                /* Edit tanpa pergantian media → pertahankan video lama. */
                $newVideoId = $oldVidId;
            } else {
                throw new UploadException('Pilih video dari pustaka atau unggah file video (MP4/WebM, maks ' . format_size(UPLOAD_VIDEO_MAX_SIZE) . ').');
            }
        } else {
            /* ---------- BRANCH GAMBAR (infografis/foto/lainnya-gambar) ---------- */
            $newMedia = 'image';
            if ($hasFile) {
                $up = process_image_upload($_FILES['file'], 'karya', true);
                $newFile = ['path' => $up['image'], 'thumb' => $up['thumb'], 'mime' => $up['mime'], 'size' => $up['size']];
            } elseif (!$item) {
                throw new UploadException('Unggah file media karya (gambar JPG/PNG/WebP atau video MP4/WebM).');
            } elseif ($oldMedia === 'video') {
                throw new UploadException('Jenis karya berubah menjadi gambar — unggah file gambar pengganti.');
            }
        }

        /* Thumbnail/cover opsional (relevan untuk karya video; gambar sudah punya thumb otomatis). */
        if (!$errors && isset($_FILES['thumbnail']) && $_FILES['thumbnail']['name'] !== '') {
            $t = process_image_upload($_FILES['thumbnail'], 'karya', false);
            $newThumb = $t['image'];
        }
    } catch (UploadException $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'Tipe file') === 0) {
            $msg = 'Format file tidak didukung. Gunakan JPG/PNG/WebP untuk gambar, MP4/WebM untuk video.';
        } elseif (strpos($msg, 'Ukuran') === 0) {
            $msg = 'Ukuran melebihi batas maksimal.';
        }
        $errors[] = $msg;
    }

    if (!$errors) {
        $publishedAt = $item['published_at'] ?? null;
        if ($status === 'published' && !$publishedAt && (!$item || $item['status'] !== 'published')) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $finalThumb = $newThumb ?? ($item['thumbnail'] ?? null);
        if ($newMedia === 'image' && $newFile) {
            $finalThumb = $newThumb ?? $newFile['thumb'];
        }

        if ($item) {
            $ok = q_exec(
                'UPDATE karya SET title=?, description=?, jenis=?, media_type=?, file_path=?, mime_type=?, file_size=?, thumbnail=?, team=?, week_number=?, video_id=?, is_edukasi=?, status=?, published_at=?, updated_at=NOW() WHERE id=?',
                [
                    $title,
                    $description !== '' ? $description : null,
                    $jenis,
                    $newMedia !== '' ? $newMedia : $oldMedia,
                    $newFile['path'] ?? ($newMedia === 'video' ? '' : $oldFile),
                    $newFile['mime'] ?? ($newMedia === 'video' ? null : ($item['mime_type'] ?? null)),
                    $newFile['size'] ?? ($newMedia === 'video' ? null : ($item['file_size'] ?? null)),
                    $finalThumb,
                    $team !== '' ? $team : null,
                    $week > 0 ? $week : null,
                    $newMedia === 'video' ? $newVideoId : null,
                    $isEdukasi,
                    $status,
                    $publishedAt,
                    $id,
                ]
            );
            if ($ok) {
                /* Bersihkan media lama yang tergantikan (HANYA milik karya ini). */
                if ($newMedia === 'image' && $oldMedia === 'image' && $newFile && $newFile['path'] !== $oldFile) {
                    delete_uploaded('karya', $oldFile);
                }
                if ($newThumb !== null && $oldThumb && $newThumb !== $oldThumb) {
                    delete_uploaded('karya', $oldThumb);
                }
                if ($oldMedia === 'video' && $newMedia !== 'video' && $oldVidId > 0) {
                    /* Video lama dibuat oleh karya ini DAN tak dipakai karya lain → hapus. */
                    $shared = (int) (q_one('SELECT COUNT(*) n FROM karya WHERE video_id = ? AND id != ?', [$oldVidId, $id])['n'] ?? 0) > 0;
                    $ov = $shared ? null : q_one("SELECT id, file_path, thumbnail FROM videos WHERE id = ? AND source = 'karya'", [$oldVidId]);
                    if ($ov) {
                        delete_uploaded('videos', $ov['file_path']);
                        if ($ov['thumbnail']) {
                            delete_uploaded('videos', $ov['thumbnail']);
                        }
                        q_exec('DELETE FROM videos WHERE id = ?', [$oldVidId]);
                    }
                }
                if ($newMedia === 'video' && $oldMedia === 'video' && $oldVidId > 0 && $newVideoId !== $oldVidId) {
                    /* Video lama digantikan — hapus bila buatan karya ini & tak dipakai bersama. */
                    $shared = (int) (q_one('SELECT COUNT(*) n FROM karya WHERE video_id = ? AND id != ?', [$oldVidId, $id])['n'] ?? 0) > 0;
                    $ov = $shared ? null : q_one("SELECT id, file_path, thumbnail FROM videos WHERE id = ? AND source = 'karya'", [$oldVidId]);
                    if ($ov) {
                        delete_uploaded('videos', $ov['file_path']);
                        if ($ov['thumbnail']) {
                            delete_uploaded('videos', $ov['thumbnail']);
                        }
                        q_exec('DELETE FROM videos WHERE id = ?', [$oldVidId]);
                    }
                }
                if ($newMedia === 'video' && $newVideoId > 0) {
                    karya_sync_video($newVideoId, $title, $description, (bool) $isEdukasi, $status);
                }
                log_activity('update', 'karya', $id, 'Perbarui karya: ' . $title);
                flash_redirect('success', 'Karya disimpan.', 'admin/karya.php');
            }
            flash_redirect('danger', 'Gagal menyimpan karya.', 'admin/karya.php');
        } else {
            $ok = q_exec(
                'INSERT INTO karya (title, description, jenis, media_type, file_path, mime_type, file_size, thumbnail, team, week_number, video_id, is_edukasi, status, published_at, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $title,
                    $description !== '' ? $description : null,
                    $jenis,
                    $newMedia,
                    $newFile['path'] ?? '',
                    $newFile['mime'] ?? null,
                    $newFile['size'] ?? null,
                    $finalThumb,
                    $team !== '' ? $team : null,
                    $week > 0 ? $week : null,
                    $newMedia === 'video' ? $newVideoId : null,
                    $isEdukasi,
                    $status,
                    $publishedAt,
                    (int) current_user()['id'],
                ]
            );
            $newId = $ok ? (int) db()->lastInsertId() : 0;
            log_activity('create', 'karya', $newId, 'Tambah karya: ' . $title);
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'Karya ditambahkan.' : 'Gagal menyimpan karya.', 'admin/karya.php');
        }
    }

    /* Gagal validasi: bersihkan file yang sempat terunggah. */
    if ($errors) {
        if ($newFile) {
            @unlink(UPLOADS_PATH . '/karya/' . $newFile['path']);
            if (!empty($newFile['thumb'])) {
                @unlink(UPLOADS_PATH . '/karya/' . $newFile['thumb']);
            }
        }
        if ($newThumb) {
            @unlink(UPLOADS_PATH . '/karya/' . $newThumb);
        }
        if (!empty($row['name'] ?? null)) {
            @unlink(UPLOADS_PATH . '/videos/' . $row['name']);
        }
    }
}

admin_layout_start($item ? 'Edit Karya' : 'Tambah Karya', 'karya');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit Karya' : 'Tambah Karya' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/karya.php')) ?>">&larr; Kembali</a>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger"><strong>Perbaiki hal berikut:</strong>
        <ul class="mb-0 mt-1"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-card" id="karyaForm">
    <?= csrf_field() ?>
    <div class="admin-card-body">
        <div class="admin-form-grid">
            <div class="admin-form-full">
                <label class="form-label-admin">Judul Karya <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="title" maxlength="200" value="<?= e($title) ?>" required placeholder="mis. Bijak Bermedia Sosial">
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Deskripsi</label>
                <textarea class="form-control" name="description" rows="3" maxlength="5000" placeholder="mis. Drama pendek edukasi mengenai pentingnya menjaga etika dalam menggunakan media sosial."><?= e($description) ?></textarea>
            </div>
            <div>
                <label class="form-label-admin">Jenis Karya</label>
                <select class="form-select" name="jenis" id="jenis">
                    <?php foreach (['video' => 'Video', 'infografis' => 'Infografis', 'foto' => 'Foto', 'lainnya' => 'Lainnya'] as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $jenis === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draf (tidak tampil publik)</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Terbit</option>
                </select>
            </div>
            <div>
                <label class="form-label-admin">Tim</label>
                <select class="form-select" name="team">
                    <option value="">— Tanpa tim —</option>
                    <?php foreach (KARYA_TIM as $t): ?>
                        <option value="<?= e($t) ?>" <?= $team === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label-admin">Periode / Minggu</label>
                <select class="form-select" name="week_number">
                    <option value="">— Tanpa periode —</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= (string) $week === (string) $i ? 'selected' : '' ?>>Minggu ke-<?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="admin-form-full">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_edukasi" value="1" <?= $isEdukasi ? 'checked' : '' ?>>
                    <span class="form-check-label">Tandai sebagai <strong>Konten Edukasi</strong> — saat terbit, karya video ini juga tampil di halaman Konten Edukasi (tanpa upload ulang).</span>
                </label>
            </div>

            <div class="admin-form-full">
                <label class="form-label-admin">File Media <span class="required-star">*</span></label>

                <?php if ($item): ?>
                    <div class="border rounded p-2 mb-2 d-flex align-items-center gap-3 flex-wrap">
                        <?php if ($mediaType === 'video'):
                            $vp = $currentVideo ? q_one('SELECT file_path, thumbnail FROM videos WHERE id = ?', [$currentVideo['id']]) : null;
                        ?>
                            <?php if ($vp): ?>
                                <video controls muted preload="metadata" playsinline style="max-width:260px;width:100%;height:auto;border-radius:.5rem;background:#000"
                                       src="<?= e(upload_url('videos/' . $vp['file_path'])) ?>"
                                    <?php if (($item['thumbnail'] ?: ($vp['thumbnail'] ?? null)) && upload_file_exists('karya', $item['thumbnail'])): ?>
                                        poster="<?= e(upload_url('karya/' . $item['thumbnail'])) ?>"
                                    <?php elseif (!empty($vp['thumbnail']) && upload_file_exists('videos', $vp['thumbnail'])): ?>
                                        poster="<?= e(upload_url('videos/' . $vp['thumbnail'])) ?>"
                                    <?php endif; ?>>
                                    Browser Anda tidak mendukung pemutaran video.
                                </video>
                                <small class="text-muted">
                                    Video pustaka #<?= (int) $currentVideo['id'] ?>: <?= e($currentVideo['title']) ?>
                                    <?= $currentVideo['source'] === 'library' ? '(dibuat dari menu Video)' : '' ?>
                                </small>
                            <?php else: ?>
                                <small class="text-danger">File video tidak ditemukan — pilih ulang sumber video.</small>
                            <?php endif; ?>
                        <?php elseif (upload_file_exists('karya', $item['file_path'])): ?>
                            <img src="<?= e(upload_url('karya/' . ($item['thumbnail'] ?: $item['file_path']))) ?>" alt="" style="max-width:220px;height:auto;border-radius:.5rem">
                            <small class="text-muted">File saat ini: <?= e(pathinfo($item['file_path'], PATHINFO_BASENAME)) ?> · <?= e(format_size($fileSize)) ?></small>
                        <?php else: ?>
                            <small class="text-danger">File media tidak ditemukan di server.</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Panel sumber video (khusus jenis Video) -->
                <div id="videoSourceWrap" style="display:none">
                    <div class="d-flex gap-4 mb-2">
                        <label class="form-check"><input class="form-check-input" type="radio" name="video_source" value="upload" checked> Upload video baru</label>
                        <label class="form-check"><input class="form-check-input" type="radio" name="video_source" value="library" <?= ($item && $mediaType === 'video') ? 'checked' : '' ?>> Pilih dari Pustaka Video</label>
                    </div>
                    <div id="vsLibrary" style="display:none" class="mb-2">
                        <select class="form-select" name="video_library_id">
                            <option value="">— Pilih video pustaka —</option>
                            <?php foreach ($library as $lv): ?>
                                <option value="<?= (int) $lv['id'] ?>" <?= $videoId === (int) $lv['id'] ? 'selected' : '' ?>>
                                    #<?= (int) $lv['id'] ?> · <?= e($lv['title']) ?> (<?= $lv['status'] === 'published' ? 'terbit' : 'draf' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="admin-help">Satu file video yang sama dipakai bersama — tidak ada duplikasi.</div>
                    </div>
                    <div id="vsUpload">
                        <input class="form-control" type="file" name="file" id="fileInput"
                               accept=".mp4,.webm,.mov,video/mp4,video/webm,video/quicktime">
                        <div class="admin-help" id="fileHelp"></div>
                        <div id="filePreview" class="mt-2"></div>
                    </div>
                </div>

                <!-- Input gambar (Infografis / Foto / Lainnya) -->
                <div id="imageInputWrap">
                    <input class="form-control" type="file" name="file" id="fileImg"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp<?= $jenis === 'lainnya' ? ',.mp4,.webm,.mov,video/mp4,video/webm,video/quicktime' : '' ?>">
                    <div class="admin-help" id="imgHelp"></div>
                    <div id="imgPreview" class="mt-2"></div>
                </div>
            </div>

            <div class="admin-form-full" id="thumbWrap">
                <label class="form-label-admin">Thumbnail / Cover (jpg/jpeg/png/webp, opsional)</label>
                <?php if ($item && $item['thumbnail'] && upload_file_exists('karya', $item['thumbnail'])): ?>
                    <div class="mb-2"><img src="<?= e(upload_url('karya/' . $item['thumbnail'])) ?>" alt="" style="max-width:180px;height:auto;border-radius:.5rem"></div>
                <?php endif; ?>
                <input class="form-control" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <div class="admin-help">Gambar kecil sebagai cover kartu &amp; poster sebelum video diputar. Kosongkan untuk mempertahankan yang ada.</div>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Karya</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/karya.php')) ?>">Batal</a>
    </div>
</form>

<script>
(function () {
    'use strict';
    var jenis = document.getElementById('jenis');
    var vsWrap = document.getElementById('videoSourceWrap');
    var imgWrap = document.getElementById('imageInputWrap');
    var vsLib = document.getElementById('vsLibrary');
    var vsUp = document.getElementById('vsUpload');
    var fileHelp = document.getElementById('fileHelp');
    var imgHelp = document.getElementById('imgHelp');
    var preview = document.getElementById('filePreview');
    var imgPreview = document.getElementById('imgPreview');

    var MAX_VIDEO = <?= e(format_size(UPLOAD_VIDEO_MAX_SIZE)) ?>;
    var MAX_IMG = <?= e(format_size(UPLOAD_MAX_SIZE)) ?>;

    function radios() {
        return document.querySelectorAll('input[name="video_source"]');
    }
    function currentSource() {
        var r = radios();
        for (var i = 0; i < r.length; i++) if (r[i].checked) return r[i].value;
        return 'upload';
    }
    function clearPreview(el) {
        el.innerHTML = '';
    }
    function syncSource() {
        var lib = currentSource() === 'library';
        vsLib.style.display = lib ? '' : 'none';
        vsUp.style.display = lib ? 'none' : '';
    }
    function syncType() {
        var isVideo = jenis.value === 'video';
        var isLainnya = jenis.value === 'lainnya';
        vsWrap.style.display = isVideo ? '' : 'none';
        imgWrap.style.display = isVideo ? 'none' : '';
        document.getElementById('thumbWrap').style.display =
            (isVideo || isLainnya || <?= $item && $mediaType === 'video' ? 'true' : 'false' ?>) ? '' : 'none';
        fileHelp.textContent = 'Format video: MP4/WebM. Maksimal ' + MAX_VIDEO + '.';
        imgHelp.textContent = isLainnya
            ? 'Format: JPG/PNG/WebP (maks ' + MAX_IMG + ') atau video MP4/WebM (maks ' + MAX_VIDEO + ') — dideteksi otomatis.'
            : 'Format gambar: JPG, JPEG, PNG, WebP. Maksimal ' + MAX_IMG + '.';
        clearPreview(preview);
        clearPreview(imgPreview);
        if (isVideo) syncSource();
    }

    function showPreview(input, target, forceVideo) {
        clearPreview(target);
        var f = input.files && input.files[0];
        if (!f) return;
        var isVid = forceVideo || /\.(mp4|webm|mov|ogg)$/i.test(f.name) || (f.type && f.type.indexOf('video/') === 0);
        var url = URL.createObjectURL(f);
        var el;
        if (isVid) {
            el = document.createElement('video');
            el.controls = true;
            el.muted = true;
            el.preload = 'metadata';
            el.playsInline = true;
            el.src = url;
            el.style.cssText = 'max-width:320px;width:100%;height:auto;border-radius:.5rem;background:#000';
        } else {
            el = document.createElement('img');
            el.src = url;
            el.alt = 'Pratinjau';
            el.style.cssText = 'max-width:260px;height:auto;border-radius:.5rem';
        }
        target.appendChild(el);
    }

    Array.prototype.forEach.call(radios(), function (r) { r.addEventListener('change', syncSource); });
    jenis.addEventListener('change', syncType);

    var fu = document.getElementById('fileInput');
    if (fu) fu.addEventListener('change', function () { showPreview(fu, preview, true); });
    var fi = document.getElementById('fileImg');
    if (fi) fi.addEventListener('change', function () { showPreview(fi, imgPreview, false); });

    syncType();
})();
</script>
<?php
admin_layout_end();
