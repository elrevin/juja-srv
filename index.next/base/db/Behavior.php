<?php
namespace app\base\db;

class Behavior extends \yii\base\Behavior
{
    protected static $useInOwnerModuleOnly = false;
    protected static $title = '';

    public static function getUseInOwnerModuleOnly()
    {
        return static::$useInOwnerModuleOnly;
    }

    public static function getTitle()
    {
        return static::$title;
    }
}