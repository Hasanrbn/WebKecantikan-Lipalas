<?php
/**
 * Titik masuk untuk Vercel (runtime vercel-php).
 *
 * Vercel hanya menjalankan berkas di folder api/, sedangkan aplikasi ini terdiri dari banyak
 * halaman PHP di projectpw/. Berkas ini menerima semua permintaan (lihat vercel.json), memilih
 * halaman yang sesuai dari DAFTAR PUTIH di bawah, lalu menjalankannya. Berkas lain (core/, includes/,
 * tools/, dst.) tidak pernah bisa dipanggil langsung dari browser.
 *
 * Di XAMPP / hosting Apache berkas ini TIDAK dipakai.
 */

// Bawaan untuk Vercel; dapat ditimpa lewat Environment Variables di dashboard Vercel.
$defaults = [
    'LIPALAS_BASE_PATH'   => '/',
    'LIPALAS_SESSION'     => 'db',
    'LIPALAS_TRUST_PROXY' => '1',
    'LIPALAS_ENV'         => 'production',
    'DB_SSL'              => '1',
];
foreach ($defaults as $key => $value) {
    if (getenv($key) === false) {
        putenv($key . '=' . $value);
    }
}

$root = dirname(__DIR__) . '/projectpw';

$pages = [
    ''                           => 'index.php',
    'index.php'                  => 'index.php',
    'auth/login.php'             => 'auth/login.php',
    'auth/register.php'          => 'auth/register.php',
    'auth/lupapw.php'            => 'auth/lupapw.php',
    'auth/logout.php'            => 'auth/logout.php',
    'user/dashboard.php'         => 'user/dashboard.php',
    'user/booking_process.php'   => 'user/booking_process.php',
    'admin/dashboard.php'        => 'admin/dashboard.php',
    'admin/booking_edit.php'     => 'admin/booking_edit.php',
    'admin/booking_delete.php'   => 'admin/booking_delete.php',
];

$path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$key  = trim(rawurldecode($path), '/');

if (!isset($pages[$key])) {
    require_once $root . '/core/helpers.php';
    render_error_page(404, 'Halaman tidak ditemukan', 'Alamat yang Anda buka tidak ada atau sudah dipindahkan.');
}

$script = $root . '/' . $pages[$key];
chdir(dirname($script));
$_SERVER['SCRIPT_FILENAME'] = $script;
$_SERVER['SCRIPT_NAME']     = '/' . $pages[$key];
$_SERVER['PHP_SELF']        = '/' . $pages[$key];

require $script;
