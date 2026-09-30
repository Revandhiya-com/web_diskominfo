<?php
/**
 * CMS Agenda — tambah/edit.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$id = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM agendas WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'Agenda tidak ditemukan.', 'admin/agendas.php');
}

$errors = [];
$title = $item['title'] ?? '';
$description = $item['description'] ?? '';
$location = $item['location'] ?? '';
$sourceUrl = $item['source_url'] ?? '';
$startDt = $item['start_datetime'] ?? '';
$endDt = $item['end_datetime'] ?? '';
$status = $item['status'] ?? 'draft';

if (is_post()) {
    require_csrf();
    $title = post_max('title', 200);
    $description = post_max('description', 60000);
    $location = post_max('location', 255);
    $sourceUrl = post_max('source_url', 500);
    $startRaw = post_val('start_datetime');
    $endRaw = post_val('end_datetime');
    $status = post_val('status') === 'published' ? 'published' : 'draft';

    if ($title === '') {
        $errors[] = 'Judul agenda wajib diisi.';
    }
    $startDt = $startRaw !== '' ? str_replace('T', ' ', $startRaw) . ':00' : '';
    if ($startDt === '' || !strtotime($startDt)) {
        $errors[] = 'Tanggal mulai wajib diisi dengan benar.';
    }
    $endDt = $endRaw !== '' ? str_replace('T', ' ', $endRaw) . ':00' : '';
    if ($endDt !== '' && strtotime($endDt) < strtotime($startDt)) {
        $errors[] = 'Waktu selesai harus setelah waktu mulai.';
    }

    if (!$errors) {
        if ($id > 0) {
            $ok = q_exec('UPDATE agendas SET title=?, description=?, location=?, source_url=?, start_datetime=?, end_datetime=?, status=? WHERE id=?',
                [$title, $description ?: null, $location ?: null, $sourceUrl ?: null, $startDt, $endDt ?: null, $status, $id]);
            log_activity('update', 'agenda', $id, 'Perbarui agenda: ' . $title);
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'Agenda disimpan.' : 'Gagal menyimpan.', 'admin/agendas.php');
        }
        $ok = q_exec('INSERT INTO agendas (title, description, location, source_url, start_datetime, end_datetime, status) VALUES (?,?,?,?,?,?,?)',
            [$title, $description ?: null, $location ?: null, $sourceUrl ?: null, $startDt, $endDt ?: null, $status]);
        $newId = $ok ? (int) db()->lastInsertId() : 0;
        log_activity('create', 'agenda', $newId, 'Buat agenda: ' . $title);
        flash_redirect($ok ? 'success' : 'danger', $ok ? 'Agenda dibuat.' : 'Gagal menyimpan.', 'admin/agendas.php');
    }
}

admin_layout_start($item ? 'Edit Agenda' : 'Tambah Agenda', 'agendas');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit Agenda' : 'Tambah Agenda' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/agendas.php')) ?>">&larr; Kembali</a>
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
            <div class="admin-form-full">
                <label class="form-label-admin">Judul <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="title" maxlength="200" value="<?= e($title) ?>" required>
            </div>
            <div>
                <label class="form-label-admin">Mulai <span class="required-star">*</span></label>
                <input class="form-control" type="datetime-local" name="start_datetime"
                       value="<?= e($startDt ? date('Y-m-d\TH:i', strtotime($startDt)) : '') ?>" required>
            </div>
            <div>
                <label class="form-label-admin">Selesai (opsional)</label>
                <input class="form-control" type="datetime-local" name="end_datetime"
                       value="<?= e($endDt ? date('Y-m-d\TH:i', strtotime($endDt)) : '') ?>">
            </div>
            <div>
                <label class="form-label-admin">Lokasi</label>
                <input class="form-control" type="text" name="location" maxlength="255" value="<?= e($location) ?>">
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">URL Sumber (mis. rilis resmi instansi)</label>
                <input class="form-control" type="url" name="source_url" maxlength="500" value="<?= e($sourceUrl) ?>" placeholder="https://diskominfo.bantenprov.go.id/...">
                <div class="admin-help">Diisi bila agenda disusun dari sumber eksternal; ditampilkan sebagai tautan di halaman publik.</div>
            </div>
            <div>
                <label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draf</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Terbit</option>
                </select>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Deskripsi</label>
                <textarea class="form-control" name="description" rows="4"><?= e($description) ?></textarea>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Agenda</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/agendas.php')) ?>">Batal</a>
    </div>
</form>
<?php
admin_layout_end();