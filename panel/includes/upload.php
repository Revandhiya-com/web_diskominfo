<?php
/**
 * Upload file AMAN + optimasi gambar (GD).
 *
 * Aturan:
 * - Validasi MIME asli (finfo) — tidak pernah percaya nama/ekstensi user.
 * - Whitelist ekstensi & MIME; ukuran dibatasi.
 * - Nama file acak (random_bytes) — filename user dibuang.
 * - File disimpan di folder uploads; database HANYA menyimpan nama path.
 * - Gambar besar di-resize (max 1920px); thumbnail 480px untuk galeri ringan.
 */

/** Daftar MIME + ekstensi gambar yang diizinkan (FASE 7: jpg/jpeg/png/webp). */
const IMAGE_MIME_EXT = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

const IMAGE_FULL_MAX_SIDE = 1920;
const IMAGE_THUMB_MAX_SIDE = 480;
const IMAGE_JPEG_QUALITY = 85;
const IMAGE_WEBP_QUALITY = 82;

/** Daftar MIME + ekstensi video yang diizinkan (mp4/webm/ogg/mov). */
const VIDEO_MIME_EXT = [
    'video/mp4'       => 'mp4',
    'video/webm'      => 'webm',
    'video/ogg'       => 'ogg',
    'video/quicktime' => 'mov',
];

/** Exception khusus upload. */
class UploadException extends RuntimeException
{
}

/** Dipanggil untuk memastikan folder upload subdirectory siap ditulis. Fallback ke sys_get_temp_dir() jika read-only (misal Vercel). */
function ensure_upload_dir(string $subdir): string
{
    $dir = UPLOADS_PATH . '/' . $subdir;
    if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
        $dir = sys_get_temp_dir() . '/uploads/' . $subdir;
        if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
            throw new UploadException('Folder upload tidak dapat dibuat. Periksa izin folder.');
        }
    }
    return $dir;
}

/** Nama file acak aman + ekstensi aman (prefix opsional, karakter non-aman dibuang). */
function safe_filename(string $ext, string $prefix = ''): string
{
    $prefix = preg_replace('~[^a-zA-Z0-9_-]~', '', $prefix);
    return ($prefix !== '' ? $prefix . '_' : '') . bin2hex(random_bytes(12)) . '.' . strtolower($ext);
}

/** Validasi dasar $_FILES (error, ukuran) + MIME asli via finfo. */
function validate_upload(array $file, array $mimeWhitelist, int $maxSize): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        throw new UploadException('Tidak ada file yang dipilih.');
    }
    if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
        $map = [
            UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi batas konfigurasi server.',
            UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas maksimum.',
            UPLOAD_ERR_PARTIAL    => 'Upload terputus, coba lagi.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file di server.',
        ];
        throw new UploadException($map[$file['error']] ?? 'Upload gagal.');
    }
    if ((int) $file['size'] <= 0) {
        throw new UploadException('File kosong.');
    }
    if ((int) $file['size'] > $maxSize) {
        throw new UploadException('Ukuran file melebihi batas ' . format_size($maxSize) . '.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    if (!isset($mimeWhitelist[$mime])) {
        throw new UploadException('Tipe file tidak diizinkan (hanya: ' . implode(', ', array_values($mimeWhitelist)) . ').');
    }
    return $mime;
}

/** Gambar: pastikan benar-benar gambar yang dapat didekode (bukan payload mencurigakan). */
function validate_image(array $file): array
{
    $mime = validate_upload($file, IMAGE_MIME_EXT, UPLOAD_MAX_SIZE);
    $info = @getimagesize($file['tmp_name']);
    if (!$info || ($info[0] ?? 0) < 1 || ($info[1] ?? 0) < 1) {
        throw new UploadException('File bukan gambar yang valid.');
    }
    return ['mime' => $mime, 'ext' => IMAGE_MIME_EXT[$mime], 'w' => (int) $info[0], 'h' => (int) $info[1]];
}

/** Buka gambar ke GD image berdasarkan ekstensi. */
function image_open(string $path, string $ext)
{
    switch ($ext) {
        case 'png':  return @imagecreatefrompng($path);
        case 'webp': return @imagecreatefromwebp($path);
        default:     return @imagecreatefromjpeg($path);
    }
}

/** Simpan GD image sesuai ekstensi (JPEG/PNG/WebP) dengan kualitas terkontrol. */
function image_save($im, string $path, string $ext): void
{
    switch ($ext) {
        case 'png':
            imagealphablending($im, false);
            imagesavealpha($im, true);
            imagepng($im, $path, 6);
            break;
        case 'webp':
            imagealphablending($im, false);
            imagesavealpha($im, true);
            imagewebp($im, $path, IMAGE_WEBP_QUALITY);
            break;
        default:
            imagejpeg($im, $path, IMAGE_JPEG_QUALITY);
    }
}

/** Resize proporsional ke max side; kembalikan GD image baru (atau asli jika kecil). */
function image_resize($im, int $maxSide)
{
    $w = imagesx($im);
    $h = imagesy($im);
    if ($w <= $maxSide && $h <= $maxSide) {
        return $im;
    }
    $ratio = min($maxSide / $w, $maxSide / $h);
    $nw = (int) round($w * $ratio);
    $nh = (int) round($h * $ratio);
    $out = imagecreatetruecolor($nw, $nh);
    if (imageistruecolor($im)) {
        imagesavealpha($out, true);
        imagealphablending($out, false);
        $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefill($out, 0, 0, $transparent);
    }
    imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
    return $out;
}

