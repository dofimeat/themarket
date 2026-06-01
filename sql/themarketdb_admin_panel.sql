-- Админ-панель: модерация, блокировка, расширенные статусы.
-- Выполните в themarketdb.

-- Пользователи: статус (active|blocked)
ALTER TABLE `users` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'active';

-- Бренды: статус модерации + блокировка
ALTER TABLE `brands` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'pending';
ALTER TABLE `brands` ADD COLUMN `is_blocked` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `brands` ADD COLUMN `admin_notes` text DEFAULT NULL;

-- Товары: расширенные статусы модерации
-- Меняем VARCHAR(32) на VARCHAR(32) если нужно, и обновляем статусы
-- Старые active → published, draft → draft (оставляем)
UPDATE `products` SET `status` = 'published' WHERE `status` = 'active';
UPDATE `products` SET `status` = 'draft' WHERE `status` = 'draft';

-- Для новых полей можно добавить индексы
CREATE INDEX `idx_users_status` ON `users` (`status`);
CREATE INDEX `idx_brands_status` ON `brands` (`status`);
CREATE INDEX `idx_products_status` ON `products` (`status`);
