<?php
/**
 * Halaman 404 — route tidak dikenal.
 */
$pageTitle = 'Halaman Tidak Ditemukan';
http_response_code(404);
?>
<section class="dp-section">
    <div class="container">
        <div class="dp-empty py-5">
            <span class="dp-empty-icon"><i class="bi bi-compass" aria-hidden="true"></i></span>
            <h1 class="dp-empty-title">Halaman Tidak Ditemukan</h1>
            <p class="dp-empty-text">Halaman yang Anda tuju belum tersedia atau telah dipindahkan.</p>
            <a class="btn dp-btn-primary" href="<?= e(base_url()) ?>"><i class="bi bi-house" aria-hidden="true"></i> Kembali ke Beranda</a>
        </div>
    </div>
</section>