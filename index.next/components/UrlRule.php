<?php
namespace app\components;
class UrlRule extends \yii\web\UrlRule
{
    public $sectionType = '';
    public function parseRequest($manager, $request)
    {
        $result = parent::parseRequest($manager, $request);
        if ($result) {
            \Yii::$app->params['currentSectionType'] = $this->sectionType;
        }
        return $result;
    }
}