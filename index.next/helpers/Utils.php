<?php
namespace app\helpers;

class Utils
{
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
}