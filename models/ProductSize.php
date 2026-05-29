<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $product_id
 * @property string|null $size
 * @property int|null $quantity
 *
 * @property Product $product
 */
class ProductSize extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%product_sizes}}';
    }

    public function rules(): array
    {
        return [
            [['product_id'], 'required'],
            [['product_id', 'quantity'], 'integer'],
            [['size'], 'string', 'max' => 64],
            [['quantity'], 'integer', 'min' => 0],
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
            ->orderBy(['id' => SORT_ASC])
            ->all();
    }
}
