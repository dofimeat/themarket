<?php

namespace app\models;

use Yii;
use yii\base\Model;

class RegisterForm extends Model
{
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $password_repeat;

    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email', 'password', 'password_repeat'], 'trim'],
            [['first_name', 'last_name', 'email', 'password', 'password_repeat'], 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email', 'message' => 'Этот e-mail уже зарегистрирован.'],
            ['password', 'string', 'min' => 6, 'tooShort' => 'Пароль должен быть не короче 6 символов.'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли должны совпадать.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'email' => 'E-mail',
            'password' => 'Пароль',
            'password_repeat' => 'Повторите пароль',
        ];
    }

    /**
     * @return bool
     */
    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = new User();
        $user->email = $this->email;
        $user->setPassword($this->password);
        if ($user->hasAttribute('first_name')) {
            $user->first_name = $this->first_name;
        }
        if ($user->hasAttribute('last_name')) {
            $user->last_name = $this->last_name;
        }
        if ($user->hasAttribute('username')) {
            $user->username = User::generateUniqueUsername($this->email);
        }

        if ($user->hasAttribute('avatar')) {
            $user->avatar = User::DEFAULT_AVATAR;
        }

        if (!$user->save()) {
            return false;
        }

        return Yii::$app->user->login($user, 3600 * 24 * 30);
    }
}
