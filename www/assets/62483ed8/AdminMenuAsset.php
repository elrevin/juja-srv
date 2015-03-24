<?php
namespace app\modules\site\assets;

use yii\web\AssetBundle;

class AdminMenuAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/site/assets';
    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}