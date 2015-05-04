<?php
namespace app\base;
class Module extends \yii\base\Module
{
    static public function getModuleTitle()
    {
        return static::className();
    }

}