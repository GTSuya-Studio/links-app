-- ============================================================
-- Links App — database schema
--
-- Usage:
--   mysql -u youruser -p your_database_name < install.sql
-- or import it via phpMyAdmin / Adminer.
--
-- Compatible with MySQL 5.7+ and MariaDB 10.3+.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name`       varchar(100) NOT NULL,
  `color`      varchar(7) DEFAULT '#6366f1',
  `is_locked`  tinyint(1) NOT NULL DEFAULT 0,
  `slug`       varchar(110) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `subcategories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `subcategories`;
CREATE TABLE `subcategories` (
  `id`          int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `name`        varchar(100) NOT NULL,
  `color`       varchar(7) DEFAULT '#6366f1',
  `sort_order`  smallint(6) NOT NULL DEFAULT 0,
  `created_at`  timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_sub_cat` (`category_id`),
  CONSTRAINT `fk_sub_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `links`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `id`              int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subcategory_id`  int(10) unsigned NOT NULL,
  `title`           varchar(200) NOT NULL,
  `url`             text NOT NULL,
  `description`     varchar(500) DEFAULT NULL,
  `sort_order`      smallint(6) NOT NULL DEFAULT 0,
  `created_at`      timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_link_sub` (`subcategory_id`),
  CONSTRAINT `fk_link_sub` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `settings` — simple key/value store used by the app
-- (site title, theme, PIN hash, admin password hash, etc.)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `key`   varchar(80) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed data — required for a working first login.
-- Default admin password is "admin" (SHA-256 hash below).
-- You will be warned in Settings > System until you change it.
-- --------------------------------------------------------
INSERT INTO `settings` (`key`, `value`) VALUES
  ('admin_password_hash', SHA2('admin', 256));

SET FOREIGN_KEY_CHECKS = 1;
