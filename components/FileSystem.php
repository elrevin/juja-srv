<?php
namespace app\components;
class FileSystem {

    private static function getPathChunks($hash) {
        $chunks = str_split($hash, 2);
        $path = implode('/', array_slice($chunks, 0, 4));
        $fileName = implode('', array_slice($chunks, 4));

        return [
            'path' => $path,
            'fileName' => $fileName
        ];
    }

    /**
     * Возвращает полный путь файла по его имени (хэшу)
     * @param string $name
     * @param string $dir
     * @return bool|string
     */
    public static function getFilePath($name, $dir = 'sources') {
        $name = explode('.', $name);
        $hash = $name[0];
        $ext = strtolower($name[1]);
        $chunks = static::getPathChunks($hash);

        $path = $chunks['path'];

        $fileName = $chunks['fileName'].'.'.$ext;

        $fileName = \Yii::getAlias('@webroot/fs/'.$dir.'/'.$path)."/".$fileName;

        if (file_exists($fileName)) {
            return $fileName;
        }
        return false;
    }

    /**
     * Генерирует и возвращает имя файла пооригинальному имени
     * @param $originalName
     * @param string $dir
     * @return string
     */
    private static function getFilePathByOriginalName($originalName, $dir = 'sources') {
        $pathInfo = pathinfo($originalName);
        $ext = strtolower($pathInfo['extension']);
        $hash = md5($pathInfo['filename']."-".microtime(false).'-'.\Yii::$app->security->generateRandomString(32));
        $chunks = static::getPathChunks($hash);
        $path = \Yii::getAlias('@webroot/fs/'.$dir.'/'.$chunks['path']);
        $fileName = $chunks['fileName'].".".$ext;

        return [
            'path' => $path,
            'fileName' => $fileName,
            'hash' => $hash.".".$ext
        ];
    }

    /**
     * Копирование файла, путь к которому указан в аргументе $sourceFileName, с оригинальным именем $originalFileName
     * Возвращает полученный хеш файла
     * @param $sourceFileName
     * @param $originalFileName
     * @param string $dir
     */
    public static function copyFile($sourceFileName, $originalFileName, $dir = 'sources') {
        $filePath = static::getFilePathByOriginalName($originalFileName, $dir);
        if (!file_exists($filePath['path'])) {
            // Папки нет, создаем

            mkdir($filePath['path'], 0766, true);
        }
        copy($sourceFileName, $filePath['path'].'/'.$filePath['fileName']);
        return $filePath['hash'];
    }

    /**
     * Загрузка файла переданного от клиента. Имя поля типа file передается в аргументе $paramName
     * Возвращает хэш загруженного файла.
     *
     * @param $paramName
     * @param string $dir
     * @return bool
     */
    public static function upload($paramName, $dir = 'source') {
        $file = \yii\web\UploadedFile::getInstancesByName($paramName);
        if ($file) {
            return static::copyFile($file[0]->tempName, $file[0]->name);
        }
        return false;
    }
}