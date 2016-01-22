<?php
namespace app\base\components;

use app\base\db\ActiveRecord;
use app\base\Module;
use yii\base\Component;
use yii\base\Exception;

class PrintForm extends Component
{
    private $_view;
    private $_viewPath;

    protected static $title = '';
    protected static $format = 'html';
    protected static $model = '';
    protected $record = null;

    /**
     * @var Module
     */
    public $module = null;

    function __construct($module, $recordId, $config)
    {
        $this->module = $module;

        if (!$recordId) {
            throw new Exception("Record id expected");
        }

        /**
         * @var $modelClass ActiveRecord
         */
        $modelClass = '\app\modules\\'.$this->module->id.'\models\\'.static::$model;
        $this->record = $modelClass::find()->andWhere(['id' => $recordId])->one();

        if (!$this->record) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
            throw new \yii\web\HttpException(404, 'Not found');
        }

        parent::__construct($config);
    }

    /**
     * @param ActiveRecord $record
     */
    public function setRecord($record)
    {
        $this->record = $record;
    }

    public static function getTitle()
    {
        return static::$title;
    }

    public static function getFormat()
    {
        return static::$format;
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

    function printItem()
    {
        return '';
    }

}