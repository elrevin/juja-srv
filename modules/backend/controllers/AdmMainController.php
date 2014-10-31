<?php
namespace app\modules\backend\controllers;

use app\modules\backend\models\TestTable;
use \Yii;

class AdmMainController extends \app\base\web\BackendController
{
    public function actionCpMenu()
    {
        $interface = $this->getCurrentInterfaceType();

        $list = \yii\helpers\Json::decode($this->getDataFile('cpmenu.json'));
        return ['list' => $list[$interface]];
    }

    public function actionGetInterface() {
        $modelName = Yii::$app->request->post('modelName', '');
        if (!$modelName) {
            $this->ajaxError('\app\base\web\BackendController\actionGetInterface');
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

        return ("
          var module = Ext.create('App.core.SingleModelEditor', {
            fields: {$fields},
            getDataAction: {$getDataAction},
            modelName: '{$modelName}'
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

            $list = call_user_func([$modelName, 'getList'], []);
            return $list;
        }
        $this->ajaxError('app\modules\backend\controllers\AdmMainController\actionList');
        return null;
    }

    public function actionSave() {
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
                    return [
                        'success' => true
                    ];
                } else {
                    $this->ajaxError('\app\base\web\BackendController\AdmMainController\actionSave?add=1&modelName='.$modelName);
                }
            } elseif ($data['id']) {
                $model = call_user_func([$modelName, 'findOne'], $data['id']);
                $model->mapJson($data);
                if ($model->save()) {
                    return [
                        'success' => true
                    ];
                } else {
                    $this->ajaxError('\app\base\web\BackendController\AdmMainController\actionSave?add=1&modelName='.$modelName);
                }
            }
        }
        $this->ajaxError('\app\base\web\BackendController\AdmMainController\actionSave?add=1&modelName='.$modelName);
        return null;
    }
}
