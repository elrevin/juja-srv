<?php
namespace app\base;
use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface
{
    static protected $inSiteStructure = true;
    static protected $moduleTitle = '';
    static protected $siteSectionTypes = [];
    static public function getModuleTitle()
    {
        return (static::$moduleTitle ? static::$moduleTitle : static::className());
    }

    static public function getInSiteStructure()
    {
        return static::$inSiteStructure;
    }

    static public function getSiteSectionTypes ()
    {
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
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\\'.$this->id.'\commands';
            $this->consoleBootstrap($app);
        } else {
            $this->webBootstrap($app);
        }
    }
}