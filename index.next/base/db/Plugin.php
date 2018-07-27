<?php
namespace app\base\db;

use app\helpers\Utils;

class Plugin
{
    /**
     * Имя класса модели, для которой данный плагин
     * @var string
     */
    protected static $for = '';

    protected static $structure = [];

    protected static $detailModels = [];

    public static function getFor()
    {
        return static::$for;
    }

    public static function getStructure (string $fieldName = '')
    {
        if ($fieldName) {
            $field = null;
            if (isset(static::$structure[$fieldName])) {
                $field = static::$structure[$fieldName];
                if (is_string($field) && $field != 'delete') {
                    $field = [
                        "title" => $field,
                        "type" => "string",
                    ];
                }

                if (is_string($field) && $field == 'delete') {
                    return "delete";
                }

                $field['name'] = $fieldName;
                $field['fake'] = (isset($field['fake']) ? $field['fake'] : false);
                $field['calc'] = (isset($field['calc']) ? $field['calc'] : false);
                $field['addition'] = (isset($field['addition']) ? $field['addition'] : false);
                $field['additionTable'] = (isset($field['additionTable']) ? $field['additionTable'] : '');
                $field['expression'] = (isset($field['expression']) ? $field['expression'] : false);
                $field['selectOptions'] = (isset($field['selectOptions']) ? $field['selectOptions'] : []);
                $field['required'] = (isset($field['required']) ? $field['required'] : false);
                $field['autoNumber'] = (isset($field['autoNumber']) ? $field['autoNumber'] : false);
                $field['autoNumberReset'] = (isset($field['autoNumberReset']) ? $field['autoNumberReset'] : Utils::AUTONUMBER_RESET_NEVER);
                $field['nullAllow'] = (isset($field['nullAllow']) ? $field['nullAllow'] : false);
            }
            return $field;
        }
        $structure = [];
        foreach (static::$structure as $fieldName => $field) {
            $field = static::getStructure($fieldName);
            $structure[$fieldName] = $field;
        }
        return $structure;
    }

    public static function getDetailModels()
    {
        return static::$detailModels;
    }
}