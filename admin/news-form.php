<?php
/**
 * CMS Berita — tambah / edit (termasuk thumbnail + publish/unpublish).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

/**
 * Proses aksi galeri foto dokumentasi untuk sebuah berita:
 * hapus foto, set foto utama, upload foto baru, ubah grup/caption.
 * Return: daftar pesan error (string) bila ada, selain itu [].
 */
function process_news_gallery(int $newsId): array
{
    $errors = [];

    /* 1. Hapus foto yang ditandai */
    $deleteIds = array_map('intval', (array) post_val('gallery_delete'));
    foreach ($deleteIds as $pid) {
        if ($pid <= 0) continue;
        $row = q_one('SELECT image FROM news_photos WHERE id = ? AND news_id = ?', [$pid, $newsId]);
        if ($row) {
            delete_uploaded('news', $row['image']);
            q_exec('DELETE FROM news_photos WHERE id = ?', [$pid]);
        }
    }

    /* 2. Jadikan salah satu foto sebagai foto utama (thumbnail berita) */
    $mainId = (int) post_val('gallery_main');
    if ($mainId > 0) {
        $row = q_one('SELECT image FROM news_photos WHERE id = ? AND news_id = ?', [$mainId, $newsId]);
        if ($row) {
            q_exec('UPDATE news SET thumbnail = ? WHERE id = ?', [$row['image'], $newsId]);
        }
    }

    /* 3. Ubah grup (jenis lomba) & caption foto yang sudah ada */
    $groups = (array) post_val('group_name');
    $captions = (array) post_val('caption');
    foreach ($groups as $pid => $g) {
        $pid = (int) $pid;
        if ($pid <= 0) continue;
        $g = trim((string) $g);
        $c = trim((string) ($captions[$pid] ?? ''));
        q_exec(
            'UPDATE news_photos SET group_name = ?, caption = ? WHERE id = ? AND news_id = ?',
            [$g !== '' ? mb_substr($g, 0, 120) : null, $c !== '' ? mb_substr($c, 0, 255) : null, $pid, $newsId]
        );
    }

    /* 4. Upload foto baru (bisa banyak sekaligus) */
    $group = trim((string) post_val('gallery_group'));
    $caption = trim((string) post_val('gallery_caption'));
    if (!empty($_FILES['gallery_photos']['name'][0])) {
        $max = (int) (q_one(
            'SELECT COALESCE(MAX(sort_order), 0) AS m FROM news_photos WHERE news_id = ?',
            [$newsId]
        )['m'] ?? 0);
        $files = $_FILES['gallery_photos'];
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            $one = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            try {
                $up = process_media_upload($one, 'news', true);
                q_exec(
                    'INSERT INTO news_photos (news_id, group_name, caption, image, thumbnail, sort_order, media_type) VALUES (?,?,?,?,?,?,?)',
                    [$newsId, $group !== '' ? mb_substr($group, 0, 120) : null, $caption !== '' ? mb_substr($caption, 0, 255) : null, $up['image'], $up['thumb'], $max + $i + 1, $up['type']]
                );
                log_activity('create', 'news_photos', (int) db()->lastInsertId(), 'Tambah media dokumentasi berita #' . $newsId);
            } catch (UploadException $e) {
                $errors[] = ($files['name'][$i] ?: 'File ke-' . ($i + 1)) . ': ' . $e->getMessage();
            }
        }
    }

    return $errors;
}

$id = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM news WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'Berita tidak ditemukan.', 'admin/news.php');
}

$errors = [];
$title = $item['title'] ?? '';
$slug = $item['slug'] ?? '';
$categoryId = (int) ($item['category_id'] ?? 0);
$excerpt = $item['excerpt'] ?? '';
$content = $item['content'] ?? '';
$sourceUrl = $item['source_url'] ?? '';
$status = $item['status'] ?? 'draft';
$publishedAt = $item['published_at'] ?? '';
$thumbnail = $item['thumbnail'] ?? null;

