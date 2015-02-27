<?php
/**
 * Базовый класс контроллера фронтенда
 */

namespace app\base\web;
use Yii;
use yii\web\Controller;


class FrontendController extends Controller
{
    public $layout = false;

    public function init()
    {
        $this->enableCsrfValidation = false;
        Yii::$app->view->setActiveTheme(Yii::$app->params['themeName']);
        Yii::$app->mailer->themeName = Yii::$app->params['themeName'];
        
        Yii::$app->mailer->viewPath = '@themeroot/views/mail/views';
        Yii::$app->mailer->htmlLayout = '@themeroot/views/mail/layouts/html';
        parent::init();
    }
}