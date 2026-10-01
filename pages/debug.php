<?php
/**
 * Debug — test query home page album.
 * Akses via: https://webdiskominfo.vercel.app/?page=debug
 */
$pageTitle = 'Debug Albums';

// Test query persis yang dipakai home.php
$latestAlbums = q_all(
    "SELECT a.id, a.title, a.slug, a.status, a.event_date,
            a.cover_image, c.name AS category_name,
            (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id) AS photo_count
     FROM albums a
     LEFT JOIN album_categories c ON c.id = a.category_id
     WHERE a.status = 'published'
     ORDER BY a.created_at DESC
     LIMIT 6"
);

// Test query langsung tanpa JOIN
$rawAlbums = q_all("SELECT id, title, status FROM albums");
$pubAlbums = q_all("SELECT id, title, status FROM albums WHERE status = 'published'");
?>
<pre style="font-family:monospace;padding:20px;background:#111;color:#0f0;max-width:1000px;margin:30px auto;border-radius:8px;overflow:auto;">
=== DIAGNOSA ALBUM HOME PAGE ===

--- Test 1: Semua albums (tanpa filter) ---
Hasil: <?= count($rawAlbums) ?> baris
<?php foreach ($rawAlbums as $r): ?>
  [<?= $r['id'] ?>] "<?= $r['title'] ?>" status=<?= $r['status'] ?>
<?php endforeach; ?>

--- Test 2: Albums WHERE status='published' ---
Hasil: <?= count($pubAlbums) ?> baris
<?php foreach ($pubAlbums as $r): ?>
  [<?= $r['id'] ?>] "<?= $r['title'] ?>" status=<?= $r['status'] ?>
<?php endforeach; ?>

--- Test 3: Query lengkap sama seperti home.php ---
Hasil: <?= count($latestAlbums) ?> baris
<?php foreach ($latestAlbums as $a): ?>
  [<?= $a['id'] ?>] "<?= $a['title'] ?>" | status=<?= $a['status'] ?> | event=<?= $a['event_date'] ?> | photos=<?= $a['photo_count'] ?>
<?php endforeach; ?>

--- Test 4: Cek konstanta dan versi ---
PHP Version  : <?= PHP_VERSION ?>

PDO drivers  : <?= implode(', ', PDO::getAvailableDrivers()) ?>

DB_DRIVER    : <?= DB_DRIVER ?>

UPLOADS_PATH : <?= UPLOADS_PATH ?>

--- Test 5: Cek apakah query exception tertangkap ---
<?php
try {
    $test = db()->query("SELECT id, title, status FROM albums WHERE status = 'published'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Direct PDO query: " . count($test) . " baris\n";
    foreach ($test as $t) {
        echo "  [{$t['id']}] {$t['title']} ({$t['status']})\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
</pre>
