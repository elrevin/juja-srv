<?php
/**
 * Переопределенный класс Theme.
 * Причиной переопределения был глюк с поиском файла представления с расширением не php в теме, я писал об этом:
 * http://yiiframework.ru/forum/viewtopic.php?f=27&t=18974
 *
 * К сожалению проблему не решили (по крайней мере в RC), по этому пришлось делать такой костыль, так же пришлось
 * переопределить View.
 *
 */
namespace app\components;

use \yii\helpers;
use \yii\base;

class Theme extends base\Theme
{
    /**
     * @var \app\components\View
     */
    public $view = null;
    public function applyTo($path)
    {
        $path = \yii\helpers\FileHelper::normalizePath($path);
        foreach ($this->pathMap as $from => $tos) {
            $from = \yii\helpers\FileHelper::normalizePath(\Yii::getAlias($from)) . DIRECTORY_SEPARATOR;
            if (strpos($path, $from) === 0) {
                $n = strlen($from);
                foreach ((array) $tos as $to) {
                    $to = \yii\helpers\FileHelper::normalizePath(\Yii::getAlias($to)) . DIRECTORY_SEPARATOR;
                    $file = $to . substr($path, $n);
                    if (is_file($file)) {
                        return $file;
                    } elseif ($this->view && preg_match("/\\.php$/", $file)) { // Собственно описанный выше костыль
                        $file = $to . substr(substr($path, 0, strlen($path) - 3), $n) . $this->view->defaultExtension;
                        if (is_file($file)) {
                            return $file;
                        }
                    } // Конец костыля
                }
            }
        }
        return $path;
    }
}