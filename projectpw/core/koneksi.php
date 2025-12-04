<?php

// --- 1. DEFINISI KONSTANTA KONFIGURASI ---
// Menggunakan konstanta (define) agar nilainya tidak bisa diubah
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'project');

// --- 2. LOGIKA KONEKSI ---

// Non-aktifkan pelaporan error internal, kita akan tangani sendiri
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Membuat koneksi baru
    $connection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    // Set charset ke UTF8MB4 (untuk dukungan emoji dan karakter internasional yang lebih luas)
    $connection->set_charset("utf8mb4");

} catch (Exception $e) {
    // Jika ada exception (misalnya, salah nama database), tampilkan error.
    
    // Untuk pengembangan (development): tampilkan pesan error detail
    // echo "Koneksi database gagal: " . $e->getMessage();
    
    // Untuk produksi (production): tampilkan pesan error umum saja
    http_response_code(500); // Mengatur status HTTP
    die("Error: Koneksi ke database gagal.");
}

// Opsional: jika Anda ingin memastikan koneksi berhasil
// if ($connection) {
//     echo "berhasil konek";
// }

// File ini HANYA menghasilkan objek $connection. Tidak ada output HTML/teks lain.