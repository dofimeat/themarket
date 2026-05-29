-- Выполните в themarketdb (phpMyAdmin или mysql CLI).
-- Если индекс уже есть — пропустите соответствующую строку или удалите дубликаты в таблице.

USE themarketdb;

-- Опционально: порядок фото (код работает и без этой колонки)
-- ALTER TABLE product_images ADD COLUMN sort_order INT NULL DEFAULT 0 AFTER is_main;

-- Удалить дубликаты перед уникальным индексом (пример для favorites):
-- DELETE f1 FROM favorites f1
-- INNER JOIN favorites f2 ON f1.user_id = f2.user_id AND f1.product_id = f2.product_id AND f1.id > f2.id;

ALTER TABLE favorites
    ADD UNIQUE INDEX uq_favorites_user_product (user_id, product_id);

-- DELETE c1 FROM cart c1 INNER JOIN cart c2 ON c1.user_id = c2.user_id AND c1.id > c2.id;

ALTER TABLE cart
    ADD UNIQUE INDEX uq_cart_user (user_id);

ALTER TABLE product_sizes
    ADD UNIQUE INDEX uq_product_sizes_product_size (product_id, size);
