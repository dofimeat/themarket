<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $product_id
 * @property string|null $name
 * @property string|null $value
 * @property int|null $sort_order
 *
 * @property Product $product
 */
class ProductFeature extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%product_features}}';
    }

    public function rules(): array
    {
        return [
            [['product_id'], 'required'],
            [['product_id', 'sort_order'], 'integer'],
            [['name'], 'string', 'max' => 128],
            [['value'], 'string', 'max' => 512],
        ];
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * @return static[]
     */
    public static function findForProduct(int $productId): array
    {
        return static::find()
            ->where(['product_id' => $productId])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }

    /**
     * Check if sort_order column exists.
     */
    public static function hasSortOrderColumn(): bool
    {
        $schema = static::getTableSchema();
        return $schema !== null && $schema->getColumn('sort_order') !== null;
    }
}
