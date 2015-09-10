<?php

$params = [];
$modules = require_once(__DIR__ . '/modules.php');
if (file_exists(__DIR__ . '/params.php')) {
    $params = require(__DIR__ . '/params.php');
}

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => $modules,
    'components' => [
        'request' => [
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
            'errorAction' => 'site/default/error',
        ],
        'mailer' => [
            'class' => 'app\components\Mailer',
            'useFileTransport' => false,
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

                'directrequest/<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-\.]+>' => '<module>/directrequest-<controller>/<action>',
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
    'params' => array_merge(['breadCrumbs' => []], [
        'passwordRestoreLetterSubject' => 'Востановление доступа к панели управления сайта '.$_SERVER['SERVER_NAME'],
        'supportLetterSubject' => 'Обращение в службу поддержки сайта '.$_SERVER['SERVER_NAME'],
    ], [
        'cmsEmail' => 'admin@localhost',
        'cmsEmailName' => 'index.next CMS',
        'defaultImageBgColor' => '#FFFFFF',
        'themeName' => 'base',
    ], $params),
];

if (YII_ENV_DEV) {
    if (strncmp(trim($_SERVER['REQUEST_URI'], "/"), 'admin', 5)) {
        $config['bootstrap'][] = 'debug';
        $config['modules']['debug'] = [
            'class' => 'yii\debug\Module',
            'allowedIPs' => ['127.0.0.1']
        ];
    }

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

if (file_exists(__DIR__ . '/web-project.php')) {
    $overrideConfig = require_once(__DIR__ . '/web-project.php');
    $config = array_merge($config, $overrideConfig);
}

return $config;