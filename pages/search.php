<?php
/**
 * Halaman Pencarian Global (FASE 9).
 * Mencari: berita, dokumentasi, agenda, pengumuman, dokumen, dan FAQ.
 *
 * Parameter (semua via GET, semua query memakai prepared statement):
 *   q         — kata kunci (judul/isi/deskripsi)
 *   type      — jenis konten: '' (semua) | news | albums | agendas | announcements | documents | faqs
 *   category  — kategori ber-prefix: news:ID, albums:ID, documents:ID, faqs:NAMA
 *   year      — tahun (berita & dokumentasi)
 *   month     — bulan 1-12 (dokumentasi)
 *   ftype     — tipe file/ekstensi (dokumen)
 */

$pageTitle = 'Pencarian';
$pageDescription = 'Cari berita, dokumentasi, agenda, pengumuman, dokumen, dan FAQ di portal PKL dan Magang.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Pencarian'],
];

/* ------------------------------------------------------------
 * 1. BACA & SANITASI INPUT (TIDAK PERNAH DITEMPUH LANGSUNG KE SQL)
 * ------------------------------------------------------------ */
$q        = mb_substr(get_val('q'), 0, 100);
$type     = get_val('type');
$catRaw   = get_val('category');
$year     = (int) get_val('year');
$month    = (int) get_val('month');
$ftype    = strtolower(preg_replace('/[^a-z0-9]/', '', get_val('ftype')) ?? '');

$allowedTypes = ['news', 'albums', 'agendas', 'announcements', 'documents', 'faqs'];
if (!in_array($type, $allowedTypes, true)) {
    $type = '';
}

/* Parsing kategori ber-prefix: "news:3", "albums:2", "documents:1", "faqs:Umum" */
$catSource = '';
$catValue  = '';
if ($catRaw !== '') {
    $pos = strpos($catRaw, ':');
    if ($pos !== false) {
        $catSource = substr($catRaw, 0, $pos);
        $catValue  = substr($catRaw, $pos + 1);
    }
}
if (!in_array($catSource, $allowedTypes, true)) {
    $catSource = '';
    $catValue  = '';
}
$catId = $catValue !== '' && ctype_digit($catValue) ? (int) $catValue : 0;

/* Tahun & bulan dibatasi rentang masuk akal */
if ($year < 2000 || $year > (int) date('Y') + 1) {
    $year = 0;
}
if ($month < 1 || $month > 12) {
    $month = 0;
}

/* Kata kunci: escape wildcard LIKE agar tidak mengubah makna pencarian */
$like = $q !== '' ? '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%' : '';

/* ------------------------------------------------------------
 * 2. PILIHAN UNTUK DROPDOWN (data asli dari DB — bukan input user)
 * ------------------------------------------------------------ */
$newsCats      = q_all('SELECT id, name FROM news_categories ORDER BY name ASC');
$albumCats     = q_all('SELECT id, name FROM album_categories ORDER BY name ASC');
$documentCats  = q_all('SELECT id, name FROM document_categories ORDER BY name ASC');
$faqCats       = q_all("SELECT DISTINCT category AS name FROM faqs WHERE is_active = 1 AND category IS NOT NULL AND category <> '' ORDER BY category ASC");
$fileTypes     = q_all("SELECT DISTINCT LOWER(SUBSTRING_INDEX(file, '.', -1)) AS t FROM documents WHERE file IS NOT NULL AND file <> '' ORDER BY t ASC");

$newsYears    = q_all("SELECT DISTINCT EXTRACT(YEAR FROM COALESCE(published_at, created_at)) AS y FROM news WHERE status = 'published' AND COALESCE(published_at, created_at) IS NOT NULL ORDER BY y DESC");
$albumYears   = q_all("SELECT DISTINCT EXTRACT(YEAR FROM COALESCE(event_date, created_at)) AS y FROM albums WHERE status = 'published' AND COALESCE(event_date, created_at) IS NOT NULL ORDER BY y DESC");

$months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
           7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

/* ------------------------------------------------------------
 * 3. QUERY PER JENIS KONTEN (semua prepared statement)
 * ------------------------------------------------------------ */
$results = [];

