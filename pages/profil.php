<?php
/**
 * Halaman Profil â€” Profil Program, Tujuan, Visi & Misi.
 * Konten setiap seksi diambil dari tabel `pages` (dikelola admin/CMS).
 * Jika belum diisi, ditampilkan placeholder â€” bukan data resmi karangan.
 */

$pageTitle = 'Profil';
$pageDescription = 'Profil program Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Profil'],
];

/* Ambil konten seksi dari tabel pages (slug tetap). */
$pageSlugs = ['profil-program', 'tujuan', 'visi-misi'];
$pageRows = [];
foreach (q_all(
    'SELECT slug, title, content, source_url FROM pages WHERE slug IN (' . implode(',', array_fill(0, count($pageSlugs), '?')) . ')',
    $pageSlugs
) as $row) {
    $pageRows[$row['slug']] = $row;
}

$section = function (string $slug, string $fallbackTitle, string $fallbackContent): array {
    global $pageRows;
    $row = $pageRows[$slug] ?? null;
    return [
        'title'      => $row['title'] ?? $fallbackTitle,
        'content'    => $row['content'] ?? $fallbackContent,
        'source_url' => $row['source_url'] ?? null,
    ];
};

$profil  = $section('profil-program', 'Profil Program',
    '<p>Portal ini merupakan pusat informasi resmi program Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten.</p>');
$tujuan  = $section('tujuan', 'Tujuan Program',
    '<p>Tujuan program PKL dan Magang di lingkungan Diskominfo Provinsi Banten.</p>');
$visiMisi = $section('visi-misi', 'Visi & Misi',
    '<p>Visi dan misi Pemerintah Provinsi Banten sesuai situs resmi Diskominfo Provinsi Banten.</p>');

$sections = [
    ['id' => 'program',    'icon' => 'bi-briefcase',      'title' => $profil['title'],    'content' => $profil['content'],    'source_url' => $profil['source_url']],
    ['id' => 'tujuan',     'icon' => 'bi-bullseye',       'title' => $tujuan['title'],    'content' => $tujuan['content'],    'source_url' => $tujuan['source_url']],
    ['id' => 'visi-misi',  'icon' => 'bi-eye',            'title' => $visiMisi['title'],  'content' => $visiMisi['content'],  'source_url' => $visiMisi['source_url']],
];
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Informasi program Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <div class="row g-4">
            <!-- Navigasi seksi -->
            <div class="col-lg-3">
                <nav class="dp-side-nav" aria-label="Navigasi seksi profil">
                    <div class="dp-side-nav-title">Isi Halaman</div>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($sections as $i => $sec): ?>
                            <li>
                                <a href="#<?= e($sec['id']) ?>" class="<?= $i === 0 ? 'active' : '' ?>">
                                    <i class="bi <?= e($sec['icon']) ?>" aria-hidden="true"></i> <?= e($sec['title']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li>
                            <a href="<?= e(base_url('?page=dokumen')) ?>">
                                <i class="bi bi-download" aria-hidden="true"></i> Dokumen Terkait
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Konten seksi -->
            <div class="col-lg-9">
                <?php foreach ($sections as $i => $sec): ?>
                    <article id="<?= e($sec['id']) ?>" class="dp-article-card <?= $i > 0 ? 'mt-4' : '' ?>">
                        <div class="dp-article-card-head">
                            <span class="dp-article-card-icon" aria-hidden="true"><i class="bi <?= e($sec['icon']) ?>"></i></span>
                            <h2 class="dp-article-card-title"><?= e($sec['title']) ?></h2>
                            <span class="dp-article-card-num" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="dp-article-card-body">
                            <div class="dp-prose">
                                <?= sanitize_admin_html($sec['content']) ?>
                            </div>
                            <?php if (!empty($sec['source_url'])): ?>
                                <p class="dp-source-note small mt-3 mb-0">
                                    <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Sumber:
                                    <a href="<?= e($sec['source_url']) ?>" target="_blank" rel="noopener nofollow"><?= e($sec['source_url']) ?></a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>