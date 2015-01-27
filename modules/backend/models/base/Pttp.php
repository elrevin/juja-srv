<?php

namespace app\modules\backend\models\base;

use Yii;

class Pttp extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'required' => true
        ],
        'count' => [
            'title' => 'Количество',
            'type' => 'int',
            'settings' => [
                "min" => 0,
                "max" => 20000
            ],
            'required' => true,
        ],
        'point' => [
            'title' => 'Ссылка',
            'type' => 'pointer',
            'relativeModel' => '\app\modules\backend\models\TestTable',
            'required' => false,
            'showCondition' => [
                'count' => [
                    'operation' => '>',
                    'value' => 5
                ]
            ],
        ],
        'cool' => [
            'title' => 'Большой текст',
            'type' => 'text',
            'required' => false,
            'showCondition' => [
                'point' => [
                    [
                        'operation' => 'set'
                    ], [
                        'operation' => '==',
                        'value' => 'Супертест'
                    ]
                ]
            ],
        ],
    ];

    public static $permanentlyDelete = false;

    protected static $hiddable = true;

    protected static $modelTitle = 'Детализация тестовая';

    protected static $masterModel = '\app\modules\backend\models\PointTestTable';

    protected static $recordTitle = 'Штука такая';

    protected static $accusativeRecordTitle = 'Штуку такую';

    public static $sortable = false;

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
            [['title'], 'required', 'message' => 'Поле "' . static::$structure['title']['title'] . '" обязательно для заполнения.'],
            [['count'], 'required', 'message' => 'Поле "' . static::$structure['count']['title'] . '" обязательно для заполнения.'],
            [['point'], 'required', 'message' => 'Поле "' . static::$structure['point']['title'] . '" обязательно для заполнения.'],
            [['cool'], 'required', 'message' => 'Поле "' . static::$structure['cool']['title'] . '" обязательно для заполнения.'],
            [['title'], 'string', 'max' => 1024, 'tooLong' => 'Поле "' . static::$structure['title']['title'] . '" не может быть длинее 1024 символов.',],
            [['count'], 'number', 'integerOnly' => true, 'min' => 0, "tooSmall" => 'Поле "' . static::$structure['count']['title'] . '" не может принимать значения меньше 0'],
        ];
    }
}
