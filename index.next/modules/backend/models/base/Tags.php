<?php

namespace app\modules\backend\models\base;

use Yii;

/**
 * @property integer $id
 * @property string $title
 */
class Tags extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
        ],
    ];

    public static $permanentlyDelete = true;

    protected static $hiddable = false;

    protected static $modelTitle = 'Теги';

    protected static $recordTitle = 'Тег';

    protected static $accusativeRecordTitle = 'Тег';

    public static $modalSelect = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tags';
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
