<?php
/**
 * Panel visual di sisi kiri halaman masuk/daftar (disembunyikan di layar kecil).
 * Variabel: $authLine (kalimat pendek di bawah foto).
 */
$authLine = $authLine ?? '';
?>
<aside class="auth__visual" aria-hidden="true">
  <div class="petal">
    <div class="petal__img">
      <img src="<?= e(asset('img/ruang-potret-480.jpg')) ?>" width="480" height="600" alt="">
    </div>
    <svg class="petal__line" viewBox="0 0 400 500" focusable="false">
      <path pathLength="1" d="M1,499 L1,260 C1,140 110,60 200,1 C290,60 399,140 399,260 L399,499 Z"/>
    </svg>
  </div>
  <?php if ($authLine !== ''): ?><p><?= e($authLine) ?></p><?php endif; ?>
</aside>
