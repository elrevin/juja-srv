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

    public function actionCpMenu()
    {
        $interface = $this->getCurrentInterfaceType();

        $list = \yii\helpers\Json::decode($this->getDataFile('cpmenu.json'));

        // Обходим меню и вносим коррективы, там где требуется


        return ['list' => $this->processCpMenu($list[$interface])];
    }

}
