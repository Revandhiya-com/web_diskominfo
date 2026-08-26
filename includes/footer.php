<?php
/**
 * Footer halaman — identitas, navigasi, kontak, sosial media, copyright.
 */
if (!defined('BASE_PATH')) {
    exit('Akses langsung tidak diizinkan.');
}

$footerAbout   = setting('footer_about', 'Portal informasi, layanan, berita, dan dokumentasi kegiatan Praktik Kerja Lapangan dan Magang.');
$contactAddr   = setting('contact_address', 'Alamat resmi akan ditampilkan setelah konfirmasi data.');
$contactEmail  = setting('contact_email', '');
$contactPhone  = setting('contact_phone', '');
$contactMap    = setting('contact_map_link', '#');
$socials       = [
    'facebook'  => setting('social_facebook', '#'),
    'instagram' => setting('social_instagram', '#'),
    'twitter'   => setting('social_twitter', '#'),
    'youtube'   => setting('social_youtube', '#'),
];
?>
<footer class="dp-footer">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="dp-footer-brand d-flex align-items-center gap-2 mb-3">
                    <?php $logo = site_logo(); if ($logo): ?>
                        <img class="dp-footer-logo-img" src="<?= e($logo['url']) ?>" alt="Logo Diskominfo Provinsi Banten">
                    <?php else: ?>
                        <span class="dp-footer-logo"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></span>
                    <?php endif; ?>
                    <div>
                        <strong class="text-white">Portal PKL &amp; Magang</strong>
                        <div class="small">Diskominfo Provinsi Banten</div>
                    </div>
                </div>
                <p class="small mb-3"><?= e($footerAbout) ?></p>
                <ul class="dp-footer-social list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="<?= e($socials['facebook']) ?>" class="dp-social-link" aria-label="Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
                    </li>
                    <li class="list-inline-item">
                        <a href="<?= e($socials['instagram']) ?>" class="dp-social-link" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
                    </li>
                    <li class="list-inline-item">
                        <a href="<?= e($socials['twitter']) ?>" class="dp-social-link" aria-label="Twitter / X"><i class="bi bi-twitter-x" aria-hidden="true"></i></a>
                    </li>
                    <li class="list-inline-item">
                        <a href="<?= e($socials['youtube']) ?>" class="dp-social-link" aria-label="YouTube"><i class="bi bi-youtube" aria-hidden="true"></i></a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="dp-footer-title">Navigasi</h6>
                <ul class="list-unstyled dp-footer-links">
                    <li><a href="<?= e(base_url()) ?>">Home</a></li>
                    <li><a href="<?= e(base_url('?page=profil')) ?>">Profil</a></li>
                    <li><a href="<?= e(base_url('?page=layanan')) ?>">Layanan</a></li>
                    <li><a href="<?= e(base_url('?page=berita')) ?>">Berita</a></li>
                    <li><a href="<?= e(base_url('?page=agenda')) ?>">Agenda</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="dp-footer-title">Informasi</h6>
                <ul class="list-unstyled dp-footer-links">
                    <li><a href="<?= e(base_url('?page=dokumentasi')) ?>">Dokumentasi</a></li>
                    <li><a href="<?= e(base_url('?page=video')) ?>">Video</a></li>
                    <li><a href="<?= e(base_url('?page=karya')) ?>">Karya Peserta</a></li>
                    <li><a href="<?= e(base_url('?page=pengumuman')) ?>">Pengumuman</a></li>
                    <li><a href="<?= e(base_url('?page=faq')) ?>">FAQ</a></li>
                    <li><a href="<?= e(base_url('?page=dokumen')) ?>">Download</a></li>
                    <li><a href="<?= e(base_url('?page=kontak')) ?>">Kontak</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h6 class="dp-footer-title">Kontak</h6>
                <ul class="list-unstyled dp-footer-contact">
                    <li class="d-flex gap-2"><i class="bi bi-geo-alt" aria-hidden="true"></i><span><?= e($contactAddr) ?></span></li>
                    <li class="d-flex gap-2"><i class="bi bi-envelope" aria-hidden="true"></i><span><?= e($contactEmail !== '' ? $contactEmail : '-') ?></span></li>
                    <li class="d-flex gap-2"><i class="bi bi-telephone" aria-hidden="true"></i><span><?= e($contactPhone !== '' ? $contactPhone : '-') ?></span></li>
                    <li class="d-flex gap-2"><i class="bi bi-map" aria-hidden="true"></i><a href="<?= e($contactMap) ?>" target="_blank" rel="noopener">Lihat Peta</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="dp-footer-bottom">
        <div class="container py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="small text-center text-md-start">
                &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Hak cipta dilindungi.
            </div>
            <div class="small d-flex gap-3">
                <a href="<?= e(base_url('?page=privacy')) ?>">Kebijakan Privasi</a>
                <a href="<?= e(base_url('?page=sitemap')) ?>">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<button type="button" class="dp-back-to-top" id="backToTop" aria-label="Kembali ke atas">
    <i class="bi bi-chevron-up" aria-hidden="true"></i>
</button>

<script src="<?= asset_url('vendor/bootstrap/bootstrap.bundle.min.js') ?>?v=<?= APP_ASSET_VERSION ?>" defer></script>
<script src="<?= asset_url('js/main.js') ?>?v=<?= APP_ASSET_VERSION ?>" defer></script>
</body>
</html>
