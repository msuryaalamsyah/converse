-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for converse_store


-- Dumping structure for table converse_store.admin_users
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table converse_store.admin_users: ~1 rows (approximately)
DELETE FROM `admin_users`;
INSERT INTO `admin_users` (`id`, `username`, `password`, `name`, `created_at`) VALUES
	(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Admin', '2026-05-21 05:55:18');

-- Dumping structure for table converse_store.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table converse_store.categories: ~5 rows (approximately)
DELETE FROM `categories`;
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
	(1, 'High Top', 'high-top', 'Classic high-top sneakers for the streets', '2026-05-21 05:55:17'),
	(2, 'Low Top', 'low-top', 'Versatile low-top everyday sneakers', '2026-05-21 05:55:17'),
	(3, 'Platform', 'platform', 'Elevated platform sole sneakers', '2026-05-21 05:55:17'),
	(4, 'Slip-On', 'slip-on', 'Easy slip-on canvas sneakers', '2026-05-21 05:55:17'),
	(5, 'Skate', 'skate', 'Skate-inspired durable sneakers', '2026-05-21 05:55:17');

-- Dumping structure for table converse_store.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_new` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table converse_store.products: ~6 rows (approximately)
DELETE FROM `products`;
INSERT INTO `products` (`id`, `name`, `slug`, `sku`, `category_id`, `description`, `price`, `image`, `is_featured`, `is_new`, `created_at`, `updated_at`) VALUES
	(1, 'Chuck Taylor All Star Classic', 'chuck-taylor-all-star-classic', 'CV-100293-BLK', 1, 'The original canvas sneaker that started it all. Timeless style, relentless self-expression.', 85.00, 'product_6a0e9f79d7515.webp', 1, 0, '2026-05-21 05:55:17', '2026-05-21 06:00:25'),
	(2, 'Chuck 70 Vintage Canvas', 'chuck-70-vintage-canvas', 'CV-700112-MUS', 2, 'The Chuck 70 updates the classic with premium materials and retro details.', 90.00, 'product_6a0ea04731561.webp', 1, 1, '2026-05-21 05:55:17', '2026-05-21 06:03:51'),
	(3, 'Run Star Hike Platform', 'run-star-hike-platform', 'CV-200481-WHT', 3, 'Dramatic platform sole meets the iconic Chuck Taylor upper.', 110.00, 'product_6a0ea0d27d815.webp', 0, 0, '2026-05-21 05:55:17', '2026-05-21 06:06:10'),
	(4, 'One Star Pro Skate', 'one-star-pro-skate', 'CV-300572-RED', 5, 'Skate-ready construction with the iconic one star branding.', 75.00, 'product_6a0ea114aff0c.jpg', 0, 1, '2026-05-21 05:55:17', '2026-05-21 06:07:16'),
	(5, 'Chuck Taylor All Star Lugged', 'chuck-taylor-all-star-lugged', 'CV-400683-BLK', 3, 'A platform take on the All Star with chunky lugged sole.', 95.00, 'product_6a0ea155c4949.webp', 0, 0, '2026-05-21 05:55:17', '2026-05-21 06:08:21'),
	(6, 'Chuck 70 Leather High Top', 'chuck-70-leather-high-top', 'CV-500794-BLK', 1, 'Premium leather upper on the beloved Chuck 70 silhouette.', 95.00, 'product_6a0ea19a16d5d.webp', 0, 0, '2026-05-21 05:55:17', '2026-05-21 06:09:30');

-- Dumping structure for table converse_store.product_variants
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `size` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Default',
  `stock` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table converse_store.product_variants: ~37 rows (approximately)
DELETE FROM `product_variants`;
INSERT INTO `product_variants` (`id`, `product_id`, `size`, `color`, `stock`) VALUES
	(38, 1, '7', 'Black', 45),
	(39, 1, '7', 'White', 38),
	(40, 1, '8', 'Black', 62),
	(41, 1, '8', 'White', 55),
	(42, 1, '9', 'Black', 120),
	(43, 1, '9', 'White', 98),
	(44, 1, '10', 'Black', 89),
	(45, 1, '10', 'White', 72),
	(46, 1, '11', 'Black', 34),
	(47, 1, '11', 'White', 15),
	(48, 1, '12', 'Black', 22),
	(49, 1, '12', 'White', 8),
	(50, 2, '7', 'Mustard', 20),
	(51, 2, '8', 'Mustard', 44),
	(52, 2, '9', 'Mustard', 60),
	(53, 2, '10', 'Mustard', 38),
	(54, 2, '11', 'Mustard', 12),
	(55, 2, '12', 'Mustard', 10),
	(56, 3, '7', 'White', 5),
	(57, 3, '8', 'White', 3),
	(58, 3, '9', 'White', 2),
	(59, 3, '10', 'White', 1),
	(60, 3, '11', 'White', 1),
	(61, 4, '7', 'Red', 30),
	(62, 4, '8', 'Red', 45),
	(63, 4, '9', 'Red', 55),
	(64, 4, '10', 'Red', 40),
	(65, 4, '11', 'Red', 25),
	(66, 5, '7', 'Black', 15),
	(67, 5, '8', 'Black', 28),
	(68, 5, '9', 'Black', 35),
	(69, 5, '10', 'Black', 22),
	(70, 6, '7', 'Black', 18),
	(71, 6, '8', 'Black', 32),
	(72, 6, '9', 'Black', 48),
	(73, 6, '10', 'Black', 35),
	(74, 6, '11', 'Black', 20);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
