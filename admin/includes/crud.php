<?php
/**
 * Helper umum area admin: flash message, aktivitas, dan utilitas kecil.
 */

/** Set flash message yang tampil sekali di halaman berikutnya. */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Ambil & kosongkan semua flash message. */
function flash_get(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/** Redirect internal dengan flash message (redirect-once pattern). */
function flash_redirect(string $type, string $message, string $url): void
{
    flash_set($type, $message);
    header('Location: ' . base_url($url));
    exit;
}

/** Catat aktivitas admin (dipakai dashboard "Aktivitas Terbaru"). */
function log_activity(string $action, string $entity, ?int $entityId = null, ?string $detail = null): void
{
    $user = current_user();
    q_exec(
        'INSERT INTO activity_logs (user_id, action, entity, entity_id, detail) VALUES (?, ?, ?, ?, ?)',
        [$user['id'] ?? null, $action, $entity, $entityId, $detail]
    );
}

/** Nilai teks dari POST dengan panjang maksimum. */
function post_max(string $key, int $maxLen, string $default = ''): string
{
    $v = post_val($key, $default);
    return mb_substr($v, 0, $maxLen);
}

/** Slug unik: tambahkan angka bila sudah dipakai. */
function unique_slug(string $slug, string $table, int $ignoreId = 0): string
{
    $base = $slug;
    $i = 1;
    while (q_one("SELECT id FROM $table WHERE slug = ? AND id <> ?", [$slug, $ignoreId])) {
        $slug = $base . '-' . (++$i);
    }
    return $slug;
}

/** Status badge untuk tabel admin. */
function status_badge(string $status): string
{
    $label = $status === 'published' ? 'Terbit' : 'Draf';
    $class = $status === 'published' ? 'dp-badge-gold' : 'dp-badge-gray';
    return '<span class="dp-badge ' . $class . '">' . $label . '</span>';
}

/** Halaman khusus admin; pengguna editor dialihkan ke dashboard. */
function require_admin(): void
{
    if ((current_user()['role'] ?? '') !== 'admin') {
        http_response_code(403);
        flash_redirect('danger', 'Halaman ini khusus admin.', 'admin/index.php');
    }
}

/** URL halaman admin saat ini dengan query GET yang dipertahankan (tanpa p). */
function admin_self_url(): string
{
    $qs = $_GET;
    unset($qs['p']);
    $file = basename($_SERVER['SCRIPT_NAME'] ?? '');
    return 'admin/' . $file . ($qs ? '?' . http_build_query($qs) : '');
}

/** Quick toggle publish/draft (status ENUM) lewat POST, lalu redirect balik. */
function toggle_status(string $table, string $titleCol, string $label): void
{
    $id = (int) post_val('id');
    $row = $id > 0 ? q_one("SELECT id, status, $titleCol AS t FROM $table WHERE id = ?", [$id]) : null;
    if (!$row) {
        flash_redirect('danger', 'Data tidak ditemukan.', admin_self_url());
    }
    $new = $row['status'] === 'published' ? 'draft' : 'published';
    static $hasPub = [];
    if (!array_key_exists($table, $hasPub)) {
        $hasPub[$table] = (bool) q_one(
            "SELECT COUNT(*) n FROM information_schema.columns
             WHERE table_schema = current_database() AND table_name = ? AND column_name = 'published_at'",
            [$table]
        )['n'];
    }
    if ($hasPub[$table]) {
        $ok = q_exec(
            "UPDATE $table SET status=?, published_at = CASE WHEN ? = 'published' THEN COALESCE(published_at, NOW()) ELSE published_at END WHERE id=?",
            [$new, $new, $id]
        );
    } else {
        $ok = q_exec("UPDATE $table SET status=? WHERE id=?", [$new, $id]);
    }
    log_activity('update', $table, $id, ($new === 'published' ? 'Terbitkan ' : 'Tarik ke draf ') . $label . ': ' . $row['t']);
    flash_redirect(
        $ok ? 'success' : 'danger',
        $ok ? ($new === 'published' ? 'Diterbitkan.' : 'Dialihkan ke draf.') : 'Gagal mengubah status.',
        admin_self_url()
    );
}

/** Quick toggle is_active (TINYINT) lewat POST, lalu redirect balik. */
function toggle_active(string $table, string $titleCol, string $label): void
{
    $id = (int) post_val('id');
    $row = $id > 0 ? q_one("SELECT id, is_active, $titleCol AS t FROM $table WHERE id = ?", [$id]) : null;
    if (!$row) {
        flash_redirect('danger', 'Data tidak ditemukan.', admin_self_url());
    }
    $new = (int) $row['is_active'] ? 0 : 1;
    q_exec("UPDATE $table SET is_active = ? WHERE id = ?", [$new, $id]);
    log_activity('update', $table, $id, ($new ? 'Aktifkan ' : 'Nonaktifkan ') . $label . ': ' . $row['t']);
    flash_redirect('success', $new ? 'Diaktifkan.' : 'Dinonaktifkan.', admin_self_url());
}
