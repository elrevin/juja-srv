<?php
namespace app\base\web;
class Request extends \yii\web\Request
{
    public $baseDomain = '';
    
    function init()
    {
        $this->baseDomain = ($this->baseDomain ? $this->baseDomain : $_SERVER['SERVER_NAME']);
        parent::init();
    }
}