if (is_post()) {
    require_csrf();
    $title = post_max('title', 200);
    $slug = post_max('slug', 220);
    $categoryId = (int) post_val('category_id');
    $excerpt = post_max('excerpt', 500);
    $content = (string) ($_POST['content'] ?? '');
    $sourceUrl = post_max('source_url', 500);
    $status = post_val('status') === 'published' ? 'published' : 'draft';
    $pubRaw = post_val('published_at');
    $publishedAt = $pubRaw !== '' ? str_replace('T', ' ', $pubRaw) . ':00' : '';

    if ($title === '') {
        $errors[] = 'Judul wajib diisi.';
    }
    if ($content === '') {
        $errors[] = 'Isi berita wajib diisi.';
    }
    if (!empty($_FILES['thumbnail']['name'])) {
        try {
            $thumb = process_image_upload($_FILES['thumbnail'], 'news', false);
        } catch (UploadException $e) {
            $errors[] = 'Thumbnail: ' . $e->getMessage();
        }
    }

    if (!$errors) {
        $slug = unique_slug($slug !== '' ? slugify($slug) : slugify($title), 'news', $id);
        if ($slug === '') {
            $errors[] = 'Slug tidak valid.';
        } else {
            if ($id > 0) {
                $ok = q_exec(
                    'UPDATE news SET category_id=?, title=?, slug=?, excerpt=?, content=?, source_url=?, thumbnail=COALESCE(?, thumbnail), status=?, published_at=? WHERE id=?',
                    [$categoryId ?: null, $title, $slug, $excerpt ?: null, $content, $sourceUrl ?: null, $thumb['image'] ?? null, $status, $publishedAt ?: null, $id]
                );
                $newsId = $id;
                log_activity('update', 'news', $id, 'Perbarui berita: ' . $title);
            } else {
                $ok = q_exec(
                    'INSERT INTO news (category_id, title, slug, excerpt, content, source_url, thumbnail, author_id, status, published_at) VALUES (?,?,?,?,?,?,?,?,?,?)',
                    [$categoryId ?: null, $title, $slug, $excerpt ?: null, $content, $sourceUrl ?: null, $thumb['image'] ?? null, current_user()['id'], $status, $publishedAt ?: null]
                );
                $newsId = $ok ? (int) db()->lastInsertId() : 0;
                log_activity('create', 'news', $newsId, 'Buat berita: ' . $title);
            }

            if ($ok && $newsId > 0) {
                $galleryErrors = process_news_gallery($newsId);
                if ($galleryErrors) {
                    flash_redirect(
                        'warning',
                        'Berita disimpan. Catatan galeri foto: ' . implode('; ', $galleryErrors),
                        'admin/news.php'
                    );
                }
                flash_redirect('success', 'Berita disimpan.', 'admin/news.php');
            } else {
                flash_redirect('danger', 'Gagal menyimpan berita.', 'admin/news.php');
            }
        }
    }
}

$categories = q_all('SELECT id, name FROM news_categories ORDER BY name ASC');
$newsPhotos = $id > 0 ? q_all(
    'SELECT id, group_name, caption, image, thumbnail, media_type FROM news_photos WHERE news_id = ? ORDER BY group_name, sort_order, id',
    [$id]
) : [];

