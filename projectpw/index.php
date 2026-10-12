<?php
/**
 * Beranda publik Lipalas Beauty Clinic.
 * Bisa dibuka siapa saja tanpa login. Data layanan dari core/services.php,
 * data kontak dari core/clinic.php.
 */
require_once __DIR__ . '/core/helpers.php';

$clinic   = clinic();
$services = services();

// Data terstruktur schema.org: hanya memuat data yang benar-benar terisi.
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'BeautySalon',
    'name'     => APP_NAME,
    'url'      => absolute_url(''),
    'logo'     => absolute_url('aset/img/logo-512.webp'),
    'image'    => absolute_url('aset/img/og-image.jpg'),
];
if ($clinic['address'] !== '')  { $jsonLd['address'] = $clinic['address']; }
if ($clinic['phone'] !== '')    { $jsonLd['telephone'] = $clinic['phone']; }
if ($clinic['email'] !== '')    { $jsonLd['email'] = $clinic['email']; }
if ($clinic['instagram'] !== ''){ $jsonLd['sameAs'] = [$clinic['instagram']]; }

$pageTitle       = null;
$metaDescription = 'Lipalas Beauty Clinic: klinik kecantikan dengan layanan perawatan wajah, tubuh, relaksasi, dan konsultasi. Lihat layanan dan kirim permintaan janji temu secara online.';

