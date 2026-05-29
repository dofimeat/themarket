-- Аватар пользователя (личный кабинет).
-- Выполните в themarketdb: mysql -u root themarketdb < sql/themarketdb_avatar.sql

ALTER TABLE `users` ADD COLUMN `avatar` varchar(255) DEFAULT NULL AFTER `last_name`;

-- Для уже существующих пользователей без аватарки:
-- UPDATE `users` SET `avatar` = 'images/defolt-avatar.png' WHERE `avatar` IS NULL OR `avatar` = '';
