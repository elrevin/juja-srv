<?php

$params = require(__DIR__ . '/params.php');
$params = array_merge($params, require(__DIR__ . '/webParams.php'));
$modules = require_once(__DIR__ . '/modules.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => $modules,
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'U3vahA9HZrCImrMtw5Y2HgfNDXRPTJOi',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\UserIdentity',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'app\components\Mailer',
            'useFileTransport' => false,
//            'viewPath' => '@themeroot/mail/views',
//            'htmlLayout' => '@themeroot/mail/layouts/html',
            'view' => [
                'defaultExtension' => 'twig',
                'class' => 'app\components\View',
                'renderers' => [
                    'twig' => [
                        'class' => 'app\components\TwigViewRenderer',
                        'globals' => ['html' => '\yii\helpers\Html'],
                        'uses' => ['yii\bootstrap'],
                        'options' => ['auto_reload' => true, 'autoescape' => ''],
                    ],
                ],
            ],
//            'transport' => [
//                'class' => 'Swift_SmtpTransport',
//                'host' => 'smtp.gmail.com',
//                'username' => 'username@gmail.com',
//                'password' => 'password',
//                'port' => '587',
//                'encryption' => 'tls',
//            ],
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
        'db' => require(__DIR__ . '/db.php'),
        'view' => [
            'defaultExtension' => 'twig',
            'class' => 'app\components\View',
            'renderers' => [
                'twig' => [
                    'class' => 'app\components\TwigViewRenderer',
                    'globals' => ['html' => '\yii\helpers\Html'],
                    'uses' => ['yii\bootstrap'],
                    'options' => ['auto_reload' => true, 'autoescape' => '', 'strict_variables' => false],
                ],
            ],
        ],
        'urlManager' => [
            'class' => '\app\components\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '/',
            'rules' => [
                'debug/<controller>/<action>' => 'debug/<controller>/<action>',
                'admin' => 'backend/default/index',
                'admin/<action:[\w-\.]+>' => 'backend/default/<action>',
                'admin/<controller:[\w-]+>/<action:[\w-\.]+>' => 'backend/<controller>/<action>',
                'admin/<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-\.]+>' => '<module>/adm-<controller>/<action>',

                'admin/getJS/<module:[\w-]+>/<modelName:[\w-]+>/<file:[\w-\./]+>' => '<module>/adm-main/get-js-file.js',

                'directrequest/<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-\.]+>' => '<module>/dr-<controller>/<action>',
            ],
        ],
        'response' => [
            'formatters' => [
                'js' => [
                    'class' => '\app\components\JsResponseFormatter'
                ],
                'tjson' => [
                    'class' => '\app\components\TjsonResponseFormatter'
                ],
            ]
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'defaultRoles' => ['manager', 'admin'],
        ],
    ],
    'params' => array_merge(['breadCrumbs'=>[]], $params),
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    if (strncmp(trim($_SERVER['REQUEST_URI'], "/"), 'admin', 5)) {
        $config['bootstrap'][] = 'debug';
        $config['modules']['debug'] = [
            'class' => 'yii\debug\Module',
            'allowedIPs' => ['127.0.0.1']
        ];
    }

//    $config['bootstrap'][] = 'gii';
//    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
