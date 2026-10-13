<?php
/**
 * Header bersama. Tombol di kanan menyesuaikan status login (tamu / pelanggan / admin).
 * Tanpa JavaScript, daftar menu tetap tampil (menu lipat hanya aktif bila kelas .js ada).
 */
require_once __DIR__ . '/../core/helpers.php';

$navUser = null;
$navRole = $_SESSION['role'] ?? null;
if (is_logged_in()) {
    $navUser = current_user();
    $navRole = $navUser['role'] ?? null;
}
$navLinks = [
    'layanan'      => 'Perawatan',
    'tentang'      => 'Tentang',
    'cara-booking' => 'Cara booking',
    'faq'          => 'FAQ',
    'kontak'       => 'Kontak',
];
?>
<header class="site-header" id="top">
  <div class="wrap site-header__bar">
    <a class="brand" href="<?= e(url('')) ?>" aria-label="<?= e(APP_NAME) ?>, ke beranda">
      <img class="brand__mark" src="<?= e(asset('img/lotus.svg')) ?>" width="38" height="38" alt="">
      <span class="brand__text">
        <span class="brand__name">Lipalas</span>
        <span class="brand__sub">Beauty Clinic</span>
      </span>
    </a>

    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-menu">
      <?= icon('menu', 'icon--open') ?><?= icon('x', 'icon--close') ?>
      <span class="nav-toggle__label">Menu</span>
    </button>

    <div class="site-nav" id="site-menu">
      <nav aria-label="Navigasi utama">
        <ul class="site-nav__list">
          <?php foreach ($navLinks as $linkId => $linkLabel): ?>
            <li><a class="site-nav__link" href="<?= e(url('#' . $linkId)) ?>"><?= e($linkLabel) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="site-nav__actions">
        <?php if ($navUser === null): ?>
          <a class="link-quiet" href="<?= e(url('auth/login.php')) ?>">Masuk</a>
          <a class="button button--solid button--small" href="<?= e(url('user/dashboard.php#booking')) ?>">Buat janji temu</a>
        <?php else: ?>
          <?php if ($navRole === 'admin'): ?>
            <a class="button button--solid button--small" href="<?= e(url('admin/dashboard.php')) ?>">Dashboard admin</a>
          <?php else: ?>
            <a class="button button--solid button--small" href="<?= e(url('user/dashboard.php')) ?>">Akun saya</a>
          <?php endif; ?>
          <form method="post" action="<?= e(url('auth/logout.php')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="link-quiet">Keluar</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>
