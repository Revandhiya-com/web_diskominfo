<?php
/**
 * CMS FAQ — tambah/edit.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$id = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM faqs WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'FAQ tidak ditemukan.', 'admin/faqs.php');
}

$errors = [];
$question = $item['question'] ?? '';
$answer = $item['answer'] ?? '';
$category = $item['category'] ?? '';
$sortOrder = (int) ($item['sort_order'] ?? 0);
$isActive = (int) ($item['is_active'] ?? 1);

if (is_post()) {
    require_csrf();
    $question = post_max('question', 255);
    $answer = (string) ($_POST['answer'] ?? '');
    $category = post_max('category', 60);
    $sortOrder = (int) post_val('sort_order', '0');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($question === '' || trim($answer) === '') {
        $errors[] = 'Pertanyaan dan jawaban wajib diisi.';
    }

    if (!$errors) {
        if ($id > 0) {
            $ok = q_exec('UPDATE faqs SET question=?, answer=?, category=?, sort_order=?, is_active=? WHERE id=?',
                [$question, $answer, $category ?: null, $sortOrder, $isActive, $id]);
            log_activity('update', 'faq', $id, 'Perbarui FAQ: ' . $question);
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'FAQ disimpan.' : 'Gagal menyimpan.', 'admin/faqs.php');
        }
        $maxOrder = (int) (q_one('SELECT COALESCE(MAX(sort_order),0) n FROM faqs')['n'] ?? 0);
        $ok = q_exec('INSERT INTO faqs (question, answer, category, sort_order, is_active) VALUES (?,?,?,?,?)',
            [$question, $answer, $category ?: null, $sortOrder > 0 ? $sortOrder : $maxOrder + 1, $isActive]);
        $newId = $ok ? (int) db()->lastInsertId() : 0;
        log_activity('create', 'faq', $newId, 'Buat FAQ: ' . $question);
        flash_redirect($ok ? 'success' : 'danger', $ok ? 'FAQ dibuat.' : 'Gagal menyimpan.', 'admin/faqs.php');
    }
}

admin_layout_start($item ? 'Edit FAQ' : 'Tambah FAQ', 'faqs');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit FAQ' : 'Tambah FAQ' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/faqs.php')) ?>">&larr; Kembali</a>
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
                <label class="form-label-admin">Pertanyaan <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="question" maxlength="255" value="<?= e($question) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label-admin">Jawaban <span class="required-star">*</span></label>
                <textarea class="form-control" name="answer" rows="5" required><?= e($answer) ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label-admin">Kategori</label>
                <input class="form-control" type="text" name="category" maxlength="60" value="<?= e($category) ?>" placeholder="Contoh: Umum">
            </div>
            <div class="col-md-4">
                <label class="form-label-admin">Urutan</label>
                <input class="form-control" type="number" min="0" name="sort_order" value="<?= (int) $sortOrder ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end pb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" <?= $isActive ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isActive">Tampil di halaman publik</label>
                </div>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan FAQ</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/faqs.php')) ?>">Batal</a>
    </div>
</form>
<?php
admin_layout_end();