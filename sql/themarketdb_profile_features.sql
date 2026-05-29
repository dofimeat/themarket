-- База: themarketdb
-- Избранное и заказы для личного кабинета.
-- Выполните в phpMyAdmin или: mysql -u root themarketdb < sql/themarketdb_profile_features.sql
-- (Индексы без FOREIGN KEY — чтобы не конфликтовать с разными типами id в существующих таблицах.)

CREATE TABLE IF NOT EXISTS `user_favorites` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_product` (`user_id`,`product_id`),
  KEY `idx_user_favorites_user` (`user_id`),
  KEY `idx_user_favorites_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'new',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_orders_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Настройки уведомлений (чекбоксы в профиле). Выполните отдельно, если колонок ещё нет:
-- ALTER TABLE `users` ADD COLUMN `notify_news` tinyint(1) NOT NULL DEFAULT 1;
-- ALTER TABLE `users` ADD COLUMN `notify_orders` tinyint(1) NOT NULL DEFAULT 1;