$hasContact = $clinic['address'] !== '' || $clinic['phone'] !== '' || $clinic['whatsapp'] !== ''
           || $clinic['email'] !== '' || !empty($clinic['hours']);

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/navbar.php';
?>
<main id="main">

  <?php $flash = flash_render(); if ($flash !== ''): ?>
    <div class="container pt-3"><?= $flash ?></div>
  <?php endif; ?>

  <!-- HERO -->
  <section id="home" class="hero" aria-labelledby="hero-title">
    <div class="container">
      <div class="row align-items-center gy-4 gx-lg-5">
        <div class="col-lg-6">
          <span class="eyebrow">Klinik Kecantikan</span>
          <h1 id="hero-title">Rawat diri Anda di <?= e(APP_NAME) ?></h1>
          <p class="lead">
            Jelajahi layanan perawatan kami dan kirim permintaan janji temu secara online.
            Jadwal akan dikonfirmasi oleh tim klinik.
          </p>
          <div class="hero-actions">
            <a class="btn btn-lipalas btn-lg" href="#layanan">Lihat Layanan</a>
            <a class="btn btn-lipalas-outline btn-lg" href="<?= e(url('user/dashboard.php#booking')) ?>">Buat Janji</a>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="hero-photo">
            <picture>
              <source type="image/webp"
                      srcset="<?= e(asset('img/ruang-480.webp')) ?> 480w, <?= e(asset('img/ruang-800.webp')) ?> 800w, <?= e(asset('img/ruang-1200.webp')) ?> 1200w"
                      sizes="(min-width: 992px) 560px, 100vw">
              <img src="<?= e(asset('img/ruang-800.jpg')) ?>"
                   srcset="<?= e(asset('img/ruang-480.jpg')) ?> 480w, <?= e(asset('img/ruang-800.jpg')) ?> 800w, <?= e(asset('img/ruang-1200.jpg')) ?> 1200w"
                   sizes="(min-width: 992px) 560px, 100vw"
                   width="800" height="533"
                   alt="Ruang perawatan dengan kursi treatment dan dinding berwarna blush"
                   fetchpriority="high">
            </picture>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TENTANG -->
  <section id="tentang" class="section" aria-labelledby="tentang-title">
    <div class="container">
      <div class="row gy-4 gx-lg-5 align-items-center">
        <div class="col-lg-6">
          <span class="eyebrow">Tentang Klinik</span>
          <h2 id="tentang-title" class="section-title text-start">Tentang <?= e(APP_NAME) ?></h2>
          <?php foreach ($clinic['about'] as $para): ?>
            <p><?= e($para) ?></p>
          <?php endforeach; ?>
        </div>
        <div class="col-lg-6">
          <div class="panel">
            <h3 class="h5 mb-3">Kemudahan di website ini</h3>
            <ul class="contact-list mb-0">
              <li><i class="fa-solid fa-list-check" aria-hidden="true"></i>
                <span>Lihat informasi layanan kapan saja tanpa perlu membuat akun.</span></li>
              <li><i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                <span>Kirim permintaan janji temu secara online dengan memilih layanan dan tanggal.</span></li>
              <li><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                <span>Pantau status permintaan Anda di halaman Akun Saya.</span></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- LAYANAN -->
  <section id="layanan" class="section section-alt" aria-labelledby="layanan-title">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Layanan</span>
        <h2 id="layanan-title" class="section-title">Layanan Kami</h2>
        <p class="section-lead">Pilih layanan yang Anda minati, lalu kirim permintaan janji temu.</p>
      </div>

      <div class="row g-4">
        <?php foreach ($services as $s): ?>
          <div class="col-md-6 col-lg-4">
            <article class="service-card">
              <div class="service-media">
                <?php if (!empty($s['image'])): ?>
                  <img src="<?= e(asset($s['image'])) ?>" alt="<?= e($s['name']) ?>" loading="lazy" width="600" height="400">
                <?php else: ?>
                  <i class="fa-solid <?= e($s['icon']) ?>" aria-hidden="true"></i>
                <?php endif; ?>
              </div>
              <div class="service-body">
                <h3><?= e($s['name']) ?></h3>
                <p><?= e($s['summary']) ?></p>
                <div class="service-meta">
                  <?php if (!empty($s['duration'])): ?>
                    <div><i class="fa-regular fa-clock me-1" aria-hidden="true"></i><?= e($s['duration']) ?></div>
                  <?php endif; ?>
                  <div><i class="fa-solid fa-tag me-1" aria-hidden="true"></i>
                    <?= !empty($s['price']) ? e($s['price']) : 'Hubungi klinik untuk informasi harga' ?></div>
                </div>
                <div class="service-actions">
                  <button type="button" class="btn btn-lipalas-outline" data-bs-toggle="modal"
                          data-bs-target="#svc-<?= e($s['slug']) ?>">Detail</button>
                  <a class="btn btn-lipalas" href="<?= e(url('user/dashboard.php?layanan=' . $s['slug'] . '#booking')) ?>">Buat Janji</a>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php foreach ($services as $s): ?>
    <div class="modal fade" id="svc-<?= e($s['slug']) ?>" tabindex="-1"
         aria-labelledby="svc-<?= e($s['slug']) ?>-label" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px;">
          <div class="modal-header">
            <h3 class="modal-title h5" id="svc-<?= e($s['slug']) ?>-label"><?= e($s['name']) ?></h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            <p><?= e($s['detail']) ?></p>
            <?php if (!empty($s['duration'])): ?><p class="mb-1"><strong>Durasi:</strong> <?= e($s['duration']) ?></p><?php endif; ?>
            <p class="mb-0"><strong>Harga:</strong>
              <?= !empty($s['price']) ? e($s['price']) : 'Hubungi klinik untuk informasi harga.' ?></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-lipalas-outline" data-bs-dismiss="modal">Tutup</button>
            <a class="btn btn-lipalas" href="<?= e(url('user/dashboard.php?layanan=' . $s['slug'] . '#booking')) ?>">Buat Janji</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- CARA BOOKING -->
  <section id="cara-booking" class="section" aria-labelledby="cara-title">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Cara Booking</span>
        <h2 id="cara-title" class="section-title">Tiga langkah mudah</h2>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="step-card">
            <span class="step-num" aria-hidden="true">1</span>
            <h3>Pilih layanan</h3>
            <p>Lihat daftar layanan di atas dan pilih perawatan yang Anda inginkan.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="step-card">
            <span class="step-num" aria-hidden="true">2</span>
            <h3>Masuk atau daftar</h3>
            <p>Buat akun gratis atau masuk, lalu isi tanggal yang Anda inginkan.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="step-card">
            <span class="step-num" aria-hidden="true">3</span>
            <h3>Tunggu konfirmasi</h3>
            <p>Permintaan Anda berstatus &ldquo;Menunggu konfirmasi&rdquo; sampai klinik mengonfirmasi jadwal. Status dapat dipantau di Akun Saya.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($clinic['testimonials'])): ?>
  <!-- TESTIMONI (tampil hanya jika ada testimoni asli di core/clinic.php) -->
  <section id="testimoni" class="section section-alt" aria-labelledby="testimoni-title">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Testimoni</span>
        <h2 id="testimoni-title" class="section-title">Kata Pelanggan</h2>
      </div>
      <div class="row g-4">
        <?php foreach ($clinic['testimonials'] as $t): ?>
          <div class="col-md-4">
            <figure class="panel h-100 mb-0">
              <blockquote class="mb-3"><p class="mb-0"><?= e($t['isi'] ?? '') ?></p></blockquote>
              <figcaption class="text-muted-lipalas">&mdash; <?= e($t['nama'] ?? '') ?></figcaption>
            </figure>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- FAQ -->
  <section id="faq" class="section section-alt faq" aria-labelledby="faq-title">
    <div class="container" style="max-width: 820px;">
      <div class="section-head">
        <span class="eyebrow">FAQ</span>
        <h2 id="faq-title" class="section-title">Pertanyaan yang sering diajukan</h2>
      </div>
      <?php
      $faqs = [
          ['Apakah saya perlu akun untuk melihat informasi klinik?',
           'Tidak. Semua informasi di halaman ini dapat dilihat tanpa login. Akun hanya diperlukan saat Anda mengirim permintaan janji temu.'],
          ['Apakah booking langsung terkonfirmasi?',
           'Belum. Setelah dikirim, permintaan Anda berstatus “Menunggu konfirmasi” sampai tim klinik mengonfirmasi jadwalnya. Anda dapat melihat statusnya di halaman Akun Saya.'],
          ['Bagaimana jika saya ingin mengubah atau membatalkan janji?',
           'Hubungi klinik melalui kontak yang tersedia di bagian Kontak. Tim klinik dapat memperbarui status booking Anda.'],
          ['Berapa biaya perawatan?',
           'Harga dikonfirmasi langsung oleh klinik dan dapat berbeda untuk setiap kebutuhan. Silakan hubungi klinik untuk informasi harga.'],
          ['Apakah perawatan ini cocok untuk saya?',
           'Kesesuaian perawatan dibahas bersama tenaga klinik saat konsultasi. Informasi di website ini bersifat umum dan bukan nasihat medis.'],
      ];
      ?>
      <div class="accordion" id="faqAccordion">
        <?php foreach ($faqs as $i => [$q, $a]): ?>
          <div class="accordion-item">
            <h3 class="accordion-header" id="faq-h<?= $i ?>">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                      data-bs-target="#faq-c<?= $i ?>" aria-expanded="false" aria-controls="faq-c<?= $i ?>">
                <?= e($q) ?>
              </button>
            </h3>
            <div id="faq-c<?= $i ?>" class="accordion-collapse collapse" aria-labelledby="faq-h<?= $i ?>" data-bs-parent="#faqAccordion">
              <div class="accordion-body"><?= e($a) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- KONTAK -->
  <section id="kontak" class="section" aria-labelledby="kontak-title">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Kontak &amp; Lokasi</span>
        <h2 id="kontak-title" class="section-title">Hubungi Kami</h2>
      </div>
      <div class="row g-4">
        <div class="col-lg-7">
          <div class="contact-card">
            <?php if ($hasContact): ?>
              <ul class="contact-list">
                <?php if ($clinic['address'] !== ''): ?>
                  <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    <div><strong>Alamat</strong><br><?= nl2br(e($clinic['address'])) ?>
                    <?php if ($clinic['maps_url'] !== ''): ?>
                      <br><a href="<?= e($clinic['maps_url']) ?>" target="_blank" rel="noopener noreferrer">Buka di peta<span class="visually-hidden"> (tab baru)</span></a>
                    <?php endif; ?></div></li>
                <?php endif; ?>
                <?php if ($clinic['phone'] !== ''): ?>
                  <li><i class="fa-solid fa-phone" aria-hidden="true"></i>
                    <div><strong>Telepon</strong><br>
                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $clinic['phone'])) ?>"><?= e($clinic['phone']) ?></a></div></li>
                <?php endif; ?>
                <?php if ($clinic['whatsapp'] !== ''): ?>
                  <li><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                    <div><strong>WhatsApp</strong><br>
                    <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $clinic['whatsapp'])) ?>" target="_blank" rel="noopener noreferrer">Chat via WhatsApp<span class="visually-hidden"> (tab baru)</span></a></div></li>
                <?php endif; ?>
                <?php if ($clinic['email'] !== ''): ?>
                  <li><i class="fa-solid fa-envelope" aria-hidden="true"></i>
                    <div><strong>Email</strong><br>
                    <a href="mailto:<?= e($clinic['email']) ?>"><?= e($clinic['email']) ?></a></div></li>
                <?php endif; ?>
                <?php if (!empty($clinic['hours'])): ?>
                  <li><i class="fa-regular fa-clock" aria-hidden="true"></i>
                    <div><strong>Jam operasional</strong>
                    <?php foreach ($clinic['hours'] as $label => $jam): ?>
                      <br><?= e($label) ?>: <?= e($jam) ?>
                    <?php endforeach; ?></div></li>
                <?php endif; ?>
              </ul>
            <?php else: ?>
              <div class="contact-pending">
                <strong>Informasi kontak resmi klinik sedang disiapkan.</strong><br>
                Sementara itu, Anda tetap dapat mengirim permintaan janji temu melalui formulir booking.
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="contact-card text-center d-flex flex-column justify-content-center">
            <h3 class="h4">Siap membuat janji?</h3>
            <p class="text-muted-lipalas">Kirim permintaan janji temu Anda. Tim klinik akan mengonfirmasi jadwalnya.</p>
            <div><a class="btn btn-lipalas" href="<?= e(url('user/dashboard.php#booking')) ?>">Buat Janji</a></div>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
