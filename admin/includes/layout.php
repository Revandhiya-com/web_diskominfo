<?php
/**
 * Layout admin — header, sidebar, flash message, dan penutup halaman.
 * Pola: admin_layout_start($title, $activeMenu); ... konten ...; admin_layout_end();
 */

function admin_layout_start(string $title, string $active): void
{
    $user = current_user();
    $menu = [
        'dashboard'     => ['label' => 'Dashboard',         'icon' => 'bi-speedometer2'],
        'albums'        => ['label' => 'Dokumentasi',       'icon' => 'bi-images'],
        'videos'        => ['label' => 'Video',             'icon' => 'bi-camera-video'],
        'karya'         => ['label' => 'Karya',             'icon' => 'bi-palette2'],
        'news'          => ['label' => 'Berita',            'icon' => 'bi-newspaper'],
        'alumni'        => ['label' => 'Alumni',            'icon' => 'bi-mortarboard'],
        'agendas'       => ['label' => 'Agenda',            'icon' => 'bi-calendar2-week'],
        'announcements' => ['label' => 'Pengumuman',        'icon' => 'bi-megaphone'],
        'documents'     => ['label' => 'Dokumen',           'icon' => 'bi-file-earmark-text'],
        'faqs'          => ['label' => 'FAQ',               'icon' => 'bi-question-circle'],
        'pages'         => ['label' => 'Halaman Statis',    'icon' => 'bi-layout-text-window'],
        'messages'      => ['label' => 'Pesan Masuk',       'icon' => 'bi-envelope'],
        'users'         => ['label' => 'Pengguna',          'icon' => 'bi-people'],
        'settings'      => ['label' => 'Pengaturan',        'icon' => 'bi-gear'],
    ];
    if (($user['role'] ?? '') !== 'admin') {
        unset($menu['users'], $menu['settings']);
    }
    $flashes = flash_get();
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= e($title) ?> — Admin <?= e(APP_NAME_SHORT) ?></title>
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap/bootstrap.min.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>?v=<?= APP_ASSET_VERSION ?>">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <span class="admin-brand-icon"><i class="bi bi-buildings"></i></span>
            <div>
                <strong>Portal PKL</strong>
                <small>Panel Admin</small>
            </div>
        </div>
        <nav class="admin-nav">
            <?php foreach ($menu as $key => $item): ?>
                <a class="admin-nav-item <?= $active === $key ? 'active' : '' ?>" href="<?= e(base_url('admin/' . $key . '.php')) ?>">
                    <i class="bi <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-2">
                <span class="d-lg-none" id="adminMenuToggle"><i class="bi bi-list fs-4"></i></span>
                <span class="admin-topbar-title"><?= e($title) ?></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url()) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i>Lihat Situs</a>
                <span class="admin-user"><i class="bi bi-person-circle me-1"></i><?= e($user['name'] ?? 'Admin') ?></span>
                <form method="post" action="<?= e(base_url('admin/logout.php')) ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-box-arrow-right me-1"></i>Keluar</button>
                </form>
            </div>
        </header>
        <main class="admin-content">
            <?php foreach ($flashes as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) === 'danger' ? 'danger' : (e($flash['type']) === 'success' ? 'success' : (e($flash['type']) === 'warning' ? 'warning' : 'info')) ?> alert-dismissible fade show admin-flash" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endforeach; ?>
    <?php
}

function admin_layout_end(): void
{
    ?>
        </main>
    </div>
</div>
<script src="<?= asset_url('vendor/bootstrap/bootstrap.bundle.min.js') ?>?v=<?= APP_ASSET_VERSION ?>"></script>
<script src="<?= asset_url('js/admin.js') ?>?v=<?= APP_ASSET_VERSION ?>"></script>
</body>
</html>
    <?php
}
