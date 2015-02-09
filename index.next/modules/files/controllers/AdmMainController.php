<?php
namespace app\modules\files\controllers;

use app\components\FileSystem;
use \Yii;
use \app\modules\files\models;
//use \app\modules\files\controllers\GetFiles;


class AdmMainController extends \app\base\web\BackendController
{
    use GetFiles;

    protected $accessList = [
        'thumbnail' => 'backend-read'
    ];

    public function actionSaveRecord()
    {
        $id = intval(Yii::$app->request->post('id', 0));
        $title = Yii::$app->request->post('title', '');
        $tmp = intval(Yii::$app->request->post('tmp', 0));
        if (!$id) {
            if ($file = FileSystem::upload('file')) {
                $model = new models\Files();
                if ($result = $model->saveData([
                    'name' => $file['hash'],
                    'original_name' => $file['uploadedFile']->name,
                    'title' => $title,
                    'tmp' => $tmp,
                    'upload_time' => time()
                ], true)) {
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
                    $this->ajaxError('\app\base\web\BackendController\actionSaveRecord', 'Ошибка сохранения данных:<br/>'.$errors);
                    return null;
                }
            } else {
                $this->ajaxError('\app\base\web\BackendController\actionSaveRecord', 'Не удалось загрузить файл');
            }
        } else {
            $data = [
                'id' => $id,
                'title' => $title
            ];
            $model = models\Files::findOne($id);

            if ($file = FileSystem::upload('file')) {
                $data['original_name'] = $file['uploadedFile']->name;
                $data['name'] = $file['hash'];
            }

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
                $this->ajaxError('\app\base\web\BackendController\actionSave?&id='.$id, 'Ошибка сохранения данных:<br/>'.$errors);
                return null;
            }
        }
        return null;
    }


}
