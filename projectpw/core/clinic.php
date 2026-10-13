<?php
/**
 * Data klinik: SATU tempat untuk memperbarui identitas, kontak, dan jam operasional.
 *
 * MODE DEMO: Lipalas Beauty Clinic adalah proyek fiksi untuk portofolio, jadi semua data
 * di bawah adalah DATA CONTOH yang sengaja dibuat sementara (alamat, telepon, email, jam
 * buka, harga, durasi). Selama 'demo' => true, website menampilkan catatan "situs demo"
 * di footer dan meminta mesin pencari tidak mengindeksnya.
 *
 * Jika suatu hari dipakai oleh klinik sungguhan: ganti seluruh data di bawah dengan data
 * yang benar, lalu ubah 'demo' menjadi false.
 *
 * Kolom yang dikosongkan tidak akan ditampilkan di website.
 */
return [
    'demo'    => true,
    'name'    => 'Lipalas Beauty Clinic',
    'tagline' => 'Perawatan wajah, tubuh, dan relaksasi dalam satu ruang yang tenang.',

    // Paragraf pada bagian "Tentang". Tulis hanya hal yang tidak berlebihan dan tidak menjanjikan hasil.
    'about' => [
        'Lipalas dibuka untuk orang yang ingin merawat diri tanpa terburu-buru. Setiap kunjungan '
            . 'dimulai dengan ngobrol singkat tentang kebutuhan kulit dan kenyamanan Anda, baru kemudian '
            . 'perawatan dipilih.',
        'Ruang perawatannya privat dan tenang. Permintaan janji temu dikirim lewat website ini, '
            . 'lalu tim kami mengonfirmasi jadwalnya.',
    ],

    // Tiga hal yang bisa dipegang pengunjung. Tampil sebagai daftar di bagian "Tentang".
    'about_points' => [
        ['Konsultasi dulu', 'Perawatan dipilih setelah kami mengenal kondisi kulit dan kebutuhan Anda.'],
        ['Ruang privat', 'Satu ruang perawatan untuk Anda selama kunjungan, bukan antrean.'],
        ['Jadwal dikonfirmasi', 'Permintaan baru menjadi janji setelah tim kami mengonfirmasi tanggalnya.'],
    ],

    // ---- DATA KONTAK (CONTOH / FIKTIF) ----
    'address'   => "Jl. Kenanga Contoh No. 12\nYogyakarta 55281",
    'phone'     => '(0274) 555-0142',
    'whatsapp'  => '6281200000142',          // format internasional tanpa + dan spasi
    'email'     => 'halo@lipalas.example',   // domain .example memang khusus untuk contoh
    'maps_url'  => '',                       // kosongkan karena alamat fiktif
    'instagram' => '',

    // Jam operasional: label => jam.
    'hours' => [
        'Senin - Jumat' => '09.00 - 18.00',
        'Sabtu'         => '09.00 - 15.00',
        'Minggu'        => 'Tutup',
    ],

    // Testimoni: biarkan kosong sampai ada testimoni asli yang boleh dipublikasikan.
    // Format: [['nama' => 'Nama', 'isi' => 'Teks testimoni']]
    'testimonials' => [],
];
