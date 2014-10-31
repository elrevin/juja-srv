<?php
/**
 * Переопределенный класс View.
 * Причиной переопределения был глюк с поиском файла представления с расширением не php в теме, я писал об этом:
 * http://yiiframework.ru/forum/viewtopic.php?f=27&t=18974
 *
 * К сожалению проблему не решили (по крайней мере в RC), по этому пришлось делать такой костыль, так же пришлось
 * переопределить Theme.
 *
 * Попутно немного упростил установку текущей темы, путем ввода двух методов:
 * setTheme($theme) - принимает описание темы (например, массив, как в конфиге), и устанавливает эту тему
 * setActiveTheme($themeName) - принимает имя темы, формирует конфиг и вызывает setTheme
 *
 */
namespace app\components;
use yii\web;
class View extends web\View
{
    public $themeName;

    public function init()
    {
        if ($this->themeName) {
            $this->setActiveTheme($this->themeName);
        }

        parent::init();
        /*
         * Костылим проблему с поиском файла представления
         */
        if ($this->theme) {
            $this->theme->view = $this;
        }
    }

    private function setTheme($theme)
    {
        if (!$this->theme) {
            if (is_array($theme)) {
                if (!isset($theme['class'])) {
                    $theme['class'] = 'yii\base\Theme';
                }
                if (!isset($theme['view'])) {
                    $theme['view'] = $this;
                }
                $this->theme = \Yii::createObject($theme);
            } elseif (is_string($theme)) {
                $this->theme = \Yii::createObject($theme);
            }
        }
    }

    public function setActiveTheme($themeName)
    {
        \Yii::setAlias('@theme', '@web/themes/'.$themeName);
        \Yii::setAlias('@themeroot', '@webroot/themes/'.$themeName);
        if ($this->renderers && isset($this->renderers['twig']) && $this->renderers['twig']) {
            if (is_array($this->renderers['twig'])) {
                $this->renderers['twig']['namespaces'] = [
                    '@webroot/themes/'.$themeName => 'themeroot',
                    '@webroot/themes/'.$themeName.'/views/layouts' => 'themelayouts'
                ];
            } elseif (is_object($this->renderers['twig']) && is_callable([$this->renderers['twig'], 'addPath'])) {
                $this->renderers['twig']->addPath('themeroot', '@webroot/themes/'.$themeName);
                $this->renderers['twig']->addPath('themelayouts', '@webroot/themes/'.$themeName.'/views/layout');
            }
        }
        $this->setTheme([
            'class' => 'app\components\Theme',
            'pathMap' => [
                '@app/views' => '@webroot/themes/'.$themeName.'/views',
                '@app/modules' => '@webroot/themes/'.$themeName.'/views'
            ],
            'baseUrl' => '@web/themes/'.$themeName,
            'basePath' => '@webroot/themes/'.$themeName
        ]);
    }
}