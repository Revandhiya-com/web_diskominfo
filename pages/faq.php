<?php
/**
 * Halaman FAQ — pertanyaan yang sering diajukan, dikelompokkan per kategori,
 * ditampilkan dengan accordion (Bootstrap).
 */

$pageTitle = 'FAQ';
$pageDescription = 'Pertanyaan yang sering diajukan (FAQ) mengenai program PKL dan Magang Diskominfo Provinsi Banten.';
$breadcrumbs = [
    ['label' => 'Beranda', 'url' => base_url()],
    ['label' => 'FAQ'],
];

/* Kelompokkan FAQ per kategori (aktif, urut sort_order) */
$faqRows = q_all(
    'SELECT id, question, answer, category
     FROM faqs
     WHERE is_active = 1
     ORDER BY category ASC, sort_order ASC, id ASC'
);

$groups = [];
foreach ($faqRows as $faq) {
    $cat = $faq['category'] !== null && $faq['category'] !== '' ? $faq['category'] : 'Umum';
    $groups[$cat][] = $faq;
}

$accordionIndex = 0;
?>
<section class="dp-page-header" aria-labelledby="page-header-title">
    <div class="container">
        <h1 id="page-header-title"><?= e($pageTitle) ?></h1>
        <p class="mb-0">Pertanyaan yang sering diajukan seputar PKL dan Magang. Jawaban akan diperbarui sesuai ketentuan resmi instansi.</p>
    </div>
</section>

<section class="dp-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (empty($groups)): ?>
                    <div class="dp-empty">
                        <span class="dp-empty-icon"><i class="bi bi-question-circle" aria-hidden="true"></i></span>
                        <p class="dp-empty-title">Belum Ada FAQ</p>
                        <p class="dp-empty-text">Daftar pertanyaan dan jawaban akan ditambahkan oleh tim pengelola.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($groups as $category => $items): ?>
                        <div class="mb-4">
                            <h2 class="dp-section-title mb-3">
                                <i class="bi bi-tag text-primary me-2" aria-hidden="true"></i><?= e($category) ?>
                            </h2>
                            <div class="accordion dp-accordion" id="faq-accordion-<?= (int) $accordionIndex ?>">
                                <?php foreach ($items as $i => $faq):
                                    $itemId = 'faq-' . $accordionIndex . '-' . $i;
                                ?>
                                    <div class="accordion-item">
                                        <h3 class="accordion-header">
                                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($itemId) ?>" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="<?= e($itemId) ?>">
                                                <i class="bi bi-question-circle-fill me-2" aria-hidden="true"></i><?= e($faq['question']) ?>
                                            </button>
                                        </h3>
                                        <div id="<?= e($itemId) ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faq-accordion-<?= (int) $accordionIndex ?>">
                                            <div class="accordion-body dp-prose">
                                                <?= nl2br(e($faq['answer'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php $accordionIndex++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <aside class="col-lg-4" aria-label="Bantuan tambahan">
                <div class="dp-side-block">
                    <h2 class="dp-side-block-title">Butuh Bantuan Lain?</h2>
                    <p class="small text-secondary">Pertanyaan Anda belum terjawab di halaman ini? Hubungi tim pengelola melalui halaman kontak.</p>
                    <a class="btn dp-btn-primary w-100" href="<?= e(base_url('?page=kontak')) ?>"><i class="bi bi-envelope" aria-hidden="true"></i> Hubungi Kami</a>
                </div>
                <div class="dp-side-block mt-3">
                    <h2 class="dp-side-block-title">Informasi Terkait</h2>
                    <ul class="list-unstyled dp-side-links mb-0">
                        <li><a href="<?= e(base_url('?page=layanan')) ?>"><i class="bi bi-list-check" aria-hidden="true"></i> Layanan &amp; Persyaratan</a></li>
                        <li><a href="<?= e(base_url('?page=pengumuman')) ?>"><i class="bi bi-megaphone" aria-hidden="true"></i> Pengumuman</a></li>
                        <li><a href="<?= e(base_url('?page=dokumen')) ?>"><i class="bi bi-download" aria-hidden="true"></i> Dokumen Pendukung</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>