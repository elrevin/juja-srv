<?php
/*
 * Переопределенный UrlManager, в нем отрабатываем слеши в конце Url
 */
namespace app\components;

class UrlManager extends \yii\web\UrlManager
{
    public function init()
    {
        // Загружаем правила

        foreach (\Yii::$app->modules as $name => $moduleConf) {
            $file = \Yii::getAlias("@app/modules/{$name}/urlRules.php");
            if (is_file($file)) {
                $this->addRules(require_once($file));
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

        // Обрабатываем тип ответа AJAX запроса
        if (\Yii::$app->request->isAjax || 1==1) {
            $action = explode('/', $route[0]);
            $action = $action[2];

            if (preg_match('/\.([a-zA-Z]+)$/', $action, $matches)) {
                $type = strtolower($matches[1]);
                if ($type == 'json') {
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                } elseif ($type == 'xml') {
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
                } elseif ($type == 'html') {
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
                } elseif ($type == 'tjson') {
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
                } elseif ($type == 'js') {
                    \Yii::$app->response->format = 'js';
                }

                $route[0] = str_replace($matches[0], '', $route[0]);
            }
        }

        return $route;
    }
}