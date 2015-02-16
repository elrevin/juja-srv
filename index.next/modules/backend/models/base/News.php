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
class News extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
        ],
        'anons' => [
            'title' => 'Анонс',
            'type' => 'string',
        ],
        'content' => [
            'title' => 'Текст',
            'type' => 'html',
        ],
        'publish_date' => [
            'title' => 'Дата публикации',
            'type' => 'datetime'
        ],
        'module' => [
            'title' => 'Модуль',
            'type' => 'pointer',
            'relativeModel' => [
                'moduleName' => 'backend',
                'name' => 'tags',
                'modalSelect' => false
            ]
        ],
        'template' => [
            'title' => 'Шаблон',
            'type' => 'pointer',
            'relativeModel' => [
                'moduleName' => 'backend',
                'name' => 'goods',
                'modalSelect' => false
            ],
            'showCondition' => [
                'module' => [
                    [
                        'operation' => 'set',
                    ]
                ],
            ],
            'filterCondition' => [
                'module' => 'eq',
                'type' => 'numeric'
            ]
        ]
    ];

    public static $permanentlyDelete = true;

    protected static $hiddable = false;

    protected static $modelTitle = 'Новости';

    protected static $recordTitle = 'Новость';

    protected static $accusativeRecordTitle = 'Новость';

    public static $modalSelect = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news';
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
