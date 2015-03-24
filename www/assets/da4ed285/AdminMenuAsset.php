<?php
namespace app\modules\catalog\assets;

use yii\web\AssetBundle;

class AdminMenuAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/catalog/assets';
    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}