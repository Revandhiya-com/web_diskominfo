<?php
/**
 * Halaman Kontak — informasi alamat/email/telepon/peta (dari tabel settings,
 * placeholder sampai data resmi) + form kontak yang tersimpan ke tabel
 * contact_messages (CSRF + validasi).
 */

$pageTitle = 'Kontak';
$pageDescription = 'Hubungi tim pengelola program PKL dan Magang Diskominfo Provinsi Banten.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'Kontak'],
];

/* Data dari settings (placeholder resmi menunggu konfirmasi) */
$contactAddress = setting('contact_address', '');
$contactEmail   = setting('contact_email', '');
$contactPhone   = setting('contact_phone', '');
$contactMap     = setting('contact_map_link', '');

/* ---------- Form kontak ---------- */
$formErrors = [];
$formSuccess = false;

if (is_post()) {
    require_csrf();

    $name    = sanitize_text(post_val('name', ''));
    $email   = sanitize_text(post_val('email', ''));
    $subject = sanitize_text(post_val('subject', ''));
    $message = sanitize_text(post_val('message', ''));
    $website = post_val('website', ''); // honeypot anti-spam

    if (mb_strlen($name) < 2) {
        $formErrors['name'] = 'Nama minimal 2 karakter.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formErrors['email'] = 'Alamat email tidak valid.';
    }
    if (mb_strlen($message) < 10) {
        $formErrors['message'] = 'Pesan minimal 10 karakter.';
    }

    if ($website === '' && empty($formErrors)) {
        $ok = q_exec(
            'INSERT INTO contact_messages (name, email, subject, message, ip)
             VALUES (?, ?, ?, ?, ?)',
            [
                $name, $email,
                $subject !== '' ? $subject : null,
                $message,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]
        );
        if ($ok) {
            $_SESSION['contact_flash'] = 'Pesan Anda telah terkirim. Tim pengelola akan menindaklanjuti melalui email.';
            redirect('?page=kontak');
        }
        $formErrors['general'] = 'Pesan gagal dikirim. Silakan coba kembali beberapa saat lagi.';
    }
}

if (!empty($_SESSION['contact_flash'])) {
    $formSuccess = (string) $_SESSION['contact_flash'];
    unset($_SESSION['contact_flash']);
}

$old = [
    'name'    => post_val('name', ''),
    'email'   => post_val('email', ''),
    'subject' => post_val('subject', ''),
    'message' => post_val('message', ''),
];

$infoItems = [
    ['icon' => 'bi-geo-alt', 'title' => 'Alamat', 'value' => $contactAddress !== '' ? $contactAddress : 'Alamat resmi akan ditampilkan setelah konfirmasi data.'],
    ['icon' => 'bi-envelope', 'title' => 'Email', 'value' => $contactEmail !== '' ? $contactEmail : 'Email pengelola program akan ditampilkan setelah konfirmasi data.'],
    ['icon' => 'bi-telephone', 'title' => 'Telepon / Help Desk', 'value' => $contactPhone !== '' ? $contactPhone : '(0254) 000-0000'],
    ['icon' => 'bi-clock', 'title' => 'Jam Layanan', 'value' => 'Hari dan jam kerja resmi akan ditampilkan setelah konfirmasi data.'],
];
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Hubungi tim pengelola program PKL dan Magang di lingkungan Diskominfo Provinsi Banten.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <div class="row g-4">
            <!-- Info kontak -->
            <div class="col-lg-5">
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($infoItems as $item): ?>
                        <div class="dp-contact-card d-flex gap-3">
                            <span class="dp-contact-icon"><i class="bi <?= e($item['icon']) ?>" aria-hidden="true"></i></span>
                            <div>
                                <h2 class="dp-contact-title"><?= e($item['title']) ?></h2>
                                <p class="mb-0"><?= e($item['value']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Peta (placeholder) -->
                    <div class="dp-contact-card d-flex gap-3">
                        <span class="dp-contact-icon"><i class="bi bi-map" aria-hidden="true"></i></span>
                        <div class="flex-grow-1">
                            <h2 class="dp-contact-title">Lokasi</h2>
                            <?php if ($contactMap !== '' && $contactMap !== '#') : ?>
                                <div class="dp-map-placeholder">
                                    <i class="bi bi-map-fill" aria-hidden="true"></i>
                                    <p>Peta lokasi akan ditampilkan di sini.</p>
                                    <a class="btn dp-btn-outline-primary btn-sm" href="<?= e($contactMap) ?>" target="_blank" rel="noopener">Buka di Google Maps</a>
                                </div>
                            <?php else: ?>
                                <div class="dp-map-placeholder">
                                    <i class="bi bi-map-fill" aria-hidden="true"></i>
                                    <p class="mb-0">Peta lokasi resmi akan ditampilkan setelah data dikonfirmasi.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form kontak -->
            <div class="col-lg-7">
                <div class="dp-card">
                    <div class="dp-card-body">
                        <h2 class="dp-card-title fs-4 mb-1">Kirim Pesan</h2>
                        <p class="dp-text-muted small mb-4">Isi formulir berikut untuk menghubungi tim pengelola. Seluruh field bertanda <span class="dp-required">*</span> wajib diisi.</p>

                        <?php if ($formSuccess): ?>
                            <div class="dp-alert dp-alert-success" role="alert">
                                <i class="bi bi-check-circle me-1" aria-hidden="true"></i><?= e($formSuccess) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($formErrors['general'])): ?>
                            <div class="dp-alert dp-alert-danger" role="alert">
                                <i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i><?= e($formErrors['general']) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?= e(base_url('?page=kontak')) ?>" novalidate>
                            <?= csrf_field() ?>
                            <div class="d-none" aria-hidden="true">
                                <label for="website">Jangan diisi</label>
                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="dp-form-label" for="contact-name">Nama Lengkap <span class="dp-required">*</span></label>
                                    <input type="text" class="form-control dp-form-control <?= isset($formErrors['name']) ? 'is-invalid' : '' ?>" id="contact-name" name="name" value="<?= e($old['name']) ?>" maxlength="100" required>
                                    <?php if (isset($formErrors['name'])): ?>
                                        <div class="invalid-feedback"><?= e($formErrors['name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="dp-form-label" for="contact-email">Email <span class="dp-required">*</span></label>
                                    <input type="email" class="form-control dp-form-control <?= isset($formErrors['email']) ? 'is-invalid' : '' ?>" id="contact-email" name="email" value="<?= e($old['email']) ?>" maxlength="150" required>
                                    <?php if (isset($formErrors['email'])): ?>
                                        <div class="invalid-feedback"><?= e($formErrors['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <label class="dp-form-label" for="contact-subject">Subjek</label>
                                    <input type="text" class="form-control dp-form-control" id="contact-subject" name="subject" value="<?= e($old['subject']) ?>" maxlength="200" placeholder="Contoh: Pertanyaan pendaftaran PKL">
                                </div>
                                <div class="col-12">
                                    <label class="dp-form-label" for="contact-message">Pesan <span class="dp-required">*</span></label>
                                    <textarea class="form-control dp-form-control <?= isset($formErrors['message']) ? 'is-invalid' : '' ?>" id="contact-message" name="message" rows="6" maxlength="2000" required><?= e($old['message']) ?></textarea>
                                    <?php if (isset($formErrors['message'])): ?>
                                        <div class="invalid-feedback"><?= e($formErrors['message']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn dp-btn-primary"><i class="bi bi-send" aria-hidden="true"></i> Kirim Pesan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>