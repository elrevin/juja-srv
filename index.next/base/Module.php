<?php
namespace app\base;
class Module extends \yii\base\Module
{
    static protected $inSiteStructure = true;
    static public function getModuleTitle()
    {
        return static::className();
    }

    static public function getInSiteStructure()
    {
        return static::$inSiteStructure;
    }
}