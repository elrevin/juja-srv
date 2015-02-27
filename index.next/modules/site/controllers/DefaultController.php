<?php
namespace app\modules\site\controllers;
use app\modules\site\models\base\SiteStructure;

class DefaultController extends \app\base\web\FrontendController
{
    public function actionIndex()
    {
        $data = SiteStructure::findOne(['id' => \Yii::$app->request->get('id', 0)]);
        return $this->render('index', ['data' => $data]);
    }
}