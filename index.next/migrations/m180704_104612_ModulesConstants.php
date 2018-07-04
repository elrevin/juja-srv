<?php

use yii\db\Migration;

/**
 * Class m180704_104612_ModulesConstants
 */
class m180704_104612_ModulesConstants extends Migration
{

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $alias = \Yii::getAlias("@app/modules");
        $modules = scandir($alias);
        foreach ($modules as $module) {
            if ($module[0] == '.') {
                continue;
            }
            $filename = $alias . "/{$module}/constants.json";
            if (file_exists($filename)) {
                $constants = json_decode(file_get_contents($filename), true);
                foreach ($constants as $name => $constant) {
                    $columns = [
                        "module" => $module,
                        "name" => $name,
                        "title" => $constant["title"],
                        "type" => $constant["type"],
                    ];
                    if ($constant["type"] == "string") {
                        $columns["val_string"] = $constant["value"];
                    }

                    if ($constant["type"] == "int") {
                        $columns["val_int"] = $constant["value"];
                    }

                    if ($constant["type"] == "float") {
                        $columns["val_float"] = $constant["value"];
                    }

                    if ($constant["type"] == "text") {
                        $columns["val_text"] = $constant["value"];
                    }

                    if ($constant["type"] == "html") {
                        $columns["val_html"] = $constant["value"];
                    }

                    if ($constant["type"] == "date") {
                        $columns["val_date"] = $constant["value"];
                    }

                    if ($constant["type"] == "datetime") {
                        $columns["val_datetime"] = $constant["value"];
                    }

                    if ($constant["type"] == "select") {
                        $columns["val_select"] = $constant["value"];
                        $columns["select_options"] = json_encode($constant["options"]);
                    }

                    if ($constant["type"] == "pointer") {
                        $columns["val_pointer"] = $constant["value"];
                        $columns["related_model"] = $constant["model"];
                    }

                    $this->insert("s_constants", $columns);
                }
            }
        }
    }

    public function down()
    {
        echo "m180704_104612_ModulesConstants cannot be reverted.\n";

        return false;
    }

}
