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
            'required' => true
        ],
        'text' => [
            'title' => 'Текст',
            'type' => 'text'
        ],
        'price' => [
            'title' => 'Цена',
            'type' => 'float',
            'settings' => [
                "round" => 2,
                "min" => 0,
                "max" => 20000
            ]
        ],
        'dt' => [
            'title' => 'Дата',
            'type' => 'date'
        ],
        'flag' => [
            'title' => 'Флаг',
            'type' => 'bool'
        ],
        'dtt' => [
            'title' => 'Дата и время',
            'type' => 'datetime'
        ],
    ];

    public static $permanentlyDelete = false;

    protected static $hiddable = true;

    protected static $modelTitle = 'Тестовый справочник';

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
            [['text'], 'string'],
            [['title'], 'string', 'max' => 1024],
            [['dt'], 'date', 'format' => 'Y-m-d'],
            [['dtt'], 'date', 'format' => 'Y-m-d H:i:s'],
            [['price'], 'number'],
            [['flag'], 'number', 'integerOnly' => true, 'min' => 0, 'max' => 1],
        ];
    }
}
