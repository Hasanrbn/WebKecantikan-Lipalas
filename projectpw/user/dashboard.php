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

$isDemo = !empty(clinic()['demo']);
$bulanPendek = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

$pageTitle       = 'Akun Saya';
$metaDescription = 'Kirim permintaan janji temu dan pantau status booking Anda di ' . APP_NAME . '.';
$noindex         = true;
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/navbar.php';
?>
<main id="main">
  <div class="page-head">
    <div class="wrap">
      <h1>Halo, <?= e($user['nama']) ?></h1>
      <p>Kirim permintaan janji temu dan pantau statusnya di sini.</p>
    </div>
  </div>

  <div class="wrap page-body">
    <?= flash_render() ?>

    <?php if (!$bookingReady): ?>
      <div class="notice notice--warning" role="alert">
        <?= icon('triangle-alert') ?>
        <p>Sistem booking sedang disiapkan dan belum dapat menerima permintaan. Silakan kembali beberapa saat lagi.</p>
      </div>
    <?php endif; ?>

    <div class="book-grid">
      <!-- FORMULIR BOOKING -->
      <section id="booking" class="panel" aria-labelledby="booking-title">
        <h2 id="booking-title">Permintaan janji temu</h2>
        <p class="panel__lead">
          Permintaan Anda berstatus <strong>Menunggu konfirmasi</strong> sampai tim kami mengonfirmasi tanggalnya.
        </p>

        <form method="post" action="<?= e(url('user/booking_process.php')) ?>" data-single-submit data-validate>
          <?= csrf_field() ?>

          <div class="field__row">
            <div class="field">
              <label class="field__label" for="nama">Nama</label>
              <input type="text" class="field__control" id="nama" value="<?= e($user['nama']) ?>" readonly
                     aria-describedby="akunHelp">
            </div>
            <div class="field">
              <label class="field__label" for="email">Email</label>
              <input type="email" class="field__control" id="email" value="<?= e($user['email']) ?>" readonly
                     aria-describedby="akunHelp">
            </div>
          </div>
          <p id="akunHelp" class="field__hint" style="margin-top:-.6rem;margin-bottom:1.3rem">Nama dan email diambil dari akun Anda.</p>

          <div class="field__row">
            <div class="field">
              <label class="field__label" for="phone">Telepon / WhatsApp</label>
              <input type="tel" inputmode="tel" class="field__control" id="phone" name="phone"
                     value="<?= e($oldPhone) ?>" placeholder="08xxxxxxxxxx" autocomplete="tel"
                     pattern="\+?[0-9\s\(\)\.\-]{9,25}" maxlength="25"
                     data-msg-pattern="Gunakan nomor telepon yang valid, misalnya 081234567890." required>
            </div>
            <div class="field">
              <label class="field__label" for="tanggal">Tanggal yang diinginkan</label>
              <input type="date" class="field__control" id="tanggal" name="tanggal"
                     value="<?= e($oldDate) ?>" data-min-today required>
            </div>
          </div>

          <div class="field">
            <label class="field__label" for="service">Perawatan</label>
            <select class="field__control" id="service" name="service" data-summary-source
                    data-msg-missing="Pilih perawatan yang Anda inginkan." required>
              <option value="" <?= $selectedService === '' ? 'selected' : '' ?> disabled>Pilih perawatan</option>
              <?php foreach ($services as $s): ?>
                <option value="<?= e($s['name']) ?>"
                        data-duration="<?= e((string) ($s['duration'] ?? '')) ?>"
                        data-price="<?= e((string) ($s['price'] ?? '')) ?>"
                        data-blurb="<?= e($s['summary']) ?>"
                        <?= $selectedService === $s['name'] ? 'selected' : '' ?>>
                  <?= e($s['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label class="field__label" for="message">Catatan <small>(opsional)</small></label>
            <textarea class="field__control" id="message" name="message" rows="4" maxlength="1000"
                      aria-describedby="messageHelp"><?= e($oldMessage) ?></textarea>
            <p id="messageHelp" class="field__hint">Misalnya keluhan, preferensi waktu, atau pertanyaan untuk tim kami.</p>
            <p class="field__count" data-count-for="message" aria-hidden="true"></p>
          </div>

          <button type="submit" class="button button--solid" <?= $bookingReady ? '' : 'disabled' ?>>
            Kirim permintaan janji temu
          </button>
        </form>
      </section>

      <!-- RINGKASAN + AKUN -->
      <aside class="summary" data-summary aria-labelledby="ringkasan-title">
        <h2 id="ringkasan-title">Ringkasan</h2>
        <div aria-live="polite">
          <dl>
            <div><dt>Perawatan</dt><dd class="summary__service" data-sum="service">Belum dipilih</dd></div>
            <div><dt>Durasi<?= $isDemo ? ' (contoh)' : '' ?></dt><dd data-sum="duration">-</dd></div>
            <div><dt>Harga<?= $isDemo ? ' (contoh)' : '' ?></dt><dd data-sum="price">-</dd></div>
            <div><dt>Tanggal</dt><dd data-sum="date">Belum dipilih</dd></div>
          </dl>
          <p data-sum="note">Pilih perawatan untuk melihat ringkasannya.</p>
        </div>

        <div class="summary__account">
          <strong><?= e($user['nama']) ?></strong><br>
          <span class="muted"><?= e($user['email']) ?></span>
          <form method="post" action="<?= e(url('auth/logout.php')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="link-quiet">Keluar</button>
          </form>
          <p class="mt-3 mb-0">Ingin mengubah atau membatalkan janji? <a href="<?= e(url('#kontak')) ?>">Hubungi kami</a>.</p>
        </div>
      </aside>
    </div>

    <!-- RIWAYAT -->
    <section id="riwayat" class="history" aria-labelledby="riwayat-title">
      <h2 id="riwayat-title">Riwayat permintaan</h2>

      <?php if (!$bookings): ?>
        <div class="empty">
          <?= icon('calendar-days') ?>
          Belum ada permintaan. Kirim yang pertama lewat formulir di atas.
        </div>
      <?php else: ?>
        <ul class="history__list">
          <?php foreach ($bookings as $b):
              $parts = valid_date_ymd((string) $b['tanggal']) ? array_map('intval', explode('-', (string) $b['tanggal'])) : null; ?>
            <li class="history__item">
              <div class="date-block" aria-hidden="true">
                <?php if ($parts): ?>
                  <span class="date-block__day"><?= $parts[2] ?></span>
                  <span class="date-block__month"><?= e($bulanPendek[$parts[1]] . ' ' . $parts[0]) ?></span>
                <?php else: ?>
                  <span class="date-block__day">-</span>
                <?php endif; ?>
              </div>
              <div>
                <h3 class="history__service"><?= e($b['service']) ?></h3>
                <p class="history__meta"><span class="visually-hidden">Tanggal: </span><?= e(format_tanggal($b['tanggal'])) ?></p>
                <?php if ((string) $b['message'] !== ''): ?>
                  <p class="history__meta"><?= nl2br(e($b['message'])) ?></p>
                <?php endif; ?>
                <p class="history__meta small">Dikirim <?= e(date('d/m/Y H:i', strtotime((string) $b['created_at']))) ?></p>
              </div>
              <?= status_badge((string) $b['status']) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
