<?php
namespace app\base\db\fields;

use app\base\db\ActiveQuery;
use app\base\db\ActiveRecord;
use yii\base\Object;
use yii\db\Expression;

class Simple extends Object
{
    /**
     * Тип поля
     * @var string 
     */
    public $type = '';

    /**
     * Алиас таблицы - источника данных
     * @var string
     */
    public $tableAlias = '';

    /**
     * Имя поля
     * @var string
     */
    public $name = '';

    /**
     * Алиас поля
     * @var string
     */
    public $alias = '';


    /**
     * @var string|ActiveRecord
     */
    public $modelClass = '';
    
    public $level = 0;

    public $expression = false;

    /**
     * @var null|ActiveQuery
     */
    public $query = null;
    
    public $params = [];

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function getSelect()
    {
        $value = ($this->expression ? "({$this->expression})" : "`{$this->tableAlias}`.`{$this->name}`");
        return [$this->alias => $value];
    }
    
    public function getWhere($operation, $value, $filterType = null)
    {
        $left = ($this->expression ? new Expression($this->expression) : "`{$this->tableAlias}`.`{$this->name}`");
        if (in_array($this->type, ['int', 'float', 'date', 'datetime'])) {
            if ($operation == '==' || $operation == 'eq') {
                return ['=', $left, $value];
            } elseif ($operation == '>' || $operation == 'gt') {
                return ['>', $left, $value];
            } elseif ($operation == '<' || $operation == 'lt') {
                return ['<', $left, $value];
            } elseif ($operation == 'noteq' || $operation == '!=') {
                return ['<>', $left, $value];
            }
        } elseif (in_array($this->type, ['string', 'tinystring', 'text', 'html', 'code'])) {
            if ($operation == 'start') {
                return ['like', $left, $value."%", false];
            } elseif ($operation == 'end') {
                return ['like', $left, "%".$value, false];
            } elseif ($operation == 'eq' || $operation == '==') {
                return ['=', $left, $value];
            } else {
                return ['like', $left, $value];
            }
        }
        return [];
    }
    
    function getListVal ($row)
    {
        return $row[$this->alias];
    }
}