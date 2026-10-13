<?php
/**
 * Fungsi bantu bersama untuk Lipalas Beauty Clinic.
 *
 * Isi file ini:
 *  - konfigurasi (config.php + config.local.php opsional)
 *  - session yang aman, flash message, CSRF
 *  - guard login/admin
 *  - rate limit sederhana berbasis database
 *  - validasi dan format data
 *
 * File ini aman di-include berkali-kali.
 */

if (defined('LIPALAS_HELPERS_LOADED')) {
    return;
}
define('LIPALAS_HELPERS_LOADED', true);
define('APP_ROOT', dirname(__DIR__));
define('APP_NAME', 'Lipalas Beauty Clinic');

require_once __DIR__ . '/icons.php';

/* ------------------------------------------------------------------ *
 * KONFIGURASI
 * ------------------------------------------------------------------ */

/**
 * Ambil nilai konfigurasi. Contoh: config('db.host'), config('env').
 */
function config(?string $key = null, $default = null)
{
    static $cfg = null;

    if ($cfg === null) {
        $cfg = [
            'env'       => 'production',   // 'development' | 'production'
            'timezone'  => 'Asia/Jakarta',
            'base_path' => null,           // null = terdeteksi otomatis, contoh manual: '/projectpw'
            'db'        => [
                'host' => 'localhost',
                'port' => 3306,
                'user' => 'root',
                'pass' => '',
                'name' => 'project',
                'ssl'  => false,           // true = koneksi terenkripsi (wajib di banyak database cloud)
            ],
            'session'     => 'file',       // 'file' (bawaan PHP) | 'db' (tabel php_session; untuk hosting serverless)
            'trust_proxy' => false,        // true hanya jika di belakang proxy tepercaya (mis. Vercel)
        ];

        $local = __DIR__ . '/config.local.php';
        if (is_file($local)) {
            $override = require $local;
            if (is_array($override)) {
                $cfg = array_replace_recursive($cfg, $override);
            }
        }

        // Environment variable menimpa semuanya (dipakai di hosting tanpa berkas config.local.php, mis. Vercel).
        $env = static function (string $name) {
            $v = getenv($name);
            return ($v === false || $v === '') ? null : $v;
        };
        $map = [
            'LIPALAS_ENV'      => ['env'],
            'LIPALAS_TIMEZONE' => ['timezone'],
            'LIPALAS_BASE_PATH' => ['base_path'],
            'LIPALAS_SESSION'  => ['session'],
            'DB_HOST'          => ['db', 'host'],
            'DB_PORT'          => ['db', 'port'],
            'DB_USER'          => ['db', 'user'],
            'DB_PASS'          => ['db', 'pass'],
            'DB_NAME'          => ['db', 'name'],
        ];
        foreach ($map as $var => $path) {
            if (($v = $env($var)) !== null) {
                if (count($path) === 1) { $cfg[$path[0]] = $v; } else { $cfg[$path[0]][$path[1]] = $v; }
            }
        }
        if (($v = $env('DB_SSL')) !== null) {
            $cfg['db']['ssl'] = in_array(strtolower($v), ['1', 'true', 'yes', 'on'], true);
        }
        if (($v = $env('LIPALAS_TRUST_PROXY')) !== null) {
            $cfg['trust_proxy'] = in_array(strtolower($v), ['1', 'true', 'yes', 'on'], true);
        }
    }

    if ($key === null) {
        return $cfg;
    }

    $value = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function is_dev(): bool
{
    return config('env') === 'development';
}

error_reporting(E_ALL);
ini_set('display_errors', is_dev() ? '1' : '0');
ini_set('log_errors', '1');
date_default_timezone_set((string) config('timezone', 'Asia/Jakarta'));

/* ------------------------------------------------------------------ *
 * SESSION
 * ------------------------------------------------------------------ */

function request_is_https(): bool
{
    if (config('trust_proxy') && strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
        return true;
    }
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
}

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    if (config('session') === 'db') {
        require_once __DIR__ . '/session_db.php';
        session_set_save_handler(new LipalasDbSessionHandler(), true);
    }
    session_name('LIPALASSESS');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => request_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Sesi kedaluwarsa setelah 2 jam tanpa aktivitas.
    $idleLimit = 2 * 60 * 60;
    if (isset($_SESSION['last_activity']) && (time() - (int) $_SESSION['last_activity']) > $idleLimit) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['last_activity'] = time();
}

