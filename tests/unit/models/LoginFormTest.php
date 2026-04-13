<?php

namespace tests\unit\models;

use app\models\LoginForm;
use app\models\User;
use Yii;

class LoginFormTest extends \Codeception\Test\Unit
{
    private const DEMO_EMAIL = 'unit_demo@example.com';
    private const DEMO_PASSWORD = 'demo_pass';

    protected function _before()
    {
        User::deleteAll(['email' => self::DEMO_EMAIL]);
        $user = new User();
        $user->email = self::DEMO_EMAIL;
        $user->setPassword(self::DEMO_PASSWORD);
        $user->first_name = 'Unit';
        $user->last_name = 'Test';
        if (!$user->save(false)) {
            $this->markTestSkipped('База данных недоступна или нет таблицы user.');
        }
    }

    protected function _after()
    {
        Yii::$app->user->logout();
        User::deleteAll(['email' => self::DEMO_EMAIL]);
    }

    public function testLoginNoUser()
    {
        $model = new LoginForm([
            'email' => 'missing@example.com',
            'password' => 'x',
        ]);
        verify($model->login())->false();
        verify(Yii::$app->user->isGuest)->true();
    }

    public function testLoginWrongPassword()
    {
        $model = new LoginForm([
            'email' => self::DEMO_EMAIL,
            'password' => 'wrong_password',
        ]);
        verify($model->login())->false();
        verify(Yii::$app->user->isGuest)->true();
        verify($model->errors)->arrayHasKey('password');
    }

    public function testLoginCorrect()
    {
        $model = new LoginForm([
            'email' => self::DEMO_EMAIL,
            'password' => self::DEMO_PASSWORD,
        ]);
        verify($model->login())->true();
        verify(Yii::$app->user->isGuest)->false();
        verify($model->errors)->arrayHasNotKey('password');
    }
}
