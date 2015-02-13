<?php

namespace app\modules\backend\models\base;

use Yii;

class NewsTags extends \app\base\db\ActiveRecord
{
    protected static $structure = [
        'tag' => [
            'title' => 'Тег',
            'type' => 'pointer',
            'relativeModel' => [
                'moduleName' => 'backend',
                'name' => 'tags',
                'modalSelect' => true
            ]
        ],
    ];

    public static $permanentlyDelete = true;

    protected static $hiddable = false;

    protected static $modelTitle = 'Теги новости';

    protected static $recordTitle = 'Тег новости';

    protected static $masterModel = '\app\modules\backend\models\News';

    protected static $accusativeRecordTitle = 'Тег новости';

    protected static $linkModelName = '\app\modules\backend\models\Tags';

    public static $tabClassName = 'Many2ManyPanel';

    public static $typeGrid = 'checkbox';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag'], 'required']
        ];
    }
}
