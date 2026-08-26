<?php
/**
 * Halaman Kebijakan Privasi — penjelasan data yang dikumpulkan dan
 * cara penggunaannya pada portal ini.
 */

$pageTitle = 'Kebijakan Privasi';
$pageDescription = 'Kebijakan privasi portal PKL dan Magang Diskominfo Provinsi Banten: data yang dikumpulkan, tujuan penggunaan, dan kontak pengelola.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Kebijakan Privasi'],
];
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Daftar informasi mengenai data yang dikumpulkan oleh portal ini dan cara penggunaannya.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <div class="col-lg-8">
            <div class="dp-card">
                <div class="dp-card-body">
                    <h2 class="dp-card-title fs-4">Data yang Dikumpulkan</h2>
                    <p class="dp-card-text">Portal ini menyimpan data yang Anda kirimkan secara sukarela melalui formulir, antara lain: nama, alamat email, subjek, dan isi pesan pada formulir kontak. Data tersebut digunakan semata-mata untuk menindaklanjuti pesan Anda.</p>

                    <h2 class="dp-card-title fs-4 mt-4">Data Teknis</h2>
                    <p class="dp-card-text">Saat mengunjungi portal ini, server mencatat data teknis dasar seperti alamat IP, jenis peramban, dan waktu akses. Data ini digunakan untuk keamanan, analisis kunjungan, dan perbaikan layanan.</p>

                    <h2 class="dp-card-title fs-4 mt-4">Cookie dan Sesi</h2>
                    <p class="dp-card-text">Portal ini menggunakan cookie sesi untuk menjaga sesi login Anda saat mengakses halaman pengelolaan (admin). Cookie ini tidak digunakan untuk pelacakan iklan.</p>

                    <h2 class="dp-card-title fs-4 mt-4">Penggunaan Data</h2>
                    <p class="dp-card-text">Data yang dikumpulkan tidak dibagikan kepada pihak ketiga, kecuali diwajibkan oleh peraturan perundang-undangan yang berlaku.</p>

                    <h2 class="dp-card-title fs-4 mt-4">Kontak</h2>
                    <p class="dp-card-text">Pertanyaan mengenai kebijakan privasi ini dapat disampaikan melalui <a href="<?= e(base_url('?page=kontak')) ?>">halaman Kontak</a>.</p>
                </div>
            </div>
        </div>
    </div>
</section>