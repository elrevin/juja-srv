<?php
/*
 * Переопределенный UrlManager, в нем отрабатываем слеши в конце Url
 */
namespace app\components;

use app\modules\backend\models\RedirectsSettings;

class UrlManager extends \yii\web\UrlManager
{
    /**
     * @var UrlRule
     */
    public $currentUrlRule = null;
    
    public function init()
    {
        // Отрабатываем редиректы
        $redirectsSettings = RedirectsSettings::find()->one();
        if ($redirectsSettings) {
            $currentUrl = parse_url(\Yii::$app->request->absoluteUrl);
            $web = $redirectsSettings->web->id;
            $protocol = $redirectsSettings->protocol->id;
            $statusCode = $redirectsSettings->status_code->id;

            foreach ($redirectsSettings->redirectsSettingsUrls as $redirect) {
                if(strpos($currentUrl['path'], $redirect->url_from) !== false) {
                    if ($web)
                        $finalUrl = $protocol . '://' . $web . '.' . $currentUrl['host'] . '/' . trim($redirect->url_to, '/');
                    else
                        $finalUrl = $protocol . '://' . $currentUrl['host'] . '/' . trim($redirect->url_to, '/');

                    \Yii::$app->response->statusCode = $statusCode;
                    \Yii::$app->response->redirect($finalUrl);
                    \Yii::$app->end();
                }
            }

            if ($currentUrl['scheme'] != $protocol || ($web && strpos($currentUrl['host'], $web) !== false)) {
                if ($web)
                    $finalUrl = $protocol . '://' . $web . '.' . $currentUrl['host'];
                else
                    $finalUrl = $protocol . '://' . $currentUrl['host'];

                \Yii::$app->response->statusCode = $statusCode;
                \Yii::$app->response->redirect($finalUrl);
                \Yii::$app->end();
            }
        }

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