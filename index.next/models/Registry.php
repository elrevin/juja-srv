<?php
namespace app\models;
use yii\db\ActiveRecord;

/**
 * Class Registry
 * @package app\models
 *
 * @property string $module
 * @property string $key
 * @property string $val
 * @property string $date
 */
class Registry extends ActiveRecord
{
    public static function tableName()
    {
        return "s_registry";
    }
}