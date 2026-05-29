<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $product_id
 * @property string|null $image
 * @property int|bool|null $is_main
 * @property int|null $sort_order
 *
 * @property Product $product
 */
class ProductImage extends ActiveRecord
{
    private static ?bool $hasSortOrder = null;

    public static function tableName(): string
    {
        return '{{%product_images}}';
    }

    public function rules(): array
    {
        return [
            [['product_id'], 'required'],
            [['product_id', 'sort_order'], 'integer'],
            [['image'], 'string', 'max' => 255],
            [['is_main'], 'boolean'],
        ];
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public static function hasSortOrderColumn(): bool
    {
        if (self::$hasSortOrder === null) {
            $schema = static::getTableSchema();
            self::$hasSortOrder = $schema !== null && $schema->getColumn('sort_order') !== null;
        }

        return self::$hasSortOrder;
    }

    /**
     * @return array<string, int>
     */
    public static function orderByColumns(): array
    {
        if (static::hasSortOrderColumn()) {
            return ['sort_order' => SORT_ASC, 'id' => SORT_ASC];
        }

        return ['id' => SORT_ASC];
    }

    /**
     * @return static[]
     */
    public static function findForProduct(int $productId): array
    {
        return static::find()
            ->where(['product_id' => $productId])
            ->orderBy(static::orderByColumns())
            ->all();
    }

    public static function maxSortOrderForProduct(int $productId): int
    {
        if (!static::hasSortOrderColumn()) {
            return 0;
        }

        return (int) static::find()
            ->where(['product_id' => $productId])
            ->max('sort_order');
    }
}
