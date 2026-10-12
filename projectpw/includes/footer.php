<?php
/**
 * Footer bersama + skrip. Penutup </body></html>.
 * Variabel opsional: $extraScripts (string HTML tambahan sebelum </body>).
 */
require_once __DIR__ . '/../core/helpers.php';
$c = clinic();
$extraScripts = $extraScripts ?? '';
?>
<footer class="site-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-5">
        <h2><?= e(APP_NAME) ?></h2>
        <p class="mb-0"><?= e($c['tagline']) ?></p>
      </div>
      <div class="col-6 col-md-3">
        <h2>Jelajahi</h2>
        <ul class="footer-links">
          <li><a href="<?= e(url('#layanan')) ?>">Layanan</a></li>
          <li><a href="<?= e(url('#cara-booking')) ?>">Cara Booking</a></li>
          <li><a href="<?= e(url('#faq')) ?>">FAQ</a></li>
          <li><a href="<?= e(url('#kontak')) ?>">Kontak</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-4">
        <h2>Akun</h2>
        <ul class="footer-links">
          <?php if (is_logged_in()): ?>
            <li><a href="<?= e(url('user/dashboard.php')) ?>">Akun Saya</a></li>
          <?php else: ?>
            <li><a href="<?= e(url('auth/login.php')) ?>">Masuk</a></li>
            <li><a href="<?= e(url('auth/register.php')) ?>">Daftar</a></li>
          <?php endif; ?>
          <li><a href="<?= e(url('user/dashboard.php#booking')) ?>">Buat Janji</a></li>
        </ul>
      </div>
    </div>
    <div class="copy">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Informasi di website ini bersifat umum dan bukan nasihat medis.</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/main.js')) ?>"></script>
<?= $extraScripts ?>
</body>
</html>
