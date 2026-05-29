<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * Таблица `users`: id, username, email, password_hash, role, created_at, first_name, last_name (и др.).
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $role
 * @property string|null $auth_key
 * @property string $first_name
 * @property string $last_name
 * @property int|null $notify_news
 * @property int|null $notify_orders
 * @property string|null $created_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    /** Роль новых пользователей при регистрации (должна быть допустима в колонке `role`). */
    public const ROLE_DEFAULT = 'user';

    public static function tableName()
    {
        return '{{%users}}';
    }

    public function behaviors()
    {
        return [];
    }

    public function rules()
    {
        $required = [];
        if ($this->hasAttribute('password_hash')) {
            $required[] = [['email', 'password_hash'], 'required'];
        } elseif ($this->hasAttribute('password')) {
            $required[] = [['email', 'password'], 'required'];
        } else {
            $required[] = [['email'], 'required'];
        }
        if ($this->hasAttribute('username')) {
            $required[] = [['username'], 'required'];
            $required[] = [['username'], 'unique'];
        }
        $common = [
            ['email', 'email'],
            ['email', 'unique'],
        ];
        if ($this->hasAttribute('role')) {
            $common[] = ['role', 'string', 'max' => 50];
        }
        if ($this->hasAttribute('auth_key')) {
            $common[] = ['auth_key', 'string', 'max' => 32];
        }
        if ($this->hasAttribute('first_name') && $this->hasAttribute('last_name')) {
            $common[] = [['first_name', 'last_name'], 'string', 'max' => 100];
            $common[] = [['first_name', 'last_name'], 'default', 'value' => ''];
        }
        return array_merge($required, $common);
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public static function findByEmail($email)
    {
        $email = trim((string) $email);
        if ($email === '') {
            return null;
        }
        return static::findOne(['email' => $email]);
    }

    /**
     * Поиск по e-mail или по полю username (вход «как логин»).
     */
    public static function findByLogin(string $login): ?self
    {
        $login = trim($login);
        if ($login === '') {
            return null;
        }
        $byEmail = static::findOne(['email' => $login]);
        if ($byEmail !== null) {
            return $byEmail;
        }
        $schema = static::getTableSchema();
        if ($schema !== null && isset($schema->columns['username'])) {
            return static::findOne(['username' => $login]);
        }
        return null;
    }

    /**
     * Уникальный логин на основе части e-mail до @.
     */
    public static function generateUniqueUsername(string $email): string
    {
        $local = strstr($email, '@', true);
        $base = $local !== false ? preg_replace('/[^a-zA-Z0-9_]/', '_', $local) : 'user';
        $base = trim($base, '_');
        if ($base === '' || $base === '_') {
            $base = 'user';
        }
        $candidate = $base;
        $n = 0;
        while (static::find()->where(['username' => $candidate])->exists()) {
            $n++;
            $candidate = $base . '_' . $n;
        }
        return $candidate;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        if (!$this->hasAttribute('auth_key')) {
            return '';
        }
        return (string) ($this->getAttribute('auth_key') ?? '');
    }

    public function validateAuthKey($authKey)
    {
        // Без колонки auth_key Yii кладёт в сессию пустую строку — иначе validateAuthKey(false) ломает вход на каждом запросе.
        if (!$this->hasAttribute('auth_key')) {
            return true;
        }
        return hash_equals($this->getAuthKey(), (string) $authKey);
    }

    public function validatePassword($password)
    {
        $hash = $this->getPasswordHashForValidation();
        if ($hash === '') {
            return false;
        }
        if (self::looksLikePasswordHash($hash)) {
            return Yii::$app->security->validatePassword($password, $hash);
        }
        // Старые записи: не bcrypt (например тестовый текст вместо хэша).
        if (strlen($hash) === 32 && ctype_xdigit($hash)) {
            return hash_equals($hash, md5($password));
        }
        return hash_equals($hash, (string) $password);
    }

    protected static function looksLikePasswordHash(string $stored): bool
    {
        return str_starts_with($stored, '$2y$')
            || str_starts_with($stored, '$2a$')
            || str_starts_with($stored, '$2b$')
            || str_starts_with($stored, '$argon2');
    }

    protected function getPasswordHashForValidation(): string
    {
        if ($this->hasAttribute('password_hash')) {
            $v = $this->getAttribute('password_hash');
            if ($v !== null && $v !== '') {
                return (string) $v;
            }
        }
        if ($this->hasAttribute('password')) {
            return (string) $this->getAttribute('password');
        }
        return '';
    }

    public function setPassword($password)
    {
        $hash = Yii::$app->security->generatePasswordHash($password);
        if ($this->hasAttribute('password_hash')) {
            $this->password_hash = $hash;
        } elseif ($this->hasAttribute('password')) {
            $this->password = $hash;
        }
    }

    public function generateAuthKey()
    {
        if ($this->hasAttribute('auth_key')) {
            $this->auth_key = Yii::$app->security->generateRandomString();
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert && $this->hasAttribute('role') && ($this->getAttribute('role') === null || $this->getAttribute('role') === '')) {
            $this->role = self::ROLE_DEFAULT;
        }
        if ($insert && $this->hasAttribute('created_at') && ($this->getAttribute('created_at') === null || $this->getAttribute('created_at') === '')) {
            $this->created_at = new Expression('NOW()');
        }
        if (
            $insert
            && $this->hasAttribute('auth_key')
            && ($this->getAttribute('auth_key') === null || $this->getAttribute('auth_key') === '')
        ) {
            $this->generateAuthKey();
        }
        return true;
    }

    public function getDisplayName()
    {
        $fn = $this->hasAttribute('first_name') ? (string) $this->getAttribute('first_name') : '';
        $ln = $this->hasAttribute('last_name') ? (string) $this->getAttribute('last_name') : '';
        $name = trim($fn . ' ' . $ln);
        if ($name !== '') {
            return $name;
        }
        return (string) $this->getAttribute('email');
    }
}
