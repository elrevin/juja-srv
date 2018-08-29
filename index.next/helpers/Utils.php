<?php
namespace app\helpers;

use app\components\Morpher;
use app\models\Registry;

class Utils
{
    const AUTONUMBER_RESET_NEVER = 0;
    const AUTONUMBER_RESET_DAY = 1;
    const AUTONUMBER_RESET_MONTH = 2;
    const AUTONUMBER_RESET_YEAR = 3;

    public static function getDataFile($moduleName, $fileName)
    {
        return file_get_contents(\Yii::getAlias("@app/data/{$moduleName}/{$fileName}"));
    }

    /**
     * @param $path
     * @param bool $absolutePath
     * @param bool $firstIteration
     * @return array
     */
    public static function getFiles($path, $relPath = '', $absolutePath = true, $firstIteration = true)
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
            $file = $path . "/" . $file;
            if (is_dir($file)) {
                $res = array_merge($res, Utils::getFiles($file, $relPath, $absolutePath, false));
            } else {
                $res[] = $file;
            }
        }
        if ($firstIteration) {
            foreach ($res as $i => $item) {
                if ($absolutePath) {
                    $res[$i] = $relPath . '/' . str_replace($path . '/', '', $res[$i]);
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
            $value->val = ($default ? $default : intval($value->val) + 1);
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

        if (!$value->val && !$default) {
            $value->val = intval($value->val) + 1;
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

    static function chown($path, $user)
    {
        $files = scandir($path);
        chown($path, $user);
        chgrp($path, $user);
        foreach ($files as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (is_file($path . "/" . $item)) {
                chown($path . "/" . $item, $user);
                chgrp($path . "/" . $item, $user);
            } elseif (is_dir($path . "/" . $item)) {
                static::chown($path . "/" . $item, $user);
            }
        }
    }

    static function chmod($path, $mod = 0755)
    {
        $files = scandir($path);
        $umask = umask(0);
        chmod($path, $mod);
        umask($umask);
        foreach ($files as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (is_file($path . "/" . $item)) {
                $umask = umask(0);
                chmod($path . "/" . $item, $mod);
                umask($umask);
            } elseif (is_dir($path . "/" . $item)) {
                static::chmod($path . "/" . $item, $mod);
            }
        }
    }

    public static function getMonthName($m, $case = "base")
    {
        $case = strtolower($case);

        switch ($case) {
            case 'base':
                $case = Morpher::CASE_BASE;
                break;
            case 'prepositional':
                $case = Morpher::CASE_PREPOSITIONAL;
                break;
            case 'instrumental':
                $case = Morpher::CASE_INSTRUMENTAL;
                break;
            case 'accusative':
                $case = Morpher::CASE_ACCUSATIVE;
                break;
            case 'dative':
                $case = Morpher::CASE_DATIVE;
                break;
            case 'genitive':
                $case = Morpher::CASE_GENITIVE;
                break;
        }

        $m = intval($m);
        switch ($m) {
            case 1:
                $m = 'январь';
                break;
            case 2:
                $m = 'февраль';
                break;
            case 3:
                $m = 'март';
                break;
            case 4:
                $m = 'апрель';
                break;
            case 5:
                $m = 'май';
                break;
            case 6:
                $m = 'июнь';
                break;
            case 7:
                $m = 'июль';
                break;
            case 8:
                $m = 'август';
                break;
            case 9:
                $m = 'сентябрь';
                break;
            case 10:
                $m = 'октябрь';
                break;
            case 11:
                $m = 'ноябрь';
                break;
            case 12:
                $m = 'декабрь';
                break;
        }

        if ($case != Morpher::CASE_BASE) {
            /**
             * @var $morpher Morpher
             */
            $morpher = \Yii::$app->morpher;
            $m = $morpher->inflect($m, $case);
        }

        return $m;
    }

    public static function numToStr($num, $unit, $case = "base")
    {
        $case = strtolower($case);

        switch ($case) {
            case 'base':
                $case = Morpher::CASE_BASE;
                break;
            case 'prepositional':
                $case = Morpher::CASE_PREPOSITIONAL;
                break;
            case 'instrumental':
                $case = Morpher::CASE_INSTRUMENTAL;
                break;
            case 'accusative':
                $case = Morpher::CASE_ACCUSATIVE;
                break;
            case 'dative':
                $case = Morpher::CASE_DATIVE;
                break;
            case 'genitive':
                $case = Morpher::CASE_GENITIVE;
                break;
        }

        /**
         * @var $morpher Morpher
         */
        $morpher = \Yii::$app->morpher;
        $m = $morpher->getStrNum($num, $unit, $case);
        return $m;
    }

    public static function getRuDate($date, $format = "d M Y", $case = "base")
    {
        $date = explode("-", $date);
        $date = str_replace("d", $date[2], str_replace("M", static::getMonthName($date[1], $case), str_replace("Y", $date[0], $format)));
        return $date;
    }

    public static function formatDate($date, $format)
    {
        $dt = new \DateTime($date);
        return $dt->format($format);
    }

    /**
     * Вставляет элемент с ключем $newKey и значением $newValue массив $array до ключа $key
     *
     * @param $key
     * @param array $array
     * @param mixed $newKey
     * @param mixed $newValue
     * @return array|bool
     */
    public function array_insert_before($key, array $array, $newKey, $newValue = null)
    {
        if (array_key_exists($key, $array)) {
            $new = array();
            foreach ($array as $k => $value) {
                if ($k === $key) {
                    if (is_array($newKey) && count($newKey) > 0) {
                        $new = array_merge($new, $newKey);
                    } else {
                        $new[$newKey] = $newValue;
                    }
                }
                $new[$k] = $value;
            }
            return $new;
        }
        return false;
    }

    /**
     * Вставляет элемент с ключем $newKey и значением $newValue массив $array после ключа $key
     *
     * @param $key
     * @param array $array
     * @param $newKey
     * @param null $newValue
     * @return array|bool
     */
    public static function array_insert_after($key, array  $array, $newKey, $newValue = null)
    {
        if (array_key_exists($key, $array)) {
            $new = array();

            foreach ($array as $k => $value) {
                $new[$k] = $value;
                if ($k === $key) {
                    if (is_array($newKey) && count($newKey) > 0) {
                        $new = array_merge($new, $newKey);
                    } else {
                        $new[$newKey] = $newValue;
                    }
                }
            }

            return $new;
        }
        return false;
    }

    /**
     * Check if a given ip is in a network
     * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
     * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
     * @return boolean true if the ip is in this range / false if not.
     */
    public static function ipInRange( $ip, $range ) {
        if ( strpos( $range, '/' ) == false ) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list( $range, $netmask ) = explode( '/', $range, 2 );
        $range_decimal = ip2long( $range );
        $ip_decimal = ip2long( $ip );
        $wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
    }

    public static function setCurrentSectionPath($path)
    {
        \Yii::$app->params["currentSectionPath"] = $path;
    }
}