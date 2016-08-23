<?php
namespace app\base\db\fields;

use yii\helpers\Json;

class Pointer extends Simple
{
    /**
     * Поле содержащее ID записи
     * @var null|Simple
     */
    public $idField = null;

    /**
     * Поле содержащее значение записи
     * @var null|Simple|Pointer
     */
    public $valueField = null;

    public function getValueField()
    {
        if ($this->valueField->type == 'pointer') {
            return $this->valueField->getValueField();
        }

        return $this->valueField;
    }

    public function getSelect()
    {
        $arr = $this->idField->getSelect();
        $arr = array_merge($arr, $this->getValueField()->getSelect());

        return $arr;
    }

    public function getWhere($operation, $value, $filterType = null)
    {
        if ($filterType == 'numeric') {
            // Сравниваем id
            return $this->idField->getWhere($operation, $value);
        }
        return $this->getValueField()->getWhere($operation, $value);
    }

    function getListVal ($row)
    {
        return Json::encode([
            'id' => $row[$this->alias],
            'value' => $row['valof_'.$this->alias]
        ]);
    }
}