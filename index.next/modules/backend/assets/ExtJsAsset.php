<?php
namespace app\modules\backend\assets;

use app\helpers\Utils;
use yii\web\AssetBundle;

class ExtJsAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/backend/assets';
    public $js = [
        'js/ext/ext-all.js',
        'js/ext/locale/ext-lang-ru.js',
        'js/ext/ext-theme-neptune.js',
        'js/ext-ux/tinymce/tiny_mce_src.js',
    ];
    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
}