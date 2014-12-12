<?php
namespace app\modules\files\controllers;

use app\components\FileSystem;
use \Yii;
use \app\modules\files\models;

class AdmMainController extends \app\base\web\BackendController
{
    protected $accessList = [
        'thumbnail' => 'backend-read'
    ];

    public function actionSaveRecord()
    {
        $id = intval(Yii::$app->request->post('id', 0));
        $title = Yii::$app->request->post('title', '');
        if (!$id) {
            if ($file = \app\components\FileSystem::upload('file')) {
                $model = new models\Files();
                if ($result = $model->saveData([
                    'name' => $file['hash'],
                    'original_name' => $file['uploadedFile']->name,
                    'title' => $title
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
                    $this->ajaxError('\app\base\web\BackendController\AdmMainController\actionSaveRecord', 'Ошибка сохранения данных:<br/>'.$errors);
                    return null;
                }
            } else {
                $this->ajaxError('\app\base\web\BackendController\AdmMainController\actionSaveRecord', 'Не удалось загрузить файл');
            }
        }
        return null;
    }

    public function afterList($modelName, $list)
    {
        $data = $list['data'];
        $fileTypes = \yii\helpers\Json::decode($this->getDataFile('fileTypes.json'));
        foreach ($data as $key => $item) {
            $ext = explode('.', $item['name'])[1];
            if (isset($fileTypes[$ext])) {
                if ($fileTypes[$ext]['type'] == 'img') {
                    $data[$key]['icon'] = \yii\helpers\Url::to(['admmain/thumbnail.png', 'id' => $item['id']]);
                } else {
                    $data[$key]['icon'] = Yii::getAlias('@theme/cp-files/images/files/file-types/'.$fileTypes[$ext]['icon'].'.png');
                }
            }
        }
        $list['data'] = $data;
        return $list;
    }

    public function actionThumbnail()
    {
        $id = intval(Yii::$app->request->get('id', 0));
        $name = Yii::$app->request->get('name', 0);
        $width = intval(Yii::$app->request->get('width', 0));
        $height = intval(Yii::$app->request->get('height', 0));
        $bgColor = Yii::$app->request->get('bgColor', \Yii::$app->params['defaultImageBgColor']);

        if ($bgColor && $bgColor[0] != '#') {
            $bgColor = '#'.$bgColor;
        }


        if ($id) {
            $name = models\Files::find()->where(['id' => $id])->select('name')->scalar();
        }

        if ($name && preg_match("/^[a-f0-9]+\\.?[a-z0-9]*$/", $name)) {
            $image = \app\components\TinyImage::createImage($name, [
                'width' => $width,
                'height' => $height,
                'bgColor' => $bgColor
            ]);

            header('Pragma: public');
            header('Cache-Control: max-age='.(60*60*10));
            header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + (60*60*10)));
            header('Content-Type: image/png');
            imagepng($image);
            Yii::$app->end();
        }
        Yii::$app->end();
    }
}
