<?php
/**
 * Hapus booking. Hanya admin, hanya POST, wajib token CSRF.
 * Membuka halaman ini lewat GET tidak menghapus apa pun.
 */
require_once __DIR__ . '/../core/helpers.php';

$admin = require_admin();
$list  = url('admin/dashboard.php');

if (!is_post()) {
    redirect($list);
}

require_csrf($list);

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    flash_set('danger', 'ID booking tidak valid.');
    redirect($list);
}

try {
    $stmt = db()->prepare('DELETE FROM table_booking WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();
} catch (Throwable $t) {
    error_log('[Lipalas] hapus booking #' . $id . ' gagal: ' . $t->getMessage());
    flash_set('danger', 'Booking gagal dihapus. Silakan coba lagi.');
    redirect($list);
}

if ($deleted > 0) {
    flash_set('success', 'Booking #' . $id . ' berhasil dihapus.');
} else {
    flash_set('warning', 'Booking #' . $id . ' tidak ditemukan. Mungkin sudah dihapus.');
}
redirect($list);
