-- База: themarketdb
-- Характеристики товаров (key-value).
-- Выполните в phpMyAdmin или: mysql -u root themarketdb < sql/themarketdb_product_features.sql

CREATE TABLE IF NOT EXISTS `product_features` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `name` varchar(128) NOT NULL DEFAULT '',
  `value` varchar(512) NOT NULL DEFAULT '',
  `sort_order` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_product_features_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
