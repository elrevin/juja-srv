<?php

namespace app\modules\files\models\base;

use Yii;

class Files extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
        ],
        'original_name' => [
            'title' => 'Имя файла',
            'type' => 'string'
        ],
        'name' => [
            'title' => 'Хэш',
            'type' => 'string'
        ]
    ];

    public static $permanentlyDelete = true;

    protected static $hiddable = false;

    protected static $modelTitle = 'Файлы и изображения';

    protected static $recordTitle = 'Файл';

    protected static $accusativeRecordTitle = 'Файл';

    public static $modalSelect = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_files';
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
