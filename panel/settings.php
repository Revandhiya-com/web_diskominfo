<?php
/**
 * Pengaturan Situs — satu form berisi seluruh key settings.
 * Khusus admin (role editor dialihkan).
 */
define('ADMIN_ONLY', true);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$errors = [];

$groups = [
    'Umum' => [
        'site_tagline'   => ['label' => 'Tagline Situs', 'type' => 'text'],
        'footer_about'   => ['label' => 'Teks Footer (tentang)', 'type' => 'textarea'],
    ],
    'Hero Beranda' => [
        'hero_title'       => ['label' => 'Judul Hero', 'type' => 'text'],
        'hero_subtitle'    => ['label' => 'Subjudul Hero', 'type' => 'text'],
        'hero_description' => ['label' => 'Deskripsi Hero', 'type' => 'textarea'],
        'hero_btn_primary' => ['label' => 'Tombol Utama', 'type' => 'text'],
        'hero_btn_primary_link' => ['label' => 'Link Tombol Utama', 'type' => 'text'],
        'hero_btn_secondary' => ['label' => 'Tombol Kedua', 'type' => 'text'],
        'hero_btn_secondary_link' => ['label' => 'Link Tombol Kedua', 'type' => 'text'],
    ],
    'Tentang & CTA' => [
        'about_title' => ['label' => 'Judul Bagian Tentang', 'type' => 'text'],
        'about_content' => ['label' => 'Isi Tentang', 'type' => 'textarea'],
        'about_btn' => ['label' => 'Tombol Tentang', 'type' => 'text'],
        'about_btn_link' => ['label' => 'Link Tombol Tentang', 'type' => 'text'],
        'cta_title' => ['label' => 'Judul CTA', 'type' => 'text'],
        'cta_text' => ['label' => 'Teks CTA', 'type' => 'textarea'],
        'cta_btn' => ['label' => 'Tombol CTA', 'type' => 'text'],
        'cta_btn_link' => ['label' => 'Link Tombol CTA', 'type' => 'text'],
    ],
    'Kontak' => [
        'contact_address' => ['label' => 'Alamat', 'type' => 'textarea'],
        'contact_email'   => ['label' => 'Email', 'type' => 'text'],
        'contact_phone'   => ['label' => 'Telepon', 'type' => 'text'],
        'contact_map_link' => ['label' => 'Link Peta (Google Maps)', 'type' => 'text'],
    ],
    'Sosial Media' => [
        'social_facebook'  => ['label' => 'Facebook', 'type' => 'text'],
        'social_instagram' => ['label' => 'Instagram', 'type' => 'text'],
        'social_twitter'   => ['label' => 'Twitter / X', 'type' => 'text'],
        'social_youtube'   => ['label' => 'YouTube', 'type' => 'text'],
    ],
    'Lain-lain' => [
        'privacy_note' => ['label' => 'Catatan Privasi', 'type' => 'textarea'],
    ],
];

if (is_post()) {
    require_csrf();
    $updates = 0;
    foreach ($groups as $g => $fields) {
        foreach ($fields as $key => $cfg) {
            $value = (string) ($_POST['setting_' . $key] ?? '');
            if (q_changes('UPDATE settings SET "value" = ? WHERE "key" = ?', [$value, $key]) > 0) {
                $updates++;
            }
        }
    }
    log_activity('update', 'settings', null, 'Perbarui pengaturan situs (' . $updates . ' kolom)');
    flash_redirect('success', 'Pengaturan berhasil disimpan.', 'panel/settings.php');
}

$current = [];
foreach (q_all('SELECT "key", "value" FROM settings') as $row) {
    $current[$row['key']] = $row['value'];
}

admin_layout_start('Pengaturan Situs', 'settings');
?>
<div class="admin-card mb-3">
    <div class="admin-card-head"><h2>Pengaturan Situs</h2></div>
    <div class="admin-card-body">
        <form method="post">
            <?= csrf_field() ?>
            <?php foreach ($groups as $group => $fields): ?>
                <h3 class="h6 text-uppercase text-secondary mt-4 mb-2 border-bottom pb-1"><?= e($group) ?></h3>
                <div class="admin-form-grid">
                    <?php foreach ($fields as $key => $cfg): ?>
                        <div>
                            <label class="form-label-admin" for="s_<?= e($key) ?>"><?= e($cfg['label']) ?></label>
                            <?php if (($cfg['type'] ?? 'text') === 'textarea'): ?>
                                <textarea class="form-control" id="s_<?= e($key) ?>" name="setting_<?= e($key) ?>" rows="3"><?= e($current[$key] ?? '') ?></textarea>
                            <?php else: ?>
                                <input class="form-control" type="text" id="s_<?= e($key) ?>" name="setting_<?= e($key) ?>" value="<?= e($current[$key] ?? '') ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
<?php
admin_layout_end();
