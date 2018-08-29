<?php
namespace app\components;

class ClassMaps
{
    private static $modelsMaps = [];
    private static $pluginsMaps = [];

    public static function loadMaps()
    {
        static::$pluginsMaps = json_decode(file_get_contents(\Yii::getAlias("@app/maps/plugins.json")), true);
        static::$modelsMaps = json_decode(file_get_contents(\Yii::getAlias("@app/maps/models.json")), true);
    }

    public static function addModelToMap($module, $className)
    {
        if (!isset(static::$modelsMaps[$module])) {
            static::$modelsMaps[$module] = [];
        }

        static::$modelsMaps[$module][] = $className;

        $json = json_encode(static::$modelsMaps);
        file_put_contents(\Yii::getAlias("@app/maps/models.json"), $json);
    }

    public static function getModels($module = '')
    {
        return ($module ? (isset(static::$modelsMaps[$module]) ? static::$modelsMaps[$module] : []) : static::$modelsMaps);
    }

    public static function addPluginToMap($model, $className)
    {
        $model = trim($model, '\\');
        $className = '\\' . trim($className, '\\');
        if (!isset(static::$pluginsMaps[$model])) {
            static::$pluginsMaps[$model] = [];
        }

        static::$pluginsMaps[$model][] = $className;
        $json = json_encode(static::$pluginsMaps);
        file_put_contents(\Yii::getAlias("@app/maps/plugins.json"), $json);
    }

    public static function getPlugins($model)
    {
        $model = trim($model, '\\');
        return isset(static::$pluginsMaps[$model]) ? static::$pluginsMaps[$model] : [];
    }
}