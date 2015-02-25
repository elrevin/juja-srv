<?php
namespace app\modules\backend\behaviors;

class TestAddFields extends \app\base\db\AdditionsFieldsBehavior
{
    protected static $additionModel = '\app\modules\backend\models\AddTable';

    protected static $fields = [
        'add_title' => [
            'title' => 'Тестовое дополнительное поле',
            'type' => 'string',
        ],
    ];
}