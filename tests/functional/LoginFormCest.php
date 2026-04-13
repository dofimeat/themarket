<?php

use app\models\User;

class LoginFormCest
{
    private const DEMO_EMAIL = 'func_demo@example.com';
    private const DEMO_PASSWORD = 'demo_pass';

    public function _before(\FunctionalTester $I)
    {
        User::deleteAll(['email' => self::DEMO_EMAIL]);
        $user = new User();
        $user->email = self::DEMO_EMAIL;
        $user->setPassword(self::DEMO_PASSWORD);
        $user->first_name = 'Func';
        $user->last_name = 'Test';
        $user->save(false);

        $I->amOnRoute('site/login');
    }

    public function _after()
    {
        User::deleteAll(['email' => self::DEMO_EMAIL]);
    }

    public function openLoginPage(\FunctionalTester $I)
    {
        $I->see('Вход', 'h1');
    }

    public function internalLoginById(\FunctionalTester $I)
    {
        $id = User::findOne(['email' => self::DEMO_EMAIL])->id;
        $I->amLoggedInAs($id);
        $I->amOnRoute('site/profile');
        $I->see('Профиль', 'h1');
    }

    public function internalLoginByInstance(\FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findOne(['email' => self::DEMO_EMAIL]));
        $I->amOnRoute('site/profile');
        $I->see('Профиль', 'h1');
    }

    public function loginWithEmptyCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', []);
        $I->expectTo('see validations errors');
        $I->see('Email cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function loginWithWrongCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[email]' => self::DEMO_EMAIL,
            'LoginForm[password]' => 'wrong',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Неверный e-mail или пароль.');
    }

    public function loginSuccessfully(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[email]' => self::DEMO_EMAIL,
            'LoginForm[password]' => self::DEMO_PASSWORD,
        ]);
        $I->see('Профиль', 'h1');
        $I->dontSeeElement('form#login-form');
    }
}
