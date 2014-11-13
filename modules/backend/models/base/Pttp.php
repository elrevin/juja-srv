<?php

namespace app\modules\backend\models\base;

use Yii;

class Pttp extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
        ],
        'count' => [
            'title' => 'Количество',
            'type' => 'integer',
            'settings' => [
                "min" => 0,
                "max" => 20000
            ],
            'required' => true
        ],
     ];

    public static $permanentlyDelete = false;

    protected static $hiddable = false;

    protected static $modelTitle = 'Детализация тестовая';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pttp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => 'Поле "'.static::$structure['title']['title'].'" обязательно для заполнения.'],
            [['count'], 'required', 'message' => 'Поле "'.static::$structure['count']['title'].'" обязательно для заполнения.'],
            [['title'], 'string', 'max' => 1024, 'tooLong' => 'Поле "'.static::$structure['title']['title'].'" не может быть длинее 1024 символов.', ],
            [['count'], 'number', 'integerOnly' => true, 'min' => 0, "tooSmall" => 'Поле "'.static::$structure['count']['title'].'" не может принимать значения меньше 0'],
        ];
    }
}
