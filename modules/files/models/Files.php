<?php

namespace app\modules\files\models;

use Yii;

/**
 * @inheritdoc
 */
class Files extends \app\modules\files\models\base\Files
{
    public static function beforeReturnUserInterface($config)
    {
        $fileTypes = \yii\helpers\Json::decode(\app\helpers\Utils::getDataFile('files', 'fileTypes.json'));
        $config['fileTypes'] = $fileTypes;
        return $config;
    }
    protected static function afterList($data)
    {
        $fileTypes = \yii\helpers\Json::decode(\app\helpers\Utils::getDataFile('files', 'fileTypes.json'));
        foreach ($data as $key => $item) {
            $ext = explode('.', $item['name'])[1];
            if (isset($fileTypes[$ext])) {
                $data[$key]['type'] = $fileTypes[$ext]['type'];
                if ($fileTypes[$ext]['type'] == 'img') {
                    $data[$key]['icon'] = \yii\helpers\Url::to(['admmain/thumbnail.png', 'name' => $item['name']]);
                } else {
                    $data[$key]['icon'] = Yii::getAlias('@theme/cp-files/images/files/file-types/'.$fileTypes[$ext]['icon'].'.png?');
                }
                $data[$key]['path'] = Yii::getAlias('@web/'.\app\components\FileSystem::getFilePath($data[$key]['name'], 'sources', false));
            }
        }
        return $data;
    }
    protected static function beforeDeleteRecords($condition = '', $params = [])
    {
        // Удаление файлов
        $files = static::find()->select(['name'])->where($condition, $params)->asArray(true)->all();
        foreach ($files as $item) {
            \app\components\FileSystem::deleteFile($item['name']);
        }
        return [$condition, $params];
    }
}
