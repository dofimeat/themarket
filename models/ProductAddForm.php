<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Добавление товара продавцом (форма + сохранение в БД и файл).
 */
class ProductAddForm extends Model
{
    public $name;
    public $price;
    public $size;
    public $description;

    /** @var UploadedFile|null */
    public $imageFile;

    public function rules()
    {
        return [
            [['name', 'price', 'size', 'description'], 'trim'],
            [['name', 'price', 'size', 'description'], 'required'],
            ['name', 'string', 'max' => 255],
            ['size', 'string', 'max' => 64],
            ['description', 'string', 'max' => 20000],
            ['price', 'validatePrice'],
            [
                ['imageFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp', 'gif'],
                'maxSize' => 8 * 1024 * 1024,
                'wrongExtension' => 'Допустимы изображения: PNG, JPG, WEBP, GIF.',
            ],
        ];
    }

    public function validatePrice($attribute): void
    {
        $n = $this->parsePriceValue();
        if ($n === null || $n <= 0) {
            $this->addError($attribute, 'Укажите корректную цену.');
        }
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'price' => 'Цена',
            'size' => 'Размер',
            'description' => 'Описание',
            'imageFile' => 'Изображение',
        ];
    }

    public function parsePriceValue(): ?float
    {
        $raw = trim((string) $this->price);
        if ($raw === '') {
            return null;
        }
        $normalized = str_replace([' ', "\xc2\xa0"], '', $raw);
        $normalized = str_replace(',', '.', $normalized);
        if (!is_numeric($normalized)) {
            return null;
        }
        return round((float) $normalized, 2);
    }

    /**
     * Создаёт товар, главное фото и один размер на складе.
     *
     * @return int|null id товара или null при ошибке
     */
    public function saveProduct(int $brandId): ?int
    {
        if (!$this->validate()) {
            return null;
        }

        $file = UploadedFile::getInstance($this, 'imageFile');
        if ($file === null || $file->getHasError()) {
            $this->addError('imageFile', 'Загрузите изображение товара.');
            return null;
        }

        $priceVal = $this->parsePriceValue();
        if ($priceVal === null || $priceVal <= 0) {
            $this->addError('price', 'Укажите корректную цену.');
            return null;
        }

        $db = Yii::$app->db;
        $pSchema = $db->getTableSchema('{{%products}}', true);
        if ($pSchema === null) {
            $this->addError('name', 'Таблица товаров недоступна.');
            return null;
        }

        $productRow = [];
        if ($pSchema->getColumn('name') !== null) {
            $productRow['name'] = $this->name;
        }
        if ($pSchema->getColumn('description') !== null) {
            $productRow['description'] = $this->description;
        }
        if ($pSchema->getColumn('price') !== null) {
            $productRow['price'] = $priceVal;
        }
        if ($pSchema->getColumn('brand_id') !== null) {
            $productRow['brand_id'] = $brandId;
        }
        if ($pSchema->getColumn('status') !== null) {
            $productRow['status'] = 'active';
        }
        if ($pSchema->getColumn('created_at') !== null) {
            $productRow['created_at'] = new Expression('NOW()');
        }

        $relativePath = null;
        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()->insert('{{%products}}', $productRow)->execute();
            $productId = (int) $db->getLastInsertID();

            $dir = Yii::getAlias('@webroot/uploads/products');
            FileHelper::createDirectory($dir, 0755);
            $safeExt = strtolower($file->extension ?: 'jpg');
            $basename = 'p' . $productId . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;
            $fullPath = $dir . DIRECTORY_SEPARATOR . $basename;
            if (!$file->saveAs($fullPath, false)) {
                throw new \RuntimeException('saveAs failed');
            }
            $relativePath = 'uploads/products/' . $basename;

            $imgSchema = $db->getTableSchema('{{%product_images}}', true);
            if ($imgSchema !== null) {
                $imgRow = ['product_id' => $productId];
                if ($imgSchema->getColumn('image') !== null) {
                    $imgRow['image'] = $relativePath;
                }
                if ($imgSchema->getColumn('is_main') !== null) {
                    $imgRow['is_main'] = 1;
                }
                if ($imgSchema->getColumn('sort_order') !== null) {
                    $imgRow['sort_order'] = 0;
                }
                $db->createCommand()->insert('{{%product_images}}', $imgRow)->execute();
            }

            $szSchema = $db->getTableSchema('{{%product_sizes}}', true);
            if ($szSchema !== null) {
                $szRow = ['product_id' => $productId];
                if ($szSchema->getColumn('size') !== null) {
                    $szRow['size'] = $this->size;
                }
                if ($szSchema->getColumn('quantity') !== null) {
                    $szRow['quantity'] = 99;
                }
                $db->createCommand()->insert('{{%product_sizes}}', $szRow)->execute();
            }

            $transaction->commit();
            return $productId;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            if ($relativePath !== null) {
                $abs = Yii::getAlias('@webroot/' . ltrim($relativePath, '/'));
                if (is_file($abs)) {
                    @unlink($abs);
                }
            }
            $this->addError('name', 'Не удалось сохранить товар. Проверьте структуру таблиц products, product_images, product_sizes.');
            return null;
        }
    }
}
