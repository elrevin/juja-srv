<?php
/*
 * Переопределенный UrlManager, в нем отрабатываем слеши в конце Url
 */
namespace app\components;

class UrlManager extends \yii\web\UrlManager
{
    /**
     * @var UrlRule
     */
    public $currentUrlRule = null;
    
    public function init()
    {
        // Загружаем правила

        foreach (\Yii::$app->modules as $name => $moduleConf) {
            $file = \Yii::getAlias("@app/modules/{$name}/urlRules.php");
            if (is_file($file)) {
                $rules = require_once($file);
                foreach ($rules as $key => $item) {
                    $rules[$key]['class'] = '\app\components\UrlRule';
                }
                $this->addRules($rules);
            }
        }


        parent::init();
    }

    /**
     * @param \yii\web\Request $request
     * @return array|bool|void
     */
    public function parseRequest($request)
    {
        /*
         * Если в конце URL нет слеша, добавляем его и выполняем 301 редирект
         */
        $url = $request->getAbsoluteUrl();
        if (preg_match('|[^/]\?|', $url)) {
            \Yii::$app->response->statusCode = 301;
            \Yii::$app->response->redirect(str_replace('?', '/?', $url));
            \Yii::$app->end();
        } elseif (strpos($url, '?') === false && $url[strlen($url) - 1] != '/') {
            \Yii::$app->response->statusCode = 301;
            \Yii::$app->response->redirect($url."/");
            \Yii::$app->end();
        }

        $route = parent::parseRequest($request);

        $action = explode('/', $route[0]);
        $actionCount = count($action);
        if ($actionCount == 2) {
            $action = $action[1];
        } elseif ($actionCount == 3) {
            $action = $action[2];
        }
        if ($actionCount > 1) {
            if (preg_match('/\.([a-zA-Z]+)$/', $action, $matches)) {
                $type = strtolower($matches[1]);
                if (array_key_exists($type, \Yii::$app->response->formatters)) {
                    \Yii::$app->response->format = $type;
                }

                $route[0] = str_replace($matches[0], '', $route[0]);
            }
        }
        return $route;
    }

    public function createUrl ($params)
    {
        return preg_replace('|\%2F|i', '/', parent::createUrl($params));
    }
}