/* ------------------------------------------------------------------ *
 * URL, OUTPUT, REDIRECT
 * ------------------------------------------------------------------ */

/** Awalan URL aplikasi, misalnya "/projectpw/" (XAMPP) atau "/" (domain sendiri). */
function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $manual = config('base_path');
    if (is_string($manual)) {
        $trimmed = trim($manual, '/');
        return $base = ($trimmed === '' ? '/' : '/' . $trimmed . '/');
    }

    $rel = '';
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
    $appRoot = realpath(APP_ROOT);
    if ($docRoot !== false && $appRoot !== false) {
        $doc = str_replace('\\', '/', $docRoot);
        $app = str_replace('\\', '/', $appRoot);
        if (stripos($app, $doc) === 0) {
            $rel = trim(substr($app, strlen($doc)), '/');
        }
    }
    return $base = ($rel === '' ? '/' : '/' . $rel . '/');
}

function url(string $path = ''): string
{
    return base_path() . ltrim($path, '/');
}

/** URL aset statis dengan cache-busting berdasarkan waktu ubah file. */
function asset(string $path): string
{
    $file = APP_ROOT . '/aset/' . ltrim($path, '/');
    $v = is_file($file) ? (string) filemtime($file) : '1';
    return url('aset/' . ltrim($path, '/')) . '?v=' . $v;
}

function absolute_url(string $path = ''): string
{
    $scheme = request_is_https() ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    // Header Host dikirim klien; tolak nilai yang bentuknya aneh agar tidak masuk ke meta/JSON-LD.
    if (!preg_match('/^[A-Za-z0-9.\-]+(:[0-9]{1,5})?$/', $host)) {
        $host = 'localhost';
    }
    return $scheme . '://' . $host . url($path);
}

/** Escape untuk konteks HTML (teks dan atribut). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $to, int $code = 302): void
{
    $to = str_replace(["\r", "\n"], '', $to);
    header('Location: ' . $to, true, $code);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

/* ------------------------------------------------------------------ *
 * FLASH MESSAGE & OLD INPUT
 * ------------------------------------------------------------------ */

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $message];
}

