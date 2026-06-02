-- База: themarketdb
-- Корзина и оформление заказов.
-- Выполните в phpMyAdmin или: mysql -u root themarketdb < sql/themarketdb_cart_and_orders.sql

-- Расширение таблицы orders: контактные и платёжные данные
ALTER TABLE `orders`
  ADD COLUMN `email` varchar(255) NOT NULL DEFAULT '' AFTER `total_price`,
  ADD COLUMN `first_name` varchar(100) NOT NULL DEFAULT '' AFTER `email`,
  ADD COLUMN `last_name` varchar(100) NOT NULL DEFAULT '' AFTER `first_name`,
  ADD COLUMN `phone` varchar(32) NOT NULL DEFAULT '' AFTER `last_name`,
  ADD COLUMN `country` varchar(64) NOT NULL DEFAULT '' AFTER `phone`,
  ADD COLUMN `city` varchar(128) NOT NULL DEFAULT '' AFTER `country`,
  ADD COLUMN `address` varchar(512) NOT NULL DEFAULT '' AFTER `city`,
  ADD COLUMN `postal_code` varchar(32) NOT NULL DEFAULT '' AFTER `address`,
  ADD COLUMN `delivery_method` varchar(64) NOT NULL DEFAULT 'courier' AFTER `postal_code`,
  ADD COLUMN `delivery_cost` decimal(12,2) NOT NULL DEFAULT '0.00' AFTER `delivery_method`,
  ADD COLUMN `payment_method` varchar(64) NOT NULL DEFAULT 'card' AFTER `delivery_cost`,
  ADD COLUMN `payment_id` varchar(128) NOT NULL DEFAULT '' AFTER `payment_method`,
  ADD COLUMN `comment` text AFTER `payment_id`,
  ADD COLUMN `updated_at` datetime DEFAULT NULL AFTER `created_at`;

-- Изменение status с ENUM на VARCHAR для новых статусов
ALTER TABLE `orders` MODIFY COLUMN `status` varchar(32) NOT NULL DEFAULT 'new';

-- Позиции заказа
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_image` varchar(512) NOT NULL DEFAULT '',
  `size` varchar(64) NOT NULL DEFAULT '',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `quantity` int unsigned NOT NULL DEFAULT 1,
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
