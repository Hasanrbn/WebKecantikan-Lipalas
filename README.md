# Lipalas Beauty Clinic

Website resmi klinik kecantikan: pengunjung dapat melihat informasi layanan tanpa login, lalu mengirim **permintaan janji temu (booking)**. Admin mengelola booking lewat dashboard.

> Booking berstatus **Menunggu konfirmasi** sampai klinik mengonfirmasi. Website tidak mengklaim jadwal sudah pasti.

> **Proyek demo.** Lipalas adalah klinik fiktif untuk portofolio. Alamat, telepon, email, jam buka, harga, dan durasi di `core/clinic.php` dan `core/services.php` adalah **data contoh** (`'demo' => true`). Selama mode demo aktif, footer memberi catatan "situs demo" dan halaman memakai `noindex`. Untuk klinik sungguhan, ganti datanya lalu set `'demo' => false`.

## Fitur

**Publik (tanpa login)** – beranda, daftar perawatan bergaya menu (buka untuk membaca detail), tentang, cara booking, FAQ, kontak & lokasi.
**Pelanggan** – daftar, masuk, keluar; kirim permintaan booking; lihat riwayat dan status booking milik sendiri.
**Admin** – ringkasan statistik, filter status, pencarian, paginasi, ubah status/tanggal/layanan/catatan, hapus booking (dengan konfirmasi).
**Keamanan** – prepared statements, escape output, token CSRF, hash password, session aman, pembatasan percobaan login/daftar/booking, otorisasi role dicek ke database.

## Teknologi

PHP native, MySQL/MariaDB (MySQLi), Bootstrap 5.3 (hanya CSS, dari CDN), JavaScript ringan tanpa framework. Font **Marcellus** dan **Hanken Grotesk** dihosting sendiri di `aset/fonts/` (lisensi SIL OFL, file lisensi disertakan), ikon berupa SVG inline (set Lucide, lisensi ISC) lewat `core/icons.php`. Tidak ada Font Awesome, Google Fonts, maupun Bootstrap JavaScript.
Diuji dengan PHP 8.3 dan MariaDB 10.11. Kode ditulis untuk PHP 8.1+.

## Struktur folder

```
projectpw/
├── index.php              beranda publik
├── .htaccess              proteksi Apache + header keamanan
├── aset/                  css, js, fonts (self-hosted), img (logo & background asli dipertahankan; lotus.svg = tanda merek turunan logo)
├── auth/                  login, register, logout, lupapw
├── user/                  dashboard pelanggan + booking_process
├── admin/                 dashboard, booking_edit, booking_delete
├── core/                  helpers, koneksi, icons (ikon SVG), services (daftar layanan), clinic (kontak + flag demo), config.example
├── includes/              head, navbar, footer, auth_visual bersama
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

## Deploy online (Vercel)

Panduan lengkap ada di [DEPLOY-VERCEL.md](DEPLOY-VERCEL.md): PHP lewat `vercel-php`, MySQL gratis di Aiven, session disimpan di database. Konfigurasi dapat diisi lewat environment variable (`DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_SSL`, `LIPALAS_*`), jadi `core/config.local.php` tidak wajib di hosting. Di XAMPP tidak ada yang berubah.

## Mengubah konten (tanpa menyentuh banyak file)

| Yang diubah | File | Catatan |
|---|---|---|
| Alamat, telepon, WhatsApp, email, peta, Instagram, jam operasional, teks "Tentang", testimoni, flag `demo` | `core/clinic.php` | Kolom kosong tidak ditampilkan. Data saat ini **contoh/fiktif** (demo). Untuk klinik sungguhan isi data yang benar dan set `'demo' => false`. |
| Daftar layanan, kelompok (`group`), deskripsi, durasi, harga | `core/services.php` | Satu sumber untuk beranda, form booking (termasuk ringkasan durasi/harga), validasi, dan form edit admin. Jangan ubah `name` layanan yang sudah dipakai booking lama. Harga/durasi saat ini contoh; isi `null` untuk menampilkan "Hubungi klinik". |
| Foto ruang | `aset/img/ruang-*.jpg/.webp` | Saat ini semuanya potongan dari satu foto ruang perawatan bawaan proyek. Ganti dengan foto klinik asli bila ada (hero 4:5, bagian tentang 1:1, bagian cara booking 3:2). |
| Logo | ganti file di `aset/img/` (`logo-128.webp`, `logo-256.png`, `logo-512.webp`, `favicon-64.png`, `apple-touch-icon.png`); file asli `aset/logoproject.png` dipertahankan sebagai sumber | Buat ulang ukuran-ukuran tersebut dari logo baru. |
| FAQ | bagian `$faqs` di `index.php` | |
| Warna dan font | variabel `:root` di `aset/css/style.css` | Palet: blush, plum (dari logo), emas tipis. |
| Bentuk bingkai foto (kelopak) | `<clipPath id="petal-clip">` di `includes/head.php` | Garis emas yang tergambar saat halaman dibuka ada di `index.php` (`.petal__line`). |

Data kontak/harga yang tampil sekarang adalah **contoh fiktif** (bukan data klinik sungguhan). Testimoni sengaja **dikosongkan**: bagian testimoni baru muncul bila ada testimoni asli yang boleh dipublikasikan.

## Database

Tabel: `user`, `table_booking` (+ kolom `status`), `rate_limit_log`, `php_session` (hanya dipakai di hosting serverless; migrasi 002 untuk database lama). Skema lengkap ada di `database/schema.sql`. Status booking: `Pending`, `Confirmed`, `Completed`, `Canceled`.

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
- Tampilan (desain editorial blush/plum): lebar 320, 360, 390, 768, 1024, 1440 px tanpa scroll horizontal pada beranda, login, daftar, dashboard pelanggan, dan dashboard admin; tanpa error JavaScript; menu mobile (buka, Escape, tutup saat memilih tautan), daftar perawatan dan FAQ (satu terbuka sekali waktu), validasi inline form, ringkasan layanan di form booking, dialog konfirmasi hapus, tabel admin bertumpuk di layar kecil, fokus keyboard terlihat, `prefers-reduced-motion` mematikan animasi garis emas. Rasio kontras warna teks utama/tombol/status dihitung ≥ 4,5:1.
- Uji alur otomatis (Playwright + Chromium, 64 pemeriksaan) dan uji curl untuk CSRF, otorisasi, dan pembatasan login lulus. **Belum** dicoba di Safari/Firefox, perangkat sentuh sungguhan, atau pembaca layar.
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
- Bootstrap CSS dimuat dari CDN (perlu internet). Font dan ikon sudah lokal.
- Belum ada galeri, testimoni, profil tenaga medis, `sitemap.xml`, dan `robots.txt` karena datanya/domain finalnya belum ada. Struktur data LocalBusiness hanya memuat alamat/telepon bila `'demo' => false`.
- Foto di website baru satu (ruang perawatan), dipotong menjadi tiga bidang. Sebaiknya diganti foto klinik asli.
- Daftar `<details name="...">` (satu terbuka sekali waktu) memakai fitur browser modern; di browser lama tetap berfungsi lewat cadangan JavaScript.
