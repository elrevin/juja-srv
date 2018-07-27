<?php

use app\base\db\ActiveRecord;
use yii\db\Migration;

/**
 * Class m180709_060059_createAllTables
 */
class m180709_060059_createAllTables extends Migration
{
    private function processModule($module)
    {
        $models = scandir(\Yii::getAlias("@app/modules/{$module}/models/"));
        foreach ($models as $model) {
            if ($model == '..' || $model == '.' || is_dir(\Yii::getAlias("@app/modules/{$module}/models/") . $model)) {
                continue;
            }
            /** @var ActiveRecord $modelClass */
            $modelClass = '\app\modules\\' . $module . '\models\\' . str_replace(".php", "", $model);

            if (is_callable([$modelClass, "checkStructure"])) {
                $modelClass::checkStructure();
            }
        }
    }

    public function up()
    {
        $modules = scandir(\Yii::getAlias("@app/modules/"));
        foreach ($modules as $module) {
            if ($module == '..' || $module == '.' || !is_dir(\Yii::getAlias("@app/modules/") . $module)) {
                continue;
            }

            $this->processModule($module);
        }
    }

    public function down()
    {
        echo "m180709_060059_createAllTables cannot be reverted.\n";

        return false;
    }
}
