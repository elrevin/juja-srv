<?php
namespace app\modules\files\assets;

use app\helpers\Utils;
use yii\web\AssetBundle;

class AdminMenuAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/files/assets';
    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}