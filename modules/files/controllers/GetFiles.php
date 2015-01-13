<?php
namespace app\modules\files\controllers;
use \Yii;
use \app\modules\files\models;
trait GetFiles {
    /**
     * Возвращает в буфер вывода (в браузер пользователя) изображение, по указанным get - параметрам:
     *   * id - id файла изображения
     *   * name - хэш-имя файла (можно указать id, можно указать имя)
     *   * width - ширина
     *   * height - длина
     *   * bgColor - шестнадцатиричный код цвета
     *   * cacheAge - максимальный возраст кеша (по умолчанию 10 часов)
     *   * nc - если указан, то кеширование не применяется
     */
    public function actionThumbnail()
    {
        $id = intval(Yii::$app->request->get('id', 0));
        $name = Yii::$app->request->get('name', '');
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

            if (!isset($_GET['nc'])) {
                $age = intval(\Yii::$app->request->get('cacheAge', 0));
                $age = $age ? $age : (60*60*10);
                header('Pragma: public');
                header('Cache-Control: max-age='.($age));
                header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + ($age)));
            }
            header('Content-Type: image/png');
            imagepng($image);
            Yii::$app->end();
        }
        Yii::$app->end();
    }

    /**
     * Возвращает в буфер вывода (в браузер пользователя) файл, по указанным get - параметрам:
     *   * id - id файла
     *   * name - хэш-имя файла (можно указать id, можно указать имя)
     */
    public function actionGetFile()
    {
        $id = intval(Yii::$app->request->get('id', 0));
        $name = Yii::$app->request->get('name', '');

        $data = null;
        if ($id && !$name) {
            $data = models\Files::findOne(['id' => $id]);
        } elseif ($name) {
            $data = models\Files::findOne(['name' =>$name]);
        }

        if ($data) {
            $name = $data['name'];
            $path = \app\components\FileSystem::getFilePath($name);
            if (file_exists($path)) {
                $type = \app\components\FileSystem::getFileType($name);
                $fileTypes = \yii\helpers\Json::decode(\app\helpers\Utils::getDataFile('files', 'fileTypes.json'));

                $type = $fileTypes[$type]['mime'];
                header('Content-Description: File Transfer');
                header('content-type: '.$type);
                header('Content-Disposition: attachment; filename=' . basename($data['original_name']));
                echo file_get_contents($path);
                Yii::$app->end();
            }
        }
    }
}