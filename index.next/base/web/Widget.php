<?php
namespace app\base\web;

use yii\base;

/**
 * Базовый класс виджета
 *
 * @property string $name - имя представления (шаблона)
 * @property string $module - модуль
 */
class Widget extends base\Widget
{
    public $name = '';
    public $module = '';

    public function init()
    {
        if (!$this->name) {
            $name = $this->className();
            $name = trim(str_replace('widgets', '', str_replace('app\modules\\', '', trim($name, '\\'))), '\\');
            $name = explode('\\', str_replace("\\\\", '\\', $name));
            $this->name = $name[1];
            $this->module = $name[0];
        }
        parent::init();
    }

    public function getViewPath()
    {
        return  \Yii::getAlias("@themeroot/views/".$this->module."/widgets/".(new \ReflectionClass($this))->getShortName());
    }
}