# Lipalas Beauty Clinic

Website resmi klinik kecantikan: pengunjung dapat melihat informasi layanan tanpa login, lalu mengirim **permintaan janji temu (booking)**. Admin mengelola booking lewat dashboard.

> Booking berstatus **Menunggu konfirmasi** sampai klinik mengonfirmasi. Website tidak mengklaim jadwal sudah pasti.

## Fitur

**Publik (tanpa login)** – beranda, tentang klinik, layanan (kartu + detail), cara booking, FAQ, kontak & lokasi.
**Pelanggan** – daftar, masuk, keluar; kirim permintaan booking; lihat riwayat dan status booking milik sendiri.
**Admin** – ringkasan statistik, filter status, pencarian, paginasi, ubah status/tanggal/layanan/catatan, hapus booking (dengan konfirmasi).
**Keamanan** – prepared statements, escape output, token CSRF, hash password, session aman, pembatasan percobaan login/daftar/booking, otorisasi role dicek ke database.

## Teknologi

PHP native, MySQL/MariaDB (MySQLi), Bootstrap 5.3 (CDN), Font Awesome 6 (CDN), Google Fonts (CDN), JavaScript ringan tanpa framework.
Diuji dengan PHP 8.3 dan MariaDB 10.11. Kode ditulis untuk PHP 8.1+.

## Struktur folder

```
projectpw/
├── index.php              beranda publik
├── .htaccess              proteksi Apache + header keamanan
├── aset/                  css, js, img (img = salinan teroptimasi; logo & background asli dipertahankan)
├── auth/                  login, register, logout, lupapw
├── user/                  dashboard pelanggan + booking_process
├── admin/                 dashboard, booking_edit, booking_delete
├── core/                  helpers, koneksi, services (daftar layanan), clinic (kontak), config.example
├── includes/              head, navbar, footer bersama
├── database/              schema.sql, migrations/, legacy/ (dump lama)
└── tools/manage_user.php  alat terminal untuk akun admin
```

## Menjalankan dengan XAMPP (Windows)

