<?php
namespace app\modules\backend\assets;
use yii\web\AssetBundle;

class AdminMenuAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/backend/assets';
    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}