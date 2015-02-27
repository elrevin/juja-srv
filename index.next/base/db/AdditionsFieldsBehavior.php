<?php
namespace app\base\db;

use yii\base\Behavior;

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

    /**
     * Возвращает список дополнительных полей
     * @return array
     */
    public static function getAdditionFields()
    {
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