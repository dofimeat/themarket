<?php

namespace app\models;

use app\models\traits\ProductFormTrait;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Добавление товара: несколько размеров и изображений.
 */
class ProductAddForm extends Model
{
    use ProductFormTrait;

    public $name;
    public $price;
    public $description;

    /** @var int|string|null */
    public $mainImageId = 'new_0';

    /** @var array<int, array<string, mixed>> */
    public $sizes = [['id' => '', 'size' => '', 'quantity' => 1]];

    /** @var UploadedFile[]|null */
    public $newImageFiles;

    public function init()
    {
        parent::init();
        if ($this->sizes === []) {
            $this->sizes = [['id' => '', 'size' => '', 'quantity' => 1]];
        }
    }

    public function rules()
    {
        return [
            [['name', 'price', 'description'], 'trim'],
            [['name', 'price', 'description'], 'required'],
            ['name', 'string', 'max' => 255],
            ['description', 'string', 'max' => 20000],
            ['price', 'validatePrice'],
            ['mainImageId', 'safe'],
            ['sizes', 'validateSizes'],
            [
                ['newImageFiles'],
                'file',
                'skipOnEmpty' => false,
                'maxFiles' => 10,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp', 'gif'],
                'maxSize' => 8 * 1024 * 1024,
                'wrongExtension' => 'Допустимы изображения: PNG, JPG, WEBP, GIF.',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'price' => 'Цена',
            'description' => 'Описание',
            'newImageFiles' => 'Изображения',
        ];
    }

    /**
     * @return int|null id товара
     */
    public function saveProduct(int $brandId): ?int
    {
        $this->newImageFiles = UploadedFile::getInstances($this, 'newImageFiles');

        if (!$this->validate()) {
            return null;
        }

        $files = $this->newImageFiles ?? [];
        if ($files === []) {
            $this->addError('newImageFiles', 'Загрузите хотя бы одно изображение.');
            return null;
        }

        $priceVal = $this->parsePriceValue();
        if ($priceVal === null || $priceVal <= 0) {
            $this->addError('price', 'Укажите корректную цену.');
            return null;
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $product = new Product();
            $product->brand_id = $brandId;
            $product->name = $this->name;
            $product->description = $this->description;
            $product->price = $priceVal;
            $product->status = Product::STATUS_ACTIVE;
            if ($product->hasAttribute('created_at')) {
                $product->created_at = new Expression('NOW()');
            }
            if (!$product->save(false)) {
                throw new \RuntimeException('product save failed');
            }

            $productId = (int) $product->id;
            $newImageIds = $this->saveNewImages($productId, $files);
            $this->syncSizes($productId);
            $this->applyMainImage($productId, $newImageIds);

            $transaction->commit();
            return $productId;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->addError('name', 'Не удалось сохранить товар.');
            return null;
        }
    }

    /**
     * @param UploadedFile[] $files
     * @return int[]
     */
    private function saveNewImages(int $productId, array $files): array
    {
        $dir = Yii::getAlias('@webroot/uploads/products');
        FileHelper::createDirectory($dir, 0755);
        $newIds = [];

        foreach ($files as $idx => $file) {
            if ($file->getHasError()) {
                continue;
            }
            $safeExt = strtolower($file->extension ?: 'jpg');
            $basename = 'p' . $productId . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;
            $fullPath = $dir . DIRECTORY_SEPARATOR . $basename;
            if (!$file->saveAs($fullPath, false)) {
                throw new \RuntimeException('image save failed');
            }

            $image = new ProductImage();
            $image->product_id = $productId;
            $image->image = 'uploads/products/' . $basename;
            $image->is_main = $idx === 0 ? 1 : 0;
            if (ProductImage::hasSortOrderColumn()) {
                $image->sort_order = $idx;
            }
            if (!$image->save(false)) {
                throw new \RuntimeException('image db save failed');
            }
            $newIds[] = (int) $image->id;
        }

        return $newIds;
    }

    private function syncSizes(int $productId): void
    {
        foreach ($this->normalizeSizesInput() as $row) {
            if ($row['size'] === '') {
                continue;
            }
            $sizeModel = new ProductSize();
            $sizeModel->product_id = $productId;
            $sizeModel->size = $row['size'];
            $sizeModel->quantity = $row['quantity'];
            $sizeModel->save(false);
        }
    }

    /**
     * @param int[] $newImageIds
     */
    private function applyMainImage(int $productId, array $newImageIds): void
    {
        $schema = ProductImage::getTableSchema();
        if ($schema === null || $schema->getColumn('is_main') === null || $newImageIds === []) {
            return;
        }

        $mainRaw = $this->mainImageId;
        $mainId = 0;
        if (is_string($mainRaw) && str_starts_with($mainRaw, 'new_')) {
            $idx = (int) substr($mainRaw, 4);
            $mainId = $newImageIds[$idx] ?? $newImageIds[0];
        } elseif ((int) $mainRaw > 0) {
            $mainId = (int) $mainRaw;
        } else {
            $mainId = $newImageIds[0];
        }

        ProductImage::updateAll(['is_main' => 0], ['product_id' => $productId]);
        ProductImage::updateAll(['is_main' => 1], ['product_id' => $productId, 'id' => $mainId]);
    }
}
