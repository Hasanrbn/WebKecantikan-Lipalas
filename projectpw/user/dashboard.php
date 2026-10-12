<?php
/**
 * Area pelanggan: formulir permintaan booking + riwayat booking milik sendiri.
 * Informasi umum klinik (layanan, kontak, FAQ) ada di beranda publik (index.php).
 */
require_once __DIR__ . '/../core/helpers.php';

$user = require_login();
if ($user['role'] === 'admin') {
    redirect(url('admin/dashboard.php'));
}

$services     = services();
$bookingReady = schema_ready();

// Layanan terpilih: input lama (setelah error) > ?layanan=slug dari beranda.
$preselect = '';
$fromSlug  = isset($_GET['layanan']) ? service_by_slug((string) $_GET['layanan']) : null;
if ($fromSlug !== null) {
    $preselect = $fromSlug['name'];
}
$selectedService = old('service', $preselect);

$oldPhone   = old('phone');
$oldDate    = old('tanggal');
$oldMessage = old('message');

// Riwayat booking: hanya milik akun ini.
$bookings = [];
if ($bookingReady) {
    $uid  = (int) $user['id'];
    $stmt = db()->prepare(
        'SELECT id, service, tanggal, status, message, created_at
           FROM table_booking WHERE user_id = ?
          ORDER BY created_at DESC, id DESC LIMIT 50'
    );
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$pageTitle       = 'Akun Saya';
$metaDescription = 'Kirim permintaan janji temu dan pantau status booking Anda di ' . APP_NAME . '.';
$noindex         = true;
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/navbar.php';
?>
<main id="main">
  <div class="page-head">
    <div class="container">
      <h1>Halo, <?= e($user['nama']) ?></h1>
      <p>Kirim permintaan janji temu dan pantau statusnya di sini.</p>
    </div>
  </div>

  <div class="container py-4 py-lg-5">
    <?= flash_render() ?>

    <?php if (!$bookingReady): ?>
      <div class="alert alert-warning" role="alert">
        Sistem booking sedang disiapkan dan belum dapat menerima permintaan. Silakan kembali beberapa saat lagi.
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- FORMULIR BOOKING -->
      <div class="col-lg-7">
        <section id="booking" class="panel" aria-labelledby="booking-title">
          <h2 id="booking-title">Buat permintaan janji temu</h2>
          <p class="text-muted-lipalas">
            Permintaan Anda akan berstatus <strong>Menunggu konfirmasi</strong> sampai tim klinik mengonfirmasi jadwalnya.
          </p>

          <form method="post" action="<?= e(url('user/booking_process.php')) ?>" data-single-submit>
            <?= csrf_field() ?>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="nama">Nama</label>
                <input type="text" class="form-control" id="nama" value="<?= e($user['nama']) ?>" readonly
                       aria-describedby="akunHelp">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" value="<?= e($user['email']) ?>" readonly
                       aria-describedby="akunHelp">
              </div>
              <div class="col-12">
                <div id="akunHelp" class="form-text mt-0">Nama dan email diambil dari akun Anda.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="phone">Nomor telepon / WhatsApp</label>
                <input type="tel" inputmode="tel" class="form-control" id="phone" name="phone"
                       value="<?= e($oldPhone) ?>" placeholder="08xxxxxxxxxx" autocomplete="tel"
                       pattern="\+?[0-9\s().\-]{9,25}" maxlength="25" required>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="tanggal">Tanggal yang diinginkan</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal"
                       value="<?= e($oldDate) ?>" data-min-today required>
              </div>

              <div class="col-12">
                <label class="form-label" for="service">Layanan</label>
                <select class="form-select" id="service" name="service" required>
                  <option value="" <?= $selectedService === '' ? 'selected' : '' ?> disabled>Pilih layanan</option>
                  <?php foreach ($services as $s): ?>
                    <option value="<?= e($s['name']) ?>" <?= $selectedService === $s['name'] ? 'selected' : '' ?>>
                      <?= e($s['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label" for="message">Catatan tambahan <span class="text-muted-lipalas fw-normal">(opsional)</span></label>
                <textarea class="form-control" id="message" name="message" rows="3" maxlength="1000"
                          aria-describedby="messageHelp"><?= e($oldMessage) ?></textarea>
                <div id="messageHelp" class="form-text">Misalnya keluhan, preferensi waktu, atau pertanyaan untuk tim klinik.</div>
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-lipalas" <?= $bookingReady ? '' : 'disabled' ?>>
                  Kirim permintaan janji temu
                </button>
              </div>
            </div>
          </form>
        </section>
      </div>

      <!-- INFO AKUN -->
      <div class="col-lg-5">
        <aside class="panel mb-4" aria-labelledby="akun-title">
          <h2 id="akun-title">Akun Anda</h2>
          <ul class="contact-list mb-3">
            <li><i class="fa-solid fa-user" aria-hidden="true"></i><span><?= e($user['nama']) ?></span></li>
            <li><i class="fa-solid fa-envelope" aria-hidden="true"></i><span><?= e($user['email']) ?></span></li>
          </ul>
          <form method="post" action="<?= e(url('auth/logout.php')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-lipalas-outline btn-sm">Keluar</button>
          </form>
        </aside>

        <aside class="panel" aria-labelledby="info-title">
          <h2 id="info-title">Perlu bantuan?</h2>
          <p class="mb-2">Ingin mengubah atau membatalkan janji, atau punya pertanyaan tentang layanan?</p>
          <a class="btn btn-lipalas-outline btn-sm" href="<?= e(url('#kontak')) ?>">Lihat kontak klinik</a>
          <a class="btn btn-lipalas-outline btn-sm" href="<?= e(url('#layanan')) ?>">Lihat layanan</a>
        </aside>
      </div>
    </div>

    <!-- RIWAYAT -->
    <section id="riwayat" class="panel mt-4" aria-labelledby="riwayat-title">
      <h2 id="riwayat-title">Riwayat permintaan booking</h2>

      <?php if (!$bookings): ?>
        <div class="empty-state">
          <i class="fa-regular fa-calendar" aria-hidden="true"></i>
          Belum ada permintaan booking. Kirim permintaan pertama Anda lewat formulir di atas.
        </div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($bookings as $b): ?>
            <div class="col-md-6">
              <article class="booking-card h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                  <div class="bk-title"><?= e($b['service']) ?></div>
                  <?= status_badge((string) $b['status']) ?>
                </div>
                <div class="bk-meta">
                  <i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>
                  <span class="visually-hidden">Tanggal: </span><?= e(format_tanggal($b['tanggal'])) ?>
                </div>
                <?php if ((string) $b['message'] !== ''): ?>
                  <div class="bk-meta mt-1"><?= nl2br(e($b['message'])) ?></div>
                <?php endif; ?>
                <div class="bk-meta mt-2 small">Dikirim <?= e(date('d/m/Y H:i', strtotime((string) $b['created_at']))) ?></div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
