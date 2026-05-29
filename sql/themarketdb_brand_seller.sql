-- Регистрация бренда: город и привязка к пользователю (user_id).
-- Выполните в themarketdb (если колонка уже есть — пропустите строку с ошибкой).
--
-- Если раньше уже добавляли owner_user_id, переименуйте колонку:
-- ALTER TABLE `brands` CHANGE COLUMN `owner_user_id` `user_id` int unsigned DEFAULT NULL;
-- DROP INDEX `idx_brands_owner_user` ON `brands`;
-- CREATE INDEX `idx_brands_user` ON `brands` (`user_id`);

ALTER TABLE `brands` ADD COLUMN `city` varchar(150) NOT NULL DEFAULT '';
ALTER TABLE `brands` ADD COLUMN `user_id` int unsigned DEFAULT NULL;
CREATE INDEX `idx_brands_user` ON `brands` (`user_id`);
