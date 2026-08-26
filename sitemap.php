<?php
/**
 * Sitemap XML — daftar URL publik yang layak diindeks.
 * Berisi halaman statis, semua berita published, dan semua album published.
 * URL sitemap: sitemap.php (dirujuk dari robots.txt).
 */
define('BASE_PATH', __DIR__);

require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/helpers/functions.php';

date_default_timezone_set(APP_TIMEZONE);

header('Content-Type: application/xml; charset=utf-8');

$urls = [];

/* Halaman statis */
$staticPages = [
    ['', 'daily', '0.9'],
    ['?page=profil', 'monthly', '0.7'],
    ['?page=layanan', 'monthly', '0.8'],
    ['?page=berita', 'daily', '0.8'],
    ['?page=pengumuman', 'daily', '0.7'],
    ['?page=agenda', 'weekly', '0.7'],
    ['?page=dokumentasi', 'weekly', '0.7'],
    ['?page=alumni', 'weekly', '0.6'],
    ['?page=dokumen', 'monthly', '0.6'],
    ['?page=faq', 'monthly', '0.5'],
    ['?page=kontak', 'monthly', '0.6'],
];
foreach ($staticPages as $sp) {
    $urls[] = [
        'loc'     => base_url($sp[0]),
        'lastmod' => date('Y-m-d'),
        'changefreq' => $sp[1],
        'priority'   => $sp[2],
    ];
}

/* Berita published */
$news = q_all(
    'SELECT slug, published_at FROM news
     WHERE status = $$published$$ AND slug <> $$$$
     ORDER BY published_at DESC'
);
foreach ($news as $n) {
    $urls[] = [
        'loc'     => base_url('?page=detail-berita&slug=' . urlencode($n['slug'])),
        'lastmod' => date('Y-m-d', strtotime($n['published_at'])),
        'changefreq' => 'weekly',
        'priority'   => '0.6',
    ];
}

/* Album published */
$albums = q_all(
    'SELECT slug, created_at FROM albums
     WHERE status = $$published$$ AND slug <> $$$$
     ORDER BY created_at DESC'
);
foreach ($albums as $a) {
    $urls[] = [
        'loc'     => base_url('?page=album&slug=' . urlencode($a['slug'])),
        'lastmod' => date('Y-m-d', strtotime($a['created_at'])),
        'changefreq' => 'monthly',
        'priority'   => '0.5',
    ];
}

/* Alumni published */
$alumni = q_all(
    'SELECT slug, updated_at FROM alumni
     WHERE status = $$published$$ AND slug <> $$$$
     ORDER BY updated_at DESC'
);
foreach ($alumni as $al) {
    $urls[] = [
        'loc'     => base_url('?page=detail-alumni&slug=' . urlencode($al['slug'])),
        'lastmod' => date('Y-m-d', strtotime($al['updated_at'])),
        'changefreq' => 'monthly',
        'priority'   => '0.5',
    ];
}

/* Halaman CMS mandiri (pages published; seksi profil/layanan tidak diulang) */
$sectionSlugs = ['profil-program', 'tujuan', 'visi-misi', 'informasi-pkl', 'informasi-magang', 'persyaratan-pkl', 'persyaratan-magang', 'alur-pendaftaran'];
$cmsPages = q_all(
    'SELECT slug, updated_at FROM pages
     WHERE status = $$published$$ AND slug <> $$$$
       AND slug NOT IN (' . implode(',', array_fill(0, count($sectionSlugs), '?')) . ')
     ORDER BY title ASC',
    $sectionSlugs
);
foreach ($cmsPages as $cp) {
    $urls[] = [
        'loc'     => base_url('?page=' . urlencode($cp['slug'])),
        'lastmod' => date('Y-m-d', strtotime($cp['updated_at'])),
        'changefreq' => 'monthly',
        'priority'   => '0.6',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
    <url>
        <loc><?= e($u['loc']) ?></loc>
        <lastmod><?= e($u['lastmod']) ?></lastmod>
        <changefreq><?= e($u['changefreq']) ?></changefreq>
        <priority><?= e($u['priority']) ?></priority>
    </url>
<?php endforeach; ?>
</urlset>