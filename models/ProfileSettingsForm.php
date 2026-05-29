<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Настройки аккаунта: данные из User, пароль и уведомления по желанию.
 */
class ProfileSettingsForm extends Model
{
    public $first_name;
    public $last_name;
    /** Только отображение, не подставляется из POST (см. scenarios). */
    public $email;

    /** Новый пароль; пусто — не менять. */
    public $new_password;

    /** 0/1 — колонки users.notify_news / notify_orders, если есть. */
    public $notify_news = 1;
    public $notify_orders = 1;

    /** @var User */
    public $user;

    public function rules()
    {
        return [
            [['first_name', 'last_name'], 'string', 'max' => 100],
            [['first_name', 'last_name'], 'trim'],
            ['email', 'string'],
            ['new_password', 'string', 'min' => 6, 'skipOnEmpty' => true],
            [['notify_news', 'notify_orders'], 'filter', 'filter' => static function ($v) {
                return ((int) (bool) $v) ? 1 : 0;
            }],
            [['notify_news', 'notify_orders'], 'in', 'range' => [0, 1]],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_DEFAULT] = ['first_name', 'last_name', 'new_password', 'notify_news', 'notify_orders'];
        return $scenarios;
    }

    public function attributeLabels()
    {
        return [
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'email' => 'E-mail',
            'new_password' => 'Новый пароль',
            'notify_news' => 'Новости и акции',
            'notify_orders' => 'Статус заказов',
        ];
    }

    public function init()
    {
        parent::init();
        if ($this->user === null) {
            return;
        }
        $this->first_name = (string) $this->user->getAttribute('first_name');
        $this->last_name = (string) $this->user->getAttribute('last_name');
        $this->email = (string) $this->user->getAttribute('email');

        if ($this->user->hasAttribute('notify_news')) {
            $this->notify_news = (int) $this->user->getAttribute('notify_news') ? 1 : 0;
        }
        if ($this->user->hasAttribute('notify_orders')) {
            $this->notify_orders = (int) $this->user->getAttribute('notify_orders') ? 1 : 0;
        }
    }

    public function save(): bool
    {
        if (!$this->validate() || $this->user === null) {
            return false;
        }
        if ($this->user->hasAttribute('first_name')) {
            $this->user->setAttribute('first_name', $this->first_name ?? '');
        }
        if ($this->user->hasAttribute('last_name')) {
            $this->user->setAttribute('last_name', $this->last_name ?? '');
        }
        if ($this->user->hasAttribute('notify_news')) {
            $this->user->setAttribute('notify_news', $this->notify_news);
        }
        if ($this->user->hasAttribute('notify_orders')) {
            $this->user->setAttribute('notify_orders', $this->notify_orders);
        }
        if ($this->new_password !== null && trim((string) $this->new_password) !== '') {
            $this->user->setPassword((string) $this->new_password);
        }

        if (!$this->user->save(false)) {
            return false;
        }

        $this->user->refresh();
        if (Yii::$app->user->identity !== null && (int) Yii::$app->user->id === (int) $this->user->id) {
            Yii::$app->user->setIdentity($this->user);
        }

        return true;
    }
}
