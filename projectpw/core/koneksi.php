<?php
/**
 * Koneksi database (MySQLi).
 *
 * Satu-satunya tempat koneksi dibuat. Kredensial dibaca dari core/config.php,
 * dan dapat diganti per lingkungan lewat core/config.local.php (tidak masuk Git).
 * Contoh isi ada di core/config.example.php.
 *
 * File ini hanya menyiapkan objek $connection (juga tersedia lewat db()).
 * Tidak ada output HTML lain kecuali halaman error umum jika koneksi gagal.
 */

require_once __DIR__ . '/helpers.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $cfg = config('db');
    $connection = new mysqli(
        (string) $cfg['host'],
        (string) $cfg['user'],
        (string) $cfg['pass'],
        (string) $cfg['name'],
        (int) $cfg['port']
    );
    $connection->set_charset('utf8mb4');
} catch (Throwable $e) {
    // Detail teknis hanya ke log server, bukan ke pengunjung.
    error_log('[Lipalas] Koneksi database gagal: ' . $e->getMessage());
    render_error_page(
        503,
        'Layanan sementara tidak tersedia',
        'Kami tidak dapat terhubung ke database. Silakan coba lagi beberapa saat lagi.',
        is_dev() ? $e->getMessage() : null
    );
}

$GLOBALS['connection'] = $connection;
