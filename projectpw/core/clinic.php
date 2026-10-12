<?php
/**
 * Data klinik: SATU tempat untuk memperbarui identitas, kontak, dan jam operasional.
 *
 * PENTING: semua kolom kontak di bawah sengaja KOSONG karena belum dikonfirmasi pemilik.
 * Data contoh lama ("Jl. Elegance No. 123", "+62 812-3456-7890", "glow@glowupclinic.com",
 * "Mon-Sat 9AM-8PM") adalah placeholder dan tidak dipakai.
 *
 * Kolom yang kosong tidak akan ditampilkan di website. Isi hanya data yang benar.
 * Setelah semua kontak terisi, peringatan di dashboard admin otomatis hilang dan
 * data terstruktur (schema.org) di homepage ikut memuat alamat, telepon, dan jam buka.
 */
return [
    'name'    => 'Lipalas Beauty Clinic',
    'tagline' => 'Klinik kecantikan dengan layanan perawatan wajah, tubuh, relaksasi, dan konsultasi.',

    // Paragraf pada bagian "Tentang Klinik". Tulis hanya hal yang sudah pasti benar.
    'about' => [
        'Lipalas Beauty Clinic adalah klinik kecantikan yang menyediakan layanan perawatan wajah, '
            . 'perawatan tubuh, relaksasi, dan konsultasi gaya hidup sehat.',
        'Melalui website ini Anda dapat melihat informasi layanan dan mengirim permintaan janji temu. '
            . 'Detail prosedur, kesesuaian perawatan, dan jadwal akan dikonfirmasi langsung oleh tim klinik.',
    ],

    // ---- DATA KONTAK (BELUM DIKONFIRMASI - isi sendiri) ----
    'address'   => '',   // contoh: 'Jl. Contoh No. 1, Yogyakarta'
    'phone'     => '',   // contoh: '0274 123456'
    'whatsapp'  => '',   // format internasional tanpa +/spasi, contoh: '6281234567890'
    'email'     => '',   // contoh: 'halo@domainanda.id'
    'maps_url'  => '',   // tautan Google Maps lokasi klinik
    'instagram' => '',   // contoh: 'https://www.instagram.com/akunanda'

    // Jam operasional: label => jam. Contoh: ['Senin - Sabtu' => '09.00 - 17.00']
    'hours' => [],

    // Testimoni: biarkan kosong sampai ada testimoni asli yang boleh dipublikasikan.
    // Format: [['nama' => 'Nama', 'isi' => 'Teks testimoni']]
    'testimonials' => [],
];
