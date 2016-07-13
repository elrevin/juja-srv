<?php
namespace app\modules\test\models\base;
use app\base\db\ActiveRecord;

class Project extends ActiveRecord
{
    static protected $structure = [
        'title' => [
            'title' => 'Название',
            'type' => 'string',
            'identify' => true,
        ],
    ];

    // Отключаем перманетное удаление
    public static $permanentlyDelete = false;

    // Записи можно скрывать с сайта
    protected static $hiddable = true;

    // Название справочника
    protected static $modelTitle = 'Проекты';

    // Название записи справочика в единственном числе в иминительном падеже (отвечает на вопрос - "Что это?")
    protected static $recordTitle = 'Проект';

    // Название записи справочика в единственном числе в винительном падеже (отвечает на вопрос - "Создать что?")
    protected static $accusativeRecordTitle = 'проект';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'test_projects';
    }
}