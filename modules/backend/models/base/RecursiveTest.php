<?php

namespace app\modules\backend\models\base;

use Yii;

class RecursiveTest extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
        ],
        'dt' => [
            'title' => 'Дата',
            'type' => 'date',
        ],
    ];

    public static $permanentlyDelete = false;

    protected static $hiddable = false;

    protected static $recursive = true;

    protected static $modelTitle = 'Рекурсивный справочник';

    protected static $recordTitle = 'Какая-то запись';

    protected static $accusativeRecordTitle = 'Какую-то запись';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'recursive_test';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => 'Поле "' . static::$structure['title']['title'] . '" обязательно для заполнения.'],
            [['title'], 'string', 'max' => 1024, 'tooLong' => 'Поле "' . static::$structure['title']['title'] . '" не может быть длинее 1024 символов.',],
        ];
    }
}