/**
 * Proses upload gambar: validasi → resize → thumbnail → simpan.
 * Return: ['image' => namaFile, 'thumb' => namaThumb|null] untuk disimpan di DB.
 */
function process_image_upload(array $file, string $subdir, bool $withThumb = true): array
{
    $info = validate_image($file);
    $dir = ensure_upload_dir($subdir);

    $source = $file['tmp_name'];
    $im = image_open($source, $info['ext']);
    if (!$im) {
        throw new UploadException('File gambar tidak dapat diproses.');
    }

    $name = safe_filename($info['ext']);
    $final = $im;
    $thumb = null;

    try {
        if ($info['w'] > IMAGE_FULL_MAX_SIDE || $info['h'] > IMAGE_FULL_MAX_SIDE) {
            $final = image_resize($im, IMAGE_FULL_MAX_SIDE);
        }
        image_save($final, $dir . '/' . $name, $info['ext']);

        if ($withThumb) {
            $thumbName = pathinfo($name, PATHINFO_FILENAME) . '-thumb.' . $info['ext'];
            $thumb = image_resize($im, IMAGE_THUMB_MAX_SIDE);
            image_save($thumb, $dir . '/' . $thumbName, $info['ext']);
        }
    } catch (Throwable $e) {
        @unlink($dir . '/' . ($name ?? ''));
        @unlink($dir . '/' . ($thumbName ?? ''));
        throw new UploadException('Gagal memproses gambar: ' . $e->getMessage());
    } finally {
        if (is_resource($im) || ($im instanceof GdImage)) {
            imagedestroy($im);
        }
    }

    return ['image' => $name, 'thumb' => $thumbName ?? null, 'mime' => $info['mime'], 'size' => (int) $file['size']];
}

/**
 * Proses upload file VIDEO modul Video: validasi whitelist MIME → simpan apa adanya
 * di uploads/videos/.
 */
function process_video_upload(array $file): array
{
    $mime = validate_upload($file, VIDEO_MIME_EXT, UPLOAD_VIDEO_MAX_SIZE);
    $dir = ensure_upload_dir('videos');
    $name = safe_filename(VIDEO_MIME_EXT[$mime], 'video_' . date('Ymd'));
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new UploadException('Video gagal diunggah. Silakan coba lagi.');
    }
    return ['name' => $name, 'mime' => $mime, 'size' => (int) $file['size']];
}

/**
 * Proses upload media (gambar ATAU video) untuk galeri album.
 */
function process_media_upload(array $file, string $subdir, bool $withThumb = true): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && is_string($file['tmp_name'] ?? null) && $file['tmp_name'] !== '' && is_file($file['tmp_name'])) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = strtolower((string) ($finfo->file($file['tmp_name']) ?: ''));
        if (isset(VIDEO_MIME_EXT[$mime])) {
            validate_upload($file, VIDEO_MIME_EXT, UPLOAD_VIDEO_MAX_SIZE);
            $dir = ensure_upload_dir($subdir);
            $name = safe_filename(VIDEO_MIME_EXT[$mime]);
            if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
                throw new UploadException('Gagal menyimpan file video di server.');
            }
            return ['image' => $name, 'thumb' => null, 'type' => 'video'];
        }
    }

    $res = process_image_upload($file, $subdir, $withThumb);
    $res['type'] = 'image';
    return $res;
}

/** Hapus file (dan thumbnail) dari folder upload; abaikan bila tidak ada. */
function delete_uploaded(string $subdir, ?string $file): void
{
    if (!$file || $file === '' || strpos($file, '/') !== false || strpos($file, '\\') !== false) {
        return;
    }
    $paths = [
        UPLOADS_PATH . '/' . $subdir . '/' . $file,
        sys_get_temp_dir() . '/uploads/' . $subdir . '/' . $file,
    ];
    foreach ($paths as $path) {
        if (is_file($path)) {
            @unlink($path);
        }
    }
    $thumb = pathinfo($file, PATHINFO_FILENAME) . '-thumb.' . pathinfo($file, PATHINFO_EXTENSION);
    foreach ([UPLOADS_PATH . '/' . $subdir . '/' . $thumb, sys_get_temp_dir() . '/uploads/' . $subdir . '/' . $thumb] as $thumbPath) {
        if (is_file($thumbPath)) {
            @unlink($thumbPath);
        }
    }
}

/** Proses upload dokumen (PDF/Word/Excel/ZIP) untuk CMS Dokumen. */
function process_document_upload(array $file, string $subdir): array
{
    $allowed = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/zip' => 'zip',
        'application/vnd.rar' => 'rar',
        'application/octet-stream' => 'zip',
    ];
    $mime = validate_upload($file, $allowed, 10 * 1024 * 1024);
    $dir = ensure_upload_dir($subdir);
    $name = safe_filename($allowed[$mime]);
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new UploadException('Gagal menyimpan file di server.');
    }
    return ['name' => $name, 'mime' => $mime, 'size' => (int) $file['size']];
}
