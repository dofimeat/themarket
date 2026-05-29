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
 * @property string|null $city
 * @property string|null $created_at
 *
 * @property Product[] $products
 */
class Brand extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%brands}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'string', 'max' => 200],
            [['description'], 'string'],
            [['logo', 'city'], 'string', 'max' => 255],
            [['user_id'], 'integer'],
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
}
