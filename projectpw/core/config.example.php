<?php
/**
 * Contoh konfigurasi lokal.
 *
 * Salin file ini menjadi  core/config.local.php  lalu sesuaikan.
 * config.local.php sudah diabaikan Git, jadi password database tidak ikut ter-commit.
 *
 * Hanya isi nilai yang ingin Anda ubah; sisanya memakai default dari core/helpers.php.
 */
return [
    // 'development' menampilkan detail error (hanya untuk komputer sendiri).
    // 'production'  menyembunyikan detail error dari pengunjung (default).
    'env' => 'development',

    // Hanya perlu diisi jika alamat otomatis salah. Contoh XAMPP: '/projectpw'
    // 'base_path' => '/projectpw',

    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',          // XAMPP bawaan: kosong. Di server nyata WAJIB password kuat.
        'name' => 'project',
    ],
];
