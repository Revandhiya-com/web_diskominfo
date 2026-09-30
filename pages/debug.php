<?php
/**
 * Debug tool — cari di mana file admin sebenarnya berada di Vercel Lambda
 * Hapus file ini setelah debugging selesai!
 */
echo '<pre style="font-family:monospace;padding:20px;background:#111;color:#0f0;max-width:900px;margin:30px auto;border-radius:8px;overflow:auto;">';
echo "=== VERCEL LAMBDA PATH DEBUGGER ===\n\n";

echo "__DIR__      : " . __DIR__ . "\n";
echo "__FILE__     : " . __FILE__ . "\n";
echo "dirname(__DIR__) : " . dirname(__DIR__) . "\n";
echo "dirname(dirname(__DIR__)) : " . dirname(dirname(__DIR__)) . "\n\n";

// Cek berbagai kemungkinan lokasi admin/login.php
$candidates = [
    __DIR__ . '/admin/login.php',
    dirname(__DIR__) . '/admin/login.php',
    dirname(dirname(__DIR__)) . '/admin/login.php',
    '/var/task/admin/login.php',
    '/var/task/user/admin/login.php',
    '/var/task/api/../admin/login.php',
];

echo "=== CANDIDATE PATHS ===\n";
foreach ($candidates as $path) {
    $real = realpath($path);
    echo ($real ? '✓ FOUND' : '✗ missing') . " : $path\n";
    if ($real) echo "  realpath: $real\n";
}

echo "\n=== DIRECTORY LISTING /var/task ===\n";
if (is_dir('/var/task')) {
    foreach (scandir('/var/task') as $f) {
        if ($f === '.' || $f === '..') continue;
        $type = is_dir('/var/task/' . $f) ? '[DIR]' : '[file]';
        echo "$type $f\n";
    }
}

echo "\n=== DIRECTORY LISTING /var/task/user (if exists) ===\n";
if (is_dir('/var/task/user')) {
    foreach (scandir('/var/task/user') as $f) {
        if ($f === '.' || $f === '..') continue;
        $type = is_dir('/var/task/user/' . $f) ? '[DIR]' : '[file]';
        echo "$type $f\n";
    }
}

echo "\n=== __DIR__ LISTING ===\n";
if (is_dir(dirname(__DIR__))) {
    foreach (scandir(dirname(__DIR__)) as $f) {
        if ($f === '.' || $f === '..') continue;
        $type = is_dir(dirname(__DIR__) . '/' . $f) ? '[DIR]' : '[file]';
        echo "$type $f\n";
    }
}

echo '</pre>';
