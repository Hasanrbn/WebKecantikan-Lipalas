<?php
/**
 * Edit booking oleh admin: status, tanggal, layanan, catatan.
 * Nama, email, dan telepon pelanggan hanya ditampilkan (tidak diubah di sini).
 */
require_once __DIR__ . '/../core/helpers.php';

$admin    = require_admin();
$list     = url('admin/dashboard.php');
$statuses = booking_statuses();

if (!schema_ready()) {
    flash_set('danger', 'Database belum dimigrasi. Jalankan migrasi terlebih dahulu (lihat README).');
    redirect($list);
}

$id = filter_var($_POST['id'] ?? $_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    flash_set('danger', 'ID booking tidak valid.');
    redirect($list);
}
$self = url('admin/booking_edit.php?id=' . $id);

$stmt = db()->prepare(
    'SELECT id, user_id, nama, email, phone, service, tanggal, message, status, created_at
       FROM table_booking WHERE id = ? LIMIT 1'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    flash_set('danger', 'Booking tidak ditemukan. Mungkin sudah dihapus.');
    redirect($list);
}

// Opsi layanan: daftar resmi + nilai tersimpan (jika booking lama memakai nama yang sudah tidak ada).
$serviceOptions = service_names();
if ((string) $booking['service'] !== '' && !in_array($booking['service'], $serviceOptions, true)) {
    $serviceOptions[] = $booking['service'];
}

if (is_post()) {
    require_csrf($self);

    $status  = (string) ($_POST['status'] ?? '');
    $service = trim((string) ($_POST['service'] ?? ''));
    $tanggal = trim((string) ($_POST['tanggal'] ?? ''));
    $message = clean_text((string) ($_POST['message'] ?? ''), 1000);

    $errors = [];
    if (!isset($statuses[$status]) && $status !== (string) $booking['status']) {
        $errors[] = 'Status tidak valid.';
    }
    if (!in_array($service, $serviceOptions, true)) {
        $errors[] = 'Layanan tidak valid.';
    }
    if (!valid_date_ymd($tanggal)) {
        $errors[] = 'Format tanggal tidak valid.';
    }

    if ($errors) {
        foreach ($errors as $err) {
            flash_set('danger', $err);
        }
        old_set(['status' => $status, 'service' => $service, 'tanggal' => $tanggal, 'message' => $message]);
        redirect($self);
    }

    try {
        $up = db()->prepare('UPDATE table_booking SET tanggal = ?, service = ?, message = ?, status = ? WHERE id = ?');
        $up->bind_param('ssssi', $tanggal, $service, $message, $status, $id);
        $up->execute();
        $up->close();
    } catch (Throwable $t) {
        error_log('[Lipalas] update booking #' . $id . ' gagal: ' . $t->getMessage());
        flash_set('danger', 'Perubahan gagal disimpan. Silakan coba lagi.');
        old_set(['status' => $status, 'service' => $service, 'tanggal' => $tanggal, 'message' => $message]);
        redirect($self);
    }

    flash_set('success', 'Booking #' . $id . ' berhasil diperbarui.');
    redirect($list);
}

$vStatus  = old('status', (string) $booking['status']);
$vService = old('service', (string) $booking['service']);
$vTanggal = old('tanggal', (string) $booking['tanggal']);
$vMessage = old('message', (string) $booking['message']);

$statusOptions = array_keys($statuses);
if ((string) $booking['status'] !== '' && !in_array($booking['status'], $statusOptions, true)) {
    $statusOptions[] = $booking['status'];
}

$pageTitle       = 'Edit Booking #' . $id;
$metaDescription = 'Edit booking pelanggan.';
$noindex         = true;
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/navbar.php';
?>
<main id="main">
  <div class="page-head">
    <div class="wrap">
      <h1>Edit booking #<?= (int) $id ?></h1>
      <p><a class="link-back" href="<?= e($list) ?>"><?= icon('arrow-left') ?>Kembali ke dashboard</a></p>
    </div>
  </div>

  <div class="edit-wrap page-body">
    <?= flash_render() ?>

    <div class="stack">
      <section class="panel" aria-labelledby="pel-title">
        <h2 id="pel-title" class="panel__title">Data pelanggan</h2>
        <ul class="person">
          <li><?= icon('user') ?><span><?= e($booking['nama']) ?></span></li>
          <li><?= icon('mail') ?><span><?= e($booking['email']) ?></span></li>
          <li><?= icon('phone') ?><span><?= e((string) $booking['phone'] !== '' ? $booking['phone'] : '-') ?></span></li>
          <li><?= icon('clock') ?>
            <span>Dikirim <?= e(date('d/m/Y H:i', strtotime((string) $booking['created_at']))) ?>.
            Status saat ini: <?= status_badge((string) $booking['status']) ?></span></li>
        </ul>
      </section>

      <section class="panel" aria-labelledby="edit-title">
        <h2 id="edit-title" class="panel__title">Ubah booking</h2>
        <form method="post" action="<?= e($self) ?>" data-single-submit data-validate>
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int) $id ?>">

          <div class="field">
            <label class="field__label" for="status">Status</label>
            <select class="field__control" id="status" name="status" required>
              <?php foreach ($statusOptions as $key): ?>
                <option value="<?= e($key) ?>" <?= $vStatus === $key ? 'selected' : '' ?>>
                  <?= e($statuses[$key]['label'] ?? $key) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label class="field__label" for="tanggal">Tanggal</label>
            <input type="date" class="field__control" id="tanggal" name="tanggal" value="<?= e($vTanggal) ?>" required>
          </div>

          <div class="field">
            <label class="field__label" for="service">Perawatan</label>
            <select class="field__control" id="service" name="service" required>
              <?php foreach ($serviceOptions as $name): ?>
                <option value="<?= e($name) ?>" <?= $vService === $name ? 'selected' : '' ?>><?= e($name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label class="field__label" for="message">Catatan pelanggan</label>
            <textarea class="field__control" id="message" name="message" rows="4" maxlength="1000"><?= e($vMessage) ?></textarea>
            <p class="field__count" data-count-for="message" aria-hidden="true"></p>
          </div>

          <div class="form-actions">
            <button type="submit" class="button button--solid">Simpan perubahan</button>
            <a class="button" href="<?= e($list) ?>">Batal</a>
          </div>
        </form>
      </section>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
