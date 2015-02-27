<?php
namespace app\modules\site\models;
use app\base\db\ActiveRecord;

class Modules extends ActiveRecord
{
    protected static $structure = [
        "title" => [
            "type" => "string",
            "title" => "Название",
            'identify' => true,
        ]
    ];

    private static $modulesListNumKeys = [];
    private static $modulesList = [];

    public static function getModulesList($numericKeys = true)
    {
        if (static::$modulesListNumKeys && $numericKeys) {
            return static::$modulesListNumKeys;
        } elseif (static::$modulesList && !$numericKeys) {
            return static::$modulesList;
        }
        $dir = \Yii::getAlias("@app/modules");
        $files = scandir($dir);
        $res = [];

        $i = 0;
        foreach ($files as $item) {
            if (is_dir($dir ."/".$item) && $item != '.' && $item != '..') {
                $res[($numericKeys ? $i : $item)] = [
                    "id" => count($res)+1,
                    "name" => $item,
                    "title" => $item,
                ];
                $i++;
            }
        }

        if (static::$modulesListNumKeys && $numericKeys) {
            static::$modulesListNumKeys = $res;
        } elseif (static::$modulesList && !$numericKeys) {
            static::$modulesList = $res;
        }

        return $res;
    }

    public static function getList($params)
    {
        $res = static::getModulesList();
        $dataKey = (isset($params['dataKey']) ? $params['dataKey'] : 'data');
        $res = [$dataKey => $res];
        return $res;
    }
}