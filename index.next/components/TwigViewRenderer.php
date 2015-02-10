<?php
/**
 * Свой рендерер, стандартный не позволяет добавлять алиасы путей.
 * Здесь же можно добавлять их либо в конфиг в виде массива в котором ключи - дирректории (можно использовать алиасы),
 * значения - алиасы (без символа "@")
 *
 * Можно добавить алиасы функцией addPathAlias
 */

namespace app\components;
use yii\helpers\Url;

class TwigViewRenderer extends \yii\twig\ViewRenderer
{
    public $namespaces = [];

    public static function assetBaseUrl($assetBundleName)
    {
        $assetBundleName = str_replace('/', '\\', $assetBundleName);
        return \Yii::$app->view->getAssetManager()->getBundle($assetBundleName)->baseUrl;
    }

    public function init()
    {
        $this->functions['urlTo'] = '\yii\helpers\Url::to';
        $this->functions['assetBaseUrl'] = '\app\components\TwigViewRenderer::assetBaseUrl';
        parent::init();
    }

    public function render($view, $file, $params)
    {
        $this->twig->addGlobal('this', $view);
        $loader = new \Twig_Loader_Filesystem(dirname($file));
        $this->addAliases($loader, \Yii::$aliases);
        $this->twig->setLoader($loader);

        foreach ($this->namespaces as $dir => $namespace) {
            if ($path = \Yii::getAlias($dir, false)) {
                $loader->addPath($path, $namespace);
            }
        }

        return $this->twig->render(pathinfo($file, PATHINFO_BASENAME), $params);
    }

    /**
     * Добавление алиаса пути
     * @param $alias
     * @param $dir
     */
    public function addPathAlias($alias, $dir)
    {
        $this->namespaces[$dir] = $alias;
    }
}