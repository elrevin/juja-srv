<?php

namespace app\modules\backend\models\base;

use Yii;

class TestTable extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
            'required' => true,
        ],
        'text' => [
            'title' => 'Текст',
            'type' => 'text',
            'group' => 'Группа 1'
        ],
        'flag' => [
            'title' => 'Флаг',
            'type' => 'bool',
            'group' => 'Группа 1'
        ],
        'price' => [
            'title' => 'Цена',
            'type' => 'float',
            'settings' => [
                "round" => 2,
                "min" => 0,
                "max" => 20000
            ],
            'group' => 'Группа 1',
            'showCondition' => [
                'flag' => [
                    'operation' => 'set'
                ]
            ],
        ],
        'dt' => [
            'title' => 'Дата',
            'type' => 'date',
            'group' => 'Группа 2',
            'showCondition' => [
                'price' => [
                    'operation' => 'gt',
                    'value' => 300
                ]
            ],
        ],
        'dtt' => [
            'title' => 'Дата и время',
            'type' => 'datetime',
            'group' => 'Группа 2',
            'showCondition' => [
                'dt' => [
                    'operation' => 'lt',
                    'value' => '2015-01-31'
                ],'dt' => [
                    'operation' => 'set'
                ],
            ],
        ],
        'file' => [
            'title' => 'Файл',
            'type' => 'file',
            'group' => 'Группа 2',
            'settings' => [
                'types' => ['img', '.png', '.jpg']
            ],
            'showCondition' => [
                'dtt' => [
                    'operation' => 'lt',
                    'value' => '2015-01-31 10:00:00'
                ],'dtt' => [
                    'operation' => 'set'
                ],
            ],
        ],
    ];

    public static $permanentlyDelete = false;

    protected static $hiddable = true;

    protected static $modelTitle = 'Тестовый справочник';

    protected static $recordTitle = 'Какая-то хрень';

    protected static $accusativeRecordTitle = 'какую-то хрень';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'test_table';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => 'Поле "' . static::$structure['title']['title'] . '" обязательно для заполнения.'],
            [['price'], 'required', 'message' => 'Поле "' . static::$structure['price']['title'] . '" обязательно для заполнения.'],
            [['dt'], 'required', 'message' => 'Поле "' . static::$structure['dt']['title'] . '" обязательно для заполнения.'],
            [['text'], 'string', 'message' => 'В поле "' . static::$structure['text']['title'] . '" ожидается строка.'],
            [['title'], 'string', 'max' => 1024, 'tooLong' => 'Поле "' . static::$structure['title']['title'] . '" не может быть длинее 1024 символов.',],
            [['dt'], 'date', 'format' => 'php:Y-m-d', 'message' => 'Неверный формат даты в поле "' . static::$structure['dt']['title'] . '"'],
            [['dtt'], 'date', 'format' => 'php:Y-m-d H:i:s', 'message' => 'Неверный формат даты в поле "' . static::$structure['dtt']['title'] . '"'],
            [['price'], 'number', 'integerOnly' => false, 'min' => 0, 'max' => 20000, "tooBig" => 'Поле "' . static::$structure['price']['title'] . '" не может принимать значения больше 20000', "tooSmall" => 'Поле "' . static::$structure['price']['title'] . '" не может принимать значения меньше 0'],
            [['flag'], 'number', 'integerOnly' => true, 'min' => 0, 'max' => 1],
        ];
    }
}
