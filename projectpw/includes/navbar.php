<?php
/**
 * Navbar bersama untuk semua halaman yang memakai layout utama.
 * Tampilan tombol di kanan menyesuaikan status login (tamu / pelanggan / admin).
 */
require_once __DIR__ . '/../core/helpers.php';

$navUser  = null;
$navRole  = $_SESSION['role'] ?? null;
if (is_logged_in()) {
    $navUser = current_user();
    $navRole = $navUser['role'] ?? null;
}
?>
<header>
  <nav class="navbar navbar-expand-lg sticky-top lipalas-nav" aria-label="Navigasi utama">
    <div class="container">
      <a class="navbar-brand" href="<?= e(url('')) ?>">
        <img src="<?= e(asset('img/logo-128.webp')) ?>" width="42" height="42" alt="">
        <span><?= e(APP_NAME) ?></span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
              aria-controls="navMenu" aria-expanded="false" aria-label="Buka menu navigasi">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="<?= e(url('#home')) ?>">Beranda</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('#tentang')) ?>">Tentang</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('#layanan')) ?>">Layanan</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('#cara-booking')) ?>">Cara Booking</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('#faq')) ?>">FAQ</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('#kontak')) ?>">Kontak</a></li>
        </ul>

        <div class="nav-actions ms-lg-3">
          <?php if ($navUser === null): ?>
            <a class="btn-link-quiet" href="<?= e(url('auth/login.php')) ?>">Masuk</a>
            <a class="btn btn-lipalas btn-sm" href="<?= e(url('user/dashboard.php#booking')) ?>">Buat Janji</a>
          <?php else: ?>
            <?php if ($navRole === 'admin'): ?>
              <a class="btn btn-lipalas btn-sm" href="<?= e(url('admin/dashboard.php')) ?>">Dashboard Admin</a>
            <?php else: ?>
              <a class="btn btn-lipalas btn-sm" href="<?= e(url('user/dashboard.php')) ?>">Akun Saya</a>
            <?php endif; ?>
            <form method="post" action="<?= e(url('auth/logout.php')) ?>" class="d-inline">
              <?= csrf_field() ?>
              <button type="submit" class="btn-link-quiet">Keluar</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
</header>
