<?php
/**
 * CMS Halaman Statis — tambah/edit (konten HTML).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$id = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM pages WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'Halaman tidak ditemukan.', 'admin/pages.php');
}

$errors = [];
$title = $item['title'] ?? '';
$slug = $item['slug'] ?? '';
$content = $item['content'] ?? '';
$sourceUrl = $item['source_url'] ?? '';
$status = $item['status'] ?? 'draft';

if (is_post()) {
    require_csrf();
    $title = post_max('title', 200);
    $slug = post_max('slug', 220);
    $content = (string) ($_POST['content'] ?? '');
    $sourceUrl = post_max('source_url', 500);
    $status = post_val('status') === 'published' ? 'published' : 'draft';

    if ($title === '') {
        $errors[] = 'Judul wajib diisi.';
    }
    $slug = $slug !== '' ? slugify($slug) : slugify($title);

    if (!$errors && $slug !== '') {
        $slug = unique_slug($slug, 'pages', $id);
        if ($id > 0) {
            $ok = q_exec('UPDATE pages SET title=?, slug=?, content=?, source_url=?, status=? WHERE id=?', [$title, $slug, $content, $sourceUrl ?: null, $status, $id]);
            log_activity('update', 'page', $id, 'Perbarui halaman: ' . $title);
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'Halaman disimpan.' : 'Gagal menyimpan.', 'admin/pages.php');
        }
        $ok = q_exec('INSERT INTO pages (title, slug, content, source_url, status) VALUES (?,?,?,?,?)', [$title, $slug, $content, $sourceUrl ?: null, $status]);
        $newId = $ok ? (int) db()->lastInsertId() : 0;
        log_activity('create', 'page', $newId, 'Buat halaman: ' . $title);
        flash_redirect($ok ? 'success' : 'danger', $ok ? 'Halaman dibuat.' : 'Gagal menyimpan.', 'admin/pages.php');
    }
}

admin_layout_start($item ? 'Edit Halaman' : 'Tambah Halaman', 'pages');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit Halaman' : 'Tambah Halaman' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/pages.php')) ?>">&larr; Kembali</a>
</div>
<?php if ($errors): ?>
    <div class="alert alert-danger"><strong>Perbaiki hal berikut:</strong>
        <ul class="mb-0 mt-1"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<form method="post" class="admin-card">
    <?= csrf_field() ?>
    <div class="admin-card-body">
        <div class="admin-form-grid">
            <div>
                <label class="form-label-admin">Judul <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="title" maxlength="200" value="<?= e($title) ?>" required>
            </div>
            <div>
                <label class="form-label-admin">Slug <span class="required-star">*</span> (menentukan URL publik)</label>
                <input class="form-control" type="text" name="slug" maxlength="220" value="<?= e($slug) ?>" required>
                <div class="admin-help">URL publik: ?page=<?= e($slug) ?></div>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draf (tidak tampil publik)</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Terbit</option>
                </select>
            </div>
            <div class="admin-form-full">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                    <label class="form-label-admin mb-0">Isi Halaman (HTML diizinkan)</label>
                    <span class="d-inline-flex align-items-center gap-2">
                        <button class="btn-admin-sm" type="button" data-video-insert>
                            <i class="bi bi-film me-1"></i>Sisipkan Video
                        </button>
                        <input class="d-none" type="file" data-video-input
                               accept=".mp4,.webm,.mov,.ogg,video/mp4,video/webm,video/ogg,video/quicktime">
                    </span>
                </div>
                <textarea class="form-control font-monospace" name="content" rows="16" maxlength="100000" data-video-target><?= e($content) ?></textarea>
                <div class="admin-help">Klik "Sisipkan Video" untuk mengunggah mp4/webm/mov (maks <?= e(format_size(UPLOAD_VIDEO_MAX_SIZE)) ?>) — tag &lt;video&gt; otomatis ditambahkan di posisi kursor.</div>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">URL Sumber (mis. halaman resmi instansi)</label>
                <input class="form-control" type="url" name="source_url" maxlength="500" value="<?= e($sourceUrl) ?>" placeholder="https://diskominfo.bantenprov.go.id/...">
                <div class="admin-help">Diisi bila konten disusun dari sumber eksternal; ditampilkan sebagai tautan di halaman publik.</div>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Halaman</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/pages.php')) ?>">Batal</a>
    </div>
</form>
<?php
admin_layout_end();