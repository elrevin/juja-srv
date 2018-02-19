<?php

$url = strtolower(ltrim($_SERVER['REQUEST_URI'], " /"));
if (strncmp($url, "index.php", 9) == 0) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: ".substr($url, 9));
    exit();
}

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../index.next/vendor/autoload.php');
require(__DIR__ . '/../index.next/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../index.next/config/web.php');

(new yii\web\Application($config))->run();
