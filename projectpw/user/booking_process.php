<?php
/**
 * Memproses formulir permintaan booking (hanya POST).
 *
 * Aturan:
 *  - wajib login; admin tidak membuat booking lewat sini
 *  - token CSRF wajib
 *  - nama dan email diambil dari AKUN yang login, bukan dari form
 *  - layanan harus ada di core/services.php, tanggal harus valid dan tidak di masa lalu
 *  - status awal selalu 'Pending' (menunggu konfirmasi klinik)
 *  - pesan sukses hanya muncul jika INSERT benar-benar berhasil
 */
require_once __DIR__ . '/../core/helpers.php';

const BOOKING_MAX_DAYS_AHEAD = 365;   // batas wajar jadwal ke depan
const BOOKING_MAX_PER_HOUR   = 10;    // anti-spam per akun

$back = url('user/dashboard.php');

if (!is_post()) {
    redirect($back);
}

$user = require_login();
if ($user['role'] === 'admin') {
    flash_set('warning', 'Akun admin tidak membuat booking. Kelola booking pelanggan lewat dashboard admin.');
    redirect(url('admin/dashboard.php'));
}

require_csrf($back . '#booking');

$phoneRaw = (string) ($_POST['phone'] ?? '');
$service  = trim((string) ($_POST['service'] ?? ''));
$tanggal  = trim((string) ($_POST['tanggal'] ?? ''));
$message  = clean_text((string) ($_POST['message'] ?? ''), 1000);

// Simpan input agar formulir terisi kembali bila ada kesalahan.
old_set(['phone' => $phoneRaw, 'service' => $service, 'tanggal' => $tanggal, 'message' => $message]);

if (!schema_ready()) {
    error_log('[Lipalas] booking ditolak: schema database belum dimigrasi (lihat database/migrations).');
    flash_set('danger', 'Sistem booking sedang disiapkan dan belum dapat menerima permintaan. Silakan coba lagi nanti.');
    redirect($back . '#booking');
}

$errors = [];

$phone = normalize_phone($phoneRaw);
if ($phone === null) {
    $errors[] = 'Nomor telepon tidak valid. Gunakan 9-15 digit angka, boleh diawali tanda +.';
}

if (!in_array($service, service_names(), true)) {
    $errors[] = 'Silakan pilih layanan dari daftar yang tersedia.';
}

if ($tanggal === '') {
    $errors[] = 'Tanggal wajib diisi.';
} elseif (!valid_date_ymd($tanggal)) {
    $errors[] = 'Format tanggal tidak valid.';
} else {
    $today = new DateTimeImmutable('today');
    $date  = new DateTimeImmutable($tanggal);
    if ($date < $today) {
        $errors[] = 'Tanggal tidak boleh di masa lalu.';
    } elseif ($date > $today->modify('+' . BOOKING_MAX_DAYS_AHEAD . ' days')) {
        $errors[] = 'Tanggal terlalu jauh ke depan. Pilih tanggal dalam ' . BOOKING_MAX_DAYS_AHEAD . ' hari ke depan.';
    }
}

if ($errors) {
    foreach ($errors as $err) {
        flash_set('danger', $err);
    }
    redirect($back . '#booking');
}

$uid = (int) $user['id'];

if (rate_limit_blocked('booking', 'u' . $uid, BOOKING_MAX_PER_HOUR, 3600)) {
    flash_set('warning', 'Anda telah mengirim terlalu banyak permintaan dalam waktu singkat. Silakan coba lagi nanti.');
    redirect($back . '#booking');
}

try {
    $db = db();

    // Cegah permintaan ganda yang sama persis (layanan + tanggal) yang masih aktif.
    // Ini BUKAN pengecekan ketersediaan jadwal klinik; klinik tetap mengonfirmasi manual.
    $dup = $db->prepare(
        "SELECT id FROM table_booking
          WHERE user_id = ? AND service = ? AND tanggal = ? AND status IN ('Pending','Confirmed')
          LIMIT 1"
    );
    $dup->bind_param('iss', $uid, $service, $tanggal);
    $dup->execute();
    $isDuplicate = $dup->get_result()->num_rows > 0;
    $dup->close();

    if ($isDuplicate) {
        flash_set('warning', 'Anda sudah memiliki permintaan untuk layanan dan tanggal yang sama. Lihat statusnya di riwayat booking.');
        redirect($back . '#riwayat');
    }

    rate_limit_hit('booking', 'u' . $uid);

    $status = 'Pending';
    $stmt = $db->prepare(
        'INSERT INTO table_booking (user_id, nama, email, phone, service, tanggal, message, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('isssssss', $uid, $user['nama'], $user['email'], $phone, $service, $tanggal, $message, $status);
    $stmt->execute();
    $saved = $stmt->affected_rows === 1;
    $stmt->close();
} catch (Throwable $t) {
    error_log('[Lipalas] booking gagal disimpan: ' . $t->getMessage());
    $saved = false;
}

if (!$saved) {
    flash_set('danger', 'Permintaan booking gagal disimpan. Silakan coba lagi.');
    redirect($back . '#booking');
}

unset($_SESSION['old']);
flash_set('success', 'Permintaan booking berhasil dikirim dan menunggu konfirmasi dari klinik. Anda dapat memantau statusnya di riwayat booking.');
redirect($back . '#riwayat');
