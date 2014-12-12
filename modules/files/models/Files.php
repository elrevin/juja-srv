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
}
