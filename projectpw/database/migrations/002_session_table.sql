-- ============================================================================
-- Lipalas Beauty Clinic - MIGRASI 002: tabel session di database
-- ============================================================================
-- Hanya perlu untuk hosting serverless (Vercel) dengan LIPALAS_SESSION=db.
-- Aman dijalankan berulang kali (IF NOT EXISTS) dan tidak mengubah data lain.
-- ============================================================================
CREATE TABLE IF NOT EXISTS `php_session` (
  `id`         VARCHAR(128) NOT NULL,
  `data`       MEDIUMBLOB   NOT NULL,
  `updated_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_session_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
