<?php
/**
 * Konfigurasi koneksi database.
 *
 * FILE INI BERISI KREDENSIAL DAN TIDAK BOLEH DI-COMMIT KE REPOSITORI PUBLIK.
 * Untuk salinan contoh tanpa kredensial, gunakan database.php.example.
 *
 * Mendukung overriding via environment variables (Vercel/CI):
 *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, DB_CHARSET
 */

define('DB_DRIVER',   getenv('DB_DRIVER')   ?: 'mysql');
define('DB_HOST',     getenv('DB_HOST')     ?: '127.0.0.1');
define('DB_PORT',     getenv('DB_PORT')     ?: (DB_DRIVER === 'pgsql' ? '5432' : '3306'));
define('DB_NAME',     getenv('DB_NAME')     ?: (DB_DRIVER === 'pgsql' ? 'postgres' : 'diskominfo_pkl'));
define('DB_USER',     getenv('DB_USER')     ?: (DB_DRIVER === 'pgsql' ? 'postgres' : 'diskominfo_pkl'));
define('DB_PASS',     getenv('DB_PASS')     ?: '2AwRrVkhL6swgK9ut4Dd');
define('DB_CHARSET',  getenv('DB_CHARSET')  ?: 'utf8mb4');
