<?php

namespace app\modules\backend\models\base;

use Yii;

/**
 * Модель для таблицы "test_table", справочник .
 *
 * @property integer $id
 * @property integer $del
 * @property string $title
 * @property int $flag
 */
class Goods extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
        ],
        'flag' => [
            'title' => 'Флаг',
            'type' => 'bool',
            'group' => 'Группа 1'
        ]
    ];

    public static $permanentlyDelete = false;

    protected static $hiddable = false;

    protected static $modelTitle = 'Товары';

    protected static $recordTitle = 'Товар';

    protected static $accusativeRecordTitle = 'Товар';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => 'Поле "' . static::$structure['title']['title'] . '" обязательно для заполнения.'],
        ];
    }
}
