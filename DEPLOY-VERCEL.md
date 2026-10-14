# Deploy ke Vercel (untuk link portofolio)

Aplikasi ini PHP + MySQL, sedangkan Vercel tidak menjalankan PHP secara bawaan dan tidak punya MySQL. Karena itu:

- PHP dijalankan lewat runtime komunitas [`vercel-php`](https://github.com/vercel-community/php) (direkomendasikan di [dokumentasi Vercel](https://vercel.com/docs/functions/runtimes#community-runtimes)). `vercel.json` memakai `vercel-php@0.7.4` (PHP 8.3).
- Database dipasang terpisah. Panduan ini memakai MySQL gratis dari [Aiven](https://aiven.io/free-mysql-database) (1 GB; layanan gratis bisa dimatikan Aiven saat tidak aktif, nyalakan lagi dari konsol Aiven).
- Disk fungsi Vercel tidak permanen, jadi session login disimpan di tabel `php_session` (otomatis aktif di Vercel).

> **Belum diuji di Vercel sungguhan.** Yang sudah diuji: seluruh alur (daftar, login, booking, admin, logout, CSRF, rate limit) lewat simulasi lokal yang meniru Vercel (titik masuk `api/index.php`, session di database, header proxy, koneksi MySQL terenkripsi). Bila ada yang berbeda saat deploy pertama, lihat bagian "Kalau ada masalah".

## 1. Buat database MySQL (Aiven)

1. Daftar di Aiven, buat layanan **MySQL** dengan paket **Free**.
2. Setelah layanan berjalan, catat: **Host**, **Port**, **User** (biasanya `avnadmin`), **Password**, dan nama database (biasanya `defaultdb`).

## 2. Isi tabel dan buat admin (dari komputermu)

Buka Command Prompt di folder `projectpw` (XAMPP Windows), lalu isi nilainya sesuai Aiven:

```
set DB_HOST=xxxx.aivencloud.com
set DB_PORT=12345
set DB_USER=avnadmin
set DB_PASS=password-dari-aiven
set DB_NAME=defaultdb
set DB_SSL=1

C:\xampp\php\php.exe tools\import_schema.php
C:\xampp\php\php.exe tools\manage_user.php create-admin email@anda.com "Nama Admin"
```

Perintah pertama membuat tabel (aman diulang, tidak menghapus data). Perintah kedua meminta password admin. Pakai password yang kuat, karena situs ini akan bisa dibuka siapa saja.

## 3. Deploy di Vercel

1. Vercel → **Add New → Project** → impor repo `WebKecantikan-Lipalas`.
2. **Root Directory**: biarkan di akar repo (jangan pilih `projectpw`). **Framework Preset**: *Other*. Pengaturan build dibiarkan, karena sudah diatur di `vercel.json`.
3. **Environment Variables** (tambahkan lima ini):

   | Nama | Isi |
   |---|---|
   | `DB_HOST` | host dari Aiven |
   | `DB_PORT` | port dari Aiven |
   | `DB_USER` | user dari Aiven |
   | `DB_PASS` | password dari Aiven |
   | `DB_NAME` | nama database (mis. `defaultdb`) |

   Pengaturan lain (`LIPALAS_SESSION=db`, `LIPALAS_BASE_PATH=/`, `LIPALAS_TRUST_PROXY=1`, `DB_SSL=1`, `LIPALAS_ENV=production`) sudah menjadi bawaan di `api/index.php`; isi sendiri hanya bila ingin mengubahnya.
4. Klik **Deploy**. Setelah selesai, buka alamat `*.vercel.app`, coba daftar, login, dan kirim booking, lalu login sebagai admin.
5. Salin alamatnya ke bagian **About** repo GitHub.

## Kalau ada masalah

| Gejala | Kemungkinan penyebab |
|---|---|
| Halaman "Layanan sementara tidak tersedia" | Variabel `DB_*` salah, atau layanan Aiven sedang mati (nyalakan di konsol Aiven). |
| Halaman terbuka tapi tanpa gaya/gambar | Cek log build: harus ada langkah `cp -r projectpw/aset public/aset`. Pastikan Root Directory = akar repo. |
| Semua halaman "Halaman tidak ditemukan" | Vercel mengubah `REQUEST_URI` untuk fungsi. Kirim log fungsi, ini mudah disesuaikan di `api/index.php`. |
| Login terus kembali ke halaman login | Tabel `php_session` belum ada: jalankan lagi `tools\import_schema.php`. |
| Error 500 saat mulai | Cek **Logs** di dashboard Vercel (error PHP ditulis ke log, tidak ditampilkan). |

## Catatan keamanan

- Jangan memasukkan password database ke Git. Isi hanya di Environment Variables Vercel.
- Koneksi ke database terenkripsi, tetapi sertifikat server tidak diverifikasi (cukup untuk portofolio). Untuk verifikasi penuh gunakan sertifikat CA dari Aiven di `core/koneksi.php`.
- Data klinik masih berstatus **demo** (`core/clinic.php`), jadi halaman memakai `noindex` dan footer menyebut situs demo.
- Hanya sepuluh halaman di daftar putih `api/index.php` yang bisa dipanggil dari browser. Folder `core`, `includes`, `database`, dan `tools` tidak bisa diakses (hasilnya 404).
- Di Vercel `.htaccess` tidak dipakai. Header keamanan yang setara ada di `vercel.json`.
