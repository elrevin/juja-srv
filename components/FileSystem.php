<?php
namespace app\components;
class FileSystem {
    /**
     * Возвращает полный путь файла по его ID
     * @param $id
     * @param string $dir
     * @return bool|string
     */
    public static function getFile($id, $dir = 'sources') {
        $id = explode('.', $id);
        $hash = $id[0];
        $ext = $id[1];
        $chunks = str_split($hash, 2);
        $path = implode('/', array_slice($chunks, 0, 4));
        $fileName = implode('', array_slice($chunks, 4)).'.'.$ext;

        $fileName = \Yii::getAlias('@app/fs/'.$dir.'/'.$path)."/".$fileName;

        if (file_exists($fileName)) {
            return $fileName;
        }
        return false;
    }
}