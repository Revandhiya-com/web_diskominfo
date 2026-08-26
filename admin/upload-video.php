<?php
/**
 * Endpoint AJAX unggah video untuk disisipkan ke isi konten (berita/halaman).
 * POST multipart: file + csrf_token. Return JSON {ok, url|error}.
 */
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!is_post()) {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metode tidak diizinkan.']);
    exit;
}

/* POST melebihi post_max_size server → PHP membuang seluruh body. */
if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    http_response_code(413);
    echo json_encode(['ok' => false, 'error' => 'Ukuran file melebihi batas server (maks ' . format_size(UPLOAD_VIDEO_MAX_SIZE) . ').']);
    exit;
}

require_csrf();

try {
    $res = process_video_upload($_FILES['file'] ?? []);

    log_activity('upload', 'video', null, 'Unggah video konten: videos/' . $res['name']);
    echo json_encode(['ok' => true, 'url' => 'uploads/videos/' . $res['name']]);
} catch (UploadException $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    log_error('Upload video konten gagal', $e);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Terjadi kesalahan server. Coba lagi.']);
}
