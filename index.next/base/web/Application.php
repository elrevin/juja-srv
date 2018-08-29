<?php
namespace app\base\web;

use app\base\db\Plugin;
use app\components\ClassMaps;

class Application extends \yii\web\Application
{
    private function fillMaps()
    {
        $modulesDir = \Yii::getAlias("@app/modules/");
        $modules = scandir($modulesDir);
        foreach ($modules as $module) {
            $currentModuleDir = $modulesDir . $module;
            if ($module != "." && $module != ".." && is_dir($currentModuleDir)) {
                $pluginsDir = $currentModuleDir . "/plugins/";
                if (file_exists($pluginsDir)) {
                    $plugins = scandir($pluginsDir);
                    foreach ($plugins as $plugin) {
                        if (preg_match("/^[a-z0-9]+\\.php\$/i", $plugin)) {
                            /** @var Plugin $pluginClass */
                            $pluginClass = '\app\modules\\' . $module . '\plugins\\' . str_replace(".php", "", $plugin);
                            ClassMaps::addPluginToMap($pluginClass::getFor(), $pluginClass);
                        }
                    }
                }

                $modelsDir = $currentModuleDir . "/models/";
                if (file_exists($modelsDir)) {
                    $models = scandir($modelsDir);
                    foreach ($models as $model) {
                        if (preg_match("/^[a-z0-9]+\\.php\$/i", $model)) {
                            /** @var Plugin $pluginClass */
                            $model = str_replace(".php", "", $model);
                            $modelClass = '\app\modules\\' . $module . '\models\\' . $model;
                            ClassMaps::addModelToMap($module, $modelClass);
                        }
                    }
                }
            }
        }
    }

    protected function bootstrap()
    {
        if (defined('YII_DEBUG') && YII_DEBUG) {
            $this->fillMaps();
        } else {
            ClassMaps::loadMaps();
        }
        parent::bootstrap();
    }

}