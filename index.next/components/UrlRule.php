<?php
namespace app\components;
class UrlRule extends \yii\web\UrlRule
{
    public $sectionType = '';
    public $sectionModule = '';
    public $sectionId = 0;
    public $isAdmin = false;
    public $isDirectRequest = false;

    public function parseRequest($manager, $request)
    {
        $result = parent::parseRequest($manager, $request);
        if ($result) {
            \Yii::$app->urlManager->currentUrlRule = $this;
        }
        return $result;
    }
}