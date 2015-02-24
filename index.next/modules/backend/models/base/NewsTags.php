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

    public static $masterModelRelationsType = self::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY;

    public static $slaveModelAddMethod = self::SLAVE_MODEL_ADD_METHOD_CHECK;

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
