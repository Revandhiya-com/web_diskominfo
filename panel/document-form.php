<?php
/**
 * CMS Dokumen — tambah/edit: nama, kategori, upload file (PDF/Word/Excel/ZIP).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$id = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM documents WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'Dokumen tidak ditemukan.', 'admin/documents.php');
}

$errors = [];
$name = $item['name'] ?? '';
$categoryId = (int) ($item['category_id'] ?? 0);

if (is_post()) {
    require_csrf();
    $name = post_max('name', 200);
    $categoryId = (int) post_val('category_id');
    $replaceFile = !empty($_FILES['file']['name']);

    if ($name === '') {
        $errors[] = 'Nama dokumen wajib diisi.';
    }
    if (!$item && !$replaceFile) {
        $errors[] = 'File dokumen wajib diunggah (pdf, doc, docx, xls, xlsx, zip; maks 10 MB).';
    }
    if ($replaceFile) {
        try {
            $up = process_document_upload($_FILES['file'], 'documents');
        } catch (UploadException $e) {
            $errors[] = 'File: ' . $e->getMessage();
        }
    }

    if (!$errors) {
        if ($id > 0) {
            $oldFile = $item['file'];
            $oldSize = $item['size'];
            $oldMime = $item['mime'];
            if ($replaceFile && isset($up)) {
                $oldFile = $up['name'];
                $oldSize = $up['size'];
                $oldMime = $up['mime'];
                delete_uploaded('documents', $item['file']);
            }
            $ok = q_exec('UPDATE documents SET category_id=?, name=?, file=?, size=?, mime=? WHERE id=?',
                [$categoryId ?: null, $name, $oldFile, $oldSize, $oldMime, $id]);
            log_activity('update', 'document', $id, 'Perbarui dokumen: ' . $name);
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'Dokumen disimpan.' : 'Gagal menyimpan.', 'admin/documents.php');
        }
        $ok = q_exec('INSERT INTO documents (category_id, name, file, size, mime) VALUES (?,?,?,?,?)',
            [$categoryId ?: null, $name, $up['name'], $up['size'], $up['mime']]);
        $newId = $ok ? (int) db()->lastInsertId() : 0;
        log_activity('create', 'document', $newId, 'Unggah dokumen: ' . $name);
        flash_redirect($ok ? 'success' : 'danger', $ok ? 'Dokumen diunggah.' : 'Gagal menyimpan.', 'admin/documents.php');
    }
}

$categories = q_all('SELECT id, name FROM document_categories ORDER BY name ASC');

admin_layout_start($item ? 'Edit Dokumen' : 'Tambah Dokumen', 'documents');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit Dokumen' : 'Tambah Dokumen' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/documents.php')) ?>">&larr; Kembali</a>
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
            <div>
                <label class="form-label-admin">Nama Dokumen <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="name" maxlength="200" value="<?= e($name) ?>" required>
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
            <div class="admin-form-full">
                <label class="form-label-admin">File (pdf / doc / docx / xls / xlsx / zip, maks 10 MB)</label>
                <input class="form-control" type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                <?php if ($item): ?>
                    <div class="admin-help">File saat ini: <?= e($item['file']) ?> (<?= e(format_size((int) $item['size'])) ?>). Biarkan kosong untuk tetap memakai file yang ada.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Dokumen</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/documents.php')) ?>">Batal</a>
    </div>
</form>
<?php
admin_layout_end();