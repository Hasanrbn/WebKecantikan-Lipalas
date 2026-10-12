<?php
/**
 * Daftar layanan klinik: SATU sumber data untuk homepage, formulir booking,
 * validasi server, dan form edit admin. Ubah di sini, semua halaman ikut berubah.
 *
 * Field:
 *  slug      pengenal pada URL (huruf kecil, tanpa spasi)
 *  name      nama layanan. Nilai ini yang disimpan di database (table_booking.service),
 *            jadi JANGAN diubah sembarangan agar booking lama tetap cocok.
 *  icon      ikon Font Awesome
 *  summary   ringkasan satu kalimat pada kartu
 *  detail    penjelasan pada jendela "Detail"
 *  duration  contoh '60 menit' - isi HANYA jika sudah dikonfirmasi klinik, jika tidak: null
 *  price     contoh 'Mulai dari Rp 200.000' - isi HANYA jika sudah dikonfirmasi, jika tidak: null
 *  image     contoh 'img/layanan/facial.jpg' (relatif terhadap folder aset/) atau null
 *
 * CATATAN: kode lama memuat harga "Mulai dari Rp 200.000" (Premium Facial Care),
 * "Mulai dari Rp 500.000" (Advanced Laser Therapy), dan "Mulai dari Rp 150.000/jam"
 * (Aromatherapy Spa). Angka itu belum terverifikasi sehingga TIDAK ditampilkan.
 * Jika memang benar, isi pada field 'price' layanan terkait.
 *
 * Deskripsi di bawah sengaja netral dan tidak menjanjikan hasil. Sesuaikan dengan
 * informasi resmi klinik.
 */
$detailNote = 'Informasi durasi, biaya, dan persiapan perawatan akan dikonfirmasi oleh tim klinik. '
    . 'Informasi di halaman ini bersifat umum dan bukan nasihat medis.';

return [
    [
        'slug'     => 'premium-facial-care',
        'name'     => 'Premium Facial Care',
        'icon'     => 'fa-spa',
        'summary'  => 'Perawatan wajah untuk membersihkan dan merawat kulit.',
        'detail'   => 'Perawatan wajah yang meliputi pembersihan dan perawatan kulit, disesuaikan dengan kebutuhan kulit Anda. ' . $detailNote,
        'duration' => null,
        'price'    => null,
        'image'    => null,
    ],
    [
        'slug'     => 'advanced-laser-therapy',
        'name'     => 'Advanced Laser Therapy',
        'icon'     => 'fa-bolt',
        'summary'  => 'Perawatan berbasis teknologi laser.',
        'detail'   => 'Perawatan berbasis teknologi laser. Jenis prosedur, kesesuaian, dan persiapannya dijelaskan oleh tenaga klinik saat konsultasi. ' . $detailNote,
        'duration' => null,
        'price'    => null,
        'image'    => null,
    ],
    [
        'slug'     => 'aromatherapy-spa',
        'name'     => 'Aromatherapy Spa',
        'icon'     => 'fa-leaf',
        'summary'  => 'Pijat relaksasi dengan aromaterapi.',
        'detail'   => 'Perawatan relaksasi berupa pijat dengan aromaterapi untuk membantu Anda beristirahat. ' . $detailNote,
        'duration' => null,
        'price'    => null,
        'image'    => null,
    ],
    [
        'slug'     => 'microneedling',
        'name'     => 'Microneedling',
        'icon'     => 'fa-wand-magic-sparkles',
        'summary'  => 'Prosedur perawatan kulit dengan jarum mikro.',
        'detail'   => 'Prosedur perawatan kulit yang bertujuan merangsang produksi kolagen. Kesesuaian dan persiapan dibahas bersama tenaga klinik saat konsultasi. ' . $detailNote,
        'duration' => null,
        'price'    => null,
        'image'    => null,
    ],
    [
        'slug'     => 'body-sculpting',
        'name'     => 'Body Sculpting',
        'icon'     => 'fa-person',
        'summary'  => 'Perawatan non-invasif untuk membantu membentuk kontur tubuh.',
        'detail'   => 'Perawatan non-invasif untuk membantu membentuk kontur tubuh. Hasil dapat berbeda pada setiap orang. ' . $detailNote,
        'duration' => null,
        'price'    => null,
        'image'    => null,
    ],
    [
        'slug'     => 'konsultasi-nutrisi',
        'name'     => 'Konsultasi Nutrisi',
        'icon'     => 'fa-apple-whole',
        'summary'  => 'Panduan pola makan dan gaya hidup sehat.',
        'detail'   => 'Konsultasi pola makan dan gaya hidup sehat untuk mendukung kecantikan dari dalam. ' . $detailNote,
        'duration' => null,
        'price'    => null,
        'image'    => null,
    ],
];
