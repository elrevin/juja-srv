<?php

namespace app\modules\files\models;

use Yii;

/**
 * @property integer $id
 * @property integer $del
 * @property string $title
 * @property string $original_name
 * @property string $name
 */
class Files extends \app\modules\files\models\base\Files
{
    public static function beforeReturnUserInterface($config)
    {
        $config['fileTypes'] = [
            'jpg' => [
                'type' => 'img'
            ],
            'jpeg' => [
                'type' => 'img'
            ],
            'png' => [
                'type' => 'img'
            ],
            'gif' => [
                'type' => 'img'
            ],
            'doc' => [
                'type' => 'bin',
                'icon' => 'word',
            ],
            'docx' => [
                'type' => 'bin',
                'icon' => 'word',
            ],
            'xls' => [
                'type' => 'bin',
                'icon' => 'excel',
            ],
            'xlsx' => [
                'type' => 'bin',
                'icon' => 'excel',
            ],
        ];
        return $config;
    }
    protected static function afterList($data)
    {
        $fileTypes = \yii\helpers\Json::decode(\app\helpers\Utils::getDataFile('files', 'fileTypes.json'));
        foreach ($data as $key => $item) {
            $ext = explode('.', $item['name'])[1];
            if (isset($fileTypes[$ext])) {
                if ($fileTypes[$ext]['type'] == 'img') {
                    $data[$key]['icon'] = \yii\helpers\Url::to(['admmain/thumbnail.png', 'name' => $item['name']]);
                } else {
                    $data[$key]['icon'] = Yii::getAlias('@theme/cp-files/images/files/file-types/'.$fileTypes[$ext]['icon'].'.png');
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
