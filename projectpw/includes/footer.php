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
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <img src="<?= e(asset('img/lotus.svg')) ?>" width="42" height="42" alt="">
          <span><?= e(APP_NAME) ?></span>
        </div>
        <p><?= e($c['tagline']) ?></p>
      </div>
      <div>
        <h2>Jelajahi</h2>
        <ul>
          <li><a href="<?= e(url('#layanan')) ?>">Perawatan</a></li>
          <li><a href="<?= e(url('#cara-booking')) ?>">Cara booking</a></li>
          <li><a href="<?= e(url('#faq')) ?>">FAQ</a></li>
          <li><a href="<?= e(url('#kontak')) ?>">Kontak</a></li>
        </ul>
      </div>
      <div>
        <h2>Akun</h2>
        <ul>
          <?php if (is_logged_in()): ?>
            <li><a href="<?= e(url('user/dashboard.php')) ?>">Akun saya</a></li>
          <?php else: ?>
            <li><a href="<?= e(url('auth/login.php')) ?>">Masuk</a></li>
            <li><a href="<?= e(url('auth/register.php')) ?>">Daftar</a></li>
          <?php endif; ?>
          <li><a href="<?= e(url('user/dashboard.php#booking')) ?>">Buat janji temu</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-legal">
      <?php if (!empty($c['demo'])): ?>
        <p>Situs demo untuk portofolio. Klinik, alamat, kontak, harga, dan durasi di sini fiktif.</p>
      <?php endif; ?>
      <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Informasi di website ini bersifat umum dan bukan nasihat medis.</p>
    </div>
  </div>
</footer>

<script src="<?= e(asset('js/main.js')) ?>"></script>
<?= $extraScripts ?>
</body>
</html>
