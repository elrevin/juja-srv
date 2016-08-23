<?php
namespace app\base\db\fields;

class Extended extends Simple
{
    /**
     * @var Simple
     */
    protected $parentField;
    public $type = 'extended';

    /**
     * @param Simple $parentField
     */
    public function setParentField($parentField)
    {
        $this->parentField = $parentField;
        $this->type = $parentField->type;
    }

    public function getSelect()
    {
        return $this->parentField->getSelect();
    }

    public function getWhere($operation, $value)
    {
        return $this->parentField->getWhere($operation, $value);
    }
}