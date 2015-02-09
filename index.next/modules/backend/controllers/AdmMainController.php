<?php
namespace app\modules\backend\controllers;

use \Yii;

class AdmMainController extends \app\base\web\BackendController
{
    /**
     * Обработка главного меню
     * @param array $list
     * @return array
     */
    private function processCpMenu($list)
    {
        foreach ($list as $key => $item) {
            if (isset($item['list'])) {
                $list[$key]['getSubTreeAction'] = [];
                $list[$key]['modelName'] = "";
                $list[$key]['moduleName'] = "";
                $list[$key]['list'] = $this->processCpMenu($list[$key]['list']);
            } else {
                if (isset($item['modelName'])) {
                    // Указана модель, смотрим - нужно ли подгружать подменю
                    if (isset($item['moduleName'])) {
                        // Читаем свойства модели
                        $modelName = '\app\modules\\' . $item['moduleName'] . '\models\\' . $item['modelName'];
                        $recursive = call_user_func([$modelName, 'getRecursive']);
                        if ($recursive) {
                            $list[$key]['getSubTreeAction'] = [$item['moduleName'], "main", "cp-menu"];
                        } elseif (call_user_func([$modelName, 'getChildModel'])) {
                            // Ищем подчиненные модели
                            $list[$key]['getSubTreeAction'] = [$item['moduleName'], "main", "cp-menu"];
                        } else {
                            // Подменю нет
                            $list[$key]['getSubTreeAction'] = [];
                            $list[$key]['leaf'] = true;
                        }

                        // В любом случае нужен runAction
                        $list[$key]['runAction'] = [$item['moduleName'], "main", "get-interface"];
                    } else {
                        $this->ajaxError('\app\modules\backend\controllers\AdmMainController\processCpMenu', "Ошибка обработки главного меню.");
                    }
                } else {
                    $list[$key]['modelName'] = "";
                    $list[$key]['moduleName'] = "backend";
                    $list[$key]['runAction'] = [];
                    $list[$key]['getSubTreeAction'] = [];
                }
            }
        }
        return $list;
    }

    public function actionCpMenu($modelName = null, $recordId = null)
    {
        $modelName = Yii::$app->request->get('modelName', '');
        $recordId = intval(Yii::$app->request->get('id', 0));

        if ($modelName) {
            return parent::actionCPMenu($modelName, $recordId);
        } else {
            $interface = $this->getCurrentInterfaceType();
            $list = \yii\helpers\Json::decode($this->getDataFile('cpmenu.json'));
            // Обходим меню и вносим коррективы, там где требуется
            return ['list' => $this->processCpMenu($list[$interface])];
        }
    }

    public function actionGetStaticData()
    {
        $data = [];

        foreach (array_keys(Yii::$app->modules) as $moduleName) {
            $listFileName = Yii::getAlias('@app/data/'.$moduleName.'/loadingData.json');
            if (file_exists($listFileName)) {
                $list = \yii\helpers\Json::decode(file_get_contents($listFileName));
                if ($list) {
                    $data[$moduleName] = [];
                    foreach ($list as $fileName) {
                        $soreName = $fileName;
                        $fileName = Yii::getAlias('@app/data/'.$moduleName.'/'.$fileName.".json");
                        if (file_exists($fileName)) {
                            $data[$moduleName][$soreName] = \yii\helpers\Json::decode(file_get_contents($fileName));
                        }
                    }
                }
            }
        }

        return $data;
    }
}
