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
        parent::init();
    }
}