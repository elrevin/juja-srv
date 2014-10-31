<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\web\ResponseFormatterInterface;

class JsResponseFormatter extends Component implements ResponseFormatterInterface
{
    public function format($response)
    {
        $response->getHeaders()->set('Content-Type', 'application/javascript; charset=UTF-8');
        $response->content = $response->data;
    }
}