<?php
/**
 * Bagian <head> + pembuka <body>. Variabel yang bisa diset sebelum include:
 *   $pageTitle        judul halaman (null = judul beranda)
 *   $metaDescription  deskripsi untuk mesin pencari
 *   $noindex          true untuk halaman privat (login, dashboard, admin)
 *   $bodyClass        kelas tambahan pada <body>
 *   $jsonLd           array data terstruktur schema.org (opsional)
 */
require_once __DIR__ . '/../core/helpers.php';

$pageTitle       = $pageTitle ?? null;
$metaDescription = $metaDescription ?? clinic()['tagline'];
$noindex         = $noindex ?? false;
$bodyClass       = $bodyClass ?? '';
$jsonLd          = $jsonLd ?? null;

$fullTitle = $pageTitle === null
    ? APP_NAME . ' | Klinik Kecantikan'
    : $pageTitle . ' | ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($fullTitle) ?></title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <meta name="theme-color" content="#7A3568">
<?php if ($noindex): ?>
  <meta name="robots" content="noindex, nofollow">
<?php endif; ?>

  <link rel="icon" type="image/png" sizes="64x64" href="<?= e(asset('img/favicon-64.png')) ?>">
  <link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">

  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e(APP_NAME) ?>">
  <meta property="og:title" content="<?= e($fullTitle) ?>">
  <meta property="og:description" content="<?= e($metaDescription) ?>">
  <meta property="og:locale" content="id_ID">
  <meta property="og:image" content="<?= e(absolute_url('aset/img/og-image.jpg')) ?>">
  <meta name="twitter:card" content="summary_large_image">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<?php if (is_array($jsonLd)): ?>
  <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Lewati ke konten utama</a>
