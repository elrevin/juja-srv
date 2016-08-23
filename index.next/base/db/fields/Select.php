<?php
namespace app\base\db\fields;
use yii\db\Expression;

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
        
        $value = "(CASE {$expression} ".implode(' ', $options)." END)";
        return ["valof_{$this->alias}" => $value, $this->alias => $expression];
    }
}