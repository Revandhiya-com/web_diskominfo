<?php
/**
 * Bridge route untuk Login Admin — dipanggil oleh router utama via ?page=login
 * BASE_PATH sudah didefinisikan oleh index.php / api/index.php
 */
if (!defined('ADMIN_PUBLIC')) {
    define('ADMIN_PUBLIC', true);
}

ob_end_clean();

$loginFile = BASE_PATH . '/admin/login.php';
if (is_file($loginFile)) {
    require $loginFile;
    exit;
} else {
    http_response_code(500);
    echo '<div style="font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.1);">';
    echo '<h2 style="color:#d9534f">Konfigurasi Server Bermasalah</h2>';
    echo '<p>File <code>admin/login.php</code> tidak ditemukan di path: <code>' . htmlspecialchars($loginFile, ENT_QUOTES, 'UTF-8') . '</code></p>';
    echo '</div>';
    exit;
}
