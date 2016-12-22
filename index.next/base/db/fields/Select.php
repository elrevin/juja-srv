<?php
namespace app\base\db\fields;
use yii\db\Expression;
use yii\helpers\Json;

class Select extends Simple
{
    public $options = [];
    public $type = 'select';
    public function getSelect()
    {
        if ($this->expression) {
            $expression = "({$this->expression})";
        } else {
            $expression = "`{$this->tableAlias}`.`{$this->name}`";
        }

        $options = [];
        $keyIndex = 1;
        $fieldAlias = $this->alias;
        foreach ($this->options as $key => $value) {
            $options[] = "WHEN :option{$keyIndex}_{$fieldAlias}_key THEN :option{$keyIndex}_{$fieldAlias}_value";
            $this->params[":option{$keyIndex}_{$fieldAlias}_key"] = $key;
            $this->params[":option{$keyIndex}_{$fieldAlias}_value"] = $value;
            $keyIndex++;
        }

        if ($options) {
            $value = "(CASE {$expression} ".implode(' ', $options)." END)";
        } else {
            $value = new Expression("''");
        }
        return ["valof_{$this->alias}" => $value, $this->alias => $expression];
    }
    
    function getListVal($row)
    {
        return Json::encode([
            'id' => $row[$this->alias],
            'value' => $row['valof_'.$this->alias],
        ]);
    }

    public function getWhere($operation, $value, $filterType = null)
    {
        $left = ($this->expression ? new Expression($this->expression) : "`{$this->tableAlias}`.`{$this->name}`");
        if ($operation == '==' || $operation == 'eq') {
            return ['=', $left, $value];
        } elseif ($operation == 'noteq' || $operation == '!=') {
            return ['<>', $left, $value];
        }
        return [];
    }

}