<?php
/**
 * Debug — cek isi tabel albums langsung dari database.
 * HAPUS file ini setelah debugging selesai!
 *
 * Akses via: https://webdiskominfo.vercel.app/?page=debug
 * (index.php sudah include semua bootstrap sebelum memanggil file ini)
 */

$pageTitle = 'Debug Albums';
?>
<pre style="font-family:monospace;padding:20px;background:#111;color:#0f0;max-width:1000px;margin:30px auto;border-radius:8px;overflow:auto;">
<?php
echo "=== DATABASE ALBUMS DEBUG ===\n\n";

try {
    echo "Koneksi DB: OK\n\n";

    // Semua albums
    $rows = q_all('SELECT id, title, slug, status, event_date, cover_image, created_at FROM albums ORDER BY created_at DESC');
    echo "Total albums di database: " . count($rows) . "\n\n";

    foreach ($rows as $r) {
        echo "ID        : " . $r['id'] . "\n";
        echo "Judul     : " . $r['title'] . "\n";
        echo "Slug      : " . $r['slug'] . "\n";
        echo "Status    : " . $r['status'] . "\n";
        echo "Event Date: " . ($r['event_date'] ?? 'NULL') . "\n";
        echo "Cover     : " . ($r['cover_image'] ?? 'NULL') . "\n";
        echo "Created   : " . $r['created_at'] . "\n";
        echo str_repeat('-', 40) . "\n";
    }

    echo "\n=== PUBLISHED ALBUMS ===\n";
    $pub = q_all("SELECT id, title, status FROM albums WHERE status = 'published'");
    echo "Jumlah published: " . count($pub) . "\n";
    foreach ($pub as $p) {
        echo " - [{$p['id']}] {$p['title']} (status={$p['status']})\n";
    }

    echo "\n=== KOLOM TABEL albums ===\n";
    $cols = q_all("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'albums' AND table_schema = 'public' ORDER BY ordinal_position");
    foreach ($cols as $c) {
        echo " - {$c['column_name']} ({$c['data_type']})\n";
    }

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== UPLOADS_PATH ===\n";
echo UPLOADS_PATH . "\n";
echo "Writable : " . (is_writable(UPLOADS_PATH) ? 'YES' : 'NO') . "\n";
?>
</pre>
