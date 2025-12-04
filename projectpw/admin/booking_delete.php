<?php
session_start();

// --- LAPISAN KEAMANAN 1: OTORISASI ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php?message=Akses ditolak.');
    exit;
}

// Koreksi path koneksi
require_once(__DIR__ . "/../core/koneksi.php");

// Pastikan request method adalah GET dan ada ID yang valid
if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_POST['id'])) {
    $booking_id = intval($_POST['id']);

    // Cek apakah ID valid
    if ($booking_id <= 0) {
        $_SESSION['admin_message'] = '❌ ID Booking tidak valid.';
        header('Location: dashboard.php');
        exit;
    }

    // PERBAIKAN UTAMA: Menggunakan nama tabel yang benar: 'table_booking'
    $stmt = $connection->prepare("DELETE FROM table_booking WHERE id = ?");
    
    if (!$stmt) {
        $_SESSION['admin_message'] = '❌ Kesalahan sistem: Gagal menyiapkan query penghapusan.';
        header('Location: dashboard.php');
        exit;
    }

    // Bind parameter
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        // Cek apakah ada baris yang benar-benar terpengaruh (terhapus)
        if ($stmt->affected_rows > 0) {
            $_SESSION['admin_message'] = '✅ Booking ID: ' . $booking_id . ' berhasil dihapus.';
        } else {
            $_SESSION['admin_message'] = '⚠️ Booking ID: ' . $booking_id . ' tidak ditemukan untuk dihapus.';
        }
    } else {
        $_SESSION['admin_message'] = '❌ Gagal menghapus booking. Kesalahan database.';
    }
    $stmt->close();
    
    // Redirect kembali ke dashboard setelah operasi selesai
    header('Location: dashboard.php');
    exit;

} else {
    // Jika tidak ada ID atau method bukan GET, redirect dengan pesan error
    $_SESSION['admin_message'] = '❌ Permintaan penghapusan tidak valid.';
    header('Location: dashboard.php');
    exit;
}

?>
