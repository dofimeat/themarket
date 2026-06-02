<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string $product_name
 * @property string $product_image
 * @property string $size
 * @property float|string $price
 * @property int $quantity
 * @property float|string $total
 *
 * @property Order $order
 */
class OrderItem extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%order_items}}';
    }

    public function rules(): array
    {
        return [
            [['order_id', 'product_id'], 'required'],
            [['order_id', 'product_id', 'quantity'], 'integer'],
            [['price', 'total'], 'number'],
            [['product_name'], 'string', 'max' => 255],
            [['product_image'], 'string', 'max' => 512],
            [['size'], 'string', 'max' => 64],
            [['quantity'], 'integer', 'min' => 1],
        ];
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }
}