/** Render semua flash message lalu hapus dari session. */
function flash_render(): string
{
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return '';
    }
    $icons = [
        'success' => 'circle-check',
        'danger'  => 'circle-alert',
        'warning' => 'triangle-alert',
        'info'    => 'info',
    ];
    $html = '';
    foreach ($_SESSION['flash'] as $f) {
        $type = isset($icons[$f['type']]) ? $f['type'] : 'info';
        $role = $type === 'danger' ? 'alert' : 'status';
        $html .= '<div class="notice notice--' . $type . '" role="' . $role . '">'
            . icon($icons[$type])
            . '<p>' . e($f['msg']) . '</p></div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

function old_set(array $values): void
{
    $_SESSION['old'] = $values;
}

/** Ambil nilai input lama (sekali pakai per request). */
function old(string $key, string $default = ''): string
{
    static $old = null;
    if ($old === null) {
        $old = isset($_SESSION['old']) && is_array($_SESSION['old']) ? $_SESSION['old'] : [];
        unset($_SESSION['old']);
    }
    return isset($old[$key]) ? (string) $old[$key] : $default;
}

/* ------------------------------------------------------------------ *
 * CSRF
 * ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && $sent !== '' && hash_equals(csrf_token(), $sent);
}

/** Hentikan request POST jika token CSRF salah. */
function require_csrf(string $redirectTo): void
{
    if (!csrf_valid()) {
        flash_set('danger', 'Sesi formulir tidak valid atau sudah kedaluwarsa. Silakan coba lagi.');
        redirect($redirectTo);
    }
}

/* ------------------------------------------------------------------ *
 * DATABASE
 * ------------------------------------------------------------------ */

/** Koneksi MySQLi bersama (dibuat saat pertama dibutuhkan). */
function db(): mysqli
{
    if (!isset($GLOBALS['connection']) || !($GLOBALS['connection'] instanceof mysqli)) {
        require_once __DIR__ . '/koneksi.php';
    }
    return $GLOBALS['connection'];
}

/** Apakah schema terbaru (kolom status + tabel rate_limit_log) sudah terpasang? */
function schema_ready(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    try {
        $db = db();
        $col = $db->query("SHOW COLUMNS FROM table_booking LIKE 'status'");
        $tbl = $db->query("SHOW TABLES LIKE 'rate_limit_log'");
        $ready = $col && $col->num_rows > 0 && $tbl && $tbl->num_rows > 0;
    } catch (Throwable $t) {
        error_log('[Lipalas] schema_ready: ' . $t->getMessage());
        $ready = false;
    }
    return $ready;
}

/* ------------------------------------------------------------------ *
 * AUTENTIKASI & OTORISASI
 * ------------------------------------------------------------------ */

/**
 * Pengguna yang sedang login, dibaca ulang dari database (bukan dari session)
 * sehingga perubahan role atau penghapusan akun langsung berlaku.
 */
function current_user(): ?array
{
    static $loaded = false;
    static $user = null;
    if ($loaded) {
        return $user;
    }
    $loaded = true;

    $id = $_SESSION['user_id'] ?? null;
    if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
        return $user = null;
    }

    $stmt = db()->prepare('SELECT id, nama, email, role FROM user WHERE id = ? LIMIT 1');
    $uid = (int) $id;
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        unset($_SESSION['user_id'], $_SESSION['role']);
        return $user = null;
    }
    $_SESSION['role'] = $row['role'];
    return $user = $row;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin_session(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function role_home(?string $role): string
{
    return $role === 'admin' ? url('admin/dashboard.php') : url('user/dashboard.php');
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?: 'Lax',
        ]);
    }
    session_destroy();

    // Sesi baru hanya untuk membawa pesan "berhasil keluar".
    session_start();
    session_regenerate_id(true);
}

/**
 * Apakah $target aman dipakai sebagai tujuan redirect internal?
 * Harus path di dalam aplikasi ini: diawali base path, tanpa skema/host, tanpa "//" atau "\".
 */
function is_safe_internal_url(string $target): bool
{
    $base = base_path();
    return $target !== ''
        && strncmp($target, $base, strlen($base)) === 0
        && strpos($target, '//') === false
        && strpos($target, '\\') === false
        && !preg_match('/[\x00-\x1F\x7F]/', $target);
}

/** Halaman yang hanya boleh dibuka pengguna yang sudah login. */
function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        // Ingat halaman tujuan (hanya GET) agar setelah login pengguna kembali ke sana,
        // misalnya formulir booking dengan layanan yang sudah dipilih.
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
            $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
            if (is_safe_internal_url($uri)) {
                $_SESSION['intended'] = $uri;
            }
        }
        flash_set('warning', 'Silakan masuk terlebih dahulu untuk melanjutkan.');
        redirect(url('auth/login.php'));
    }
    return $user;
}

/** Ambil (sekali pakai) tujuan yang diingat require_login(), atau $fallback. */
function intended_url(string $fallback): string
{
    $target = (string) ($_SESSION['intended'] ?? '');
    unset($_SESSION['intended']);
    return is_safe_internal_url($target) ? $target : $fallback;
}

/** Halaman yang hanya boleh dibuka admin (role dicek dari database). */
function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        flash_set('danger', 'Akses ditolak. Halaman tersebut hanya untuk admin.');
        redirect(url('user/dashboard.php'));
    }
    return $user;
}

/** Halaman login/daftar: pengguna yang sudah masuk diarahkan ke dashboard-nya. */
function guest_only(): void
{
    if (is_logged_in()) {
        $user = current_user();
        if ($user !== null) {
            redirect(role_home($user['role']));
        }
    }
}

