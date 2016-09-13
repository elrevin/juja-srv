<?php
namespace app\components;
class UrlRule extends \yii\web\UrlRule
{
    public $sectionType = '';
    public $sectionId = 0;
    public function parseRequest($manager, $request)
    {
        $result = parent::parseRequest($manager, $request);
        if ($result) {
            \Yii::$app->params['currentSectionType'] = $this->sectionType;
            \Yii::$app->params['sectionId'] = $this->sectionId;
        }
        return $result;
    }
}