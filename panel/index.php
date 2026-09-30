<?php
/**
 * Dashboard admin — ringkasan statistik + data terbaru + aktivitas.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$counts = [
    'news'          => (int) (q_one('SELECT COUNT(*) n FROM news')['n'] ?? 0),
    'albums'        => (int) (q_one('SELECT COUNT(*) n FROM albums')['n'] ?? 0),
    'photos'        => (int) (q_one('SELECT COUNT(*) n FROM photos')['n'] ?? 0),
    'agendas'       => (int) (q_one('SELECT COUNT(*) n FROM agendas')['n'] ?? 0),
    'announcements' => (int) (q_one('SELECT COUNT(*) n FROM announcements')['n'] ?? 0),
    'documents'     => (int) (q_one('SELECT COUNT(*) n FROM documents')['n'] ?? 0),
    'faqs'          => (int) (q_one('SELECT COUNT(*) n FROM faqs')['n'] ?? 0),
    'messages'      => (int) (q_one('SELECT COUNT(*) n FROM contact_messages')['n'] ?? 0),
    'messages_unread' => (int) (q_one('SELECT COUNT(*) n FROM contact_messages WHERE is_read = 0')['n'] ?? 0),
    'users'         => (int) (q_one('SELECT COUNT(*) n FROM users WHERE is_active = 1')['n'] ?? 0),
];

$latestNews = q_all('SELECT id, title, status, published_at FROM news ORDER BY COALESCE(published_at, created_at) DESC LIMIT 5');
$latestAlbums = q_all('SELECT id, title, slug, status, cover_image, created_at FROM albums ORDER BY created_at DESC LIMIT 5');
$latestMessages = q_all('SELECT id, name, email, subject, is_read, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5');
$activities = q_all(
    'SELECT a.action, a.entity, a.detail, a.created_at, u.name AS user_name
     FROM activity_logs a
     LEFT JOIN users u ON u.id = a.user_id
     ORDER BY a.created_at DESC LIMIT 10'
);

$stats = [
    ['Berita', $counts['news'], 'bi-newspaper', 'news.php'],
    ['Album', $counts['albums'], 'bi-images', 'albums.php'],
    ['Foto', $counts['photos'], 'bi-camera', 'albums.php'],
    ['Agenda', $counts['agendas'], 'bi-calendar2-week', 'agendas.php'],
    ['Pengumuman', $counts['announcements'], 'bi-megaphone', 'announcements.php'],
    ['Dokumen', $counts['documents'], 'bi-file-earmark-text', 'documents.php'],
    ['FAQ', $counts['faqs'], 'bi-question-circle', 'faqs.php'],
];

admin_layout_start('Dashboard', 'dashboard');
?>
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="admin-stat-grid">
            <?php foreach ($stats as $i => $s): ?>
                <a class="admin-stat" href="<?= e(base_url('admin/' . $s[3])) ?>">
                    <span class="admin-stat-icon"><i class="bi <?= e($s[2]) ?>"></i></span>
                    <span>
                        <span class="admin-stat-value"><?= e(number_format($s[1])) ?></span>
                        <span class="admin-stat-label d-block"><?= e($s[0]) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="admin-stat d-flex" style="text-decoration:none;">
            <span class="admin-stat-icon"><i class="bi bi-people"></i></span>
            <span>
                <span class="admin-stat-value"><?= e(number_format($counts['users'])) ?></span>
                <span class="admin-stat-label d-block">Pengguna aktif — <a href="<?= e(base_url('admin/users.php')) ?>">kelola</a> (khusus admin)</span>
            </span>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="admin-stat d-flex" style="text-decoration:none;">
            <span class="admin-stat-icon"><i class="bi bi-envelope"></i></span>
            <span>
                <span class="admin-stat-value"><?= e(number_format($counts['messages_unread'])) ?> / <?= e(number_format($counts['messages'])) ?></span>
                <span class="admin-stat-label d-block">Pesan masuk belum dibaca / total — <a href="<?= e(base_url('admin/messages.php')) ?>">buka</a></span>
            </span>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="admin-card mb-3">
            <div class="admin-card-head">
                <h2>Berita Terbaru</h2>
                <a class="btn-admin-sm" href="<?= e(base_url('admin/news.php')) ?>">Kelola</a>
            </div>
            <div class="admin-card-body p-0">
                <table class="table table-admin mb-0">
                    <tbody>
                        <?php if (!$latestNews): ?>
                            <tr><td class="empty-muted">Belum ada berita.</td></tr>
                        <?php else: foreach ($latestNews as $n): ?>
                            <tr>
                                <td><?= e($n['title']) ?></td>
                                <td class="text-end" style="width:110px"><?= status_badge($n['status']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-head">
                <h2>Pesan Masuk Terbaru</h2>
                <a class="btn-admin-sm" href="<?= e(base_url('admin/messages.php')) ?>">Kelola</a>
            </div>
            <div class="admin-card-body p-0">
                <table class="table table-admin mb-0">
                    <tbody>
                        <?php if (!$latestMessages): ?>
                            <tr><td class="empty-muted">Belum ada pesan.</td></tr>
                        <?php else: foreach ($latestMessages as $m): ?>
                            <tr>
                                <td>
                                    <strong><?= e($m['name']) ?></strong>
                                    <div class="small text-muted"><?= e($m['email']) ?> — <?= e($m['subject'] ?: '-') ?></div>
                                </td>
                                <td class="text-end" style="width:90px">
                                    <?php if (!(int) $m['is_read']): ?><span class="dp-badge dp-badge-blue">Baru</span><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="admin-card mb-3">
            <div class="admin-card-head">
                <h2>Dokumentasi Terbaru</h2>
                <a class="btn-admin-sm" href="<?= e(base_url('admin/albums.php')) ?>">Kelola</a>
            </div>
            <div class="admin-card-body p-0">
                <table class="table table-admin mb-0">
                    <tbody>
                        <?php if (!$latestAlbums): ?>
                            <tr><td class="empty-muted">Belum ada album.</td></tr>
                        <?php else: foreach ($latestAlbums as $a): ?>
                            <tr>
                                <td style="width:70px">
                                    <?php if (upload_file_exists('albums', $a['cover_image'])): ?>
                                        <img class="thumb-img" src="<?= e(upload_url('albums/' . $a['cover_image'])) ?>" alt="">
                                    <?php else: ?>
                                        <span class="thumb-img d-inline-grid place-items-center bg-secondary-subtle rounded"><i class="bi bi-image text-muted"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($a['title']) ?></td>
                                <td class="text-end" style="width:110px"><?= status_badge($a['status']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-head">
                <h2>Aktivitas Terbaru</h2>
            </div>
            <div class="admin-card-body p-0">
                <table class="table table-admin mb-0">
                    <tbody>
                        <?php if (!$activities): ?>
                            <tr><td class="empty-muted">Belum ada aktivitas tercatat.</td></tr>
                        <?php else: foreach ($activities as $act): ?>
                            <tr>
                                <td>
                                    <span class="dp-badge dp-badge-blue"><?= e(ucfirst($act['action'])) ?></span>
                                    <?= e($act['entity']) ?>
                                    <div class="small text-muted"><?= e($act['detail'] ?: '') ?> — <?= e($act['user_name'] ?? 'Sistem') ?> · <?= e(format_date_id($act['created_at'], true)) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
admin_layout_end();