/* ------------------------------------------------------------------ *
 * RATE LIMIT (tabel rate_limit_log)
 * ------------------------------------------------------------------ */

function client_ip(): string
{
    // Bawaan: hanya REMOTE_ADDR, karena header X-Forwarded-For mudah dipalsukan.
    // Header proxy dipercaya HANYA bila trust_proxy aktif (mis. di Vercel, yang menimpa header ini sendiri).
    if (config('trust_proxy')) {
        foreach (['HTTP_X_VERCEL_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR'] as $h) {
            $raw = trim(explode(',', (string) ($_SERVER[$h] ?? ''))[0]);
            if ($raw !== '' && filter_var($raw, FILTER_VALIDATE_IP)) {
                return substr($raw, 0, 45);
            }
        }
    }
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'cli'), 0, 45);
}

/** Apakah $ident sudah mencapai batas $max percobaan dalam $windowSeconds detik terakhir? */
function rate_limit_blocked(string $kind, string $ident, int $max, int $windowSeconds): bool
{
    try {
        $stmt = db()->prepare(
            'SELECT COUNT(*) AS c FROM rate_limit_log
              WHERE kind = ? AND ident = ? AND created_at > (NOW() - INTERVAL ? SECOND)'
        );
        $stmt->bind_param('ssi', $kind, $ident, $windowSeconds);
        $stmt->execute();
        $count = (int) $stmt->get_result()->fetch_assoc()['c'];
        $stmt->close();
        return $count >= $max;
    } catch (Throwable $t) {
        error_log('[Lipalas] rate_limit_blocked: ' . $t->getMessage());
        return false; // gagal terbuka agar login tidak mati total bila tabel belum dibuat
    }
}

function rate_limit_hit(string $kind, string $ident): void
{
    try {
        $stmt = db()->prepare('INSERT INTO rate_limit_log (kind, ident) VALUES (?, ?)');
        $stmt->bind_param('ss', $kind, $ident);
        $stmt->execute();
        $stmt->close();

        if (random_int(1, 50) === 1) { // bersihkan data lama sesekali
            db()->query('DELETE FROM rate_limit_log WHERE created_at < (NOW() - INTERVAL 2 DAY)');
        }
    } catch (Throwable $t) {
        error_log('[Lipalas] rate_limit_hit: ' . $t->getMessage());
    }
}

function rate_limit_clear(string $kind, string $ident): void
{
    try {
        $stmt = db()->prepare('DELETE FROM rate_limit_log WHERE kind = ? AND ident = ?');
        $stmt->bind_param('ss', $kind, $ident);
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $t) {
        error_log('[Lipalas] rate_limit_clear: ' . $t->getMessage());
    }
}

/* ------------------------------------------------------------------ *
 * DATA KLINIK, LAYANAN, STATUS BOOKING
 * ------------------------------------------------------------------ */

function clinic(): array
{
    static $data = null;
    if ($data === null) {
        $data = require __DIR__ . '/clinic.php';
    }
    return $data;
}

function services(): array
{
    static $data = null;
    if ($data === null) {
        $data = require __DIR__ . '/services.php';
    }
    return $data;
}

function service_names(): array
{
    return array_column(services(), 'name');
}

function service_by_slug(string $slug): ?array
{
    foreach (services() as $s) {
        if ($s['slug'] === $slug) {
            return $s;
        }
    }
    return null;
}

/** Status booking: nilai di database => label dan kelas CSS. */
function booking_statuses(): array
{
    return [
        'Pending'   => ['label' => 'Menunggu konfirmasi', 'class' => 'status--pending'],
        'Confirmed' => ['label' => 'Dikonfirmasi',        'class' => 'status--confirmed'],
        'Completed' => ['label' => 'Selesai',             'class' => 'status--completed'],
        'Canceled'  => ['label' => 'Dibatalkan',          'class' => 'status--canceled'],
    ];
}

