<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\web\ResponseFormatterInterface;

class XlsxResponseFormatter extends Component implements ResponseFormatterInterface
{
    public function format($response)
    {
        $response->getHeaders()->set('Expires', 'Mon, 1 Apr 1974 05:00:00 GMT');
        $response->getHeaders()->set('Last-Modified', gmdate("D,d M YH:i:s") . " GMT");
        $response->getHeaders()->set('Cache-Control', 'no-cache, must-revalidate');
        $response->getHeaders()->set('Pragma', 'no-cache');
        $response->getHeaders()->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->content = $response->data;
    }
}