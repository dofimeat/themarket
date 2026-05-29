<?php

namespace app\models;

use app\helpers\ImageUploadHelper;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\web\UploadedFile;

/**
 * Регистрация бренда продавцом (название, описание, город).
 */
class BrandRegisterForm extends Model
{
    public $name;
    public $description;
    public $city;

    /** Текущий логотип (путь от web), только для отображения. */
    public $currentLogo = '';

    /** Текущий баннер (путь от web), только для отображения. */
    public $currentBannerImage = '';

    /** Текущий цвет баннера. */
    public $bannerColor = '';

    /** @var UploadedFile|null */
    public $logoFile;

    /** @var UploadedFile|null */
    public $bannerImageFile;

    /** @var bool Удалить баннер-картинку */
    public $deleteBanner = false;

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
            ['bannerColor', 'string', 'max' => 20],
            ['deleteBanner', 'boolean'],
            [
                'logoFile',
                'file',
                'skipOnEmpty' => true,
                'extensions' => ImageUploadHelper::ALLOWED_EXTENSIONS,
                'maxSize' => ImageUploadHelper::MAX_BYTES,
                'checkExtensionByMimeType' => false,
                'wrongExtension' => 'Допустимы JPG, PNG, GIF или WebP.',
                'tooBig' => 'Файл не должен превышать 5 МБ.',
            ],
            [
                'bannerImageFile',
                'file',
                'skipOnEmpty' => true,
                'extensions' => ImageUploadHelper::ALLOWED_EXTENSIONS,
                'maxSize' => ImageUploadHelper::MAX_BYTES,
                'checkExtensionByMimeType' => false,
                'wrongExtension' => 'Допустимы JPG, PNG, GIF или WebP.',
                'tooBig' => 'Файл не должен превышать 5 МБ.',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название бренда',
            'description' => 'Описание',
            'city' => 'Город',
            'logoFile' => 'Логотип бренда',
            'bannerImageFile' => 'Баннер бренда',
            'bannerColor' => 'Цвет фона баннера',
            'deleteBanner' => 'Удалить баннер',
        ];
    }

    /**
     * @return string|null путь от web
     */
    private function saveLogoUpload(int $brandId): ?string
    {
        if ($this->logoFile === null) {
            return null;
        }

        return ImageUploadHelper::saveImage($this->logoFile, 'brands', 'b' . $brandId);
    }

    public function save(int $userId): bool
    {
        $this->logoFile = UploadedFile::getInstance($this, 'logoFile');
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
            $row['logo'] = User::DEFAULT_AVATAR;
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

        if ($schema->getColumn('logo') !== null && $this->logoFile !== null) {
            $logoPath = $this->saveLogoUpload($this->brandId);
            if ($logoPath === null) {
                $this->addError('logoFile', 'Не удалось загрузить логотип.');
                return false;
            }
            Yii::$app->db->createCommand()->update('{{%brands}}', ['logo' => $logoPath], ['id' => $this->brandId])->execute();
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
        $this->currentLogo = Brand::resolveLogoPath($brand['logo'] ?? null);
        $this->currentBannerImage = trim((string) ($brand['banner_image'] ?? ''));
        $this->bannerColor = trim((string) ($brand['banner_color'] ?? ''));
    }

    /**
     * Обновление бренда владельцем.
     */
    public function updateBrand(int $brandId, int $userId): bool
    {
        $this->logoFile = UploadedFile::getInstance($this, 'logoFile');
        $this->bannerImageFile = UploadedFile::getInstance($this, 'bannerImageFile');
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

        // Logo upload
        if ($schema->getColumn('logo') !== null && $this->logoFile !== null) {
            $logoPath = ImageUploadHelper::saveImage($this->logoFile, 'brands', 'b' . $brandId);
            if ($logoPath === null) {
                $this->addError('logoFile', 'Не удалось загрузить логотип.');
                return false;
            }
            $update['logo'] = $logoPath;
            $oldLogo = trim((string) ($owner['logo'] ?? ''));
            if ($oldLogo !== '' && $oldLogo !== User::DEFAULT_AVATAR) {
                ImageUploadHelper::deleteIfUploaded($oldLogo);
            }
        }

        // Delete banner image
        if ($this->deleteBanner && $schema->getColumn('banner_image') !== null) {
            $oldBanner = trim((string) ($owner['banner_image'] ?? ''));
            if ($oldBanner !== '') {
                ImageUploadHelper::deleteIfUploaded($oldBanner);
            }
            $update['banner_image'] = null;
            $this->currentBannerImage = '';
        }

        // Banner image upload
        if ($schema->getColumn('banner_image') !== null && $this->bannerImageFile !== null && !$this->deleteBanner) {
            $bannerPath = ImageUploadHelper::saveImage($this->bannerImageFile, 'brands', 'banner' . $brandId);
            if ($bannerPath === null) {
                $this->addError('bannerImageFile', 'Не удалось загрузить баннер.');
                return false;
            }
            $update['banner_image'] = $bannerPath;
            $oldBanner = trim((string) ($owner['banner_image'] ?? ''));
            if ($oldBanner !== '') {
                ImageUploadHelper::deleteIfUploaded($oldBanner);
            }
            // When banner image is set, clear the color
            $update['banner_color'] = null;
            $this->bannerColor = '';
        }

        // Banner color (only when no banner image or image was not changed)
        if ($schema->getColumn('banner_color') !== null) {
            $colorValue = trim((string) $this->bannerColor);
            if ($colorValue !== '') {
                $update['banner_color'] = $colorValue;
                // If setting a color, keep the image only if user didn't upload new one
            } elseif (!isset($update['banner_image'])) {
                // Clear color only if no new image was uploaded
                $update['banner_color'] = null;
            }
        }

        try {
            Yii::$app->db->createCommand()->update('{{%brands}}', $update, ['id' => $brandId])->execute();
            $this->brandId = $brandId;
            if (isset($update['logo'])) {
                $this->currentLogo = (string) $update['logo'];
            }
            if (isset($update['banner_image'])) {
                $this->currentBannerImage = (string) $update['banner_image'];
            }
            if ($this->deleteBanner) {
                $this->currentBannerImage = '';
            }
        } catch (\Throwable $e) {
            $this->addError('name', 'Не удалось сохранить изменения.');
            return false;
        }

        return true;
    }
}
