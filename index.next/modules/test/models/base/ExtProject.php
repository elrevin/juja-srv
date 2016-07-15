<?php
namespace app\modules\test\models\base;
use app\base\db\ActiveRecord;

class ExtProject extends ActiveRecord
{
    static protected $structure = [
        'title' => [
            'type' => 'fromextended',
            'readonly' => true,
        ],
        'price' => [
            'title' => 'Цена',
            'type' => 'int',
        ],
    ]; 
    static protected $extendedModelName = '\app\modules\test\models\Project';
    
    // Отключаем перманетное удаление
    public static $permanentlyDelete = false;

    // Записи можно скрывать с сайта
    protected static $hiddable = true;

    // Название справочника
    protected static $modelTitle = 'Проекты расширенные';

    // Название записи справочика в единственном числе в иминительном падеже (отвечает на вопрос - "Что это?")
    protected static $recordTitle = 'Проект расширенный';

    // Название записи справочика в единственном числе в винительном падеже (отвечает на вопрос - "Создать что?")
    protected static $accusativeRecordTitle = 'проект расширенный';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'test_ext_projects';
    }
}