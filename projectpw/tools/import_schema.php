<?php
/**
 * Impor skema (database/schema.sql) ke database yang dipilih lewat konfigurasi / environment variable.
 * Berguna untuk database cloud (mis. Aiven) yang tidak punya phpMyAdmin dan tidak mengizinkan
 * CREATE DATABASE: perintah CREATE DATABASE dan USE di dalam schema.sql dilewati otomatis.
 *
 * Pakai (Windows XAMPP, di Command Prompt):
 *   set DB_HOST=xxx.aivencloud.com
 *   set DB_PORT=12345
 *   set DB_USER=avnadmin
 *   set DB_PASS=...
 *   set DB_NAME=defaultdb
 *   set DB_SSL=1
 *   C:\xampp\php\php.exe tools\import_schema.php
 *
 * Aman dijalankan berulang kali (semua tabel memakai IF NOT EXISTS). Tidak menghapus data.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../core/helpers.php';

$sql = file_get_contents(__DIR__ . '/../database/schema.sql');
if ($sql === false) {
    fwrite(STDERR, "schema.sql tidak ditemukan.\n");
    exit(1);
}
// Lewati CREATE DATABASE ... ; dan USE ... ;
$sql = preg_replace('/^\s*CREATE DATABASE[^;]*;/mi', '', $sql);
$sql = preg_replace('/^\s*USE\s+[^;]*;/mi', '', $sql);

$cfg = config('db');
echo 'Menghubungkan ke ' . $cfg['host'] . ':' . $cfg['port'] . ' / ' . $cfg['name'] . " ...\n";
$db = db();
mysqli_report(MYSQLI_REPORT_OFF);

if (!$db->multi_query($sql)) {
    fwrite(STDERR, 'Gagal: ' . $db->error . "\n");
    exit(1);
}
$count = 0;
do {
    $count++;
    if ($result = $db->store_result()) {
        $result->free();
    }
} while ($db->more_results() && $db->next_result());
if ($db->errno) {
    fwrite(STDERR, 'Gagal pada perintah ke-' . $count . ': ' . $db->error . "\n");
    exit(1);
}
echo "Selesai. Tabel: ";
$res = $db->query('SHOW TABLES');
$names = [];
while ($row = $res->fetch_row()) { $names[] = $row[0]; }
echo implode(', ', $names) . "\n";
