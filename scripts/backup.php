<?php
/**
 * Script backup: dump database + arsip folder uploads ke folder backup/.
 *
 * Penggunaan (jalankan via CLI):
 *   php backup.php                 -> simpan ke ../backup/ (atau BACKUP_DIR)
 *   php backup.php C:\path\backup  -> simpan ke folder tujuan
 *
 * Jadwalkan di cron / Task Scheduler untuk backup berkala, misalnya:
 *   Linux  : 30 2 * * * php /var/www/diskominfo-pkl/scripts/backup.php /var/backup/diskominfo-pkl
 *   Windows: schtasks /create /tn "Backup Diskominfo PKL" /tr "php C:\xampp\htdocs\diskominfo-pkl\scripts\backup.php C:\backup\diskominfo-pkl" /sc daily /st 02:30
 *
 * File backup tidak boleh berada di dalam web root yang terpublikasi.
 */

$backupDir = isset($argv[1]) ? rtrim($argv[1], '/\\') : (__DIR__ . '/../backup');

if (!is_dir($backupDir) && !@mkdir($backupDir, 0755, true)) {
    fwrite(STDERR, 'Gagal membuat folder backup: ' . $backupDir . PHP_EOL);
    exit(1);
}

$projectDir = dirname(__DIR__);
$configFile = $projectDir . '/config/database.php';

if (!is_file($configFile)) {
    fwrite(STDERR, 'config/database.php tidak ditemukan.' . PHP_EOL);
    exit(1);
}
require $configFile;

$date = date('Ymd-His');
$dumpFile = $backupDir . '/db-' . $date . '.sql';
$zipFile  = $backupDir . '/uploads-' . $date . '.zip';

/* 1) Dump database */
$mysqldump = 'mysqldump';
if (PHP_OS_FAMILY === 'Windows') {
    $candidates = [
        'C:/xampp/mysql/bin/mysqldump.exe',
        'D:/xampp/mysql/bin/mysqldump.exe',
        'C:/laragon/bin/mysql/mysqldump.exe',
    ];
    foreach ($candidates as $c) {
        if (is_file($c)) {
            $mysqldump = '"' . $c . '"';
            break;
        }
    }
}
$cmd = sprintf(
    '%s --user=%s --password=%s --host=%s --port=%d %s --routines --triggers > "%s"',
    $mysqldump,
    escapeshellarg(DB_USER),
    escapeshellarg(DB_PASS),
    escapeshellarg(DB_HOST),
    (int) DB_PORT,
    escapeshellarg(DB_NAME),
    $dumpFile
);
passthru($cmd, $code);
if ($code !== 0 || !is_file($dumpFile) || filesize($dumpFile) === 0) {
    fwrite(STDERR, 'Backup database GAGAL. Periksa kredensial dan mysqldump.' . PHP_EOL);
    exit(1);
}
echo 'Database  : ' . $dumpFile . ' (' . number_format(filesize($dumpFile)) . ' bytes)' . PHP_EOL;

/* 2) Arsip folder uploads */
if (class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($projectDir . '/uploads', FilesystemIterator::SKIP_DOTS)
        );
        foreach ($files as $file) {
            if ($file->isFile()) {
                $rel = substr($file->getPathname(), strlen($projectDir) + 1);
                $zip->addFile($file->getPathname(), str_replace('\\', '/', $rel));
            }
        }
        $zip->close();
        echo 'Uploads   : ' . $zipFile . ' (' . number_format(filesize($zipFile)) . ' bytes)' . PHP_EOL;
    } else {
        fwrite(STDERR, 'Gagal membuat arsip uploads (ZipArchive). Database tetap berhasil.' . PHP_EOL);
    }
} else {
    echo 'Peringatan: ekstensi ZipArchive tidak tersedia — folder uploads TIDAK diarsipkan.' . PHP_EOL;
    echo '           Salin folder uploads secara manual.' . PHP_EOL;
}

/* 3) Pruning: hapus backup lebih dari 30 hari */
$retention = 30 * 86400;
$deleted = 0;
foreach (glob($backupDir . '/db-*.sql') ?: [] as $old) {
    if (filemtime($old) < time() - $retention) {
        unlink($old);
        $deleted++;
    }
}
foreach (glob($backupDir . '/uploads-*.zip') ?: [] as $old) {
    if (filemtime($old) < time() - $retention) {
        unlink($old);
        $deleted++;
    }
}
if ($deleted > 0) {
    echo 'Pruning   : ' . $deleted . ' file lama dihapus (retensi 30 hari).' . PHP_EOL;
}

echo 'Selesai. Folder backup: ' . $backupDir . PHP_EOL;