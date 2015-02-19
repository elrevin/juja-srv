<?php

namespace app\modules\backend\models\base;

use Yii;

/**
 * @property integer $id
 * @property integer $del
 * @property integer $hidden
 * @property string $title
 * @property string $anons
 * @property string $content
 * @property string $publish_date
 */
class MainTable extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
        ],
        'text' => [
            'title' => 'Текст',
            'type' => 'html',
        ],
    ];

    public static $permanentlyDelete = false;

    protected static $hiddable = false;

    protected static $modelTitle = 'Тестовая главная модель';

    protected static $recordTitle = 'Главная запись';

    protected static $accusativeRecordTitle = 'Главную запись';

    public static $modalSelect = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'main_table';
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
