<?php
/**
 * CMS Alumni — tambah / edit (termasuk upload foto, slug, periode, status).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$id = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM alumni WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'Alumni tidak ditemukan.', 'admin/alumni.php');
}

$errors = [];
$name = $item['name'] ?? '';
$slug = $item['slug'] ?? '';
$photo = $item['photo'] ?? null;
$type = $item['type'] ?? 'pkl';
$institution = $item['institution'] ?? '';
$major = $item['major'] ?? '';
$startDate = $item['start_date'] ?? '';
$endDate = $item['end_date'] ?? '';
$year = $item['year'] ?? '';
$division = $item['division'] ?? '';
$description = $item['description'] ?? '';
$status = $item['status'] ?? 'draft';

if (is_post()) {
    require_csrf();
    $name = post_max('name', 150);
    $slug = post_max('slug', 170);
    $type = post_val('type') === 'magang' ? 'magang' : 'pkl';
    $institution = post_max('institution', 200);
    $major = post_max('major', 150);
    $startDate = post_val('start_date');
    $endDate = post_val('end_date');
    $yearRaw = post_val('year');
    $division = post_max('division', 150);
    $description = post_max('description', 2000);
    $status = post_val('status') === 'published' ? 'published' : 'draft';

    if ($name === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }

    /* Validasi tanggal periode */
    $parseDate = function (string $d): ?string {
        $d = trim($d);
        if ($d === '') return null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) return null;
        [$y, $m, $day] = array_map('intval', explode('-', $d));
        return checkdate($m, $day, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $day) : null;
    };
    $start = $parseDate($startDate);
    $end = $parseDate($endDate);
    if ($startDate !== '' && $start === null) {
        $errors[] = 'Tanggal mulai periode tidak valid.';
    }
    if ($endDate !== '' && $end === null) {
        $errors[] = 'Tanggal selesai periode tidak valid.';
    }
    if ($start !== null && $end !== null && $end < $start) {
        $errors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
    }

    /* Tahun: angka 4 digit atau dikosongkan */
    $year = trim($yearRaw);
    if ($year !== '') {
        if (!preg_match('/^\d{4}$/', $year) || (int) $year < 1900 || (int) $year > 2100) {
            $errors[] = 'Tahun harus berupa 4 digit (mis. 2026).';
            $year = $yearRaw;
        } else {
            $year = (string) (int) $year;
        }
    }

    if (!$errors) {
        /* Upload foto baru (jika ada) — hanya setelah validasi lain lolos */
        $newPhoto = null;
        if (!empty($_FILES['photo']['name'])) {
            try {
                $newPhoto = process_image_upload($_FILES['photo'], 'alumni', true)['image'];
            } catch (UploadException $e) {
                $errors[] = 'Foto: ' . $e->getMessage();
            }
        }
    }

    if (!$errors) {
        $slug = unique_slug($slug !== '' ? slugify($slug) : slugify($name), 'alumni', $id);
        if ($slug === '') {
            $errors[] = 'Slug tidak valid.';
        } else {
            if ($id > 0) {
                $ok = q_exec(
                    'UPDATE alumni SET name=?, slug=?, photo=COALESCE(?, photo), type=?, institution=?, major=?, start_date=?, end_date=?, year=?, division=?, description=?, status=? WHERE id=?',
                    [$name, $slug, $newPhoto, $type, $institution ?: null, $major ?: null, $start, $end, $year !== '' ? (int) $year : null, $division ?: null, $description ?: null, $status, $id]
                );
                if ($ok && $newPhoto !== null) {
                    delete_uploaded('alumni', $photo);
                }
                log_activity('update', 'alumni', $id, 'Perbarui alumni: ' . $name);
            } else {
                $ok = q_exec(
                    'INSERT INTO alumni (name, slug, photo, type, institution, major, start_date, end_date, year, division, description, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
                    [$name, $slug, $newPhoto, $type, $institution ?: null, $major ?: null, $start, $end, $year !== '' ? (int) $year : null, $division ?: null, $description ?: null, $status]
                );
                log_activity('create', 'alumni', $ok ? (int) db()->lastInsertId() : 0, 'Tambah alumni: ' . $name);
            }
            flash_redirect($ok ? 'success' : 'danger', $ok ? 'Data alumni disimpan.' : 'Gagal menyimpan data alumni.', 'admin/alumni.php');
        }
    }
}

