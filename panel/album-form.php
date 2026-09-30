<?php
/**
 * CMS Dokumentasi — tambah / edit album + upload sampul.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$id = (int) get_val('id');
$album = $id > 0 ? q_one('SELECT * FROM albums WHERE id = ?', [$id]) : null;
if ($id > 0 && !$album) {
    flash_redirect('danger', 'Album tidak ditemukan.', 'admin/albums.php');
}

$errors = [];
$title = $album['title'] ?? '';
$slug = $album['slug'] ?? '';
$eventDate = $album['event_date'] ?? '';
$location = $album['location'] ?? '';
$categoryId = (int) ($album['category_id'] ?? 0);
$description = $album['description'] ?? '';
$status = $album['status'] ?? 'draft';
$coverImage = $album['cover_image'] ?? null;

if (is_post()) {
    require_csrf();
    $title = post_max('title', 200);
    $slug = post_max('slug', 220);
    $eventDate = post_val('event_date');
    $location = post_max('location', 255);
    $categoryId = (int) post_val('category_id');
    $description = post_max('description', 60000);
    $status = post_val('status') === 'published' ? 'published' : 'draft';
    $removeCover = isset($_POST['remove_cover']);

    if ($title === '') {
        $errors[] = 'Judul album wajib diisi.';
    }
    $slug = $slug !== '' ? slugify($slug) : slugify($title);
    if (strlen($eventDate) > 0 && !strtotime($eventDate)) {
        $errors[] = 'Format tanggal acara tidak valid.';
    }
    if (!empty($_FILES['cover']['name'])) {
        try {
            $cover = process_image_upload($_FILES['cover'], 'albums', false);
        } catch (UploadException $e) {
            $errors[] = 'Sampul: ' . $e->getMessage();
        }
    }

    if (!$errors) {
        $slug = unique_slug($slug, 'albums', $id);
        if ($id > 0) {
            $ok = q_exec(
                'UPDATE albums SET title=?, slug=?, description=?, category_id=?, location=?, event_date=?, status=?, cover_image=COALESCE(?, cover_image) WHERE id=?',
                [$title, $slug, $description ?: null, $categoryId ?: null, $location ?: null, $eventDate ?: null, $status, $cover['image'] ?? null, $id]
            );
            if ($removeCover && $coverImage) {
                delete_uploaded('albums', $coverImage);
                q_exec('UPDATE albums SET cover_image = NULL WHERE id = ?', [$id]);
                $coverImage = null;
            }
            log_activity('update', 'album', $id, 'Perbarui album: ' . $title);
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'Album berhasil diperbarui.' : 'Gagal menyimpan album.', 'admin/albums.php');
        } else {
            $ok = q_exec(
                'INSERT INTO albums (title, slug, description, category_id, location, event_date, cover_image, status, created_by) VALUES (?,?,?,?,?,?,?,?,?)',
                [$title, $slug, $description ?: null, $categoryId ?: null, $location ?: null, $eventDate ?: null, $cover['image'] ?? null, $status, current_user()['id']]
            );
            if ($ok) {
                $newId = (int) db()->lastInsertId();
            }
            log_activity('create', 'album', $newId ?? 0, 'Buat album: ' . $title);
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'Album berhasil dibuat.' : 'Gagal menyimpan album.', $ok ? 'admin/photos.php?album=' . ($newId ?? 0) : 'admin/albums.php');
        }
    }
}

$categories = q_all('SELECT id, name FROM album_categories ORDER BY name ASC');

admin_layout_start($album ? 'Edit Album' : 'Tambah Album', 'albums');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><?= $album ? 'Edit Album' : 'Tambah Album' ?></h1>
        <p class="text-muted small mb-0">Sampul dan foto disimpan di folder upload — database hanya menyimpan path-nya.</p>
    </div>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/albums.php')) ?>">&larr; Kembali ke daftar</a>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <strong>Perbaiki hal berikut:</strong>
        <ul class="mb-0 mt-1"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-card" data-uploading>
    <?= csrf_field() ?>
    <div class="admin-card-body">
        <input type="hidden" name="id" value="<?= (int) $id ?>">
        <div class="admin-form-grid">
            <div>
                <label class="form-label-admin">Judul <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="title" maxlength="200" value="<?= e($title) ?>" required>
            </div>
            <div>
                <label class="form-label-admin">Slug (kosongkan untuk otomatis)</label>
                <input class="form-control" type="text" name="slug" maxlength="220" value="<?= e($slug) ?>">
                <div class="admin-help">Contoh: workshop-digitalisasi-2026</div>
            </div>
            <div>
                <label class="form-label-admin">Tanggal Acara</label>
                <input class="form-control" type="date" name="event_date" value="<?= e($eventDate) ?>">
            </div>
            <div>
                <label class="form-label-admin">Lokasi</label>
                <input class="form-control" type="text" name="location" maxlength="255" value="<?= e($location) ?>" placeholder="Contoh: Diskominfo Provinsi Banten">
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
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Terbit (tampil publik)</option>
                </select>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Deskripsi</label>
                <textarea class="form-control" name="description" rows="4" maxlength="60000"><?= e($description) ?></textarea>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Sampul Album (jpg / jpeg / png / webp, maks <?= e(format_size(UPLOAD_MAX_SIZE)) ?>)</label>
                <div class="d-flex align-items-center gap-3">
                    <img id="coverPreview" class="<?= $coverImage ? '' : 'd-none ' ?>thumb-img" style="width:160px;height:100px"
                         src="<?= $coverImage ? e(upload_url('albums/' . $coverImage)) : '#' ?>" alt="Pratinjau sampul">
                    <div class="flex-grow-1">
                        <input class="form-control" type="file" name="cover" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-cover-preview="#coverPreview">
                        <?php if ($coverImage): ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_cover" id="remove_cover" value="1">
                                <label class="form-check-label small" for="remove_cover">Hapus sampul saat ini</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Album</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/albums.php')) ?>">Batal</a>
    </div>
</form>
<?php
admin_layout_end();