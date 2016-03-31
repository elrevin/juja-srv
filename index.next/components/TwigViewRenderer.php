<?php
/**
 * Свой рендерер, стандартный не позволяет добавлять алиасы путей.
 * Здесь же можно добавлять их либо в конфиг в виде массива в котором ключи - дирректории (можно использовать алиасы),
 * значения - алиасы (без символа "@")
 *
 * Можно добавить алиасы функцией addPathAlias
 */

namespace app\components;
use app\helpers\Utils;
use app\modules\backend\models\Constants;
use yii\helpers\Url;

class TwigViewRenderer extends \yii\twig\ViewRenderer
{
    public $namespaces = [];

    public static function assetBaseUrl($assetBundleName)
    {
        $assetBundleName = str_replace('/', '\\', $assetBundleName);
        return \Yii::$app->view->getAssetManager()->getBundle($assetBundleName)->baseUrl;
    }

    public static function getConstant($constantName)
    {
        return Constants::getConstantByName($constantName);
    }

    public static function getCookie($key)
    {
        $value = \Yii::$app->getRequest()->getCookies()->getValue($key);
        return $value ? $value : null;
    }

    public static function url($path, $args = [])
    {
        if ($args !== []) {
            $path = array_merge([$path], $args);
        }
        return preg_replace('|\%2F|i', '/', Url::to($path, true));
    }

    public static function getCsrfToken()
    {
        return \Yii::$app->request->getCsrfToken();
    }

    public static function getMonthName($m, $case = "base")
    {
        return Utils::getMonthName($m, $case);
    }

    public static function numToStr($num, $unit, $case = "base")
    {
        return Utils::numToStr($num, $unit, $case);
    }

    public static function getRuDate($date, $format = "d M Y", $case = "base")
    {
        return Utils::getRuDate($date, $format, $case);
    }

    public function init()
    {
        $this->functions['urlTo'] = '\yii\helpers\Url::to';
        $this->functions['url'] = '\app\components\TwigViewRenderer::url';
        $this->functions['assetBaseUrl'] = '\app\components\TwigViewRenderer::assetBaseUrl';
        $this->functions['getCookie'] = '\app\components\TwigViewRenderer::getCookie';
        $this->functions['getConstant'] = '\app\components\TwigViewRenderer::getConstant';
        $this->functions['getCsrfToken'] = '\app\components\TwigViewRenderer::getCsrfToken';
        $this->functions['getMonthName'] = '\app\components\TwigViewRenderer::getMonthName';
        $this->functions['numToStr'] = '\app\components\TwigViewRenderer::numToStr';
        $this->functions['getRuDate'] = '\app\components\TwigViewRenderer::getRuDate';
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