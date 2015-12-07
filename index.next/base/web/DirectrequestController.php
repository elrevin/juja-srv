<?php
/**
 * Базовый класс контроллера прямыхзапросов
 */

namespace app\base\web;
use Yii;
use yii\web\Controller;


class DirectrequestController extends Controller
{
    public function init()
    {
        $this->enableCsrfValidation = false;
        Yii::$app->view->setActiveTheme(Yii::$app->params['themeName']);
        Yii::$app->mailer->view->setActiveTheme(Yii::$app->params['themeName']);
        Yii::$app->mailer->themeName = Yii::$app->params['themeName'];

        Yii::$app->mailer->viewPath = '@themeroot/views/'.$this->module->id.'/mail';
        Yii::$app->mailer->htmlLayout = false;
        parent::init();
    }
}