-- ============================================================================
-- Lipalas Beauty Clinic - SCHEMA UNTUK INSTALASI BARU
-- ============================================================================
-- Pakai file ini HANYA untuk database baru yang masih kosong
-- (phpMyAdmin > tab Import).
--
-- Sudah punya database lama? JANGAN pakai file ini. Backup dulu, lalu jalankan
-- database/migrations/001_upgrade_existing_database.sql.
--
-- File ini tidak berisi data apa pun (tanpa akun, tanpa booking).
-- Cara membuat akun admin pertama: lihat README.md (tools/manage_user.php).
-- Kompatibel dengan MariaDB 10.4+ (XAMPP) dan MySQL 8.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `project`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `project`;

CREATE TABLE IF NOT EXISTS `user` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `nama`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(100) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('user','admin') NOT NULL DEFAULT 'user',
  `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `table_booking` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11)      DEFAULT NULL,
  `nama`       VARCHAR(100) DEFAULT NULL,
  `email`      VARCHAR(100) DEFAULT NULL,
  `phone`      VARCHAR(20)  DEFAULT NULL,
  `service`    VARCHAR(100) DEFAULT NULL,
  `tanggal`    DATE         DEFAULT NULL,
  `message`    TEXT         DEFAULT NULL,
  -- Pending | Confirmed | Completed | Canceled
  `status`     VARCHAR(20)  NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_booking_user` (`user_id`),
  KEY `idx_booking_status` (`status`),
  KEY `idx_booking_tanggal` (`tanggal`),
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Catatan percobaan login/pendaftaran untuk pembatasan (rate limit).
CREATE TABLE IF NOT EXISTS `rate_limit_log` (
  `id`         BIGINT       NOT NULL AUTO_INCREMENT,
  `kind`       VARCHAR(30)  NOT NULL,
  `ident`      VARCHAR(190) NOT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_lookup` (`kind`, `ident`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
