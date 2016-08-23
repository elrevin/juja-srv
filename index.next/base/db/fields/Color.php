<?php
namespace app\base\db\fields;
class Color extends Simple
{
    public $type = 'color';

    public function getSelect()
    {
        $value = ($this->expression ? "({$this->expression})" : "`{$this->tableAlias}`.`{$this->name}`");
        return [$this->alias => $value];
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

    function getListVal ($row)
    {
        if ($row[$this->alias] && strpos($row[$this->alias], '#') === false) {
            $some = explode(',', $row[$this->alias]);
            return "#".str_pad(dechex(intval($some[0])), 2, STR_PAD_LEFT).str_pad(dechex(intval($some[1])), 2, STR_PAD_LEFT).str_pad(dechex(intval($some[2])), 2, STR_PAD_LEFT);
        }
        
        return $row[$this->alias];
    }
}