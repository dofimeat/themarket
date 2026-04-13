<?php

namespace tests\unit\models;

use app\models\User;

class UserTest extends \Codeception\Test\Unit
{
    public function testSetPasswordAndValidate()
    {
        $user = new User();
        $user->setPassword('secret');
        verify($user->validatePassword('secret'))->true();
        verify($user->validatePassword('wrong'))->false();
    }

    public function testDisplayName()
    {
        $user = new User();
        $user->email = 'a@b.com';
        if ($user->hasAttribute('first_name') && $user->hasAttribute('last_name')) {
            $user->first_name = 'Иван';
            $user->last_name = 'Иванов';
            verify($user->getDisplayName())->equals('Иван Иванов');
            $user->first_name = '';
            $user->last_name = '';
        }
        verify($user->getDisplayName())->equals('a@b.com');
    }
}
