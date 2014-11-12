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


        $moduleName = $this->module->id;
        $modelStructure = call_user_func(['\app\modules\\'.$moduleName.'\models\\'.$modelName, 'getStructure']);

        $fields = [];

        foreach ($modelStructure as $fieldName => $config) {
            $relativeModel = [];
            if ($config['type'] == 'pointer') {
                if (strncmp($config['relativeModel'], '\app\modules', 12) == 0) {
                    $relativeModelFullName = $config['relativeModel'];
                    $relativeModel = str_replace('\app\modules\\', '', str_replace('\models', '', $config['relativeModel']));
                    $relativeModel = explode('\\', $relativeModel);
                    $relativeModel[2] = call_user_func([$relativeModelFullName, 'getIdentifyFieldConf']);
                    if (!$relativeModel[2]) {
                        continue;
                    }
                    $relativeModel[3] = $relativeModel[2]['type'];
                    $relativeModel[2] = $relativeModel[2]['name'];
                } else {
                    continue;
                }
            }

            $i = count($fields);
            $fields[$i] = array_merge([
                'name' => $fieldName
            ], $config);

            if ($relativeModel) {
                $fields[$i]['relativeModel'] = [
                    'moduleName' => $relativeModel[0],
                    'name' => $relativeModel[1],
                    'identifyFieldName' => $relativeModel[2],
                    'identifyFieldType' => $relativeModel[3]
                ];
            }
        }

        $fields = \yii\helpers\Json::encode($fields);

        $getDataAction = \yii\helpers\Json::encode([$this->module->id, 'main', 'list']);

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

            $list = call_user_func([$modelName, 'getList'], ["identifyOnly" => (Yii::$app->request->get('identifyOnly', 0) ? true : false)]);
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
                if ($result = $model->saveData($data, true)) {
                    $data = [
                        'data' => $result,
                        'success' => true
                    ];
                    return $data;
                } else {
                    $errors = "";
                    foreach ($model->errors as $error) {
                        $errors .= implode("<br/>", $error);
                    }
                    $this->ajaxError('\app\modules\backend\controllers\AdmMainController\actionSave', 'Ошибка сохранения данных:<br/>'.$errors);
                    return null;
                }
            } elseif (isset($data['id']) && $data['id']) {
                $model = call_user_func([$modelName, 'findOne'], $data['id']);
                if ($result = $model->saveData($data)) {
                    $data = [
                        'data' => $result,
                        'success' => true
                    ];
                    return $data;
                } else {
                    $errors = "";
                    foreach ($model->errors as $error) {
                        $errors .= implode("<br/>", $error);
                    }
                    $this->ajaxError('\app\modules\backend\controllers\AdmMainController\actionSave', 'Ошибка сохранения данных:<br/>'.$errors);
                    return null;
                }
            }
        }
        $this->ajaxError('\app\modules\backend\controllers\AdmMainController\actionSave', 'Справочник не найден.');
        return null;
    }
}
