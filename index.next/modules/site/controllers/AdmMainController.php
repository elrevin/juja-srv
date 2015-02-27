<?php
namespace app\modules\site\controllers;

use app\modules\site\models\Modules;
use app\modules\site\models\SiteStructure;
use \Yii;
use \app\modules\files\models;
use yii\helpers\Json;

class AdmMainController extends \app\base\web\BackendController
{
    public function actionSaveRecord()
    {
        $ret = parent::actionSaveRecord();

        $data = Json::decode(Yii::$app->request->post('data', '[]'));
        $modules = Modules::getModulesList();

        $moduleName = $modules[$data['module']['id'] - 1]['name'];

        $urls = [];

        if ($moduleName == $this->module->id) {
            $urls = SiteStructure::generateSelfUrls();
            $file = $this->renderFile(Yii::getAlias('@app/modules/'. $moduleName .'/urlRules.twig'), ['urls' => $urls]);
        } else {
            $file = $this->renderFile(Yii::getAlias('@app/modules/'. $moduleName .'/urlRules.twig'), ['url' => $data['url']]);
        }

        file_put_contents(Yii::getAlias('@app/modules/'. $moduleName .'/urlRules.php'), $file);

        return $ret;
    }
}
