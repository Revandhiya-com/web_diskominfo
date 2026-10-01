<?php
/**
 * Debug + Quick Fix: publish semua album yang masih draft.
 * Akses via: https://webdiskominfo.vercel.app/?page=debug
 * HAPUS setelah selesai!
 */
$pageTitle = 'Debug Fix Albums';

// Jika ada POST action untuk publish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'publish_all') {
        try {
            $stmt = db()->prepare("UPDATE albums SET status = 'published' WHERE status = 'draft'");
            $stmt->execute();
            $affected = $stmt->rowCount();
            $fixMsg = "✅ Berhasil! $affected album diubah ke published.";
        } catch (Throwable $e) {
            $fixMsg = "❌ Error: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'publish_one' && isset($_POST['id'])) {
        $albumId = (int) $_POST['id'];
        try {
            $stmt = db()->prepare("UPDATE albums SET status = 'published' WHERE id = ?");
            $stmt->execute([$albumId]);
            $fixMsg = "✅ Album ID=$albumId berhasil dipublish!";
        } catch (Throwable $e) {
            $fixMsg = "❌ Error: " . $e->getMessage();
        }
    }
}

$allAlbums = q_all("SELECT id, title, status, event_date FROM albums ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Debug Fix Albums</title>
<style>
body{font-family:monospace;background:#111;color:#0f0;padding:20px}
.box{max-width:700px;margin:0 auto;background:#1a1a1a;border-radius:8px;padding:24px}
h2{color:#ff0;margin-top:0}
table{width:100%;border-collapse:collapse;margin:16px 0}
th,td{padding:8px 12px;border:1px solid #333;text-align:left}
th{background:#222;color:#ff0}
.draft{color:#f66}
.published{color:#0f0}
.btn{display:inline-block;padding:10px 20px;background:#ff0;color:#000;border:none;border-radius:4px;cursor:pointer;font-weight:bold;font-size:14px;text-decoration:none;margin:4px}
.btn-sm{padding:6px 12px;font-size:12px}
.msg{padding:12px;border-radius:4px;margin-bottom:16px;background:#0a2a0a;border:1px solid #0f0;font-size:15px}
</style>
</head>
<body>
<div class="box">
<h2>🔧 Debug & Fix Album Status</h2>

<?php if (!empty($fixMsg)): ?>
<div class="msg"><?= htmlspecialchars($fixMsg) ?></div>
<?php endif; ?>

<h3>Album di Database (<?= count($allAlbums) ?> total)</h3>
<table>
<tr><th>ID</th><th>Judul</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
<?php foreach ($allAlbums as $a): ?>
<tr>
    <td><?= $a['id'] ?></td>
    <td><?= htmlspecialchars($a['title']) ?></td>
    <td class="<?= $a['status'] ?>"><?= strtoupper($a['status']) ?></td>
    <td><?= $a['event_date'] ?? '-' ?></td>
    <td>
        <?php if ($a['status'] !== 'published'): ?>
        <form method="post" style="display:inline">
            <input type="hidden" name="action" value="publish_one">
            <input type="hidden" name="id" value="<?= $a['id'] ?>">
            <button class="btn btn-sm" type="submit">▶ Publish</button>
        </form>
        <?php else: ?>
        <span style="color:#0f0">✓ Published</span>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>

<?php if (count(array_filter($allAlbums, fn($a) => $a['status'] === 'draft')) > 0): ?>
<form method="post">
    <input type="hidden" name="action" value="publish_all">
    <button class="btn" type="submit">⚡ Publish SEMUA Album Sekarang</button>
</form>
<?php endif; ?>

<br>
<a class="btn" href="<?= htmlspecialchars((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/') ?>">🏠 Lihat Halaman Utama</a>
</div>
</body>
</html>
