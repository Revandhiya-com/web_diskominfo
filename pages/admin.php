<?php
/**
 * Bridge route untuk Halaman Admin Vercel
 */
if (!defined('ADMIN_PUBLIC')) {
    define('ADMIN_PUBLIC', true);
}

$loginFile = dirname(__DIR__) . '/admin/login.php';
if (is_file($loginFile)) {
    require $loginFile;
    exit;
} else {
    echo '<div style="font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;">';
    echo '<h2>File Admin Login Tidak Ditemukan</h2>';
    echo '<p>Path: ' . htmlspecialchars($loginFile, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</div>';
    exit;
}
