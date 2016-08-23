<?php
namespace app\base\db\fields;
class File extends Simple
{
    /**
     * Поле содержащее ID записи
     * @var null|Simple
     */
    public $idField = null;

    /**
     * Поле содержащее название файла
     * @var null|Simple
     */
    public $valueField = null;

    /**
     * Поле содержащее имя файла
     * @var null|Simple
     */
    public $fileField = null;

    public function getSelect()
    {
        $arr = $this->idField->getSelect();
        $arr = array_merge($arr, $this->valueField->getSelect());
        $arr = array_merge($arr, $this->fileField->getSelect());

        return $arr;
    }

    public function getWhere($operation, $value)
    {
        return $this->valueField->getWhere($operation, $value);
    }

}