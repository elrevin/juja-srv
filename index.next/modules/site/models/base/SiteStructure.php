<?php

namespace app\modules\site\models\base;

use Yii;

/**
 * Модель структура сайта
 * @package app\modules\site\models\base
 *
 * @property int id
 * @property int parent_id
 * @property int del
 * @property int hidden
 * @property string title
 * @property string text
 * @property string module
 * @property string template
 * @property string meta_title
 * @property string meta_description
 * @property string meta_keywords
 * @property string url
 */
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

    public static $behaviorsList = [
        '\app\modules\site\behaviors\MetaTagsAddFields'
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