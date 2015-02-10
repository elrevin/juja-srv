<?php
namespace app\modules\backend\assets;

use app\helpers\Utils;
use yii\web\AssetBundle;

class AuthAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/backend/assets';
    public $css = [
        'css/auth.css'
    ];
    public $publishOptions = [
        'forceCopy' => true
    ];
}