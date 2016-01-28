<?php
/**
 * Свой рендерер, стандартный не позволяет добавлять алиасы путей.
 * Здесь же можно добавлять их либо в конфиг в виде массива в котором ключи - дирректории (можно использовать алиасы),
 * значения - алиасы (без символа "@")
 *
 * Можно добавить алиасы функцией addPathAlias
 */

namespace app\components;
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
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }
        return null;
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
        $case = strtolower($case);

        switch ($case) {
            case 'base': $case = Morpher::CASE_BASE; break;
            case 'prepositional': $case = Morpher::CASE_PREPOSITIONAL; break;
            case 'instrumental': $case = Morpher::CASE_INSTRUMENTAL; break;
            case 'accusative': $case = Morpher::CASE_ACCUSATIVE; break;
            case 'dative': $case = Morpher::CASE_DATIVE; break;
            case 'genitive': $case = Morpher::CASE_GENITIVE; break;
        }

        $m = intval($m);
        switch ($m){
            case 1: $m='январь'; break;
            case 2: $m='февраль'; break;
            case 3: $m='март'; break;
            case 4: $m='апрель'; break;
            case 5: $m='май'; break;
            case 6: $m='июнь'; break;
            case 7: $m='июль'; break;
            case 8: $m='август'; break;
            case 9: $m='сентябрь'; break;
            case 10: $m='октябрь'; break;
            case 11: $m='ноябрь'; break;
            case 12: $m='декабрь'; break;
        }

        if ($case != Morpher::CASE_BASE) {
            /**
             * @var $morpher Morpher
             */
            $morpher = \Yii::$app->morpher;
            $m = $morpher->inflect($m, $case);
        }

        return $m;
    }

    public static function numToStr($num, $unit, $case = "base")
    {
        $case = strtolower($case);

        switch ($case) {
            case 'base': $case = Morpher::CASE_BASE; break;
            case 'prepositional': $case = Morpher::CASE_PREPOSITIONAL; break;
            case 'instrumental': $case = Morpher::CASE_INSTRUMENTAL; break;
            case 'accusative': $case = Morpher::CASE_ACCUSATIVE; break;
            case 'dative': $case = Morpher::CASE_DATIVE; break;
            case 'genitive': $case = Morpher::CASE_GENITIVE; break;
        }

        /**
         * @var $morpher Morpher
         */
        $morpher = \Yii::$app->morpher;
        $m = $morpher->getStrNum($num, $unit, $case);
        return $m;
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