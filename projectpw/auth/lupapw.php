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
<div class="auth">
  <?php $authLine = ''; require __DIR__ . '/../includes/auth_visual.php'; ?>

  <main id="main" class="auth__panel">
    <div class="auth__card">
      <a class="auth__brand" href="<?= e(url('')) ?>">
        <img src="<?= e(asset('img/lotus.svg')) ?>" width="38" height="38" alt="">
        <span>Lipalas</span>
      </a>
      <h1>Lupa password?</h1>
      <p class="auth__sub">Demi keamanan, password tidak bisa diatur ulang sendiri dari halaman ini.</p>

      <p>Hubungi klinik dan sebutkan email yang Anda pakai untuk mendaftar. Admin akan membantu mengatur ulang password Anda.</p>

      <ul class="contact-list mb-4">
        <?php if ($c['whatsapp'] !== ''): ?>
          <li><?= icon('message-circle') ?>
            <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $c['whatsapp'])) ?>" target="_blank" rel="noopener noreferrer">WhatsApp klinik<span class="visually-hidden"> (tab baru)</span></a></li>
        <?php endif; ?>
        <?php if ($c['phone'] !== ''): ?>
          <li><?= icon('phone') ?>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $c['phone'])) ?>"><?= e($c['phone']) ?></a></li>
        <?php endif; ?>
        <?php if ($c['email'] !== ''): ?>
          <li><?= icon('mail') ?>
            <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></li>
        <?php endif; ?>
        <?php if ($c['whatsapp'] === '' && $c['phone'] === '' && $c['email'] === ''): ?>
          <li><?= icon('info') ?><span>Kontak klinik akan ditampilkan di sini setelah tersedia.</span></li>
        <?php endif; ?>
      </ul>

      <a class="button button--solid button--block" href="<?= e(url('auth/login.php')) ?>">Kembali ke halaman masuk</a>
      <a class="link-back auth__back" href="<?= e(url('')) ?>"><?= icon('arrow-left') ?>Kembali ke beranda</a>
    </div>
  </main>
</div>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
