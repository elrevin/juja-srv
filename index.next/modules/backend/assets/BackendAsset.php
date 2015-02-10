<?php
namespace app\modules\backend\assets;

use app\helpers\Utils;
use yii\web\AssetBundle;

class BackendAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/backend/assets';
    public $js = [
        'js/ExtOverrides.js',
        'js/utils.js',
        'js/main.js',
    ];
    public $jsOptions = ['position' => \yii\web\View::POS_END];
    public $depends = [
        'app\modules\backend\assets\ExtJsAsset',
    ];
    public $publishOptions = [
        'forceCopy' => true
    ];
    public function init()
    {
        $this->css = array_merge($this->css, Utils::getFiles(\Yii::getAlias("@app/modules/backend/assets/css"), 'css'));
        parent::init();
    }
}