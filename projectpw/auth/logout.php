<?php
// 1. Mulai Sesi PHP
session_start();

// 2. Hancurkan semua variabel sesi
$_SESSION = array();

// 3. Jika menggunakan cookies sesi, hapus juga cookie-nya.
// Perhatikan: Ini akan menghancurkan sesi, bukan hanya data sesi!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hancurkan sesi secara keseluruhan
session_destroy();

// 5. Arahkan user kembali ke halaman login
header("Location: login.php?message=Anda telah berhasil logout.");
exit;
?>