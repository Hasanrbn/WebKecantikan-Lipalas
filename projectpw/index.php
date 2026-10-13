<?php
/**
 * Beranda publik Lipalas Beauty Clinic.
 * Bisa dibuka siapa saja tanpa login. Data layanan dari core/services.php,
 * data kontak dari core/clinic.php.
 */
require_once __DIR__ . '/core/helpers.php';

$clinic   = clinic();
$services = services();
$isDemo   = !empty($clinic['demo']);

// Data terstruktur schema.org: hanya memuat data yang benar-benar terisi (dan bukan data demo).
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'BeautySalon',
    'name'     => APP_NAME,
    'url'      => absolute_url(''),
    'logo'     => absolute_url('aset/img/logo-512.webp'),
    'image'    => absolute_url('aset/img/og-image.jpg'),
];
if (!$isDemo) {
    if ($clinic['address'] !== '')   { $jsonLd['address'] = $clinic['address']; }
    if ($clinic['phone'] !== '')     { $jsonLd['telephone'] = $clinic['phone']; }
    if ($clinic['email'] !== '')     { $jsonLd['email'] = $clinic['email']; }
    if ($clinic['instagram'] !== '') { $jsonLd['sameAs'] = [$clinic['instagram']]; }
}

$pageTitle       = null;
$metaDescription = 'Lipalas Beauty Clinic: perawatan wajah, tubuh, dan relaksasi dalam satu ruang yang tenang. Lihat daftar perawatan dan kirim permintaan janji temu secara online.';

// Kelompokkan layanan untuk daftar bergaya menu.
$groups = [];
foreach ($services as $s) {
    $groups[$s['group'] ?? 'Lainnya'][] = $s;
}

$hasContact = $clinic['address'] !== '' || $clinic['phone'] !== '' || $clinic['whatsapp'] !== ''
           || $clinic['email'] !== '' || !empty($clinic['hours']);

$firstHours   = !empty($clinic['hours']) ? array_key_first($clinic['hours']) : null;
$addressInline = trim(preg_replace('/\s*\R\s*/', ', ', $clinic['address']));

