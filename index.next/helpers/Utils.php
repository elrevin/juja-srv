<?php
namespace app\helpers;

use app\models\Registry;

class Utils
{
    const AUTONUMBER_RESET_NEVER = 0;
    const AUTONUMBER_RESET_DAY = 1;
    const AUTONUMBER_RESET_MONTH = 2;
    const AUTONUMBER_RESET_YEAR = 3;

    public static function getDataFile ($moduleName, $fileName)
    {
        return file_get_contents(\Yii::getAlias("@app/data/{$moduleName}/{$fileName}"));
    }

    /**
     * @param $path
     * @param bool $absolutePath
     * @param bool $firstIteration
     * @return array
     */
    public static function getFiles ($path, $relPath = '', $absolutePath = true, $firstIteration = true)
    {
        $res = [];
        $files = scandir($path);

        if ($firstIteration) {
            $path = str_replace('\\', '/', $path);
            $path = trim($path, '/');
        }

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $file = $path."/".$file;
            if (is_dir($file)) {
                $res = array_merge($res, Utils::getFiles($file, $relPath, $absolutePath, false));
            } else {
                $res[] = $file;
            }
        }
        if ($firstIteration) {
            foreach ($res as $i => $item) {
                if ($absolutePath) {
                    $res[$i] = $relPath.'/'.str_replace($path.'/', '', $res[$i]);
                }
            }
        }
        return $res;
    }

    /**
     * Автонумератор, возвращает номер документа, в качестве аргументов принимает:
     *
     * @param string $module Имя модуля
     * @param string $key Ключ, например название документа
     * @param int $reset Период сброса номера: static::AUTONUMBER_RESET_NEVER - никогда, static::AUTONUMBER_RESET_DAY - каждый день, static::AUTONUMBER_RESET_MONTH - каждый месяц, static::AUTONUMBER_RESET_YEAR - каждый год
     * @param int $default Начальное значение
     * @return int
     */
    public static function getAutoNumber($module, $key, $reset = 0, $default = 0)
    {
        /**
         * @var $value Registry
         */
        $value = Registry::findOne(['module' => $module, 'key' => $key]);

        if (!$value) {
            $value = new Registry([
                'module' => $module,
                'key' => $key,
                'val' => 0,
            ]);
        }

        if ($reset == static::AUTONUMBER_RESET_NEVER) {
            $value->val = ($default ? $default : intval($value->val)+1);
        } elseif ($reset == static::AUTONUMBER_RESET_DAY && date('Y-m-d') != $value->date) {
            $value->val = ($default ? $default : 0);
            $value->date = date('Y-m-d');
        } elseif ($reset == static::AUTONUMBER_RESET_MONTH && date('Y-m-01') != $value->date) {
            $value->val = ($default ? $default : 0);
            $value->date = date('Y-m-01');
        } elseif ($reset == static::AUTONUMBER_RESET_YEAR && date('Y-01-01') != $value->date) {
            $value->val = ($default ? $default : 0);
            $value->date = date('Y-01-01');
        }

        if (!$default) {
            $value->val = intval($value->val)+1;
        }

        $value->save();
        return intval($value->val);
    }

    static function mkdir($path, $rights = 0755)
    {
        $umask = umask(0);
        $ret = mkdir($path, 0755, true);
        umask($umask);
        return $ret;
    }

    static function chown ($path, $user)
    {
        $files = scandir($path);
        chown($path, $user);
        chgrp($path, $user);
        foreach ($files as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (is_file($path."/".$item)) {
                chown($path."/".$item, $user);
                chgrp($path."/".$item, $user);
            } elseif (is_dir($path."/".$item)) {
                static::chown($path."/".$item, $user);
            }
        }
    }

    static function chmod ($path, $mod = 0755)
    {
        $files = scandir($path);
        $umask = umask(0);
        chmod($path, $mod);
        umask($umask);
        foreach ($files as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (is_file($path."/".$item)) {
                $umask = umask(0);
                chmod($path."/".$item, $mod);
                umask($umask);
            } elseif (is_dir($path."/".$item)) {
                static::chmod($path."/".$item, $mod);
            }
        }
    }
}