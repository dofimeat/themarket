<?php

use yii\helpers\Url;

class LoginCest
{
    public function ensureThatLoginPageLoads(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/login'));
        $I->see('Вход', 'h1');
    }
}
