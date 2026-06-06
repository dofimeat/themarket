<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * @property-read User|null $user
 */
class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'trim'],
            ['email', 'validateLogin'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Custom validator for login (email or username).
     */
    public function validateLogin($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $value = (string) $this->$attribute;
        
        // If contains @, validate as email
        if (strpos($value, '@') !== false) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->addError($attribute, 'Некорректный формат email.');
            }
        } else {
            // Treat as username - just check length
            if (strlen($value) < 2) {
                $this->addError($attribute, 'Логин должен содержать минимум 2 символа.');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'email' => 'E-mail или логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный e-mail или пароль.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByLogin((string) $this->email);
        }
        return $this->_user;
    }
}
