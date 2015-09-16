<?php
namespace app\base\db;

use \Yii;

class AdditionsFieldsBehavior extends Behavior
{
    /**
     * Имя дополнительной модели
     * @var string
     */
    protected static $additionModel = '';

    /**
     * Дополнительные поля, описываются так же как и в классе ActiveRecord
     * @var array
     */
    protected static $fields = [];

    /**
     * Значения дополнительных полей
     * @var array
     */
    protected $values = [];

    protected static function createTableCol($fieldName, $field) {
        $tableName = call_user_func([static::$additionModel, 'tableName']);
        if ($field['type'] == 'string') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` VARCHAR(1024) NOT NULL DEFAULT ''
            ")->execute();
        } elseif ($field['type'] == 'tinystring') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` VARCHAR(256) NOT NULL DEFAULT ''
            ")->execute();
        } elseif ($field['type'] == 'text' || $field['type'] == 'html') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` LONGTEXT
            ")->execute();
        } elseif ($field['type'] == 'int') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` int(11) NOT NULL DEFAULT 0
            ")->execute();
        } elseif ($field['type'] == 'float') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` DOUBLE NOT NULL DEFAULT 0
            ")->execute();
        } elseif ($field['type'] == 'bool') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` tinyint(1) NOT NULL DEFAULT 0
            ")->execute();
        } elseif ($field['type'] == 'select') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` VARCHAR(256) DEFAULT NULL
            ")->execute();
        } elseif ($field['type'] == 'date') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` DATE DEFAULT NULL
            ")->execute();
        } elseif ($field['type'] == 'datetime') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` DATETIME DEFAULT NULL
            ")->execute();
        } elseif ($field['type'] == 'pointer') {
            if (is_array($field['relativeModel'])) {
                $relatedModelClass = '\app\modules\\'.$field['relativeModel']['moduleName'].'\models\\'.$field['relativeModel']['name'];
            } else {
                $relatedModelClass = $field['relativeModel'];
            }
            $tmp = call_user_func([$relatedModelClass, 'tableName']);
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` int(11) DEFAULT NULL,
                    ADD CONSTRAINT `". $tableName ."__".$fieldName."` FOREIGN KEY (`".$fieldName."`) REFERENCES `". $tmp ."`(id) ON DELETE SET NULL ON UPDATE CASCADE
            ")->execute();
        } elseif ($field['type'] == 'file') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` int(11) DEFAULT NULL,
                    ADD CONSTRAINT `". $tableName ."__".$fieldName."` FOREIGN KEY (`".$fieldName."`) REFERENCES `s_files`(id) ON DELETE SET NULL ON UPDATE CASCADE
            ")->execute();
        }
    }

    protected static function checkStructure () {
        if (YII_DEBUG) {
            // Проверяем наличие таблицы
            $tableName = call_user_func([static::$additionModel, 'tableName']);
            $table = Yii::$app->db->createCommand("Show tables like '". $tableName ."'")->queryAll();
            $cols = [];
            if (!$table) {
                // Создаем таблицу
                Yii::$app->db->createCommand("
                    CREATE TABLE `". $tableName ."` (
                        id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id),
                        master_table_id int(11) NOT NULL,
                        master_table_name varchar(1024) NOT NULL DEFAULT ''
                    )
                    ENGINE = INNODB
                    CHARACTER SET utf8
                    COLLATE utf8_general_ci
                ")->execute();
            } else {
                $tmp = Yii::$app->db->createCommand("SHOW COLUMNS FROM `". $tableName ."`")->queryAll();
                foreach ($tmp as $col) {
                    $cols[$col['Field']] = $col;
                }
            }
            // Проверяем структуру

            foreach (static::$fields as $name => $field) {
                if (!isset($field['calc']) && !array_key_exists($name, $cols)) {
                    static::createTableCol($name, $field);
                }
            }
        }
    }

    /**
     * Возвращает список дополнительных полей
     * @return array
     */
    public static function getAdditionFields()
    {
        static::checkStructure();
        foreach (static::$fields as $key => $val) {
            static::$fields[$key]['addition'] = true;
            static::$fields[$key]['additionTable'] = call_user_func([static::$additionModel, 'tableName']);
        }

        return static::$fields;
    }

    public function events()
    {
        return [
            // Внедряемся при инициализации и добавляем дополнительные поля
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'afterDelete',
        ];
    }

    public function canGetProperty($name, $checkVars = true)
    {
        if (array_key_exists($name, static::$fields)) {
            return true;
        }
        return parent::canGetProperty($name, $checkVars);
    }

    public function canSetProperty($name, $checkVars = true)
    {
        if (array_key_exists($name, static::$fields)) {
            return true;
        }
        return parent::canSetProperty($name, $checkVars);
    }

    public function afterInsert()
    {
        $id = $this->owner->id;
        // Добавляем дочернюю запись
        $model = new static::$additionModel();
        foreach ($this->values as $key => $value) {
            $model->{$key} = $value;
        }
        $model->master_table_id = $id;
        $model->master_table_name = call_user_func([$this->owner->className(), 'tableName']);
        $model->save(false);
    }

    public function afterUpdate()
    {
        $id = $this->owner->id;
        // Меняем дочернюю запись
        $model = call_user_func([static::$additionModel, 'find'])->where(['master_table_id' => $id, 'master_table_name' => call_user_func([$this->owner->className(), 'tableName'])])
            ->one();
        if (!$model) {
            $model = new static::$additionModel();
            $model->master_table_id = $id;
            $model->master_table_name = call_user_func([$this->owner->className(), 'tableName']);
        }
        foreach ($this->values as $key => $value) {
            $model->{$key} = $value;
        }
        $model->save(false);
    }

    public function afterDelete()
    {
        $id = $this->owner->id;
        $model = call_user_func([static::$additionModel, 'find'])->where(['master_table_id' => $id, 'master_table_name' => call_user_func([$this->owner->className(), 'tableName'])])
            ->one();
        if ($model) {
            $model->delete();
        }
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, static::$fields)) {
            $this->values[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function __get($name)
    {
        if (!$this->values) {
            $model = call_user_func([static::$additionModel, 'find'])->where(['master_table_id' => $this->owner->id, 'master_table_name' => call_user_func([$this->owner->className(), 'tableName'])])
                ->asArray()->one();
            if ($model) {
                $this->values = $model;
            }
        }
        if (array_key_exists($name, static::$fields)) {
            return (isset($this->values[$name]) ? $this->values[$name] : null);
        }
        return parent::__get($name);
    }

    public function __isset($name)
    {
        if (array_key_exists($name, static::$fields)) {
            return true;
        }
        return parent::__isset($name);
    }

    public function __unset($name)
    {
        if (array_key_exists($name, static::$fields)) {
            $this->values[$name] = null;
        } else {
            parent::__unset($name);
        }
    }
}