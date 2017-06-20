<?php
namespace app\base;
use app\base\db\ActiveRecord;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\Event;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module implements BootstrapInterface
{
    static protected $inSiteStructure = true;
    static protected $moduleTitle = '';

    /**
     * если true, то разделов данного модуля на сайте может быть много (теряет актуальность, если описаны типы разделов)
     * @var bool
     */
    static protected $multipleSections = false;

    /**
     * Описание типов разделов сайта для модуля, если их может быть несколько. Каждый раздел описывается структурой вида:
     * 'sectionTypeName' => [
     *      'title' => 'Название типа раздела',
     *      'multipleSections' => true, // если true, то разделов данного типа на сайте может быть много
     * ]
     *
     * но можно и так:
     *
     * 'sectionTypeName' => 'Название типа раздела' - в таком случае multipleSections = false
     *
     * @var array
     */
    static protected $siteSectionTypes = [];

    /**
     * Здесь можно описать обработчики событий, каждый элемент массива будет иметь структуру:
     *
     * [
     *      'group' => ''
     *      'module' => '',
     *      'observeClass' => '',
     *      'event' => '',
     *      'subscriberClass' => '',
     *      'subscriberComponent' => '',
     *      'method' => '',
     * ]
     *
     *  где group - группа модулей на которую подписываемся
     *      'module' - имя модуля на события которого подписываемся, если события генерирует непосредвенно объект модуля
     *      'observeClass' - полное имя класса (включая namespace) на события которого подписываемся (если определен observeClass,
     *          'module' игнорируется)
     *      'event' - событие
     *      'subscriberClass' - класс подписанный на событие
     *      'subscriberComponent' - имя компонента модуля, который будет подписан на событие, если subscriberClass и subscriberComponent пустые,
     *           то подписывается объект модуля
     *      'method' - имя метода обработчика
     *
     * @var array
     */
    protected $eventSubscribe = [];

    /**
     * Обработчики сообщений.
     * В любом месте можно послать сообщение всем модулям, сообщение обязательно содержит
     * имя, и если будет найден модуль, который сможет обработать это сообщение, упраление будет
     * передано соотвествующиму его обработчику
     *
     * описывается в следующем формате:
     *
     * [
     *      'name' => [
     *          'class' => 'КЛАСС ОБРАБОТЧИК',
     *          'component' => 'КОМПОНЕНТ ОБРАБОТЧИК',
     *          'method' => 'МЕТОД'
     *      ]
     * ]
     *
     * или
     *
     * [
     *      'name' => МЕТОД
     * ]
     *
     * @var array
     */
    protected $messageReceivers = [];

    public $extensions = [];
    protected $_extensions = [];

    protected static $models = [];

    static public function getModuleTitle()
    {
        return (static::$moduleTitle ? static::$moduleTitle : static::className());
    }

    static public function getInSiteStructure()
    {
        return static::$inSiteStructure;
    }

    static public function getMultipleSections()
    {
        return static::$multipleSections;
    }

    static public function getSiteSectionTypes ()
    {
        foreach (static::$siteSectionTypes as $key => $type) {
            if (is_string($type)) {
                static::$siteSectionTypes[$key] = [
                    'title' => $type,
                    'multipleSections' => false,
                ];
            } elseif (is_array($type)) {
                static::$siteSectionTypes[$key] = [
                    'title' => (isset($type['title']) ? $type['title'] : ''),
                    'multipleSections' => (isset($type['multipleSections']) ? $type['multipleSections'] : false),
                ];
            }
        }

        return static::$siteSectionTypes;
    }

    public function init()
    {
        parent::init();
    }

    /**
     * @param \yii\base\Application $app
     */
    public function webBootstrap($app)
    {

    }

    /**
     * @param \yii\base\Application $app
     */
    public function consoleBootstrap($app)
    {

    }

    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        $this->setComponents($this->components);

        /**
         * @var string  $name
         * @var Component $component
         */
        foreach ($this->components as $name => $component) {
            $component = $this->{$name};
            if ($component->hasProperty('module')) {
                $component->module = $this;
            }
        }

        $this->doEventSubscribe();
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\\'.$this->id.'\commands';
            $this->consoleBootstrap($app);
        } else {
            $this->webBootstrap($app);
        }
    }

    private function doEventSubscribe ()
    {
        if ($this->eventSubscribe && ArrayHelper::isAssociative($this->eventSubscribe)) {
            $this->eventSubscribe = [ $this->eventSubscribe ];
        }

        foreach ($this->eventSubscribe as $item) {
            if (isset($item['group']) && $item['group']) {
                continue;
            }
            if (isset($item['observeClass'])) {
                $observeClass = trim($item['observeClass'], '\\');
            } elseif (isset($item['module'])) {
                $observeClass = 'app\modules\\'.$item['module'].'\Module';
            } else {
                throw new Exception("Observable class not found");
            }

            $event = $item['event'];

            if (isset($item['subscriberClass'])) {
                $subscriberClass = trim($item['subscriberClass'], '\\');
            } elseif (isset($item['subscriberComponent'])) {
                $subscriberClass = $this->{$item['subscriberComponent']};
            } else {
                $subscriberClass = $this;
            }

            $method = $item['method'];

            if (!$method) {
                throw new Exception("Subscriber method not found");
            }

            if (!method_exists($subscriberClass, $method)) {
                throw new Exception("Subscriber method not exists");
            }

            Event::on($observeClass, $event, [$subscriberClass, $method]);
        }
    }

    public function onGroupEvent($group, $eventName, $event)
    {
        foreach ($this->eventSubscribe as $item) {
            if (isset($item['group']) && $item['group'] == $group) {
                if ($item['event'] == $eventName) {
                    if (isset($item['subscriberClass'])) {
                        $subscriberClass = trim($item['subscriberClass'], '\\');
                    } elseif (isset($item['subscriberComponent'])) {
                        $subscriberClass = $this->{$item['subscriberComponent']};
                    } else {
                        $subscriberClass = $this;
                    }

                    $method = $item['method'];

                    if (!$method) {
                        throw new Exception("Subscriber method not found");
                    }

                    if (!method_exists($subscriberClass, $method)) {
                        throw new Exception("Subscriber method not exists");
                    }

                    call_user_func([$subscriberClass, $method], $event);
                }
            }
        }
    }

    public function onMessage($name, &$result, $args) {
        foreach ($this->messageReceivers as $key => $messageReceiver) {
            if ($key == $name) {
                if (is_callable($messageReceiver)) {
                    $result = $messageReceiver($args);
                    return true;
                } else {
                    if (!empty($messageReceiver['class'])) {
                        $class = trim($messageReceiver['class'], '\\');
                    } elseif (!empty($messageReceiver['component'])) {
                        $class = $this->{$messageReceiver['component']};
                    } else {
                        $class = $this;
                    }

                    $method = $messageReceiver['method'];

                    if (!$method) {
                        throw new Exception("Subscriber method not found");
                    }

                    if (!method_exists($class, $method)) {
                        throw new Exception("Subscriber method not exists");
                    }

                    $result = call_user_func([$class, $method], $name, $args);
                    return true;
                }
            }
        }

        return false;
    }

    public function getModels()
    {
        $thisModuleName = explode('\\', static::className());
        $id = $thisModuleName[count($thisModuleName) - 2];

        if(!empty(self::$models[$id])) return self::$models[$id];
        $folder = \Yii::$app->basePath.'/modules/'.$id.'/models/';
        if(is_dir($folder)){
            foreach(glob($folder.'*.php') as $model){
                /** @var  ActiveRecord $modelClass */
                $model = basename($model, '.php');
                $modelClass = '\app\modules\\'.$id.'\models\\'.$model;
                if(method_exists($modelClass, 'getModelTitle'))
                    self::$models[$id][] = $modelClass;
            }
        }

        return self::$models[$id];
    }
}