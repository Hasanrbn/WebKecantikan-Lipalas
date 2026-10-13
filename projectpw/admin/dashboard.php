<?php
/**
 * Dashboard admin: ringkasan dan daftar booking.
 * Hanya untuk role 'admin' (dicek ke database lewat require_admin()).
 */
require_once __DIR__ . '/../core/helpers.php';

$admin    = require_admin();
$ready    = schema_ready();
$statuses = booking_statuses();
$clinic   = clinic();

const ADMIN_PER_PAGE = 20;

// --- Filter dari URL (semua divalidasi) ---
$filterStatus = (string) ($_GET['status'] ?? '');
if (!isset($statuses[$filterStatus])) {
    $filterStatus = '';
}
$search = clean_text((string) ($_GET['q'] ?? ''), 100);
$search = str_replace("\n", ' ', $search);
$page   = max(1, (int) filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['default' => 1]]));

$stats    = ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'completed' => 0, 'canceled' => 0, 'today' => 0];
$topService = null;
$rows     = [];
$matching = 0;
$pages    = 1;

if ($ready) {
    $db = db();

    $r = $db->query(
        "SELECT COUNT(*) AS total,
                COALESCE(SUM(status = 'Pending'), 0)   AS pending,
                COALESCE(SUM(status = 'Confirmed'), 0) AS confirmed,
                COALESCE(SUM(status = 'Completed'), 0) AS completed,
                COALESCE(SUM(status = 'Canceled'), 0)  AS canceled,
                COALESCE(SUM(tanggal = CURDATE()), 0)  AS today
           FROM table_booking"
    )->fetch_assoc();
    $stats = array_map('intval', $r);

    $top = $db->query(
        "SELECT service, COUNT(*) AS c FROM table_booking
          WHERE service IS NOT NULL AND service <> ''
          GROUP BY service ORDER BY c DESC, service ASC LIMIT 1"
    )->fetch_assoc();
    $topService = $top ?: null;

    // WHERE dinamis dengan prepared statement.
    $where  = [];
    $types  = '';
    $params = [];
    if ($filterStatus !== '') {
        $where[]  = 'status = ?';
        $types   .= 's';
        $params[] = $filterStatus;
    }
    if ($search !== '') {
        $like     = '%' . addcslashes($search, '%_\\') . '%';
        $where[]  = '(nama LIKE ? OR email LIKE ? OR phone LIKE ? OR service LIKE ?)';
        $types   .= 'ssss';
        array_push($params, $like, $like, $like, $like);
    }
    $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

    $cnt = $db->prepare('SELECT COUNT(*) AS c FROM table_booking' . $whereSql);
    if ($params) {
        $cnt->bind_param($types, ...$params);
    }
    $cnt->execute();
    $matching = (int) $cnt->get_result()->fetch_assoc()['c'];
    $cnt->close();

    $pages  = max(1, (int) ceil($matching / ADMIN_PER_PAGE));
    $page   = min($page, $pages);
    $offset = ($page - 1) * ADMIN_PER_PAGE;

    $list = $db->prepare(
        'SELECT id, nama, email, phone, service, tanggal, message, status, created_at
           FROM table_booking' . $whereSql . ' ORDER BY id DESC LIMIT ? OFFSET ?'
    );
    $listTypes  = $types . 'ii';
    $listParams = array_merge($params, [ADMIN_PER_PAGE, $offset]);
    $list->bind_param($listTypes, ...$listParams);
    $list->execute();
    $rows = $list->get_result()->fetch_all(MYSQLI_ASSOC);
    $list->close();
}

/** Bangun URL dashboard dengan filter yang sedang aktif + perubahan. */
function admin_url(array $change = []): string
{
    global $filterStatus, $search;
    $q = array_merge(['status' => $filterStatus, 'q' => $search, 'page' => 1], $change);
    $q = array_filter($q, static fn($v) => $v !== '' && $v !== null && $v !== 1);
    return url('admin/dashboard.php') . ($q ? '?' . http_build_query($q) : '');
}

$contactMissing = $clinic['address'] === '' && $clinic['phone'] === '' && $clinic['whatsapp'] === ''
               && $clinic['email'] === '' && empty($clinic['hours']);
$isFiltered = $filterStatus !== '' || $search !== '';
$tabCounts  = [
    ''          => $stats['total'],
    'Pending'   => $stats['pending'],
    'Confirmed' => $stats['confirmed'],
    'Completed' => $stats['completed'],
    'Canceled'  => $stats['canceled'],
];

