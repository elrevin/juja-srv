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
class ChildTable extends \app\base\db\ActiveRecord
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
        ],
    ];

    public static $permanentlyDelete = false;

    protected static $hiddable = false;

    protected static $modelTitle = 'Тестовая дочерняя модель';

    protected static $recordTitle = 'Дочерняя запись';

    protected static $accusativeRecordTitle = 'Дочернюю запись';

    public static $modalSelect = false;

    protected static $parentModel = '\app\modules\backend\models\MainTable';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'child_table';
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