1. Salin folder `projectpw` ke `C:\xampp\htdocs\projectpw`.
2. Jalankan **Apache** dan **MySQL** di XAMPP Control Panel.
3. Siapkan database (pilih **salah satu**):
   - **Instalasi baru:** phpMyAdmin → tab *Import* → pilih `database/schema.sql`.
   - **Sudah punya database `project` lama:** *backup dulu* (phpMyAdmin → Export), pilih database `project` → tab *SQL*/*Import* → jalankan `database/migrations/001_upgrade_existing_database.sql`. Migrasi tidak menghapus data; booking lama diberi status *Pending*. Aman dijalankan ulang.
4. Salin `core/config.example.php` menjadi `core/config.local.php`, sesuaikan isinya (`db.name`, `db.user`, `db.pass`). File ini diabaikan Git sehingga password tidak ikut ter-commit.
5. Buat akun admin dari Command Prompt:
   ```
   cd C:\xampp\htdocs\projectpw
   C:\xampp\php\php.exe tools\manage_user.php create-admin email@anda.com "Nama Admin"
   ```
   Password diketik saat diminta (tidak muncul di riwayat terminal). Perintah lain: `list`, `set-password <email>`.
6. Buka `http://localhost/projectpw/`.

Pendaftaran publik **hanya** membuat akun pelanggan; admin hanya bisa dibuat lewat alat di atas.

## Mengubah konten (tanpa menyentuh banyak file)

| Yang diubah | File | Catatan |
|---|---|---|
| Alamat, telepon, WhatsApp, email, peta, Instagram, jam operasional, testimoni | `core/clinic.php` | Kolom kosong tidak ditampilkan. Isi **hanya data yang benar**. Peringatan di dashboard admin hilang setelah terisi. |
| Daftar layanan, deskripsi, durasi, harga, gambar | `core/services.php` | Satu sumber untuk beranda, form booking, validasi, dan form edit admin. Jangan ubah `name` layanan yang sudah dipakai booking lama. Durasi/harga dibiarkan `null` sampai dikonfirmasi klinik (website menampilkan "Hubungi klinik untuk informasi harga"). |
| Gambar layanan | taruh di `aset/img/layanan/`, isi `'image' => 'img/layanan/nama.jpg'` | Ukuran disarankan 600×400, JPG/WebP. |
| Logo | ganti file di `aset/img/` (`logo-128.webp`, `logo-256.png`, `logo-512.webp`, `favicon-64.png`, `apple-touch-icon.png`); file asli `aset/logoproject.png` dipertahankan sebagai sumber | Buat ulang ukuran-ukuran tersebut dari logo baru. |
| FAQ | bagian `$faqs` di `index.php` | |
| Warna | variabel `:root` di `aset/css/style.css` | |

Testimoni, harga, dan kontak sengaja **tidak diisi** karena data lama di kode adalah contoh (alamat "Jl. Elegance", dsb.), bukan data klinik.

## Database

Tabel: `user`, `table_booking` (+ kolom `status`), `rate_limit_log`. Skema lengkap ada di `database/schema.sql`. Status booking: `Pending`, `Confirmed`, `Completed`, `Canceled`.

Booking hanya menyimpan **tanggal** (tanpa jam). Sistem **belum** mencegah dua pelanggan memesan tanggal yang sama; yang dicegah hanyalah permintaan ganda dari akun yang sama untuk layanan + tanggal yang sama.

## Testing

Pemeriksaan sintaks:
```
for f in $(find projectpw -name '*.php'); do php -l "$f"; done
```
Daftar uji manual yang sudah dijalankan pada versi ini (PHP 8.3 + MariaDB 10.11, via server bawaan PHP; **belum** dijalankan di XAMPP/Windows):

- Beranda terbuka tanpa login; tidak ada brand/kontak/testimoni contoh; tidak ada harga karangan.
- Daftar: CSRF wajib, email/password divalidasi, email ganda ditolak, `role=admin` pada POST diabaikan, password tersimpan sebagai hash.
- Login: pesan error sama untuk email tak terdaftar dan password salah, session ID berganti, diblokir setelah 5 kali gagal (15 menit), SQL injection pada email tidak lolos.
- Otorisasi: pelanggan tidak dapat membuka/menjalankan endpoint admin; tamu dialihkan ke login lalu kembali ke tujuan.
- Booking: telepon/layanan/tanggal tidak valid, tanggal lampau, tanggal >365 hari, dan duplikat ditolak; nama & email diambil dari akun; status awal `Pending`; tanpa token CSRF ditolak.
- Admin: statistik dan layanan terbanyak dihitung dari data nyata; filter, pencarian, dan paginasi; edit tervalidasi; hapus hanya lewat POST + CSRF; booking tanpa `user_id` tetap tampil.
- Tampilan: lebar 375, 768, 1280 px tanpa scroll horizontal dan tanpa error JavaScript; menu mobile dan jendela detail layanan berfungsi.
- Migrasi: dump lama (2 user, 2 booking) berhasil dimigrasi dua kali berturut-turut tanpa kehilangan data.

## Keamanan: yang sudah dan belum

Sudah: lihat bagian Fitur. Perubahan penting dari versi lama: halaman *Lupa Password* lama memungkinkan **siapa pun mengganti password akun mana pun** hanya dengan mengetik email; ini ditutup. Password sekarang diatur ulang oleh pemilik sistem (`tools/manage_user.php set-password`).

Perlu dikerjakan saat deployment:
- Pakai **HTTPS** (cookie sesi otomatis `Secure` bila HTTPS).
- Di `core/config.local.php` isi `'env' => 'production'` dan pakai user database khusus dengan password kuat (jangan `root` tanpa password).
- Pastikan Apache mengizinkan `.htaccess` (`AllowOverride All`) agar folder `core`, `includes`, `database`, `tools` tidak bisa dibuka lewat browser.
- Folder `database/legacy/` berisi dump lama dengan **email dan hash password akun contoh**. Jangan dipublikasikan; hapus dari server produksi, dan ganti password akun-akun tersebut.

## Batasan yang masih ada

- Belum ada jam appointment dan kontrol kapasitas jadwal.
- Belum ada email notifikasi atau reset password lewat email (butuh layanan email).
- Rate limit berbasis IP + tabel database; di belakang proxy/CDN perlu penyesuaian `client_ip()`.
- Bootstrap, Font Awesome, dan Google Fonts dimuat dari CDN (perlu internet).
- Belum ada galeri, testimoni, profil tenaga medis, `sitemap.xml`, dan `robots.txt` karena datanya/domain finalnya belum ada. Struktur data LocalBusiness otomatis memuat alamat/telepon setelah `core/clinic.php` diisi.
