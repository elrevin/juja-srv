<?php

namespace app\modules\backend\models\base;

use Yii;

/**
 * Модель для таблицы "point_test_table", справочник .
 *
 * @property integer $id
 * @property integer $del
 * @property string $title
 * @property float $price
 */
class PointTestTable extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
        ],
        'price' => [
            'title' => 'Цена',
            'type' => 'float',
            'settings' => [
                "round" => 2,
                "min" => 0,
                "max" => 20000
            ],
            'required' => true,
            'group' => 'Группа полей'
        ],
        'test_table_id' => [
            'title' => 'Указатель',
            'type' => 'pointer',
            'relativeModel' => [
                'moduleName' => 'backend',
                'name' => 'TestTable',
                'modalSelect' => true,
            ],//'\app\modules\backend\models\TestTable',
            'group' => 'Группа полей'
        ],
        'calc_test' => [
            'title' => 'НДС',
            'type' => 'float',
            'calc' => true,
            'expression' => '`point_test_table`.`price` * 0.18',
        ],
        'html_text' => [
            'title' => 'Какой-то текст HTML',
            'type' => 'html',
        ],
        'select_field' => [
            'title' => 'Тестовое поле Select',
            'type' => 'select',
            'selectOptions' => [
                'option1' => 'Опция первая',
                'option2' => 'Опция вторая',
                'option3' => 'Опция третья',
            ]
        ],
    ];

    public static $permanentlyDelete = false;

    protected static $hiddable = false;

    protected static $modelTitle = 'Еще один тестовый справочник';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'point_test_table';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => 'Поле "' . static::$structure['title']['title'] . '" обязательно для заполнения.'],
            [['title'], 'string', 'max' => 1024, 'tooLong' => 'Поле "' . static::$structure['title']['title'] . '" не может быть длинее 1024 символов.',],
            [['price'], 'number', 'integerOnly' => false, 'min' => 0, 'max' => 20000, "tooBig" => 'Поле "' . static::$structure['price']['title'] . '" не может принимать значения больше 20000', "tooSmall" => 'Поле "' . static::$structure['price']['title'] . '" не может принимать значения меньше 0'],
        ];
    }
}