<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;

/**
 * Регистрация бренда продавцом (название, описание, город).
 */
class BrandRegisterForm extends Model
{
    public $name;
    public $description;
    public $city;

    /** @var int заполняется после успешной вставки */
    public $brandId = 0;

    public function rules()
    {
        return [
            [['name', 'description', 'city'], 'trim'],
            [['name', 'description', 'city'], 'required'],
            ['name', 'string', 'max' => 200],
            ['city', 'string', 'max' => 150],
            ['description', 'string', 'max' => 10000],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название бренда',
            'description' => 'Описание',
            'city' => 'Город',
        ];
    }

    public function save(int $userId): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $schema = Yii::$app->db->getTableSchema('{{%brands}}', true);
        if ($schema === null) {
            $this->addError('name', 'Таблица брендов недоступна.');
            return false;
        }

        if ($schema->getColumn('user_id') !== null) {
            $taken = (new Query())
                ->from('{{%brands}}')
                ->where(['user_id' => $userId])
                ->exists();
            if ($taken) {
                $this->addError('name', 'У вас уже зарегистрирован бренд.');
                return false;
            }
        }

        $description = (string) $this->description;
        if ($schema->getColumn('city') === null) {
            $description = rtrim($description) . "\n\nГород: " . $this->city;
        }

        $row = [
            'name' => $this->name,
            'description' => $description,
        ];
        if ($schema->getColumn('city') !== null) {
            $row['city'] = $this->city;
        }
        if ($schema->getColumn('user_id') !== null) {
            $row['user_id'] = $userId;
        }
        if ($schema->getColumn('logo') !== null && !array_key_exists('logo', $row)) {
            $row['logo'] = null;
        }
        if ($schema->getColumn('created_at') !== null) {
            $row['created_at'] = new Expression('NOW()');
        }

        try {
            Yii::$app->db->createCommand()->insert('{{%brands}}', $row)->execute();
            $this->brandId = (int) Yii::$app->db->getLastInsertID();
        } catch (\Throwable $e) {
            $this->addError('name', 'Не удалось сохранить бренд. Выполните SQL из sql/themarketdb_brand_seller.sql или проверьте таблицу brands.');
            return false;
        }

        $user = User::findIdentity($userId);
        if ($user !== null && $user->hasAttribute('role')) {
            $user->setAttribute('role', 'seller');
            $user->save(false);
        }

        if (Yii::$app->user->identity !== null && (int) Yii::$app->user->id === $userId) {
            $fresh = User::findIdentity($userId);
            if ($fresh !== null) {
                Yii::$app->user->setIdentity($fresh);
            }
        }

        return $this->brandId > 0;
    }

    public function loadFromBrand(array $brand): void
    {
        $this->name = (string) ($brand['name'] ?? '');
        $this->description = (string) ($brand['description'] ?? '');
        $this->city = (string) ($brand['city'] ?? '');
        $this->brandId = (int) ($brand['id'] ?? 0);
    }

    /**
     * Обновление бренда владельцем.
     */
    public function updateBrand(int $brandId, int $userId): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $schema = Yii::$app->db->getTableSchema('{{%brands}}', true);
        if ($schema === null) {
            $this->addError('name', 'Таблица брендов недоступна.');
            return false;
        }

        $owner = (new Query())
            ->from('{{%brands}}')
            ->where(['id' => $brandId, 'user_id' => $userId])
            ->one();
        if (empty($owner)) {
            $this->addError('name', 'Бренд не найден или нет прав.');
            return false;
        }

        $description = (string) $this->description;
        if ($schema->getColumn('city') === null) {
            $description = rtrim($description) . "\n\nГород: " . $this->city;
        }

        $update = [
            'name' => $this->name,
            'description' => $description,
        ];
        if ($schema->getColumn('city') !== null) {
            $update['city'] = $this->city;
        }

        try {
            Yii::$app->db->createCommand()->update('{{%brands}}', $update, ['id' => $brandId])->execute();
            $this->brandId = $brandId;
        } catch (\Throwable $e) {
            $this->addError('name', 'Не удалось сохранить изменения.');
            return false;
        }

        return true;
    }
}
