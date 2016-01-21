<?php
namespace app\base\components;

use app\base\Module;
use yii\base\Component;

class PrintForm extends Component
{
    private $_view;
    private $_viewPath;

    /**
     * @var array|Module
     */
    public $module = null;

    function __construct($module, $config)
    {
        $this->module = $module;

        parent::__construct($config);
    }

    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->_viewPath = $this->module->getViewPath() . DIRECTORY_SEPARATOR . "print" . DIRECTORY_SEPARATOR .$this->id;
        }
        return $this->_viewPath;
    }

    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = \Yii::$app->getView();
        }
        return $this->_view;
    }

    public function render($view, $params = [])
    {
        return $this->getView()->render($view, $params, $this);
    }

    function doPrint()
    {

    }

}