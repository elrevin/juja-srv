<?php
namespace app\modules\backend\controllers;

use app\modules\backend\models\TestTable;
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
                        $modelName = '\app\modules\\'.$item['moduleName'].'\models\\'.$item['modelName'];
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

    public function actionGetInterface() {
        $modelName = Yii::$app->request->get('modelName', '');
        if (!$modelName) {
            $this->ajaxError('\app\base\web\BackendController\actionGetInterface', 'Справочник не найден.');
        }



        $modelStructure = TestTable::getStructure();

        $fields = [];

        foreach ($modelStructure as $fieldName => $config) {
            $fields[] = array_merge([
                'name' => $fieldName
            ], $config);
        }

        $fields = \yii\helpers\Json::encode($fields);

        $controllerName = $this->id;

        if (strncmp('adm', $controllerName, 3) == 0) {
            $controllerName = substr($controllerName, 3);
        }

        $getDataAction = \yii\helpers\Json::encode([$this->module->id, $controllerName, 'list']);

        $userRights = 0;

        if (Yii::$app->user->can('backend-delete-record', ['modelName' => $modelName])) {
            $userRights = 3;
        } elseif (Yii::$app->user->can('backend-save-record', ['modelName' => $modelName])) {
            $userRights = 2;
        } elseif (Yii::$app->user->can('backend-list', ['modelName' => $modelName])) {
            $userRights = 1;
        }

        return ("
          var module = Ext.create('App.core.SingleModelEditor', {
            fields: {$fields},
            getDataAction: {$getDataAction},
            modelName: '{$modelName}',
            userRights: {$userRights}
          });
        ");
    }

    /**
     * Возвращает список записей для отображения в панели управления
     * @return mixed|null
     */
    public function actionList() {
        $modelName = Yii::$app->request->get('modelName', '');
        if (preg_match('/^[a-z_0-9]+$/i', $modelName)) {
            $modelName = '\app\modules\\'.$this->module->id.'\models\\'.$modelName;

            $list = call_user_func([$modelName, 'getList'], ["sort" => [
                "id" => SORT_ASC
            ]]);
            return $list;
        }
        $this->ajaxError('\app\modules\backend\controllers\AdmMainController\actionList', 'Справочник не найден.');
        return null;
    }

    public function actionSaveRecord() {
        $modelName = Yii::$app->request->get('modelName', '');
        $add = intval(Yii::$app->request->get('add', 0));
        $data = \yii\helpers\Json::decode(Yii::$app->request->post('data', '[]'));

        if (preg_match('/^[a-z_0-9]+$/i', $modelName)) {
            $modelName = '\app\modules\\'.$this->module->id.'\models\\'.$modelName;
            if ($add) {
                /**
                 * @var \yii\db\ActiveRecord
                 */
                $model = new $modelName();
                $model->mapJson($data);
                if ($model->save()) {
                    $data = call_user_func([$modelName, 'getList'], [
                        "limit" => 1,
                        "sort" => [
                            "id" => SORT_DESC
                        ]
                    ]);
                    $data['success'] = true;
                    return $data;
                } else {
                    $errors = "";
                    foreach ($model->errors as $error) {
                        $errors .= implode("<br/>", $error);
                    }
                    $this->ajaxError('\app\modules\backend\controllers\AdmMainController\actionSave', 'Ошибка сохранения данных:<br/>'.$errors);
                }
            } elseif ($data['id']) {
                $model = call_user_func([$modelName, 'findOne'], $data['id']);
                $model->mapJson($data);
                if ($model->save()) {
                    $data = call_user_func([$modelName, 'getList'], [
                        "where" => ["id" => $data['id']]
                    ]);
                    $data['success'] = true;
                    return $data;
                } else {
                    $errors = "";
                    foreach ($model->errors as $error) {
                        $errors .= implode("<br/>", $error);
                    }
                    $this->ajaxError('\app\modules\backend\controllers\AdmMainController\actionSave', 'Ошибка сохранения данных:<br/>'.$errors);
                }
            }
        }
        $this->ajaxError('\app\modules\backend\controllers\AdmMainController\actionSave', 'Справочник не найден.');
        return null;
    }
}
