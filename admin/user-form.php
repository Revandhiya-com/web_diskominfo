<?php
/**
 * CMS Pengguna — tambah / edit (termasuk reset password, peran, aktif/nonaktif).
 * Khusus admin (role editor dialihkan).
 */
define('ADMIN_ONLY', true);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$id = (int) get_val('id');
$item = $id > 0 ? q_one('SELECT * FROM users WHERE id = ?', [$id]) : null;
if ($id > 0 && !$item) {
    flash_redirect('danger', 'Pengguna tidak ditemukan.', 'admin/users.php');
}

$errors = [];
$name = $item['name'] ?? '';
$username = $item['username'] ?? '';
$email = $item['email'] ?? '';
$role = $item['role'] ?? 'editor';
$isActive = (int) ($item['is_active'] ?? 1);
$isSelf = $id > 0 && $id === (int) current_user()['id'];

if (is_post()) {
    require_csrf();
    $name = post_max('name', 100);
    $username = post_max('username', 50);
    $email = post_max('email', 100);
    $role = post_val('role') === 'admin' ? 'admin' : 'editor';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $password = (string) ($_POST['password'] ?? '');

    if (trim($name) === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if (!preg_match('/^[a-zA-Z0-9_\.-]{3,50}$/', $username)) {
        $errors[] = 'Username wajib diisi, 3-50 karakter (huruf, angka, titik, garis bawah, tanda hubung).';
    }
    if (q_one('SELECT id FROM users WHERE username = ? AND id <> ?', [$username, $id]) && !$errors) {
        $errors[] = 'Username sudah dipakai.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if ($email !== '' && q_one('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $id]) && !$errors) {
        $errors[] = 'Email sudah dipakai.';
    }
    if (!$item && strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }
    if ($item && $password !== '' && strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }
    if ($isSelf && !$isActive) {
        $errors[] = 'Tidak bisa menonaktifkan akun sendiri.';
    }
    if ($isSelf && $role !== $item['role']) {
        $errors[] = 'Tidak bisa mengubah peran akun sendiri.';
    }

    if (!$errors) {
        if ($id > 0) {
            $params = [$name, $username, $email ?: null, $role, $isActive, $id];
            if ($password !== '') {
                q_exec('UPDATE users SET name=?, username=?, email=?, role=?, is_active=?, password=? WHERE id=?', [$name, $username, $email ?: null, $role, $isActive, password_hash($password, PASSWORD_BCRYPT), $id]);
            } else {
                q_exec('UPDATE users SET name=?, username=?, email=?, role=?, is_active=? WHERE id=?', $params);
            }
            log_activity('update', 'user', $id, 'Perbarui pengguna: ' . $name);
            flash_redirect('success', 'Pengguna disimpan.', 'admin/users.php');
        }
        $ok = q_exec(
            'INSERT INTO users (name, username, email, password, role, is_active) VALUES (?,?,?,?,?,?)',
            [$name, $username, $email ?: null, password_hash($password, PASSWORD_BCRYPT), $role, $isActive]
        );
        $newId = $ok ? (int) db()->lastInsertId() : 0;
        log_activity('create', 'user', $newId, 'Buat pengguna: ' . $name);
        flash_redirect($ok ? 'success' : 'danger', $ok ? 'Pengguna dibuat.' : 'Gagal menyimpan pengguna.', 'admin/users.php');
    }
}

admin_layout_start($item ? 'Edit Pengguna' : 'Tambah Pengguna', 'users');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $item ? 'Edit Pengguna' : 'Tambah Pengguna' ?></h1>
    <a class="btn-admin-sm" href="<?= e(base_url('admin/users.php')) ?>">&larr; Kembali</a>
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
                <label class="form-label-admin">Nama Lengkap <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="name" maxlength="100" value="<?= e($name) ?>" required>
            </div>
            <div>
                <label class="form-label-admin">Username <span class="required-star">*</span></label>
                <input class="form-control" type="text" name="username" maxlength="50" value="<?= e($username) ?>" required
                       pattern="[a-zA-Z0-9_\.\-]{3,50}" title="3-50 karakter: huruf, angka, titik, garis bawah, tanda hubung">
            </div>
            <div>
                <label class="form-label-admin">Email (opsional)</label>
                <input class="form-control" type="email" name="email" maxlength="100" value="<?= e($email) ?>">
            </div>
            <div>
                <label class="form-label-admin">Peran</label>
                <select class="form-select" name="role" <?= $isSelf ? 'disabled' : '' ?>>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin (akses penuh)</option>
                    <option value="editor" <?= $role === 'editor' ? 'selected' : '' ?>>Editor (konten saja)</option>
                </select>
                <?php if ($isSelf): ?><div class="admin-help">Peran akun sendiri tidak dapat diubah.</div><?php endif; ?>
            </div>
            <div class="d-flex align-items-end pb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" <?= $isActive ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?>>
                    <label class="form-check-label" for="isActive">Akun aktif (bisa login)</label>
                </div>
                <?php if ($isSelf): ?><div class="admin-help ms-2">Akun sendiri tidak dapat dinonaktifkan.</div><?php endif; ?>
            </div>
            <div class="admin-form-full">
                <label class="form-label-admin">Password <?= $item ? '' : '<span class="required-star">*</span>' ?></label>
                <input class="form-control" type="password" name="password" minlength="8" autocomplete="new-password"
                       placeholder="<?= $item ? 'Kosongkan jika tidak diganti' : 'Minimal 8 karakter' ?>" <?= $item ? '' : 'required' ?>>
            </div>
        </div>
    </div>
    <div class="admin-card-body border-top d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Pengguna</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/users.php')) ?>">Batal</a>
    </div>
</form>
<?php
admin_layout_end();