-- Баннер бренда: картинка или цвет фона.
-- Выполните в themarketdb: mysql -u root themarketdb < sql/themarketdb_brand_banner.sql
-- (Если колонка уже есть — пропустите строку с ошибкой "Duplicate column".)

ALTER TABLE `brands` ADD COLUMN `banner_image` varchar(255) DEFAULT NULL AFTER `logo`;
ALTER TABLE `brands` ADD COLUMN `banner_color` varchar(20) DEFAULT NULL AFTER `banner_image`;
