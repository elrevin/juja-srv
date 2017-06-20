<?php
namespace app\modules\personaloffice\models\base;
use app\base\db\ActiveRecord;

class Clients extends ActiveRecord
{
    static protected $structure = [
        'name' => [
            'title' => '•	Полное название',
            'type' => 'fromextended',
            'identify' => true,
        ],
        'username' => [
            'title' => 'Логин',
            'type' => 'fromextended',
            'required' => true,
        ],
        'password' => [
            'title' => 'Пароль',
            'type' => 'fromextended',
            'showInGrid' => false
        ],
        'email' => [
            'title' => 'E-mail',
            'type' => 'fromextended',
        ],
    ];

    static protected $modelTitle = 'Клиенты';
    static protected $recordTitle = 'Клиент';
    static protected $accusativeRecordTitle = 'клиента';
    static protected $permanentlyDelete = false;
    static protected $extendedModelName = '\app\modules\usersmanager\models\Users';
    static protected $extendedModelRelFieldName = 'user';
    
    static function tableName()
    {
        return 'personaloffice_clients';
    }
}