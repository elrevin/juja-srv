<?php
namespace app\base\components;

use app\base\db\ActiveRecord;
use app\base\Module;
use yii\base\Component;
use yii\base\Exception;
use yii\base\ViewContextInterface;
use yii\helpers\Json;

class PrintForm extends Component implements ViewContextInterface
{
    private $_view;
    private $_viewPath;

    protected static $title = '';
    protected static $format = 'html';
    protected static $model = '';
    protected $record = null;

    protected static $form = [];
    protected $id;
    public $layout = false;

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

        $className = static::className();
        $className = explode('\\', $className);
        $className = $className[count($className) - 1];

        $this->id =$className;

        \Yii::$app->view->setActiveTheme(\Yii::$app->params['themeName']);

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
            $this->_viewPath = $this->module->getBasePath() . DIRECTORY_SEPARATOR . "print" . DIRECTORY_SEPARATOR .$this->id;
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

    public static function getForm()
    {
        return static::$form;
    }

    public static function getModel()
    {
        return static::$model;
    }

    function getUserInterface()
    {
        $fields = [];

        foreach (static::$form as $fieldName => $config) {

            $relativeModel = '';

            if ($config['type'] == 'pointer') {
                // Для полей типа pointer получаем конфигурацию связанной модели

                if (is_array($config['relativeModel'])) {
                    $relativeModel = $config['relativeModel'];
                    if (!isset($relativeModel['classname'])) {
                        if (!isset($relativeModel['moduleName']) || !isset($relativeModel['name'])) {
                            return false;
                        } else {
                            $relativeModel['classname'] = '\app\modules\\'.$relativeModel['moduleName'].'\models\\'.$relativeModel['name'];
                        }
                    }
                } else {
                    $relativeModel['classname'] = $config['relativeModel'];
                }
                $relativeModelPath = '';
                if (!isset($relativeModel['moduleName']) || !isset($relativeModel['name'])) {
                    $relativeModelFullName = $relativeModel['classname'];
                    $relativeModelPath = str_replace('\app\modules\\', '', str_replace('\models', '', $relativeModelFullName));
                    $relativeModelPath = explode('\\', $relativeModelPath);
                }

                if (!isset($relativeModel['moduleName'])) {
                    $relativeModel['moduleName'] = $relativeModelPath[0];
                }

                if (!isset($relativeModel['name'])) {
                    $relativeModel['name'] = $relativeModelPath[1];
                }

                $relativeModelIdentifyFieldConf = call_user_func([$relativeModel['classname'], 'getIdentifyFieldConf']);

                $relativeModel['identifyFieldName'] = $relativeModelIdentifyFieldConf['name'];
                $relativeModel['identifyFieldType'] = $relativeModelIdentifyFieldConf['type'];

                if (!isset($relativeModel['runAction'])) {
                    $relativeModel['runAction'] = [
                        $relativeModel['moduleName'],
                        'main',
                        'get-interface'
                    ];
                }

                if (!isset($relativeModel['modalSelect'])) {
                    $relativeModel['modalSelect'] = call_user_func([$relativeModel['classname'], 'getModalSelect']);
                }
            } elseif ($config['type'] == 'img' || $config['type'] == 'file') {
                $relativeModel['classname'] = '\app\modules\files\models\Files';
                $relativeModel['moduleName'] = 'files';
                $relativeModel['name'] = 'Files';
                $relativeModelIdentifyFieldConf = call_user_func([$relativeModel['classname'], 'getIdentifyFieldConf']);
                $relativeModel['identifyFieldName'] = $relativeModelIdentifyFieldConf['name'];
                $relativeModel['identifyFieldType'] = $relativeModelIdentifyFieldConf['type'];
                $relativeModel['runAction'] = [
                    $relativeModel['moduleName'],
                    'main',
                    'get-interface'
                ];
                $relativeModel['modalSelect'] = call_user_func([$relativeModel['classname'], 'getModalSelect']);
            }

            $config['name'] = $fieldName;

            if ($relativeModel) {
                // есть связанная модель, добавляем ее конфигурацию в конфигурацию поля
                $config['relativeModel'] = [
                    'moduleName' => $relativeModel['moduleName'],
                    'name' => $relativeModel['name'],
                    'identifyFieldName' => $relativeModel['identifyFieldName'],
                    'identifyFieldType' => $relativeModel['identifyFieldType'],
                    'modalSelect' => $relativeModel['modalSelect'],
                    'runAction' => $relativeModel['runAction']
                ];
            }
            $fields[] = $config;
        }

        $className = trim(static::className(), '\\');
        $className = explode('\\', $className);

        $moduleName =$className[2];
        $className = $className[4];

        $conf = [
            "fields" => $fields,
            "title" => static::$title,
            "recordId" => $this->record->id,
            "modelName" => static::$model,
            "format" => static::$format,
            "form" => $className,
            'printItemAction' => [$moduleName, 'main', 'print-item'],
        ];

        $jsFile = \Yii::getAlias("@app/modules/{$moduleName}/js/printforms/{$className}.js");
        if (file_exists($jsFile)) {
            return ("
                  var module = Ext.create('App.modules.{$moduleName}.printforms.{$className}', ". Json::encode($conf).");
                ");
        }
        return ("
                  var module = Ext.create('App.core.PrintFormWindow', ". Json::encode($conf).");
                ");
    }

    /**
     * @param array $options
     * @return string
     */
    function printItem($options = [])
    {
        return '';
    }

}