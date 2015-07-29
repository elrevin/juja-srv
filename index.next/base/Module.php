<?php
namespace app\base;
class Module extends \yii\base\Module
{
    static protected $inSiteStructure = true;
    static protected $moduleTitle = '';
    static protected $siteSectionTypes = [];
    static public function getModuleTitle()
    {
        return (static::$moduleTitle ? static::$moduleTitle : static::className());
    }

    static public function getInSiteStructure()
    {
        return static::$inSiteStructure;
    }

    static public function getSiteSectionTypes ()
    {
        return static::$siteSectionTypes;
    }
}