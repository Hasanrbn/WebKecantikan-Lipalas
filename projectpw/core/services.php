<?php
/**
 * Daftar layanan klinik: SATU sumber data untuk beranda, formulir booking,
 * validasi server, dan form edit admin. Ubah di sini, semua halaman ikut berubah.
 *
 * Field:
 *  slug      pengenal pada URL (huruf kecil, tanpa spasi)
 *  name      nama layanan. Nilai ini yang disimpan di database (table_booking.service),
 *            jadi JANGAN diubah sembarangan agar booking lama tetap cocok.
 *  group     kelompok pada daftar di beranda (Wajah, Tubuh, Konsultasi)
 *  summary   ringkasan satu kalimat
 *  detail    penjelasan yang muncul saat baris layanan dibuka
 *  duration  contoh '60 menit' atau null bila tidak ingin ditampilkan
 *  price     contoh 'Rp 350.000' atau null (maka tampil "Hubungi klinik")
 *
 * MODE DEMO: durasi dan harga di bawah adalah angka CONTOH untuk proyek portofolio
 * dan bukan harga klinik sungguhan. Deskripsi sengaja netral dan tidak menjanjikan hasil.
 */
$detailNote = 'Informasi di halaman ini bersifat umum dan bukan nasihat medis.';

return [
    [
        'slug'     => 'premium-facial-care',
        'name'     => 'Premium Facial Care',
        'group'    => 'Wajah',
        'summary'  => 'Pembersihan dan perawatan wajah sesuai kondisi kulit.',
        'detail'   => 'Pembersihan, eksfoliasi lembut, masker, dan pijat wajah. Produk dan tahapannya disesuaikan setelah konsultasi singkat tentang kondisi kulit Anda. ' . $detailNote,
        'duration' => '75 menit',
        'price'    => 'Rp 350.000',
    ],
    [
        'slug'     => 'advanced-laser-therapy',
        'name'     => 'Advanced Laser Therapy',
        'group'    => 'Wajah',
        'summary'  => 'Perawatan wajah berbasis teknologi laser.',
        'detail'   => 'Perawatan berbasis laser untuk kebutuhan kulit tertentu. Kesesuaian, jumlah sesi, dan persiapannya dibahas bersama tenaga klinik saat konsultasi. ' . $detailNote,
        'duration' => '45 menit',
        'price'    => 'Rp 850.000',
    ],
    [
        'slug'     => 'microneedling',
        'name'     => 'Microneedling',
        'group'    => 'Wajah',
        'summary'  => 'Prosedur dengan jarum mikro untuk merangsang pembaruan kulit.',
        'detail'   => 'Prosedur perawatan kulit yang bertujuan merangsang produksi kolagen. Kesesuaian dan persiapan dibahas bersama tenaga klinik saat konsultasi. ' . $detailNote,
        'duration' => '60 menit',
        'price'    => 'Rp 700.000',
    ],
    [
        'slug'     => 'aromatherapy-spa',
        'name'     => 'Aromatherapy Spa',
        'group'    => 'Tubuh',
        'summary'  => 'Pijat relaksasi dengan minyak aromaterapi.',
        'detail'   => 'Pijat seluruh tubuh dengan minyak aromaterapi untuk membantu Anda beristirahat. Aroma bisa dipilih saat datang. ' . $detailNote,
        'duration' => '90 menit',
        'price'    => 'Rp 450.000',
    ],
    [
        'slug'     => 'body-sculpting',
        'name'     => 'Body Sculpting',
        'group'    => 'Tubuh',
        'summary'  => 'Perawatan non-invasif untuk membentuk kontur tubuh.',
        'detail'   => 'Perawatan non-invasif untuk membantu membentuk kontur tubuh. Hasil dapat berbeda pada setiap orang, dan jumlah sesi ditentukan saat konsultasi. ' . $detailNote,
        'duration' => '60 menit',
        'price'    => 'Rp 950.000',
    ],
    [
        'slug'     => 'konsultasi-nutrisi',
        'name'     => 'Konsultasi Nutrisi',
        'group'    => 'Konsultasi',
        'summary'  => 'Panduan pola makan dan kebiasaan sehari-hari.',
        'detail'   => 'Konsultasi pola makan dan gaya hidup untuk mendukung kesehatan kulit dan tubuh dari dalam. ' . $detailNote,
        'duration' => '45 menit',
        'price'    => 'Rp 250.000',
    ],
];
