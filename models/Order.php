<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int|null $user_id
 * @property float|string|null $total_price
 * @property string $status
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $phone
 * @property string $country
 * @property string $city
 * @property string $address
 * @property string $postal_code
 * @property string $delivery_method
 * @property float|string $delivery_cost
 * @property string $payment_method
 * @property string $payment_id
 * @property string|null $comment
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property OrderItem[] $items
 */
class Order extends ActiveRecord
{
    public const STATUS_NEW = 'new';
    public const STATUS_PAID = 'paid';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public static function tableName(): string
    {
        return '{{%orders}}';
    }

    public function rules(): array
    {
        return [
            [['user_id'], 'integer'],
            [['total_price', 'delivery_cost'], 'number'],
            [['status'], 'string', 'max' => 32],
            [['email'], 'string', 'max' => 255],
            [['first_name', 'last_name'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 32],
            [['country'], 'string', 'max' => 64],
            [['city'], 'string', 'max' => 128],
            [['address'], 'string', 'max' => 512],
            [['postal_code'], 'string', 'max' => 32],
            [['delivery_method', 'payment_method', 'payment_id'], 'string', 'max' => 128],
            [['comment'], 'string'],
        ];
    }

    public function getItems(): ActiveQuery
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id'])
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabel(): string
    {
        $labels = [
            self::STATUS_NEW => 'Новый',
            self::STATUS_PAID => 'Оплачен',
            self::STATUS_SHIPPED => 'Отправлен',
            self::STATUS_COMPLETED => 'Завершён',
            self::STATUS_CANCELLED => 'Отменён',
        ];
        return $labels[$this->status] ?? $this->status;
    }
}
