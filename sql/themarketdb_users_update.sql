-- База: themarketdb, таблица: users
-- Выполните весь скрипт в phpMyAdmin (вкладка SQL) или: mysql -u root themarketdb < sql/themarketdb_users_update.sql
--
-- Имя и фамилия (если колонки уже есть — удалите соответствующие строки или игнорируйте ошибку "Duplicate column").

ALTER TABLE `users` ADD COLUMN `first_name` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `users` ADD COLUMN `last_name` varchar(100) NOT NULL DEFAULT '';

-- Роль: VARCHAR, чтобы значения `user`, `seller` и др. не обрезались (вместо ENUM).
ALTER TABLE `users` MODIFY `role` varchar(32) NOT NULL DEFAULT 'user';

-- Опционально: ключ для «Запомнить меня» (если колонки ещё нет).
-- ALTER TABLE `users` ADD COLUMN `auth_key` varchar(32) NOT NULL DEFAULT '' AFTER `password_hash`;