/* --- BERITA --- */
if ($type === '' || $type === 'news') {
    $parts  = ["n.status = 'published'"];
    $params = [];
    if ($like !== '') {
        $parts[]  = '(n.title LIKE ? OR n.excerpt LIKE ? OR n.content LIKE ?)';
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    if ($catSource === 'news' && $catId > 0) {
        $parts[]  = 'n.category_id = ?';
        $params[] = $catId;
    }
    if ($year > 0) {
        $parts[]  = 'EXTRACT(YEAR FROM COALESCE(n.published_at, n.created_at)) = ?';
        $params[] = $year;
    }
    foreach (q_all(
        "SELECT n.id, n.title, n.slug, n.excerpt, COALESCE(n.published_at, n.created_at) AS d,
                c.name AS category_name
         FROM news n
         LEFT JOIN news_categories c ON c.id = n.category_id
         WHERE " . implode(' AND ', $parts) . "
         ORDER BY COALESCE(n.published_at, n.created_at) DESC",
        $params
    ) as $row) {
        $results[] = [
            'type'      => 'news',
            'typeLabel' => 'Berita',
            'title'     => $row['title'],
            'url'       => '?page=detail-berita&slug=' . rawurlencode($row['slug']),
            'date'      => $row['d'],
            'snippet'   => $row['excerpt'] ?: '',
            'extra'     => $row['category_name'] ?: '',
        ];
    }
}

/* --- DOKUMENTASI (album) --- */
if ($type === '' || $type === 'albums') {
    $parts  = ["a.status = 'published'"];
    $params = [];
    if ($like !== '') {
        $parts[]  = '(a.title LIKE ? OR a.description LIKE ? OR a.location LIKE ?)';
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    if ($catSource === 'albums' && $catId > 0) {
        $parts[]  = 'a.category_id = ?';
        $params[] = $catId;
    }
    if ($year > 0) {
        $parts[]  = 'EXTRACT(YEAR FROM COALESCE(a.event_date, a.created_at)) = ?';
        $params[] = $year;
    }
    if ($month > 0) {
        $parts[]  = 'EXTRACT(MONTH FROM COALESCE(a.event_date, a.created_at)) = ?';
        $params[] = $month;
    }
    foreach (q_all(
        "SELECT a.id, a.title, a.description, a.location, COALESCE(a.event_date, a.created_at) AS d,
                c.name AS category_name,
                (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id) AS photo_count
         FROM albums a
         LEFT JOIN album_categories c ON c.id = a.category_id
         WHERE " . implode(' AND ', $parts) . "
         ORDER BY COALESCE(a.event_date, a.created_at) DESC",
        $params
    ) as $row) {
        $results[] = [
            'type'      => 'albums',
            'typeLabel' => 'Dokumentasi',
            'title'     => $row['title'],
            'url'       => '?page=album&id=' . (int) $row['id'],
            'date'      => $row['d'],
            'snippet'   => $row['description'] ?: $row['location'] ?: '',
            'extra'     => $row['category_name'] ?: ((int) $row['photo_count'] . ' foto'),
        ];
    }
}

/* --- AGENDA --- */
if ($type === '' || $type === 'agendas') {
    $parts  = ["status = 'published'"];
    $params = [];
    if ($like !== '') {
        $parts[]  = '(title LIKE ? OR description LIKE ? OR location LIKE ?)';
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    foreach (q_all(
        "SELECT id, title, description, location, start_datetime AS d
         FROM agendas
         WHERE " . implode(' AND ', $parts) . "
         ORDER BY start_datetime DESC",
        $params
    ) as $row) {
        $results[] = [
            'type'      => 'agendas',
            'typeLabel' => 'Agenda',
            'title'     => $row['title'],
            'url'       => '?page=agenda',
            'date'      => $row['d'],
            'snippet'   => $row['description'] ?: '',
            'extra'     => $row['location'] ?: '',
        ];
    }
}

/* --- PENGUMUMAN --- */
if ($type === '' || $type === 'announcements') {
    $parts  = ["status = 'published'"];
    $params = [];
    if ($like !== '') {
        $parts[]  = '(title LIKE ? OR content LIKE ?)';
        $params[] = $like; $params[] = $like;
    }
    foreach (q_all(
        "SELECT id, title, content, is_pinned, published_at AS d
         FROM announcements
         WHERE " . implode(' AND ', $parts) . "
         ORDER BY is_pinned DESC, published_at DESC",
        $params
    ) as $row) {
        $results[] = [
            'type'      => 'announcements',
            'typeLabel' => 'Pengumuman',
            'title'     => $row['title'],
            'url'       => '?page=pengumuman',
            'date'      => $row['d'],
            'snippet'   => $row['content'] ?: '',
            'extra'     => '',
        ];
    }
}

/* --- DOKUMEN --- */
if ($type === '' || $type === 'documents') {
    $parts  = ['1 = 1'];
    $params = [];
    if ($like !== '') {
        $parts[]  = 'd.name LIKE ?';
        $params[] = $like;
    }
    if ($catSource === 'documents' && $catId > 0) {
        $parts[]  = 'd.category_id = ?';
        $params[] = $catId;
    }
    if ($ftype !== '') {
        $parts[]  = 'LOWER(SUBSTRING_INDEX(d.file, \'.\', -1)) = ?';
        $params[] = $ftype;
    }
    foreach (q_all(
        "SELECT d.id, d.name, d.file, d.uploaded_at AS d,
                c.name AS category_name
         FROM documents d
         LEFT JOIN document_categories c ON c.id = d.category_id
         WHERE " . implode(' AND ', $parts) . "
         ORDER BY d.uploaded_at DESC",
        $params
    ) as $row) {
        $results[] = [
            'type'      => 'documents',
            'typeLabel' => 'Dokumen',
            'title'     => $row['name'],
            'url'       => '?page=dokumen',
            'date'      => $row['d'],
            'snippet'   => '',
            'extra'     => $row['category_name'] ?: strtoupper(pathinfo($row['file'], PATHINFO_EXTENSION)),
        ];
    }
}

/* --- FAQ --- */
if ($type === '' || $type === 'faqs') {
    $parts  = ['is_active = 1'];
    $params = [];
    if ($like !== '') {
        $parts[]  = '(question LIKE ? OR answer LIKE ?)';
        $params[] = $like; $params[] = $like;
    }
    if ($catSource === 'faqs' && $catValue !== '') {
        $parts[]  = 'category = ?';
        $params[] = $catValue;
    }
    foreach (q_all(
        "SELECT id, question, answer, category
         FROM faqs
         WHERE " . implode(' AND ', $parts) . "
         ORDER BY sort_order ASC, id ASC",
        $params
    ) as $row) {
        $results[] = [
            'type'      => 'faqs',
            'typeLabel' => 'FAQ',
            'title'     => $row['question'],
            'url'       => '?page=faq',
            'date'      => null,
            'snippet'   => $row['answer'] ?: '',
            'extra'     => $row['category'] ?: '',
        ];
    }
}

/* ------------------------------------------------------------
 * 4. GABUNG, URUTKAN, PAGINATION (pemotongan di PHP, data kecil)
 * ------------------------------------------------------------ */
usort($results, function (array $a, array $b): int {
    $ta = $a['date'] ? strtotime($a['date']) : 0;
    $tb = $b['date'] ? strtotime($b['date']) : 0;
    return $tb <=> $ta;
});

$total    = count($results);
$perPage  = 10;
$pageNum  = pagination_current();
$maxPage  = max(1, (int) ceil($total / $perPage));
$pageNum  = min($pageNum, $maxPage);
$offset   = ($pageNum - 1) * $perPage;
$paged    = array_slice($results, $offset, $perPage);
$pagination = [
    'total' => $total, 'per_page' => $perPage, 'current' => $pageNum,
    'total_pages' => $maxPage, 'offset' => $offset,
];

$hasFilter = $q !== '' || $type !== '' || $catRaw !== '' || $year > 0 || $month > 0 || $ftype !== '';
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title">Pencarian</h1>
        <p class="mb-0">Temukan berita, dokumentasi, agenda, pengumuman, dokumen, dan FAQ dalam satu pencarian.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">

        <!-- Filter -->
        <form class="dp-filter-bar row g-2 align-items-end mb-4" method="get" action="<?= e(base_url()) ?>" role="search">
            <input type="hidden" name="page" value="search">
            <div class="col-md-4 col-lg-3">
                <label class="dp-form-label" for="s-q">Kata Kunci</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="search" class="form-control dp-form-control" id="s-q" name="q" value="<?= e($q) ?>"
                           placeholder="Kata kunci..." maxlength="100">
                </div>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="dp-form-label" for="s-type">Jenis Konten</label>
                <select class="form-select dp-form-select" id="s-type" name="type">
                    <option value="">Semua Jenis</option>
                    <option value="news" <?= $type === 'news' ? 'selected' : '' ?>>Berita</option>
                    <option value="albums" <?= $type === 'albums' ? 'selected' : '' ?>>Dokumentasi</option>
                    <option value="agendas" <?= $type === 'agendas' ? 'selected' : '' ?>>Agenda</option>
                    <option value="announcements" <?= $type === 'announcements' ? 'selected' : '' ?>>Pengumuman</option>
                    <option value="documents" <?= $type === 'documents' ? 'selected' : '' ?>>Dokumen</option>
                    <option value="faqs" <?= $type === 'faqs' ? 'selected' : '' ?>>FAQ</option>
                </select>
            </div>
            <div class="col-md-3 col-lg-3">
                <label class="dp-form-label" for="s-cat">Kategori</label>
                <select class="form-select dp-form-select" id="s-cat" name="category">
                    <option value="">Semua Kategori</option>
                    <?php if ($type === '' || $type === 'news'): ?>
                        <optgroup label="Berita">
                            <?php foreach ($newsCats as $c): ?>
                                <option value="news:<?= (int) $c['id'] ?>" <?= $catRaw === 'news:' . (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if ($type === '' || $type === 'albums'): ?>
                        <optgroup label="Dokumentasi">
                            <?php foreach ($albumCats as $c): ?>
                                <option value="albums:<?= (int) $c['id'] ?>" <?= $catRaw === 'albums:' . (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if ($type === '' || $type === 'documents'): ?>
                        <optgroup label="Dokumen">
                            <?php foreach ($documentCats as $c): ?>
                                <option value="documents:<?= (int) $c['id'] ?>" <?= $catRaw === 'documents:' . (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if ($type === '' || $type === 'faqs'): ?>
                        <optgroup label="FAQ">
                            <?php foreach ($faqCats as $c): ?>
                                <option value="faqs:<?= e($c['name']) ?>" <?= $catRaw === 'faqs:' . $c['name'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>

            <?php if ($type === '' || $type === 'news' || $type === 'albums'): ?>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="dp-form-label" for="s-year">Tahun</label>
                    <select class="form-select dp-form-select" id="s-year" name="year">
                        <option value="">Semua Tahun</option>
                        <?php $years = $type === 'albums' ? $albumYears : $newsYears; ?>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= (int) $y['y'] ?>" <?= $year === (int) $y['y'] ? 'selected' : '' ?>><?= (int) $y['y'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($type === 'albums'): ?>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="dp-form-label" for="s-month">Bulan</label>
                    <select class="form-select dp-form-select" id="s-month" name="month">
                        <option value="">Semua Bulan</option>
                        <?php foreach ($months as $m => $mName): ?>
                            <option value="<?= $m ?>" <?= $month === $m ? 'selected' : '' ?>><?= $mName ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($type === 'documents'): ?>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="dp-form-label" for="s-ftype">Tipe File</label>
                    <select class="form-select dp-form-select" id="s-ftype" name="ftype">
                        <option value="">Semua Format</option>
                        <?php foreach ($fileTypes as $ft): ?>
                            <option value="<?= e($ft['t']) ?>" <?= $ftype === $ft['t'] ? 'selected' : '' ?>><?= e(strtoupper($ft['t'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-12 col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn dp-btn-primary flex-fill"><i class="bi bi-funnel" aria-hidden="true"></i> Cari</button>
                <?php if ($hasFilter): ?>
                    <a href="<?= e(base_url('?page=search')) ?>" class="btn dp-btn-outline-primary" aria-label="Hapus filter"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (!$hasFilter): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-search" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Mulai Pencarian</p>
                <p class="dp-empty-text">Ketik kata kunci, pilih jenis konten, atau gunakan kategori untuk menemukan berita, dokumentasi, agenda, pengumuman, dokumen, dan FAQ.</p>
            </div>

        <?php elseif ($total === 0): ?>
            <div class="dp-empty">
                <span class="dp-empty-icon"><i class="bi bi-search-heart" aria-hidden="true"></i></span>
                <p class="dp-empty-title">Tidak Ada Hasil</p>
                <p class="dp-empty-text">
                    Tidak ditemukan konten yang cocok dengan kata kunci
                    <?= $q !== '' ? '&ldquo;' . e($q) . '&rdquo;' : '' ?>.
                    Coba kata kunci lain, ubah kategori, atau <a href="<?= e(base_url('?page=search')) ?>">hapus semua filter</a>.
                </p>
            </div>

        <?php else: ?>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <p class="mb-0 fw-semibold">
                    <?= e(number_format($total)) ?> hasil ditemukan
                    <?php if ($q !== ''): ?> untuk &ldquo;<?= e($q) ?>&rdquo;<?php endif; ?>
                </p>
                <small class="dp-text-muted">Diurutkan berdasarkan tanggal terbaru</small>
            </div>

            <div class="dp-card">
                <ul class="list-group list-group-flush">
                    <?php foreach ($paged as $item): ?>
                        <li class="list-group-item px-0 py-3">
                            <div class="d-flex align-items-start gap-3">
                                <span class="dp-badge dp-badge-gold text-nowrap mt-1"><?= e($item['typeLabel']) ?></span>
                                <div class="min-w-0">
                                    <a class="dp-link-strong" href="<?= e(base_url($item['url'])) ?>"><?= e($item['title']) ?></a>
                                    <?php if ($item['snippet'] !== ''): ?>
                                        <p class="mb-1 dp-text-muted"><?= e(trim_words(trim(strip_tags($item['snippet'])), 26)) ?></p>
                                    <?php endif; ?>
                                    <small class="dp-text-muted">
                                        <?php if ($item['date']): ?>
                                            <i class="bi bi-calendar3 me-1" aria-hidden="true"></i><?= e(format_date_id($item['date'])) ?>
                                        <?php endif; ?>
                                        <?php if ($item['extra'] !== ''): ?>
                                            <span class="mx-1" aria-hidden="true">&middot;</span>
                                            <i class="bi bi-tag me-1" aria-hidden="true"></i><?= e($item['extra']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php require BASE_PATH . '/includes/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>