$faqs = [
    ['Apakah saya perlu akun untuk melihat informasi klinik?',
     'Tidak. Semua informasi di halaman ini bisa dilihat tanpa login. Akun hanya diperlukan saat Anda mengirim permintaan janji temu.'],
    ['Apakah booking langsung terkonfirmasi?',
     'Belum. Setelah dikirim, permintaan Anda berstatus “Menunggu konfirmasi” sampai tim kami mengonfirmasi tanggalnya. Statusnya bisa dilihat di halaman Akun saya.'],
    ['Bagaimana jika saya ingin mengubah atau membatalkan janji?',
     'Hubungi kami lewat kontak di bagian bawah halaman ini. Tim kami akan memperbarui status booking Anda.'],
    ['Berapa biaya perawatan?',
     'Harga tiap perawatan tertera pada daftar di atas. Biaya akhir dikonfirmasi tim kami setelah konsultasi, karena kebutuhan setiap orang berbeda.'],
    ['Apakah perawatan ini cocok untuk saya?',
     'Kesesuaian perawatan dibahas bersama tenaga klinik saat konsultasi. Informasi di website ini bersifat umum dan bukan nasihat medis.'],
];
if ($isDemo) {
    $faqs[] = ['Apakah ini klinik sungguhan?',
               'Bukan. Lipalas adalah proyek fiktif untuk portofolio. Alamat, kontak, harga, dan durasi di halaman ini hanya contoh.'];
}

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/navbar.php';
?>
<main id="main">

  <?php $flash = flash_render(); if ($flash !== ''): ?>
    <div class="wrap pt-3"><?= $flash ?></div>
  <?php endif; ?>

  <!-- HERO -->
  <section id="home" class="hero" aria-labelledby="hero-title">
    <div class="wrap hero__grid">
      <div class="hero__text">
        <h1 id="hero-title">Rawat diri, tanpa terburu-buru.</h1>
        <p class="hero__lead">
          Perawatan wajah, tubuh, dan relaksasi di ruang yang tenang.
          Kirim permintaan janji temu, lalu tim kami mengonfirmasi jadwalnya.
        </p>
        <div class="hero__actions">
          <a class="button button--solid" href="#layanan">Lihat perawatan</a>
          <a class="button" href="<?= e(url('user/dashboard.php#booking')) ?>">Buat janji temu</a>
        </div>
        <?php if ($firstHours !== null || $addressInline !== ''): ?>
          <ul class="hero__facts">
            <?php if ($firstHours !== null): ?>
              <li><?= icon('clock') ?><span>Buka <?= e($firstHours) ?>, <?= e($clinic['hours'][$firstHours]) ?></span></li>
            <?php endif; ?>
            <?php if ($addressInline !== ''): ?>
              <li><?= icon('map-pin') ?><span><?= e($addressInline) ?></span></li>
            <?php endif; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div class="petal" role="img" aria-label="Ruang perawatan Lipalas dengan kursi perawatan dan dinding blush bergambar pohon berbunga">
        <div class="petal__img">
          <picture>
            <source type="image/webp"
                    srcset="<?= e(asset('img/ruang-potret-480.webp')) ?> 480w, <?= e(asset('img/ruang-potret-853.webp')) ?> 853w"
                    sizes="(min-width: 900px) 480px, 352px">
            <img src="<?= e(asset('img/ruang-potret-853.jpg')) ?>"
                 srcset="<?= e(asset('img/ruang-potret-480.jpg')) ?> 480w, <?= e(asset('img/ruang-potret-853.jpg')) ?> 853w"
                 sizes="(min-width: 900px) 480px, 352px"
                 width="853" height="1066" alt="" fetchpriority="high">
          </picture>
        </div>
        <svg class="petal__line" viewBox="0 0 400 500" aria-hidden="true" focusable="false">
          <path pathLength="1" d="M1,499 L1,260 C1,140 110,60 200,1 C290,60 399,140 399,260 L399,499 Z"/>
        </svg>
      </div>
    </div>
  </section>

  <!-- PERAWATAN -->
  <section id="layanan" class="section section--tint" aria-labelledby="layanan-title">
    <div class="wrap menu-grid">
      <div class="menu-grid__intro">
        <h2 id="layanan-title">Perawatan</h2>
        <p>Buka salah satu untuk membaca penjelasannya, atau langsung kirim permintaan janji temu untuk perawatan yang Anda pilih.</p>
        <?php if ($isDemo): ?>
          <p class="demo-note"><?= icon('info') ?><span>Harga dan durasi pada daftar ini adalah contoh untuk situs demo.</span></p>
        <?php endif; ?>
      </div>

      <div class="frame">
        <?php foreach ($groups as $groupName => $items): ?>
          <section class="menu__group" aria-labelledby="grup-<?= e(strtolower($groupName)) ?>">
            <h3 class="menu__group-title" id="grup-<?= e(strtolower($groupName)) ?>"><?= e($groupName) ?></h3>
            <ul class="menu__list">
              <?php foreach ($items as $s): ?>
                <li>
                  <details class="dish" name="layanan">
                    <summary>
                      <span class="dish__row">
                        <span class="dish__name"><?= e($s['name']) ?></span>
                        <span class="dish__dots" aria-hidden="true"></span>
                      </span>
                      <span class="dish__price">
                        <?= !empty($s['price']) ? e($s['price']) : 'Hubungi klinik' ?>
                        <?= icon('chevron-down', 'dish__chev') ?>
                      </span>
                      <span class="dish__sum">
                        <?php if (!empty($s['duration'])): ?><span class="dish__time"><?= e($s['duration']) ?></span><?php endif; ?>
                        <span><?= e($s['summary']) ?></span>
                      </span>
                    </summary>
                    <div class="dish__body">
                      <p><?= e($s['detail']) ?></p>
                      <a class="button button--small" href="<?= e(url('user/dashboard.php?layanan=' . $s['slug'] . '#booking')) ?>">
                        Buat janji untuk <?= e($s['name']) ?>
                      </a>
                    </div>
                  </details>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- TENTANG -->
  <section id="tentang" class="section" aria-labelledby="tentang-title">
    <div class="wrap about-grid">
      <div class="about-grid__text">
        <h2 id="tentang-title">Ruang untuk merawat diri, bukan antre.</h2>
        <?php foreach ($clinic['about'] as $para): ?>
          <p><?= e($para) ?></p>
        <?php endforeach; ?>
        <?php if (!empty($clinic['about_points'])): ?>
          <dl class="facts">
            <?php foreach ($clinic['about_points'] as [$term, $desc]): ?>
              <div><dt><?= e($term) ?></dt><dd><?= e($desc) ?></dd></div>
            <?php endforeach; ?>
          </dl>
        <?php endif; ?>
      </div>
      <div class="about-grid__media">
        <div class="offset-frame">
          <img src="<?= e(asset('img/ruang-mural-520.jpg')) ?>"
               srcset="<?= e(asset('img/ruang-mural-360.jpg')) ?> 360w, <?= e(asset('img/ruang-mural-520.jpg')) ?> 520w"
               sizes="(min-width: 900px) 400px, 90vw"
               width="520" height="520" loading="lazy"
               alt="Mural pohon berbunga pada dinding ruang perawatan, dengan bingkai molding tipis">
        </div>
      </div>
    </div>
  </section>

  <!-- CARA BOOKING (urutan langkah yang sebenarnya) -->
  <section id="cara-booking" class="section section--dark" aria-labelledby="cara-title">
    <div class="wrap steps-grid">
      <div>
        <h2 id="cara-title">Cara membuat janji</h2>
        <ol class="steps">
          <li>
            <div>
              <h3>Pilih perawatan</h3>
              <p>Lihat daftar perawatan dan pilih yang Anda minati.</p>
            </div>
          </li>
          <li>
            <div>
              <h3>Masuk atau daftar</h3>
              <p>Buat akun gratis atau masuk, lalu isi tanggal yang diinginkan dan nomor yang bisa kami hubungi.</p>
            </div>
          </li>
          <li>
            <div>
              <h3>Tunggu konfirmasi</h3>
              <p>Permintaan berstatus “Menunggu konfirmasi” sampai tim kami mengonfirmasi tanggalnya. Pantau di Akun saya.</p>
            </div>
          </li>
        </ol>
        <a class="button" href="<?= e(url('user/dashboard.php#booking')) ?>">Buat janji temu</a>
      </div>
      <div class="steps-grid__media">
        <div class="offset-frame">
          <picture>
            <source type="image/webp"
                    srcset="<?= e(asset('img/ruang-lebar-640.webp')) ?> 640w, <?= e(asset('img/ruang-lebar-1050.webp')) ?> 1050w"
                    sizes="(min-width: 900px) 520px, 90vw">
            <img src="<?= e(asset('img/ruang-lebar-1050.jpg')) ?>"
                 srcset="<?= e(asset('img/ruang-lebar-640.jpg')) ?> 640w, <?= e(asset('img/ruang-lebar-1050.jpg')) ?> 1050w"
                 sizes="(min-width: 900px) 520px, 90vw"
                 width="1050" height="700" loading="lazy"
                 alt="Dua kursi perawatan krem di depan dinding blush dengan cermin dan rak produk">
          </picture>
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($clinic['testimonials'])): ?>
  <!-- TESTIMONI (tampil hanya jika ada testimoni asli di core/clinic.php) -->
  <section id="testimoni" class="section section--tint" aria-labelledby="testimoni-title">
    <div class="wrap wrap--narrow">
      <h2 id="testimoni-title">Kata pelanggan</h2>
      <?php foreach ($clinic['testimonials'] as $t): ?>
        <figure class="mb-4">
          <blockquote><p><?= e($t['isi'] ?? '') ?></p></blockquote>
          <figcaption class="muted"><?= e($t['nama'] ?? '') ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- FAQ -->
  <section id="faq" class="section faq" aria-labelledby="faq-title">
    <div class="wrap wrap--narrow">
      <div class="faq-head">
        <h2 id="faq-title">Pertanyaan yang sering diajukan</h2>
      </div>
      <?php foreach ($faqs as [$q, $a]): ?>
        <details name="faq">
          <summary><?= e($q) ?><?= icon('chevron-down') ?></summary>
          <p class="faq__a"><?= e($a) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- KONTAK -->
  <section id="kontak" class="section section--tint" aria-labelledby="kontak-title">
    <div class="wrap contact-grid">
      <div>
        <h2 id="kontak-title">Datang atau hubungi kami</h2>
        <?php if ($isDemo): ?>
          <p class="demo-note"><?= icon('info') ?><span>Data kontak di bawah fiktif, hanya untuk contoh.</span></p>
        <?php endif; ?>

        <?php if ($hasContact): ?>
          <ul class="contact-list">
            <?php if ($clinic['address'] !== ''): ?>
              <li><?= icon('map-pin') ?>
                <div><strong>Alamat</strong><?= nl2br(e($clinic['address'])) ?>
                <?php if ($clinic['maps_url'] !== ''): ?>
                  <br><a href="<?= e($clinic['maps_url']) ?>" target="_blank" rel="noopener noreferrer">Buka di peta<span class="visually-hidden"> (tab baru)</span></a>
                <?php endif; ?></div></li>
            <?php endif; ?>
            <?php if (!empty($clinic['hours'])): ?>
              <li><?= icon('clock') ?>
                <div><strong>Jam operasional</strong>
                  <dl class="hours">
                    <?php foreach ($clinic['hours'] as $label => $jam): ?>
                      <dt><?= e($label) ?></dt><dd><?= e($jam) ?></dd>
                    <?php endforeach; ?>
                  </dl></div></li>
            <?php endif; ?>
            <?php if ($clinic['phone'] !== ''): ?>
              <li><?= icon('phone') ?>
                <div><strong>Telepon</strong>
                <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $clinic['phone'])) ?>"><?= e($clinic['phone']) ?></a></div></li>
            <?php endif; ?>
            <?php if ($clinic['whatsapp'] !== ''): ?>
              <li><?= icon('message-circle') ?>
                <div><strong>WhatsApp</strong>
                <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $clinic['whatsapp'])) ?>" target="_blank" rel="noopener noreferrer">Kirim pesan<span class="visually-hidden"> lewat WhatsApp (tab baru)</span></a></div></li>
            <?php endif; ?>
            <?php if ($clinic['email'] !== ''): ?>
              <li><?= icon('mail') ?>
                <div><strong>Email</strong>
                <a href="mailto:<?= e($clinic['email']) ?>"><?= e($clinic['email']) ?></a></div></li>
            <?php endif; ?>
          </ul>
        <?php else: ?>
          <p class="muted">Informasi kontak klinik sedang disiapkan. Sementara itu Anda tetap bisa mengirim permintaan janji temu.</p>
        <?php endif; ?>
      </div>

      <div class="frame cta-panel">
        <img src="<?= e(asset('img/lotus.svg')) ?>" width="64" height="64" alt="">
        <h3>Siap membuat janji?</h3>
        <p>Kirim permintaan janji temu Anda. Tim kami akan mengonfirmasi tanggalnya.</p>
        <a class="button button--solid" href="<?= e(url('user/dashboard.php#booking')) ?>">Buat janji temu</a>
      </div>
    </div>
  </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
