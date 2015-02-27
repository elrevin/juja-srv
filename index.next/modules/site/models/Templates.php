<?php
namespace app\modules\site\models;
use app\base\db\ActiveRecord;

class Templates extends ActiveRecord
{
    protected static $structure = [
        "title" => [
            "type" => "string",
            "title" => "Название",
            'identify' => true,
        ]
    ];

    private static $templatesListNumKeys = [];
    private static $templatesList = [];

    public static function getTemplatesList($module, $numericKeys = true)
    {
        if (static::$templatesListNumKeys && $numericKeys) {
            return static::$templatesListNumKeys;
        } elseif (static::$templatesList && !$numericKeys) {
            return static::$templatesList;
        }

        if (\Yii::$app->params['themeName']) {
            \Yii::setAlias('@theme', '@web/themes/'.\Yii::$app->params['themeName']);
            \Yii::setAlias('@themeroot', '@webroot/themes/'.\Yii::$app->params['themeName']);
        }

        $dir = '@themeroot/views/'.$module."/views";
        $dir = \Yii::getAlias($dir);
        if (!file_exists($dir)) {
            $dir = '@app/modules/'.$module.'/views';
        }

        $dir = \Yii::getAlias($dir);
        if (!file_exists($dir)) {
            return [];
        }

        $dirs = scandir($dir);
        $res = [];

        $i = 0;
        foreach ($dirs as $dirItem) {
            if (is_dir($dir ."/".$dirItem) && $dirItem != '.' && $dirItem != '..' && $dirItem != 'components' && $dirItem != 'widgets' && $dirItem != 'layouts' && $dirItem != 'mail') {
                $files = scandir($dir ."/".$dirItem);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..' && is_file($dir ."/".$dirItem."/".$file) && preg_match("/^[^.]+\\.twig$/i", $file)) {
                        // Читаем файл шаблона и ищем там название
                        $content = file_get_contents($dir ."/".$dirItem."/".$file);
                        $name = $dirItem.'/'.str_replace(".twig", '', $file);
                        if (preg_match("/\\{#\\s*@title\\s*([^#]+)#\\}/i", $content, $match)) {
                            $title = $match[1];
                        } else {
                            $title = $name;
                        }
                        $res[($numericKeys ? $i : $name)] = [
                            "id" => $i+1,
                            "name" => $name,
                            "title" => $title,
                        ];
                        $i++;
                    }
                }
                $i++;
            }
        }

        if (static::$templatesListNumKeys && $numericKeys) {
            static::$templatesListNumKeys = $res;
        } elseif (static::$templatesList && !$numericKeys) {
            static::$templatesList = $res;
        }

        return $res;
    }

    public static function getList($params)
    {
        $res = [];
        if (isset($params['filter']) && isset($params['filter'][0]) && isset($params['filter'][0]['value']) && $params['filter'][0]['value']) {
            $module = Modules::getModulesList()[intval($params['filter'][0]['value'])-1]['name'];
            $res = static::getTemplatesList($module);
            $dataKey = (isset($params['dataKey']) ? $params['dataKey'] : 'data');
            $res = [$dataKey => $res];
        }
        return $res;
    }
}