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

$stats    = ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'today' => 0];
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

$pageTitle       = 'Dashboard Admin';
$metaDescription = 'Kelola booking pelanggan ' . APP_NAME . '.';
$noindex         = true;
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/navbar.php';
?>
<main id="main">
  <div class="page-head">
    <div class="container">
      <h1>Dashboard Admin</h1>
      <p>Kelola permintaan booking pelanggan <?= e(APP_NAME) ?>.</p>
    </div>
  </div>

  <div class="container py-4">
    <?= flash_render() ?>

    <?php if (!$ready): ?>
      <div class="alert alert-danger" role="alert">
        <strong>Database belum dimigrasi.</strong> Kolom <code>status</code> pada <code>table_booking</code>
        atau tabel <code>rate_limit_log</code> belum ada. Backup database, lalu jalankan
        <code>database/migrations/001_upgrade_existing_database.sql</code> (lihat README).
      </div>
    <?php endif; ?>

    <?php if ($contactMissing): ?>
      <div class="alert alert-info" role="status">
        Informasi kontak klinik (alamat, telepon, jam operasional) belum diisi, sehingga belum tampil di website.
        Isi di <code>core/clinic.php</code>.
      </div>
    <?php endif; ?>

    <!-- RINGKASAN -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="stat-card"><div class="stat-label">Total booking</div><div class="stat-value"><?= $stats['total'] ?></div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card"><div class="stat-label">Menunggu konfirmasi</div><div class="stat-value"><?= $stats['pending'] ?></div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card"><div class="stat-label">Dikonfirmasi</div><div class="stat-value"><?= $stats['confirmed'] ?></div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-label">Layanan terbanyak dipesan</div>
          <div class="stat-text"><?= $topService ? e($topService['service']) . ' (' . (int) $topService['c'] . ')' : '-' ?></div>
          <div class="stat-label mt-1">Jadwal hari ini: <?= $stats['today'] ?></div>
        </div>
      </div>
    </div>

    <!-- FILTER -->
    <section class="panel mb-4" aria-labelledby="filter-title">
      <h2 id="filter-title" class="visually-hidden">Filter booking</h2>
      <div class="d-flex flex-column flex-lg-row gap-3 justify-content-between">
        <nav class="filter-pills" aria-label="Filter status">
          <a href="<?= e(admin_url(['status' => ''])) ?>" class="<?= $filterStatus === '' ? 'active' : '' ?>"
             <?= $filterStatus === '' ? 'aria-current="true"' : '' ?>>Semua</a>
          <?php foreach ($statuses as $key => $meta): ?>
            <a href="<?= e(admin_url(['status' => $key])) ?>" class="<?= $filterStatus === $key ? 'active' : '' ?>"
               <?= $filterStatus === $key ? 'aria-current="true"' : '' ?>><?= e($meta['label']) ?></a>
          <?php endforeach; ?>
        </nav>

        <form method="get" action="<?= e(url('admin/dashboard.php')) ?>" class="d-flex gap-2" role="search">
          <?php if ($filterStatus !== ''): ?><input type="hidden" name="status" value="<?= e($filterStatus) ?>"><?php endif; ?>
          <label class="visually-hidden" for="q">Cari booking</label>
          <input type="search" class="form-control" id="q" name="q" value="<?= e($search) ?>"
                 placeholder="Cari nama, email, telepon, layanan" maxlength="100">
          <button type="submit" class="btn btn-lipalas">Cari</button>
        </form>
      </div>
    </section>

    <!-- DAFTAR BOOKING -->
    <section class="panel" aria-labelledby="daftar-title">
      <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 mb-3">
        <h2 id="daftar-title" class="mb-0">Daftar booking</h2>
        <span class="text-muted-lipalas small"><?= $matching ?> data<?= ($filterStatus !== '' || $search !== '') ? ' sesuai filter' : '' ?></span>
      </div>

      <div class="table-responsive">
        <table class="table table-lipalas table-admin align-middle">
          <caption class="visually-hidden">Daftar permintaan booking pelanggan</caption>
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Pelanggan</th>
              <th scope="col">Layanan</th>
              <th scope="col">Tanggal</th>
              <th scope="col">Catatan</th>
              <th scope="col">Status</th>
              <th scope="col">Dikirim</th>
              <th scope="col" class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="8"><div class="empty-state mb-0">
                <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                <?= ($filterStatus !== '' || $search !== '') ? 'Tidak ada booking yang sesuai filter.' : 'Belum ada permintaan booking.' ?>
              </div></td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $b): ?>
              <tr>
                <td class="fw-semibold">#<?= (int) $b['id'] ?></td>
                <td>
                  <div class="fw-semibold"><?= e($b['nama']) ?></div>
                  <div class="small"><a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></div>
                  <?php if ((string) $b['phone'] !== ''): ?>
                    <div class="small"><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $b['phone'])) ?>"><?= e($b['phone']) ?></a></div>
                  <?php endif; ?>
                </td>
                <td><?= e($b['service']) ?></td>
                <td class="text-nowrap"><?= e(format_tanggal($b['tanggal'])) ?></td>
                <td><div class="cell-note" title="<?= e($b['message']) ?>"><?= e($b['message']) ?></div></td>
                <td><?= status_badge((string) $b['status']) ?></td>
                <td class="text-nowrap small"><?= e(date('d/m/Y H:i', strtotime((string) $b['created_at']))) ?></td>
                <td class="text-end text-nowrap">
                  <a class="btn btn-lipalas-outline btn-sm" href="<?= e(url('admin/booking_edit.php?id=' . (int) $b['id'])) ?>">
                    Edit<span class="visually-hidden"> booking #<?= (int) $b['id'] ?></span>
                  </a>
                  <form method="post" action="<?= e(url('admin/booking_delete.php')) ?>" class="d-inline"
                        data-confirm="Hapus booking #<?= (int) $b['id'] ?> atas nama <?= e($b['nama']) ?>? Tindakan ini tidak dapat dibatalkan.">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius:999px">
                      Hapus<span class="visually-hidden"> booking #<?= (int) $b['id'] ?></span>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($pages > 1): ?>
        <nav class="mt-3" aria-label="Halaman daftar booking">
          <ul class="pagination justify-content-center mb-0 flex-wrap">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= e(admin_url(['page' => max(1, $page - 1)])) ?>" <?= $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Sebelumnya</a>
            </li>
            <li class="page-item disabled"><span class="page-link">Halaman <?= $page ?> dari <?= $pages ?></span></li>
            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= e(admin_url(['page' => min($pages, $page + 1)])) ?>" <?= $page >= $pages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Berikutnya</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
