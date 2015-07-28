<?php
namespace app\base;
class Module extends \yii\base\Module
{
    static protected $inSiteStructure = true;
    static protected $moduleTitle = '';
    static public function getModuleTitle()
    {
        return (static::$moduleTitle ? static::$moduleTitle : static::className());
    }

    static public function getInSiteStructure()
    {
        return static::$inSiteStructure;
    }
}