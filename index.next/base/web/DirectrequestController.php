<?php
/**
 * Базовый класс контроллера прямыхзапросов
 */

namespace app\base\web;
use Yii;
use yii\web\Controller;


class DirectrequestController extends Controller
{
    public $layout = false;
    public function init()
    {
        Yii::$app->setComponents([
            'user' => [
                'class' => 'yii\web\User',
                'identityClass' => 'app\models\UserIdentity',
                'identityCookie' => [
                    'name' => 'frontendIdentity',
                    'path'=>'/'
                ],
                'enableAutoLogin' => true,
            ],
            'session' => [
                'class' => 'yii\web\Session',
                'name' => '_frontendSessionId',
               // 'savePath' => __DIR__ . '/../runtime', // a temporary folder on backend
            ],
        ]);

        $this->enableCsrfValidation = false;
        Yii::$app->view->setActiveTheme(Yii::$app->params['themeName']);
        Yii::$app->mailer->view->setActiveTheme(Yii::$app->params['themeName']);
        Yii::$app->mailer->themeName = Yii::$app->params['themeName'];

        Yii::$app->mailer->viewPath = '@themeroot/views/'.$this->module->id.'/mail';
        Yii::$app->mailer->htmlLayout = false;
        parent::init();
    }
}