<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $product_id
 * @property int $user_id
 * @property int $rating
 * @property string $text
 * @property string|null $created_at
 *
 * @property Product $product
 * @property User $user
 */
class ProductReview extends ActiveRecord
{
    public const MIN_RATING = 1;
    public const MAX_RATING = 5;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName(): string
    {
        return '{{%product_reviews}}';
    }

    public function rules(): array
    {
        return [
            [['product_id', 'user_id', 'text'], 'required'],
            [['product_id', 'user_id'], 'integer'],
            [['rating'], 'integer', 'min' => self::MIN_RATING, 'max' => self::MAX_RATING],
            [['rating'], 'default', 'value' => self::MAX_RATING],
            [['text'], 'string', 'max' => 2000],
        ];
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'На модерации',
            self::STATUS_APPROVED => 'Одобрен',
            self::STATUS_REJECTED => 'Отклонён',
        ];
    }
}
