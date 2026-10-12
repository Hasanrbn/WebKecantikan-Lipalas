-- ============================================================================
-- MIGRASI 001 - untuk database LAMA (hasil import user.sql + table_booking.sql)
-- ============================================================================
-- SEBELUM MENJALANKAN: backup database dulu
--   phpMyAdmin > pilih database > tab Export > Go.
-- LALU: pilih database `project` di phpMyAdmin > tab SQL (atau Import) > jalankan file ini.
--
-- Sifat migrasi ini:
--   * TIDAK menghapus, mengosongkan, atau mengubah isi baris data apa pun.
--   * Aman dijalankan berulang (idempotent) pada MariaDB 10.4+ (XAMPP).
--     (MySQL asli tidak mendukung "ADD COLUMN IF NOT EXISTS"; pakai MariaDB/XAMPP
--      atau jalankan perintahnya satu per satu setelah mengecek struktur tabel.)
--
-- Isi perubahan:
--   1. table_booking: tambah kolom `status` (kode admin sudah memakainya, tetapi
--      kolomnya tidak ada di SQL lama sehingga dashboard admin error).
--      Booking lama diberi status 'Pending' - TIDAK diasumsikan sudah dikonfirmasi.
--   2. table_booking: tambah index untuk filter status dan tanggal.
--   3. user: hapus index UNIQUE `username` pada kolom `created_at`. Index itu keliru:
--      dua pendaftaran pada detik yang sama akan gagal. (Hanya index yang dihapus,
--      bukan data.)
--   4. user: perlebar kolom `password` ke 255 agar muat algoritma hash masa depan.
--   5. Buat tabel `rate_limit_log` untuk pembatasan percobaan login/pendaftaran.
-- ============================================================================

ALTER TABLE `table_booking`
  ADD COLUMN IF NOT EXISTS `status` VARCHAR(20) NOT NULL DEFAULT 'Pending' AFTER `message`;

ALTER TABLE `table_booking`
  ADD INDEX IF NOT EXISTS `idx_booking_status` (`status`),
  ADD INDEX IF NOT EXISTS `idx_booking_tanggal` (`tanggal`);

ALTER TABLE `user`
  DROP INDEX IF EXISTS `username`;

ALTER TABLE `user`
  MODIFY `password` VARCHAR(255) NOT NULL;

CREATE TABLE IF NOT EXISTS `rate_limit_log` (
  `id`         BIGINT       NOT NULL AUTO_INCREMENT,
  `kind`       VARCHAR(30)  NOT NULL,
  `ident`      VARCHAR(190) NOT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_lookup` (`kind`, `ident`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