admin_layout_start($item ? 'Edit Alumni' : 'Tambah Alumni', 'alumni');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit Alumni' : 'Tambah Alumni' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/alumni.php')) ?>">&larr; Kembali</a>
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
                <label class="form-label-admin">Nama Lengkap <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="name" maxlength="150" value="<?= e($name) ?>" required>
            </div>
            <div>
                <label class="form-label-admin">Slug (kosongkan untuk otomatis)</label>
                <input class="form-control" type="text" name="slug" maxlength="170" value="<?= e($slug) ?>">
            </div>
            <div>
                <label class="form-label-admin">Jenis</label>
                <select class="form-select" name="type">
                    <option value="pkl" <?= $type === 'pkl' ? 'selected' : '' ?>>PKL (Praktik Kerja Lapangan)</option>
                    <option value="magang" <?= $type === 'magang' ? 'selected' : '' ?>>Magang</option>
                </select>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Asal Sekolah/Kampus</label>
                <input class="form-control" type="text" name="institution" maxlength="200" value="<?= e($institution) ?>" placeholder="mis. Universitas Sultan Ageng Tirtayasa">
            </div>
            <div>
                <label class="form-label-admin">Jurusan / Program Studi</label>
                <input class="form-control" type="text" name="major" maxlength="150" value="<?= e($major) ?>" placeholder="mis. Sistem Informasi">
            </div>
            <div>
                <label class="form-label-admin">Periode Mulai</label>
                <input class="form-control" type="date" name="start_date" value="<?= e($startDate) ?>">
            </div>
            <div>
                <label class="form-label-admin">Periode Selesai</label>
                <input class="form-control" type="date" name="end_date" value="<?= e($endDate) ?>">
            </div>
            <div>
                <label class="form-label-admin">Tahun</label>
                <input class="form-control" type="number" name="year" min="1900" max="2100" placeholder="2026" value="<?= e($year) ?>">
            </div>
            <div>
                <label class="form-label-admin">Bidang/Divisi Penempatan</label>
                <input class="form-control" type="text" name="division" maxlength="150" value="<?= e($division) ?>" placeholder="mis. Bidang Persandian">
            </div>
            <div>
                <label class="form-label-admin">Status</label>
                <select class="form-select" name="status">
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draf</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Terbit</option>
                </select>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Deskripsi / Pengalaman (opsional)</label>
                <textarea class="form-control" name="description" rows="5" maxlength="2000" placeholder="Pengalaman singkat selama PKL/Magang..."><?= e($description) ?></textarea>
                <div class="admin-help">Teks biasa, tidak mendukung HTML. Hanya tampilkan informasi yang boleh diketahui publik.</div>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Foto Alumni (jpg/jpeg/png/webp, maks <?= e(format_size(UPLOAD_MAX_SIZE)) ?>)</label>
                <div class="d-flex align-items-center gap-3">
                    <img id="photoPreview" class="<?= $photo ? '' : 'd-none ' ?>thumb-img"
                         style="width:100px;height:100px;object-fit:cover;border-radius:50%" src="<?= $photo ? e(upload_url('alumni/' . $photo)) : '#' ?>" alt="Pratinjau foto alumni">
                    <input class="form-control" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-cover-preview="#photoPreview">
                </div>
                <div class="admin-help mt-1">Kosongkan jika tidak mengganti foto. Foto tampil di halaman daftar dan detail alumni.</div>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Alumni</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/alumni.php')) ?>">Batal</a>
    </div>
</form>
<?php
admin_layout_end();
