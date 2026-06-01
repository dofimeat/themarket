<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'freeroblox',
            // 'baseUrl' => '',
        ],
        'assetManager' => [
            // Helps when assets/CSS look "missing" due to caching or stale published bundles.
            'appendTimestamp' => true,
            'forceCopy' => YII_ENV_DEV,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'catalog' => 'site/catalog',
                'search' => 'site/search',
                'admin' => 'admin/index',
                'admin/users' => 'admin/users',
                'admin/brands' => 'admin/brands',
                'admin/products' => 'admin/products',
                'brands' => 'site/brands',
                'brand/<id:\d+>' => 'site/brand',
                'product/<id:\d+>' => 'site/product',
                'login' => 'site/login',
                'register' => 'site/register',
                'profile' => 'site/profile',
                'register-brand' => 'seller/register-brand',
                'brand-dashboard' => 'seller/brand-dashboard',
                'edit-brand' => 'seller/edit-brand',
                'add-product' => 'seller/add-product',
                'edit-product/<id:\d+>' => 'seller/edit-product',
                'toggle-product-status/<id:\d+>' => 'seller/toggle-product-status',
                // Старые ссылки /site/... (до выноса в SellerController)
                'site/register-brand' => 'seller/register-brand',
                'site/brand-dashboard' => 'seller/brand-dashboard',
                'site/edit-brand' => 'seller/edit-brand',
                'site/add-product' => 'seller/add-product',
                'site/edit-product/<id:\d+>' => 'seller/edit-product',
                'site/toggle-product-status/<id:\d+>' => 'seller/toggle-product-status',
            ],
        ],
        
    ],
    'params' => $params,
    'language' => 'ru-ru',
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
