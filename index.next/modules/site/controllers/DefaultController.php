<?php
namespace app\modules\site\controllers;
class DefaultController extends \app\base\web\FrontendController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}