<?php
namespace app\base\db\fields;

class Linked extends Simple
{
    /**
     * @var Simple
     */
    protected $linkedField;
    public $type = 'fromlinked';

    /**
     * @param Simple $linkedField
     */
    public function setLinkedField($linkedField)
    {
        $this->linkedField = $linkedField;
        $this->type = $linkedField->type;
    }

    public function getSelect()
    {
        return $this->linkedField->getSelect();
    }

    public function getWhere($operation, $value)
    {
        return $this->linkedField->getWhere($operation, $value);
    }
}