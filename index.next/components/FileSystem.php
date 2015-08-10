<?php
namespace app\components;
class FileSystem
{

    private static function getPathChunks($hash)
    {
        $chunks = str_split($hash, 2);
        $path = implode('/', array_slice($chunks, 0, 4));
        $fileName = implode('', array_slice($chunks, 4));

        return [
            'path' => $path,
            'fileName' => $fileName
        ];
    }

    /**
     * Возвращает тип файла (расширение имени) по его хэшу
     * @param $name
     * @return string
     */
    public static function getFileType($name)
    {
        $name = explode('.', $name);
        $ln = count($name);
        if ($ln > 1) {
            return $name[$ln - 1];
        }
        return '';
    }

    /**
     * Возвращает полный путь файла по его имени (хэшу)
     * @param string $name
     * @param string $dir
     * @param bool $absolute
     * @return string
     */
    public static function getFilePath($name, $dir = 'sources', $absolute = true)
    {
        $name = explode('.', $name);
        $hash = $name[0];
        $ext = strtolower($name[1]);
        $chunks = static::getPathChunks($hash);

        $path = $chunks['path'];

        $fileName = $chunks['fileName'] . '.' . $ext;

        if ($absolute) {
            $fileName = \Yii::getAlias('@webroot/fs/' . $dir . '/' . $path) . "/" . $fileName;
        } else {
            $fileName = 'fs/' . $dir . '/' . $path . "/" . $fileName;
        }

        return $fileName;
    }

    /**
     * Генерирует и возвращает имя файла пооригинальному имени
     * @param $originalName
     * @param string $dir
     * @return string
     */
    public static function getFilePathByOriginalName($originalName, $add = '', $dir = 'sources', $mkDir = false)
    {
        $pathInfo = pathinfo($originalName);
        $ext = strtolower($pathInfo['extension']);
        $add = ($add ? $add : "-" . microtime(false) . '-' . \Yii::$app->security->generateRandomString(32));
        $hash = md5($pathInfo['filename'] . $add);
        $chunks = static::getPathChunks($hash);
        $path = \Yii::getAlias('@webroot/fs/' . $dir . '/' . $chunks['path']);
        $fileName = $chunks['fileName'] . "." . $ext;

        if (!file_exists($path) && $mkDir) {
            // Папки нет, создаем

            if (!mkdir($path, 0766, true)) {
                \Yii::error('Не удается создать папку "' . $path . '"');
                return false;
            }
        }

        return [
            'path' => $path,
            'fileName' => $fileName,
            'hash' => $hash . "." . $ext
        ];
    }

    /**
     * Копирование файла, путь к которому указан в аргументе $sourceFileName, с оригинальным именем $originalFileName
     * Возвращает полученный хеш файла
     * @param $sourceFileName
     * @param $originalFileName
     * @param string $dir
     * @return bool|string
     */
    public static function copyFile($sourceFileName, $originalFileName, $dir = 'sources')
    {
        $filePath = static::getFilePathByOriginalName($originalFileName, '', $dir);
        if (!file_exists($filePath['path'])) {
            // Папки нет, создаем

            if (!mkdir($filePath['path'], 0766, true)) {
                \Yii::error('Не удается создать папку "' . $filePath['path'] . '"');
                return false;
            }
        }
        if (!copy($sourceFileName, $filePath['path'] . '/' . $filePath['fileName'])) {
            \Yii::error('Не удается скопировать файл ' . $sourceFileName . ' в ' . $filePath['path'] . '/' . $filePath['fileName']);
            return false;
        }
        return $filePath['hash'];
    }

    /**
     * Загрузка файла переданного от клиента. Имя поля типа file передается в аргументе $paramName
     * Возвращает хэш загруженного файла.
     *
     * @param $paramName
     * @return bool
     */
    public static function upload($paramName)
    {
        $file = \yii\web\UploadedFile::getInstancesByName($paramName);
        $result = false;
        if ($file) {
            $result = static::copyFile($file[0]->tempName, $file[0]->name);
        }
        return $result ? ['hash' => $result, 'uploadedFile' => $file[0]] : false;
    }

    /**
     * Проверяет существует ли файл с указанным хешем, если да - возвращает true, иначе false
     * @param $fileHash
     * @param string $dir
     * @return bool
     */
    public static function fileExists($fileHash, $dir = 'sources')
    {
        $path = static::getFilePath($fileHash, $dir);
        return file_exists($path);
    }

    public static function createFolderForFile($fileHash, $dir = 'sources')
    {
        $path = dirname(static::getFilePath($fileHash, $dir));
        if (!file_exists($path)) {
            return mkdir($path, 0777, true);
        }
        return true;
    }

    /**
     * Удаление файла с указанным хешем
     * @param $fileHash
     * @param string $dir
     * @return bool
     */
    public static function deleteFile($fileHash, $dir = 'sources')
    {
        if (static::fileExists($fileHash, $dir)) {
            $path = dirname(static::getFilePath($fileHash, $dir));
            $fileName = static::getFilePath($fileHash, $dir);
            $ret = unlink($fileName);
            $files = scandir($path);
            $del = true;
            foreach ($files as $item) {
                if ($item != '.' && $item != '..') {
                    $del = false;
                    break;
                }
            }
            if ($del) {
                $name = explode('.', $fileHash);
                $hash = $name[0];
                $chunks = static::getPathChunks($hash);
                $path = explode('/', $chunks['path']);
                $fsPath = \Yii::getAlias('@webroot/fs/' . $dir);
                for ($i = 3; $i >= 0; $i--) {
                    $toDel = implode('/', $path);
                    if (file_exists($fsPath . '/' . $toDel)) {
                        rmdir($fsPath . '/' . $toDel);
                    }
                    array_pop($path);
                }
            }
            return $ret;
        }
        return false;
    }

}