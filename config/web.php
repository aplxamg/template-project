<?php

$params = require(__DIR__ . '/params.php');
$route  = require(__DIR__ . '/route.php');

// Database Configuration
$databases = [
    'db'  => '_commondb',
];

$dbConfig = [
    'class' => 'yii\db\Connection',
    'dsn' => '',
    'username' => 'root',
    'password' => 'dimensions',
    'charset' => 'utf8',
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];

foreach ($databases as $dbKey => $dbName) {
    $dsn = 'mysql:host=localhost;dbname=' . $dbName;
    $dbConfig['dsn'] = $dsn;
    $$dbKey = $dbConfig;
}

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Manila',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'E4594C83D7D4E649DD47CB71ADDA2',
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
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'Y/MM/dd',
            'datetimeFormat' => 'Y/MM/dd H:i:s',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
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
        'urlManager' => $route, // Routing
        // Database Configuration
        'db'         => $db, // Default

    ],
     // Modules
    'modules' => [
        'account' => [
            'class' => 'app\modules\account\Module',
        ],
        'asset' => [
            'class' => 'app\modules\asset\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'] // adjust this to your needs
    ];

}

return $config;
