<?php
namespace app\base;
use yii\base\BootstrapInterface;
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
     *      'multipleSections' => truw, // если true, то разделов данного типа на сайте может быть много
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
     *      'module' => '',
     *      'observeClass' => '',
     *      'event' => '',
     *      'subscriberClass' => '',
     *      'subscriberComponent' => '',
     *      'method' => '',
     * ]
     *
     *  где 'module' - имя модуля на события которого подписываемся, если события генерирует непосредвенно объект модуля
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
}