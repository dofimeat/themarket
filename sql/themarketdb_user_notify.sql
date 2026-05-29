-- Опционально: уведомления в настройках профиля (чекбоксы).
-- Если колонка уже есть — пропустите соответствующую строку.

ALTER TABLE `users` ADD COLUMN `notify_news` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `users` ADD COLUMN `notify_orders` tinyint(1) NOT NULL DEFAULT 1;
