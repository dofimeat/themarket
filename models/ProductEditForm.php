<?php

namespace app\models;

use app\models\ProductFeature;
use app\models\traits\ProductFormTrait;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Редактирование товара продавцом: описание, размеры, изображения.
 */
class ProductEditForm extends Model
{
    use ProductFormTrait;

    public $productId;
    public $name;
    public $price;
    public $description;

    /** @var int[] */
    public $deleteImageIds = [];

    /** @var int|string|null */
    public $mainImageId;

    /** @var array<int, array<string, mixed>> */
    public $sizes = [];

    /** @var array<int, array<string, mixed>> */
    public $features = [];

    /** @var UploadedFile[]|null */
    public $newImageFiles;

    /** @var ProductImage[] */
    public $existingImages = [];

    public function rules()
    {
        return [
            [['name', 'price', 'description'], 'trim'],
            [['name', 'price', 'description'], 'required'],
            ['name', 'string', 'max' => 255],
            ['description', 'string', 'max' => 20000],
            ['price', 'validatePrice'],
            ['productId', 'integer', 'min' => 1],
            ['deleteImageIds', 'each', 'rule' => ['integer', 'min' => 1]],
            ['mainImageId', 'safe'],
            ['sizes', 'validateSizes'],
            ['features', 'validateFeatures'],
            [
                ['newImageFiles'],
                'file',
                'skipOnEmpty' => true,
                'maxFiles' => 10,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp', 'gif'],
                'maxSize' => 8 * 1024 * 1024,
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'price' => 'Цена',
            'description' => 'Описание',
            'newImageFiles' => 'Новые изображения',
        ];
    }

    public function loadFromProduct(int $productId, int $brandId): bool
    {
        $product = Product::findOne(['id' => $productId, 'brand_id' => $brandId]);
        if ($product === null) {
            return false;
        }

        $this->productId = $productId;
        $this->name = (string) $product->name;
        $this->description = (string) $product->description;
        $this->price = $product->price !== null && $product->price !== ''
            ? number_format((float) $product->price, 0, '', ' ')
            : '';

        $this->existingImages = ProductImage::findForProduct($productId);

        $this->sizes = [];
        foreach (ProductSize::findForProduct($productId) as $sr) {
            $this->sizes[] = [
                'id' => $sr->id,
                'size' => (string) $sr->size,
                'quantity' => (int) $sr->quantity,
            ];
        }
        if ($this->sizes === []) {
            $this->sizes[] = ['id' => '', 'size' => '', 'quantity' => 1];
        }

        $this->features = [];
        foreach (ProductFeature::findForProduct($productId) as $feat) {
            $this->features[] = [
                'id' => $feat->id,
                'name' => (string) $feat->name,
                'value' => (string) $feat->value,
            ];
        }
        if ($this->features === []) {
            $this->features[] = ['id' => '', 'name' => '', 'value' => ''];
        }

        foreach ($this->existingImages as $img) {
            if (!empty($img->is_main)) {
                $this->mainImageId = (int) $img->id;
                break;
            }
        }
        if ($this->mainImageId === null && $this->existingImages !== []) {
            $this->mainImageId = (int) $this->existingImages[0]->id;
        }

        return true;
    }

    public function saveProduct(int $brandId): bool
    {
        $this->newImageFiles = UploadedFile::getInstances($this, 'newImageFiles');

        if (!$this->validate()) {
            return false;
        }

        $productId = (int) $this->productId;
        if ($productId <= 0 || !Product::find()->where(['id' => $productId, 'brand_id' => $brandId])->exists()) {
            $this->addError('name', 'Товар не найден.');
            return false;
        }

        $files = $this->newImageFiles;
        $deleteIds = array_values(array_unique(array_filter(array_map('intval', (array) $this->deleteImageIds), static fn (int $v) => $v > 0)));

        $remaining = 0;
        foreach ($this->existingImages as $img) {
            if (!in_array((int) $img->id, $deleteIds, true)) {
                $remaining++;
            }
        }
        if ($remaining === 0 && $files === []) {
            $this->addError('newImageFiles', 'Оставьте хотя бы одно изображение.');
            return false;
        }

        $priceVal = $this->parsePriceValue();
        if ($priceVal === null || $priceVal <= 0) {
            $this->addError('price', 'Укажите корректную цену.');
            return false;
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $pathsToUnlink = [];

        try {
            Product::updateAll(
                ['name' => $this->name, 'description' => $this->description, 'price' => $priceVal],
                ['id' => $productId, 'brand_id' => $brandId]
            );

            if ($deleteIds !== []) {
                foreach (ProductImage::find()->where(['product_id' => $productId, 'id' => $deleteIds])->all() as $img) {
                    if ($img->image) {
                        $pathsToUnlink[] = $img->image;
                    }
                }
                ProductImage::deleteAll(['product_id' => $productId, 'id' => $deleteIds]);
            }

            $newImageIds = $this->saveNewImages($productId, $files);
            $this->syncSizes($productId);
            $this->syncFeatures($productId);
            $this->applyMainImage($productId, $newImageIds);

            $transaction->commit();

            foreach ($pathsToUnlink as $rel) {
                $abs = Yii::getAlias('@webroot/' . ltrim($rel, '/'));
                if (is_file($abs)) {
                    @unlink($abs);
                }
            }

            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->addError('name', 'Не удалось сохранить изменения.');
            return false;
        }
    }

    /**
     * @param UploadedFile[] $files
     * @return int[]
     */
    private function saveNewImages(int $productId, array $files): array
    {
        if ($files === []) {
            return [];
        }

        $sortBase = ProductImage::maxSortOrderForProduct($productId);
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
            $image->is_main = 0;
            if (ProductImage::hasSortOrderColumn()) {
                $image->sort_order = $sortBase + $idx + 1;
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
        $rows = $this->normalizeSizesInput();
        $keepIds = [];

        foreach ($rows as $row) {
            if ($row['size'] === '') {
                continue;
            }
            if ($row['id'] !== null) {
                $sizeModel = ProductSize::findOne(['id' => $row['id'], 'product_id' => $productId]);
                if ($sizeModel !== null) {
                    $sizeModel->size = $row['size'];
                    $sizeModel->quantity = $row['quantity'];
                    $sizeModel->save(false);
                    $keepIds[] = $row['id'];
                    continue;
                }
            }

            $sizeModel = new ProductSize();
            $sizeModel->product_id = $productId;
            $sizeModel->size = $row['size'];
            $sizeModel->quantity = $row['quantity'];
            $sizeModel->save(false);
            $keepIds[] = (int) $sizeModel->id;
        }

        if ($keepIds !== []) {
            ProductSize::deleteAll([
                'and',
                ['product_id' => $productId],
                ['not in', 'id', $keepIds],
            ]);
        } else {
            ProductSize::deleteAll(['product_id' => $productId]);
        }
    }

    private function syncFeatures(int $productId): void
    {
        $rows = $this->normalizeFeaturesInput();
        $keepIds = [];
        $sortOrder = 0;

        foreach ($rows as $row) {
            if ($row['name'] === '' && $row['value'] === '') {
                continue;
            }
            if ($row['id'] !== null) {
                $featureModel = ProductFeature::findOne(['id' => $row['id'], 'product_id' => $productId]);
                if ($featureModel !== null) {
                    $featureModel->name = $row['name'];
                    $featureModel->value = $row['value'];
                    if (ProductFeature::hasSortOrderColumn()) {
                        $featureModel->sort_order = $sortOrder;
                    }
                    $featureModel->save(false);
                    $keepIds[] = $row['id'];
                    $sortOrder++;
                    continue;
                }
            }

            $featureModel = new ProductFeature();
            $featureModel->product_id = $productId;
            $featureModel->name = $row['name'];
            $featureModel->value = $row['value'];
            if (ProductFeature::hasSortOrderColumn()) {
                $featureModel->sort_order = $sortOrder;
            }
            $featureModel->save(false);
            $keepIds[] = (int) $featureModel->id;
            $sortOrder++;
        }

        if ($keepIds !== []) {
            ProductFeature::deleteAll([
                'and',
                ['product_id' => $productId],
                ['not in', 'id', $keepIds],
            ]);
        } else {
            ProductFeature::deleteAll(['product_id' => $productId]);
        }
    }

    /**
     * @param int[] $newImageIds
     */
    private function applyMainImage(int $productId, array $newImageIds): void
    {
        $schema = ProductImage::getTableSchema();
        if ($schema === null || $schema->getColumn('is_main') === null) {
            return;
        }

        $mainId = $this->resolveMainImageId($productId, $newImageIds);
        ProductImage::updateAll(['is_main' => 0], ['product_id' => $productId]);
        if ($mainId > 0) {
            ProductImage::updateAll(['is_main' => 1], ['product_id' => $productId, 'id' => $mainId]);
        }
    }

    /**
     * @param int[] $newImageIds
     */
    private function resolveMainImageId(int $productId, array $newImageIds): int
    {
        $mainRaw = $this->mainImageId;
        if (is_string($mainRaw) && str_starts_with($mainRaw, 'new_')) {
            $idx = (int) substr($mainRaw, 4);
            return $newImageIds[$idx] ?? 0;
        }

        $mainId = (int) $mainRaw;
        if ($mainId > 0 && ProductImage::find()->where(['product_id' => $productId, 'id' => $mainId])->exists()) {
            return $mainId;
        }

        $first = ProductImage::find()
            ->where(['product_id' => $productId])
            ->orderBy(ProductImage::orderByColumns())
            ->one();

        return $first ? (int) $first->id : 0;
    }
}
