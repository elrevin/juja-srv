<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');
$dbfias = require(__DIR__ . '/db-fias.php');

$rootDirPath = realpath(__DIR__ . "/../../").'/';
if (file_exists($rootDirPath."www")) {
    $wwwDir = $rootDirPath."www";
} else {
    $filesList = scandir($rootDirPath);

    $wwwDir = '';

    foreach ($filesList as $item) {
        if ($item != '.' && $item != '..' && $item != 'html' && is_dir($rootDirPath.$item) && strpos($item, '.') === false && file_exists($rootDirPath.$item."/index.php")) {
            $wwwDir = $rootDirPath .$item;
            break;
        }
    }

    if (!$wwwDir) {
        throw new \yii\console\Exception('WWW dir not found');
    }

}

$wwwDir = str_replace('\\', '/', $wwwDir);

Yii::setAlias("web", "/");
Yii::setAlias("webroot", $wwwDir);

$modules = require_once(__DIR__ . '/modules.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => array_merge($modulesNames, ['log', 'gii']),
    'controllerNamespace' => 'app\commands',
    'modules' => array_merge($modules, [
        'gii' => 'yii\gii\Module',
    ]),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error'],
                    'message' => [
                        'from' => ['log@gkh-site.ru'],
                        'to' => ['admin@test.ru'],
                        'subject' => 'Ошибки на сайте ЖКХ',
                    ],
                ],
            ],
        ],
        'db' => $db,
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
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
    ],
    'params' => $params,
];
