<?php

namespace app\modules\site\models\base;

use Yii;

class SiteStructure extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'title' => [
            'title' => 'Название раздела',
            'type' => 'string',
            'identify' => true,
        ],
        'text' => [
            'title' => 'Текст страницы раздела',
            'type' => 'html',
        ],
        'module' => [
            'title' => 'Модуль раздела',
            'type' => 'pointer',
            'relativeModel' => [
                'moduleName' => 'site',
                'name' => 'Modules',
            ],
        ],
        'template' => [
            'title' => 'Шаблон оформления',
            'type' => 'pointer',
            'relativeModel' => [
                'moduleName' => 'site',
                'name' => 'Templates',
            ],
            'showCondition' => [
                'module' => [
                    [
                        'operation' => 'set',
                    ]
                ],
            ],
            'filterCondition' => [
                'module' => [
                    'field' => 'module',
                    'comparison' => 'eq'
                ]
            ]
        ],
    ];

    public static $permanentlyDelete = false;

    protected static $hiddable = true;

    protected static $recursive = true;

    protected static $modelTitle = 'Структура сайта';

    protected static $recordTitle = 'Раздел';

    protected static $accusativeRecordTitle = 'Раздел';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site_structure';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => 'Поле "' . static::$structure['title']['title'] . '" обязательно для заполнения.'],
            [['title'], 'string', 'max' => 1024, 'tooLong' => 'Поле "' . static::$structure['title']['title'] . '" не может быть длинее 1024 символа.',],
        ];
    }
}
