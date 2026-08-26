<?php
/**
 * Halaman Agenda — agenda mendatang (upcoming) dan yang telah berlangsung (past).
 * Data dari tabel `agendas` (status published).
 */

$pageTitle = 'Agenda';
$pageDescription = 'Agenda kegiatan resmi PKL dan Magang di lingkungan Diskominfo Provinsi Banten.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Agenda'],
];

/* Mendatang: mulai sekarang ke depan, terdekat dulu */
$upcoming = q_all(
    'SELECT id, title, description, location, source_url, start_datetime, end_datetime
     FROM agendas
     WHERE status = $$published$$ AND start_datetime >= NOW()
     ORDER BY start_datetime ASC'
);

/* Telah lewat: urut paling baru */
$past = q_all(
    'SELECT id, title, description, location, source_url, start_datetime, end_datetime
     FROM agendas
     WHERE status = $$published$$ AND start_datetime < NOW()
     ORDER BY start_datetime DESC'
);

/* Bulan Indonesia (singkat) untuk kartu tanggal */
$monthShort = function (string $date): string {
    $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
               7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    return $months[(int) date('n', strtotime($date))] ?? '';
};

$renderAgenda = function (array $agenda) use ($monthShort): string {
    $ts = strtotime($agenda['start_datetime']);
    ob_start();
    ?>
    <article class="dp-agenda-item dp-agenda-item-lg">
        <div class="dp-agenda-date" aria-hidden="true">
            <span class="day"><?= e(date('j', $ts)) ?></span>
            <span class="month"><?= e($monthShort($agenda['start_datetime'])) ?></span>
            <span class="year"><?= e(date('Y', $ts)) ?></span>
        </div>
        <div class="dp-agenda-info">
            <h3 class="dp-agenda-title"><?= e($agenda['title']) ?></h3>
            <div class="dp-agenda-meta mb-1">
                <span><i class="bi bi-clock" aria-hidden="true"></i><?= e(format_date_id($agenda['start_datetime'], true)) ?><?= $agenda['end_datetime'] ? ' — ' . e(date('H:i', strtotime($agenda['end_datetime']))) . ' WIB' : '' ?></span>
                <?php if ($agenda['location']): ?>
                    <span><i class="bi bi-geo-alt" aria-hidden="true"></i><?= e($agenda['location']) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($agenda['description']): ?>
                <p class="dp-card-text mb-0"><?= e($agenda['description']) ?></p>
            <?php endif; ?>
            <?php if (!empty($agenda['source_url'])): ?>
                <p class="dp-source-note small mt-2 mb-0">
                    <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Sumber:
                    <a href="<?= e($agenda['source_url']) ?>" target="_blank" rel="noopener nofollow"><?= e($agenda['source_url']) ?></a>
                </p>
            <?php endif; ?>
        </div>
    </article>
    <?php
    return ob_get_clean();
};
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Jadwal dan agenda kegiatan resmi PKL dan Magang di lingkungan Diskominfo Provinsi Banten.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <ul class="nav nav-tabs dp-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-upcoming" data-bs-toggle="tab" data-bs-target="#panel-upcoming" type="button" role="tab" aria-controls="panel-upcoming" aria-selected="true">
                    <i class="bi bi-calendar2-event me-1" aria-hidden="true"></i> Agenda Mendatang
                    <span class="dp-tab-count"><?= count($upcoming) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-past" data-bs-toggle="tab" data-bs-target="#panel-past" type="button" role="tab" aria-controls="panel-past" aria-selected="false">
                    <i class="bi bi-clock-history me-1" aria-hidden="true"></i> Agenda Terlewat
                    <span class="dp-tab-count"><?= count($past) ?></span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Mendatang -->
            <div class="tab-pane fade show active" id="panel-upcoming" role="tabpanel" aria-labelledby="tab-upcoming">
                <?php if (empty($upcoming)): ?>
                    <div class="dp-empty">
                        <span class="dp-empty-icon"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span>
                        <p class="dp-empty-title">Belum Ada Agenda Mendatang</p>
                        <p class="dp-empty-text">Agenda resmi akan diumumkan oleh tim pengelola melalui halaman ini.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($upcoming as $agenda): ?>
                            <?= $renderAgenda($agenda) ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Terlewat -->
            <div class="tab-pane fade" id="panel-past" role="tabpanel" aria-labelledby="tab-past">
                <?php if (empty($past)): ?>
                    <div class="dp-empty">
                        <span class="dp-empty-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
                        <p class="dp-empty-title">Belum Ada Riwayat Agenda</p>
                        <p class="dp-empty-text">Agenda yang telah berlangsung akan tercatat di sini.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($past as $agenda): ?>
                            <?= $renderAgenda($agenda) ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>