function status_badge(string $status): string
{
    $all = booking_statuses();
    $meta = $all[$status] ?? ['label' => $status !== '' ? $status : 'Tidak diketahui', 'class' => 'status--pending'];
    return '<span class="status ' . e($meta['class']) . '">' . e($meta['label']) . '</span>';
}

/* ------------------------------------------------------------------ *
 * VALIDASI & FORMAT
 * ------------------------------------------------------------------ */

/** Rapikan teks bebas: buang karakter kontrol, trim, batasi panjang. */
function clean_text(string $value, int $maxLength): string
{
    $value = preg_replace('/[^\P{C}\n\t]+/u', '', $value) ?? '';
    $value = str_replace("\r\n", "\n", $value);
    $value = trim($value);
    return mb_substr($value, 0, $maxLength, 'UTF-8');
}

function valid_name(string $name): bool
{
    $len = mb_strlen($name, 'UTF-8');
    return $len >= 2 && $len <= 100;
}

function valid_email_address(string $email): bool
{
    return strlen($email) <= 100 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Password: 8-72 byte (batas bcrypt). Mengembalikan pesan error atau null jika valid. */
function password_problem(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password minimal 8 karakter.';
    }
    if (strlen($password) > 72) {
        return 'Password maksimal 72 karakter.';
    }
    return null;
}

/** Normalisasi nomor telepon; null jika tidak valid. Boleh diawali +, 9-15 digit. */
function normalize_phone(string $phone): ?string
{
    $phone = preg_replace('/[\s().\-]+/', '', $phone) ?? '';
    return preg_match('/^\+?[0-9]{9,15}$/', $phone) ? $phone : null;
}

function valid_date_ymd(string $date): bool
{
    $d = DateTime::createFromFormat('!Y-m-d', $date);
    return $d !== false && $d->format('Y-m-d') === $date;
}

function format_tanggal(?string $ymd): string
{
    if ($ymd === null || !valid_date_ymd($ymd)) {
        return '-';
    }
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
              'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    [$y, $m, $d] = array_map('intval', explode('-', $ymd));
    return $d . ' ' . $bulan[$m] . ' ' . $y;
}

/* ------------------------------------------------------------------ *
 * HALAMAN ERROR & PENANGANAN EXCEPTION
 * ------------------------------------------------------------------ */

function render_error_page(int $code, string $title, string $message, ?string $detail = null): void
{
    if (PHP_SAPI === 'cli') {   // skrip di terminal: teks biasa, bukan HTML
        fwrite(STDERR, $title . ': ' . $message . "\n" . ($detail !== null ? $detail . "\n" : ''));
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: text/html; charset=UTF-8');
    }
    $home = url('');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="robots" content="noindex">'
        . '<title>' . e($title) . ' | ' . e(APP_NAME) . '</title>'
        . '<link rel="icon" type="image/png" sizes="64x64" href="' . e(asset('img/favicon-64.png')) . '">'
        . '<link rel="stylesheet" href="' . e(asset('css/style.css')) . '">'
        . '</head><body class="page-error"><main id="main" class="error-wrap">'
        . '<img class="error-mark" src="' . e(asset('img/lotus.svg')) . '" width="72" height="72" alt="">'
        . '<p class="error-code">Kode ' . (int) $code . '</p>'
        . '<h1>' . e($title) . '</h1><p class="error-text">' . e($message) . '</p>'
        . ($detail !== null ? '<pre class="error-detail">' . e($detail) . '</pre>' : '')
        . '<a class="button button--solid" href="' . e($home) . '">Kembali ke beranda</a>'
        . '</main></body></html>';
    exit;
}

set_exception_handler(function (Throwable $t) {
    error_log('[Lipalas] ' . get_class($t) . ': ' . $t->getMessage() . ' @ ' . $t->getFile() . ':' . $t->getLine());
    render_error_page(
        500,
        'Terjadi kesalahan',
        'Maaf, terjadi kesalahan pada sistem. Silakan coba lagi beberapa saat lagi.',
        is_dev() ? get_class($t) . ': ' . $t->getMessage() : null
    );
});
