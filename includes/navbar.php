<?php
/**
 * Navbar utama — topbar pemerintah + navigasi + pencarian.
 * Menyesuaikan menu aktif berdasarkan variabel $currentPage.
 */
if (!defined('BASE_PATH')) {
    exit('Akses langsung tidak diizinkan.');
}

$siteTagline = setting('site_tagline', 'Portal Informasi PKL & Magang');
?>
<header class="dp-header">
    <!-- Topbar pemerintah -->
    <div class="dp-topbar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-globe2" aria-hidden="true"></i>
                <span class="fw-semibold">Pemerintah Provinsi Banten</span>
                <span class="dp-topbar-sep" aria-hidden="true"></span>
                <span class="d-none d-md-inline">Dinas Komunikasi, Informatika, Persandian dan Statistik</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="dp-topbar-link border-0 bg-transparent" id="themeToggle" aria-label="Ganti tema (gelap/terang)" aria-pressed="false" title="Ganti tema">
                    <i class="bi bi-moon-stars" aria-hidden="true"></i>
                </button>
                <a href="<?= e(base_url('?page=kontak')) ?>" class="dp-topbar-link"><i class="bi bi-envelope" aria-hidden="true"></i> <span class="d-none d-sm-inline">Kontak</span></a>
                <a href="<?= e(base_url('?page=pengumuman')) ?>" class="dp-topbar-link"><i class="bi bi-megaphone" aria-hidden="true"></i> <span class="d-none d-sm-inline">Pengumuman</span></a>
            </div>
        </div>
    </div>

    <!-- Navbar utama -->
    <nav class="navbar navbar-expand-lg dp-navbar sticky-top" aria-label="Navigasi utama">
        <div class="container">
            <a class="navbar-brand dp-brand" href="<?= e(base_url()) ?>">
                <?php $logo = site_logo(); if ($logo): ?>
                    <img class="dp-brand-logo-img" src="<?= e($logo['url']) ?>" alt="Logo Diskominfo Provinsi Banten">
                <?php else: ?>
                    <span class="dp-brand-logo" aria-hidden="true">
                        <i class="bi bi-mortarboard-fill"></i>
                    </span>
                <?php endif; ?>
                <span class="dp-brand-text">
                    <strong>Portal PKL &amp; Magang</strong>
                    <small><?= e($siteTagline) ?></small>
                </span>
            </a>

            <button class="navbar-toggler dp-navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Buka menu navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-lg-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link dp-nav-link <?= nav_active('home') ?>" href="<?= e(base_url()) ?>">Home</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dp-nav-link dropdown-toggle" href="<?= e(base_url('?page=profil')) ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">Profil</a>
                        <ul class="dropdown-menu dp-dropdown">
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=profil#program')) ?>">Profil Program</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=profil#tujuan')) ?>">Tujuan</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=profil#visi-misi')) ?>">Visi &amp; Misi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=tugas-dan-fungsi')) ?>">Tugas &amp; Fungsi</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=sejarah-dinas')) ?>">Sejarah Dinas</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=unit-kerja')) ?>">Unit Kerja</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=informasi-pelayanan')) ?>">Informasi Pelayanan</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=ppid')) ?>">PPID</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=informasi-setiap-saat')) ?>">Informasi Setiap Saat</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=profil-pejabat')) ?>">Profil Pejabat</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dp-nav-link dropdown-toggle" href="<?= e(base_url('?page=layanan')) ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">Layanan</a>
                        <ul class="dropdown-menu dp-dropdown">
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=layanan')) ?>">Informasi PKL &amp; Magang</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=layanan#persyaratan')) ?>">Persyaratan</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=layanan#alur')) ?>">Alur Pendaftaran</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link dp-nav-link <?= nav_active('berita') ?>" href="<?= e(base_url('?page=berita')) ?>">Berita</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link dp-nav-link <?= nav_active('agenda') ?>" href="<?= e(base_url('?page=agenda')) ?>">Agenda</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link dp-nav-link <?= nav_active('alumni') ?>" href="<?= e(base_url('?page=alumni')) ?>">Alumni</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dp-nav-link dropdown-toggle" href="<?= e(base_url('?page=dokumentasi')) ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">Dokumentasi</a>
                        <ul class="dropdown-menu dp-dropdown">
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=dokumentasi')) ?>">Dokumentasi Terbaru</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=album')) ?>">Album Foto</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=video')) ?>">Video Kegiatan</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=karya')) ?>">Karya Peserta</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dp-nav-link dropdown-toggle" href="<?= e(base_url('?page=pengumuman')) ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">Informasi</a>
                        <ul class="dropdown-menu dp-dropdown">
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=pengumuman')) ?>">Pengumuman</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=dokumen')) ?>">Dokumen Pendukung</a></li>
                            <li><a class="dropdown-item" href="<?= e(base_url('?page=faq')) ?>">FAQ</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link dp-nav-link <?= nav_active('kontak') ?>" href="<?= e(base_url('?page=kontak')) ?>">Kontak</a>
                    </li>

                    <li class="nav-item">
                        <form class="d-flex dp-search" action="<?= e(base_url()) ?>" method="get" role="search">
                            <input type="hidden" name="page" value="search">
                            <div class="input-group input-group-sm">
                                <input class="form-control dp-search-input" type="search" name="q" placeholder="Cari di situs..." aria-label="Cari di situs">
                                <button class="btn dp-btn-primary" type="submit" aria-label="Cari"><i class="bi bi-search" aria-hidden="true"></i></button>
                            </div>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