$pageTitle       = 'Dashboard Admin';
$metaDescription = 'Kelola booking pelanggan ' . APP_NAME . '.';
$noindex         = true;
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/navbar.php';
?>
<main id="main">
  <div class="page-head">
    <div class="wrap">
      <h1>Dashboard admin</h1>
      <p>Kelola permintaan booking pelanggan <?= e(APP_NAME) ?>.</p>
    </div>
  </div>

  <div class="wrap page-body">
    <?= flash_render() ?>

    <?php if (!$ready): ?>
      <div class="notice notice--danger" role="alert">
        <?= icon('circle-alert') ?>
        <p><strong>Database belum dimigrasi.</strong> Kolom <code>status</code> pada <code>table_booking</code>
        atau tabel <code>rate_limit_log</code> belum ada. Backup database, lalu jalankan
        <code>database/migrations/001_upgrade_existing_database.sql</code> (lihat README).</p>
      </div>
    <?php endif; ?>

    <?php if ($contactMissing): ?>
      <div class="notice notice--info" role="status">
        <?= icon('info') ?>
        <p>Informasi kontak klinik (alamat, telepon, jam operasional) belum diisi, sehingga belum tampil di website.
        Isi di <code>core/clinic.php</code>.</p>
      </div>
    <?php endif; ?>

    <!-- RINGKASAN -->
    <div class="tally">
      <div class="tally__item"><span class="tally__num"><?= $stats['total'] ?></span><span class="tally__label">Total booking</span></div>
      <div class="tally__item"><span class="tally__num"><?= $stats['pending'] ?></span><span class="tally__label">Menunggu konfirmasi</span></div>
      <div class="tally__item"><span class="tally__num"><?= $stats['today'] ?></span><span class="tally__label">Jadwal hari ini</span></div>
      <div class="tally__item">
        <span class="tally__text"><?= $topService ? e($topService['service']) : '-' ?></span>
        <span class="tally__label">Perawatan terbanyak dipesan<?= $topService ? ' (' . (int) $topService['c'] . ' booking)' : '' ?></span>
      </div>
    </div>

    <!-- FILTER -->
    <div class="toolbar">
      <nav class="tabs" aria-label="Filter status">
        <?php foreach (['' => 'Semua'] + array_map(static fn($m) => $m['label'], $statuses) as $key => $label): ?>
          <a href="<?= e(admin_url(['status' => $key])) ?>" <?= $filterStatus === (string) $key ? 'aria-current="true"' : '' ?>>
            <?= e($label) ?> <span class="tabs__count"><?= (int) ($tabCounts[$key] ?? 0) ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <form method="get" action="<?= e(url('admin/dashboard.php')) ?>" class="search" role="search">
        <?php if ($filterStatus !== ''): ?><input type="hidden" name="status" value="<?= e($filterStatus) ?>"><?php endif; ?>
        <div class="search__box">
          <label class="visually-hidden" for="q">Cari booking</label>
          <?= icon('search') ?>
          <input type="search" class="field__control" id="q" name="q" value="<?= e($search) ?>"
                 placeholder="Cari nama, email, telepon" maxlength="100">
        </div>
        <button type="submit" class="button button--solid">Cari</button>
      </form>
    </div>

    <!-- DAFTAR BOOKING -->
    <section class="ledger-wrap" aria-labelledby="daftar-title">
      <div class="ledger-head">
        <h2 id="daftar-title">Daftar booking</h2>
        <span class="muted small"><?= $matching ?> data<?= $isFiltered ? ' sesuai filter' : '' ?></span>
      </div>

      <table class="ledger">
        <caption class="visually-hidden">Daftar permintaan booking pelanggan</caption>
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Pelanggan</th>
            <th scope="col">Perawatan</th>
            <th scope="col">Tanggal</th>
            <th scope="col">Catatan</th>
            <th scope="col">Status</th>
            <th scope="col">Dikirim</th>
            <th scope="col"><span class="visually-hidden">Aksi</span></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="8" class="is-empty"><div class="empty">
              <?= icon('calendar-days') ?>
              <?= $isFiltered ? 'Tidak ada booking yang sesuai filter.' : 'Belum ada permintaan booking.' ?>
            </div></td></tr>
          <?php endif; ?>
          <?php foreach ($rows as $b): ?>
            <tr>
              <td data-label="ID" class="ledger__id">#<?= (int) $b['id'] ?></td>
              <td data-label="Pelanggan"><div>
                <div class="ledger__name"><?= e($b['nama']) ?></div>
                <div class="small"><a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></div>
                <?php if ((string) $b['phone'] !== ''): ?>
                  <div class="small"><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $b['phone'])) ?>"><?= e($b['phone']) ?></a></div>
                <?php endif; ?>
              </div></td>
              <td data-label="Perawatan"><?= e($b['service']) ?></td>
              <td data-label="Tanggal" class="text-nowrap"><?= e(format_tanggal($b['tanggal'])) ?></td>
              <td data-label="Catatan"><div class="ledger__note" title="<?= e($b['message']) ?>"><?= e($b['message']) ?></div></td>
              <td data-label="Status"><?= status_badge((string) $b['status']) ?></td>
              <td data-label="Dikirim" class="text-nowrap small"><?= e(date('d/m/Y H:i', strtotime((string) $b['created_at']))) ?></td>
              <td data-label="Aksi">
                <div class="ledger__actions">
                  <a class="button button--small" href="<?= e(url('admin/booking_edit.php?id=' . (int) $b['id'])) ?>">
                    Edit<span class="visually-hidden"> booking #<?= (int) $b['id'] ?></span>
                  </a>
                  <form method="post" action="<?= e(url('admin/booking_delete.php')) ?>"
                        data-confirm="Booking #<?= (int) $b['id'] ?> atas nama <?= e($b['nama']) ?> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
                        data-confirm-title="Hapus booking?" data-confirm-ok="Hapus booking">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                    <button type="submit" class="button button--danger button--small">
                      Hapus<span class="visually-hidden"> booking #<?= (int) $b['id'] ?></span>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if ($pages > 1): ?>
        <nav class="pager" aria-label="Halaman daftar booking">
          <?php if ($page > 1): ?>
            <a href="<?= e(admin_url(['page' => $page - 1])) ?>">Sebelumnya</a>
          <?php else: ?>
            <span class="is-off" aria-disabled="true">Sebelumnya</span>
          <?php endif; ?>
          <span class="pager__state">Halaman <?= $page ?> dari <?= $pages ?></span>
          <?php if ($page < $pages): ?>
            <a href="<?= e(admin_url(['page' => $page + 1])) ?>">Berikutnya</a>
          <?php else: ?>
            <span class="is-off" aria-disabled="true">Berikutnya</span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
