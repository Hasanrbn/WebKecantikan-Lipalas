<?php
/**
 * Lupa password.
 *
 * Versi lama halaman ini mengizinkan SIAPA PUN mengganti password akun mana pun (termasuk admin)
 * hanya dengan mengetik emailnya, tanpa bukti kepemilikan akun. Itu celah pengambilalihan akun,
 * sehingga reset mandiri dinonaktifkan.
 *
 * Reset yang aman membutuhkan pengiriman email berisi tautan sekali pakai, dan website ini belum
 * punya layanan email. Sampai itu tersedia, password diatur ulang oleh pemilik sistem
 * lewat tools/manage_user.php (lihat README).
 */
require_once __DIR__ . '/../core/helpers.php';

guest_only();

$c = clinic();

$pageTitle       = 'Lupa Password';
$metaDescription = 'Bantuan mengatur ulang password akun ' . APP_NAME . '.';
$noindex         = true;
$bodyClass       = 'page-auth';
require __DIR__ . '/../includes/head.php';
?>
<div class="auth-layout">
  <aside class="auth-visual" aria-hidden="true">
    <img src="<?= e(asset('img/ruang-1200.jpg')) ?>" alt="" width="1200" height="800">
    <div class="auth-visual-text">
      <h2><?= e(APP_NAME) ?></h2>
    </div>
  </aside>

  <main id="main" class="auth-panel">
    <div class="auth-card">
      <img class="auth-logo" src="<?= e(asset('img/logo-256.png')) ?>" width="96" height="96" alt="Logo <?= e(APP_NAME) ?>">
      <h1>Lupa password?</h1>
      <p class="auth-sub">Demi keamanan akun Anda, password tidak dapat diatur ulang sendiri dari halaman ini.</p>

      <div class="panel mb-3">
        <p>Silakan hubungi klinik dan sebutkan email yang Anda pakai untuk mendaftar. Admin akan membantu mengatur ulang password Anda.</p>
        <ul class="contact-list mb-0">
          <?php if ($c['whatsapp'] !== ''): ?>
            <li><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
              <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $c['whatsapp'])) ?>" target="_blank" rel="noopener noreferrer">WhatsApp klinik<span class="visually-hidden"> (tab baru)</span></a></li>
          <?php endif; ?>
          <?php if ($c['phone'] !== ''): ?>
            <li><i class="fa-solid fa-phone" aria-hidden="true"></i>
              <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $c['phone'])) ?>"><?= e($c['phone']) ?></a></li>
          <?php endif; ?>
          <?php if ($c['email'] !== ''): ?>
            <li><i class="fa-solid fa-envelope" aria-hidden="true"></i>
              <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></li>
          <?php endif; ?>
          <?php if ($c['whatsapp'] === '' && $c['phone'] === '' && $c['email'] === ''): ?>
            <li><i class="fa-solid fa-circle-info" aria-hidden="true"></i>
              <span>Kontak klinik akan ditampilkan di sini setelah dikonfirmasi.</span></li>
          <?php endif; ?>
        </ul>
      </div>

      <a class="btn btn-lipalas w-100" href="<?= e(url('auth/login.php')) ?>">Kembali ke halaman masuk</a>
      <a class="auth-back" href="<?= e(url('')) ?>"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Kembali ke beranda</a>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
