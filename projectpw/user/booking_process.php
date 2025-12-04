<?php
session_start();

// Pastikan hanya POST dan user sudah login
if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
    header('Location: dashboard.php');
    exit;
}

// PERBAIKAN PATH LOGIN
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?message=Silakan login terlebih dahulu');
    exit;
}

// PERBAIKAN PATH KONEKSI
require_once(__DIR__ . "/../core/koneksi.php"); 
// $connection diharapkan didefinisikan di koneksi.php

// Ambil user dari session (aman)
$user_id = intval($_SESSION['user_id']);
$nama  = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Ambil data POST, bersihkan dasar
$phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$service = isset($_POST['service']) ? trim($_POST['service']) : '';
$tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validasi kuat
$errors = [];
if ($phone === '' || !preg_match('/^\+?\d{6,15}$/', $phone)) {
    $errors[] = 'Nomor telepon tidak valid.';
}
if ($service === '') {
    $errors[] = 'Pilih layanan.';
}

if ($tanggal === '') {
    $errors[] = 'Tanggal tidak boleh kosong.';
} else {
    // Cek format tanggal (YYYY-MM-DD) dan validitas, serta cek tanggal lampau
    $d = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$d || $d->format('Y-m-d') !== $tanggal) {
        $errors[] = 'Format tanggal salah atau tidak valid.';
    } else {
        $today = new DateTime('today');
        if ($d < $today) {
            $errors[] = 'Booking tidak bisa dilakukan untuk tanggal yang sudah lewat.';
        }
    }
}

// Jika ada error -> redirect kembali dengan pesan flash
if (!empty($errors)) {
    $_SESSION['booking_errors'] = $errors;
    header('Location: dashboard.php');
    exit;
}

// Pastikan nama tabel adalah 'bookings' (atau yang Anda gunakan)
$stmt = $connection->prepare("
    INSERT INTO table_booking (user_id, nama, email, phone, service, tanggal, message, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");


if (!$stmt) {
    $_SESSION['booking_errors'] = ['Terjadi kesalahan pada server: Gagal menyiapkan query.'];
    header('Location: dashboard.php'); 
    exit;
}

$stmt->bind_param(
    "issssss", // i: integer (user_id), s: string (sisanya)
    $user_id,
    $nama,
    $email,
    $phone,
    $service,
    $tanggal,
    $message
);

$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    $_SESSION['booking_success'] = 'Booking berhasil dikirim. Tim kami akan mengkonfirmasi via email.';
    header('Location: dashboard.php'); // Redirect kembali ke dashboard user
    exit;
} else {
    $_SESSION['booking_errors'] = ['Gagal menyimpan booking: ' . $connection->error];
    header('Location: dashboard.php'); 
    exit;
}
