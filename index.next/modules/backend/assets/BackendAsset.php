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
    public $css = [
        'js/ext/resources/css/ext-all-neptune.css',
        'css/next.index.css',
        'css/Ext.ux.index.form.Form.css',
        'css/Ext.ux.index.form.TitleEditPanel.css',
        'css/Ext.ux.form.field.FileField.css',
        'css/data-view.css',
        'css/GridFilters.css',
        'css/RangeMenu.css',
    ];
    public $jsOptions = ['position' => \yii\web\View::POS_END];
    public $depends = [
        'app\modules\backend\assets\ExtJsAsset',
    ];
    public $publishOptions = [
        'forceCopy' => true
    ];
}