<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int|null $brand_id
 * @property int|null $category_id
 * @property string|null $name
 * @property string|null $description
 * @property float|string|null $price
 * @property string|null $status
 * @property string|null $created_at
 *
 * @property Brand|null $brand
 * @property ProductImage[] $images
 * @property ProductSize[] $sizes
 */
class Product extends ActiveRecord
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DRAFT = 'draft';

    public static function tableName(): string
    {
        return '{{%products}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['brand_id', 'category_id'], 'integer'],
            [['status'], 'string', 'max' => 32],
        ];
    }

    public function getBrand(): ActiveQuery
    {
        return $this->hasOne(Brand::class, ['id' => 'brand_id']);
    }

    public function getImages(): ActiveQuery
    {
        return $this->hasMany(ProductImage::class, ['product_id' => 'id'])
            ->orderBy(ProductImage::orderByColumns());
    }

    public function getSizes(): ActiveQuery
    {
        return $this->hasMany(ProductSize::class, ['product_id' => 'id'])
            ->orderBy(['id' => SORT_ASC]);
    }

    public static function findOwnedByUser(int $productId, int $userId): ?self
    {
        $brand = Brand::findByUserId($userId);
        if ($brand === null || $productId <= 0) {
            return null;
        }

        return static::findOne(['id' => $productId, 'brand_id' => $brand->id]);
    }
}
