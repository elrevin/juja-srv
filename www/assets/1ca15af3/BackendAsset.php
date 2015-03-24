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
    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];

    public function init()
    {
        $this->depends = [
            'app\modules\backend\assets\ExtJsAsset',
        ];
        $dir = \Yii::getAlias("@app/modules");
        $modules = scandir($dir);
        foreach ($modules as $item) {
            if (file_exists($dir."/".$item."/assets/AdminMenuAsset.php")) {
                $this->depends[] = 'app\modules\\'.$item.'\assets\AdminMenuAsset';
            }
        }
    }
}