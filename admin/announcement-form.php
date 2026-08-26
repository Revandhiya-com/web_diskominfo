<?php
/**
 * CMS Pengumuman — tambah/edit + pin + publish/unpublish.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$id = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM announcements WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'Pengumuman tidak ditemukan.', 'admin/announcements.php');
}

$errors = [];
$title = $item['title'] ?? '';
$content = $item['content'] ?? '';
$sourceUrl = $item['source_url'] ?? '';
$isPinned = (int) ($item['is_pinned'] ?? 0);
$status = $item['status'] ?? 'draft';
$publishedAt = $item['published_at'] ?? '';

if (is_post()) {
    require_csrf();
    $title = post_max('title', 200);
    $content = post_max('content', 60000);
    $sourceUrl = post_max('source_url', 500);
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
    $status = post_val('status') === 'published' ? 'published' : 'draft';
    $pubRaw = post_val('published_at');
    $publishedAt = $pubRaw !== '' ? str_replace('T', ' ', $pubRaw) . ':00' : '';

    if ($title === '') {
        $errors[] = 'Judul pengumuman wajib diisi.';
    }

    if (!$errors) {
        if ($id > 0) {
            $ok = q_exec('UPDATE announcements SET title=?, content=?, source_url=?, is_pinned=?, status=?, published_at=? WHERE id=?',
                [$title, $content ?: null, $sourceUrl ?: null, $isPinned, $status, $publishedAt ?: null, $id]);
            log_activity('update', 'announcement', $id, 'Perbarui pengumuman: ' . $title);
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'Pengumuman disimpan.' : 'Gagal menyimpan.', 'admin/announcements.php');
        }
        $ok = q_exec('INSERT INTO announcements (title, content, source_url, is_pinned, status, published_at) VALUES (?,?,?,?,?,?)',
            [$title, $content ?: null, $sourceUrl ?: null, $isPinned, $status, $publishedAt ?: null]);
        $newId = $ok ? (int) db()->lastInsertId() : 0;
        log_activity('create', 'announcement', $newId, 'Buat pengumuman: ' . $title);
        flash_redirect($ok ? 'success' : 'danger', $ok ? 'Pengumuman dibuat.' : 'Gagal menyimpan.', 'admin/announcements.php');
    }
}

admin_layout_start($item ? 'Edit Pengumuman' : 'Tambah Pengumuman', 'announcements');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit Pengumuman' : 'Tambah Pengumuman' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/announcements.php')) ?>">&larr; Kembali</a>
</div>
<?php if ($errors): ?>
    <div class="alert alert-danger"><strong>Perbaiki hal berikut:</strong>
        <ul class="mb-0 mt-1"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<form method="post" class="admin-card">
    <?= csrf_field() ?>
    <div class="admin-card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-admin">Judul <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="title" maxlength="200" value="<?= e($title) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draf</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Terbit</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label-admin">Tanggal Terbit (kosongkan = sekarang)</label>
                <input class="form-control" type="datetime-local" name="published_at"
                       value="<?= e($publishedAt ? date('Y-m-d\TH:i', strtotime($publishedAt)) : '') ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end pb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_pinned" id="isPinned" value="1" <?= $isPinned ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isPinned"><i class="bi bi-pin-angle me-1"></i>Sematkan di atas (penting)</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label-admin">Isi Pengumuman</label>
                <textarea class="form-control" name="content" rows="6"><?= e($content) ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label-admin">URL Sumber (mis. rilis resmi instansi)</label>
                <input class="form-control" type="url" name="source_url" maxlength="500" value="<?= e($sourceUrl) ?>" placeholder="https://diskominfo.bantenprov.go.id/...">
                <div class="admin-help">Diisi bila konten disusun dari sumber eksternal; ditampilkan sebagai tautan di halaman publik.</div>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Pengumuman</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/announcements.php')) ?>">Batal</a>
    </div>
</form>
<?php
admin_layout_end();