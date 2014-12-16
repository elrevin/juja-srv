<?php
namespace app\helpers;

class Utils
{
    public static function getDataFile ($moduleName, $fileName)
    {
        return file_get_contents(\Yii::getAlias("@app/data/{$moduleName}/{$fileName}"));
    }
}