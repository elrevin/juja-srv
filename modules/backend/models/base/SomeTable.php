<?php

namespace app\modules\backend\models\base;

use Yii;

class SomeTable extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'required' => true
        ],
        'price' => [
            'title' => 'Цена',
            'type' => 'int',
            'settings' => [
                "min" => 0,
                "max" => 20000
            ],
            'required' => true
        ]
     ];

    public static $permanentlyDelete = false;

    protected static $hiddable = false;

    protected static $modelTitle = 'geg56666';

    protected static $masterModel = '\app\modules\backend\models\Goods';

    protected static $recordTitle = 'Штука такая';

    protected static $accusativeRecordTitle = 'Штуку такую';

    public static $sortable = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'some_table';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => 'Поле "'.static::$structure['title']['title'].'" обязательно для заполнения.'],
            [['price'], 'required', 'message' => 'Поле "'.static::$structure['price']['title'].'" обязательно для заполнения.'],
            [['title'], 'string', 'max' => 1024, 'tooLong' => 'Поле "'.static::$structure['title']['title'].'" не может быть длинее 1024 символов.', ],
            [['price'], 'number', 'integerOnly' => true, 'min' => 0, "tooSmall" => 'Поле "'.static::$structure['price']['title'].'" не может принимать значения меньше 0'],
        ];
    }
}
