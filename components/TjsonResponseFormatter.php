<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\web\ResponseFormatterInterface;

class TjsonResponseFormatter extends Component implements ResponseFormatterInterface
{
    public function format($response)
    {
        $response->getHeaders()->set('Content-Type', 'text/plain; charset=UTF-8');
        $response->content = \yii\helpers\Json::encode($response->data);
    }
}