admin_layout_start($item ? 'Edit Berita' : 'Tambah Berita', 'news');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit Berita' : 'Tambah Berita' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/news.php')) ?>">&larr; Kembali</a>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger"><strong>Perbaiki hal berikut:</strong>
        <ul class="mb-0 mt-1"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-card" data-uploading>
    <?= csrf_field() ?>
    <div class="admin-card-body">
        <div class="admin-form-grid">
            <div class="admin-form-full">
                <label class="form-label-admin">Judul <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="title" maxlength="200" value="<?= e($title) ?>" required>
            </div>
            <div>
                <label class="form-label-admin">Slug (kosongkan untuk otomatis)</label>
                <input class="form-control" type="text" name="slug" maxlength="220" value="<?= e($slug) ?>">
            </div>
            <div>
                <label class="form-label-admin">Kategori</label>
                <select class="form-select" name="category_id">
                    <option value="0">— Tanpa kategori —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $categoryId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draf</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Terbit</option>
                </select>
            </div>
            <div>
                <label class="form-label-admin">Tanggal Terbit (kosongkan = sekarang)</label>
                <input class="form-control" type="datetime-local" name="published_at"
                       value="<?= e($publishedAt ? date('Y-m-d\TH:i', strtotime($publishedAt)) : '') ?>">
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Ringkasan (excerpt)</label>
                <textarea class="form-control" name="excerpt" rows="2" maxlength="500"><?= e($excerpt) ?></textarea>
            </div>
            <div class="admin-form-full">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                    <label class="form-label-admin mb-0">Isi Berita (HTML diizinkan)</label>
                    <span class="d-inline-flex align-items-center gap-2">
                        <button class="btn-admin-sm" type="button" data-video-insert>
                            <i class="bi bi-film me-1"></i>Sisipkan Video
                        </button>
                        <input class="d-none" type="file" data-video-input
                               accept=".mp4,.webm,.mov,.ogg,video/mp4,video/webm,video/ogg,video/quicktime">
                    </span>
                </div>
                <textarea class="form-control font-monospace" name="content" rows="12" data-video-target><?= e($content) ?></textarea>
                <div class="admin-help">Klik "Sisipkan Video" untuk mengunggah mp4/webm/mov (maks <?= e(format_size(UPLOAD_VIDEO_MAX_SIZE)) ?>) — tag &lt;video&gt; otomatis ditambahkan di posisi kursor.</div>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">URL Sumber (mis. rilis berita resmi instansi)</label>
                <input class="form-control" type="url" name="source_url" maxlength="500" value="<?= e($sourceUrl) ?>" placeholder="https://diskominfo.bantenprov.go.id/...">
                <div class="admin-help">Diisi bila konten disusun dari sumber eksternal; ditampilkan sebagai tautan di halaman publik.</div>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Thumbnail (jpg/jpeg/png/webp, maks <?= e(format_size(UPLOAD_MAX_SIZE)) ?>)</label>
                <div class="d-flex align-items-center gap-3">
                    <img id="tnPreview" class="<?= $thumbnail ? '' : 'd-none ' ?>thumb-img"
                         style="width:160px;height:100px" src="<?= $thumbnail ? e(upload_url('news/' . $thumbnail)) : '#' ?>" alt="Pratinjau thumbnail">
                    <input class="form-control" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-cover-preview="#tnPreview">
                </div>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Dokumentasi Foto &amp; Video Kegiatan</label>
                <div class="admin-help mb-2">Foto/video ini tampil di halaman detail berita, dikelompokkan per jenis lomba. Tandai salah satu foto sebagai <strong>foto utama</strong> untuk dipakai di kartu kegiatan.</div>

                <?php if ($newsPhotos): ?>
                    <div class="border rounded p-2 mb-3">
                        <?php foreach ($newsPhotos as $np): ?>
                            <div class="d-flex align-items-start gap-2 p-2 border-bottom">
                                <?php if (($np['media_type'] ?? 'image') === 'video'): ?>
                                    <video muted preload="metadata" playsinline style="width:90px;height:60px;object-fit:cover;background:#000" class="rounded flex-shrink-0"
                                           src="<?= e(upload_url('news/' . $np['image'])) ?>"
                                        <?php if ($np['thumbnail'] && upload_file_exists('news', $np['thumbnail'])): ?>poster="<?= e(upload_url('news/' . $np['thumbnail'])) ?>"<?php endif; ?>>
                                    </video>
                                <?php elseif (upload_file_exists('news', $np['image'])): ?>
                                    <img src="<?= e(upload_url('news/' . ($np['thumbnail'] ?: $np['image']))) ?>" alt="" style="width:90px;height:60px;object-fit:cover" class="rounded flex-shrink-0">
                                <?php else: ?>
                                    <span class="d-inline-block rounded bg-secondary-subtle flex-shrink-0" style="width:90px;height:60px"></span>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <input class="form-control form-control-sm mb-1" type="text" name="group_name[<?= (int) $np['id'] ?>]" maxlength="120" value="<?= e($np['group_name'] ?? '') ?>" placeholder="Kelompok (mis. Lomba Makan Kerupuk)">
                                    <input class="form-control form-control-sm" type="text" name="caption[<?= (int) $np['id'] ?>]" maxlength="255" value="<?= e($np['caption'] ?? '') ?>" placeholder="Keterangan foto/video (opsional)">
                                </div>
                                <div class="d-flex flex-column gap-1 flex-shrink-0">
                                    <label class="form-check small mb-0">
                                        <input class="form-check-input" type="radio" name="gallery_main" value="<?= (int) $np['id'] ?>" <?= $thumbnail === $np['image'] ? 'checked' : '' ?>>
                                        <span class="form-check-label">Foto utama</span>
                                    </label>
                                    <label class="form-check small mb-0 text-danger">
                                        <input class="form-check-input" type="checkbox" name="gallery_delete[]" value="<?= (int) $np['id'] ?>">
                                        <span class="form-check-label">Hapus</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="border rounded p-2 bg-body-tertiary">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label-admin small mb-1">Kelompok (jenis lomba)</label>
                            <input class="form-control form-control-sm" type="text" name="gallery_group" maxlength="120" placeholder="mis. Lomba Makan Kerupuk">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-admin small mb-1">Keterangan (opsional)</label>
                            <input class="form-control form-control-sm" type="text" name="gallery_caption" maxlength="255" placeholder="mis. Peserta lomba makan kerupuk">
                        </div>
                    </div>
                    <label class="form-label-admin small mt-2 mb-1">Pilih foto/video (bisa banyak) — video maks <?= e(format_size(UPLOAD_VIDEO_MAX_SIZE)) ?></label>
                    <input class="form-control form-control-sm" type="file" name="gallery_photos[]" multiple accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,.mov,image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime">
                </div>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Berita</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/news.php')) ?>">Batal</a>
    </div>
</form>
<?php
admin_layout_end();