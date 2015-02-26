<?php
namespace app\modules\site\models;
use app\base\db\ActiveRecord;

class Modules extends ActiveRecord
{
    protected static $structure = [
        "title" => [
            "type" => "string",
            "title" => "Название"
        ]
    ];

    public static function getList($params)
    {
        $dir = \Yii::getAlias("@app/modules");
        $files = scandir($dir);
        $res = [];

        foreach ($files as $item) {
            if (is_dir($dir ."/".$item) && $item != '.' && $item != '..') {
                $res[] = [
                    "id" => count($res)+1,
                    "title" => $item
                ];
            }
        }
        $dataKey = (isset($params['dataKey']) ? $params['dataKey'] : 'data');
        $res = [$dataKey => $res];
        return $res;
    }
}