<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $logo
 * @property string|null $banner_image
 * @property string|null $banner_color
 * @property string|null $city
 * @property string|null $created_at
 *
 * @property Product[] $products
 */
class Brand extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName(): string
    {
        return '{{%brands}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'string', 'max' => 200],
            [['description'], 'string'],
            [['logo', 'banner_image', 'city'], 'string', 'max' => 255],
            [['banner_color'], 'string', 'max' => 20],
            [['user_id'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED]],
            [['is_blocked'], 'boolean'],
        ];
    }

    public function getProducts(): ActiveQuery
    {
        return $this->hasMany(Product::class, ['brand_id' => 'id']);
    }

    public static function findByUserId(int $userId): ?self
    {
        if ($userId <= 0) {
            return null;
        }
        $schema = static::getTableSchema();
        if ($schema === null || $schema->getColumn('user_id') === null) {
            return null;
        }

        return static::find()->where(['user_id' => $userId])->one();
    }

    public static function findApprovedByUserId(int $userId): ?self
    {
        $brand = static::findByUserId($userId);
        if ($brand === null) {
            return null;
        }
        if ($brand->isBlocked()) {
            return null;
        }
        $status = $brand->hasAttribute('status') ? (string) $brand->getAttribute('status') : '';
        if ($status !== self::STATUS_APPROVED) {
            return null;
        }
        return $brand;
    }

    public function isBlocked(): bool
    {
        return $this->hasAttribute('is_blocked') && (bool) $this->getAttribute('is_blocked');
    }

    public static function resolveLogoPath(?string $logo): string
    {
        $path = trim((string) $logo);
        if ($path !== '') {
            return $path;
        }

        return User::DEFAULT_AVATAR;
    }
}
