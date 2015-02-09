<?php

namespace app\components;

class TinyImage
{
    const RETURN_IMAGE = 1;
    const RETURN_PATH = 2;
    const RETURN_URL = 3;
    /**
     * Перевод шестнадцатиричного кода цвета в массив значений R, G, B
     * @param string $Color
     * @return array|bool
     */
    static public function HexToRGB($Color)
    {
        if ($Color[0] == '#')
            $Color=substr($Color, 1);

        if (strlen($Color) == 6)
            list($r, $g, $b) = array($Color[0].$Color[1],
                $Color[2].$Color[3],
                $Color[4].$Color[5]);
        elseif (strlen($Color) == 3)
            list($r,$g,$b) = array($Color[0].$Color[0], $Color[1].$Color[1], $Color[2].$Color[2]);
        else
            return false;

        $r=hexdec($r);
        $g=hexdec($g);
        $b=hexdec($b);
        return array($r, $g, $b);
    }

    /**
     * Загрузка изображения из файла, полный путь к которому передается в аргументе $fileName
     * @param $fileName
     * @return bool|resource
     */
    private static function loadImage ($fileName)
    {
        $ImageInfo=getimagesize($fileName);
        $imageType=$ImageInfo[2];
        if ($imageType == IMAGETYPE_GIF) {
            $image=imagecreatefromgif($fileName);
        } elseif ($imageType == IMAGETYPE_JPEG) {
            $image=imagecreatefromjpeg($fileName);
        } elseif ($imageType == IMAGETYPE_PNG) {
            $image=imagecreatefrompng($fileName);
        } else {
            return false;
        }
         return $image;
    }

    /**
     * Вычисление новых размеров изображения и координаты его зимененой копии в новом холсте
     * @param $image
     * @param $imageProps
     * @return array
     */
    private static function getDimensions ($image, $imageProps)
    {
        $widthProps = (isset($imageProps['width']) && $imageProps['width'] ? $imageProps['width'] : 0);
        $heightProps = (isset($imageProps['height']) && $imageProps['height'] ? $imageProps['height'] : 0);

        $width=imagesx($image);
        $height=imagesy($image);

        // Вычисляем коэфициенты масштабирования
        $KW = 0;
        $KH = 0;
        if (!$widthProps && !$heightProps) {
            $widthProps = $width;
            $heightProps = $height;
        }
        if ($widthProps) {
            $KW=$widthProps/$width;
        }
        if ($heightProps) {
            $KH=$heightProps/$height;
        }

        // Выбираем итоговый коэфициент
        $K = ($KW && $KH ? (($KW < $KH) ? $KW : $KH) : ($KW ? $KW : ($KH ? $KH : 1)));

        $widthProps = ($widthProps ? $widthProps : round($width*$K));
        $heightProps = ($heightProps ? $heightProps : round($height*$K));

        // Позиция нового ихображения в нутри результирующего
        $X = round($widthProps/2 - $width*$K/2);
        $Y = round($heightProps/2 - $height*$K/2);

        return [
            'width' => round($widthProps),
            'height' => round($heightProps),
            'resampledWidth' => round($width*$K),
            'resampledHeight' => round($height*$K),
            'originalWidth' => $width,
            'originalHeight' => $height,
            'x' => $X,
            'y' => $Y
        ];
    }

    /**
     * Создание масштабной копии изображения из файла, хэш которого передается в аргументе $fileHash.
     *
     * В аргументе $imageProps передаеются свойства нового изображения:
     *      width - ширина,
     *      height - высота,
     *      bgColor - цвет не занятого фона
     *
     * В аргументе $return передается флаг показывающий - какой результат необходим:
     *      self::RETURN_IMAGE - возвращается изображение (по умолчанию)
     *      self::RETURN_PATH - возвращается пуст к файлу
     *      self::RETURN_URL - возвращается url нового изображения
     *
     *
     * @param string $fileHash
     * @param array $imageProps
     * @param int $return
     * @return bool|mixed|resource|string
     */
    public static function createImage ($fileHash, $imageProps, $return = 1)
    {
        //Проверяем есть ли файл в кеше
        $widthProps = (isset($imageProps['width']) && $imageProps['width'] ? $imageProps['width'] : 0);
        $heightProps = (isset($imageProps['height']) && $imageProps['height'] ? $imageProps['height'] : 0);
        $bgColor = isset($imageProps['bgColor']) ? $imageProps['bgColor'] : \Yii::$app->params['defaultImageBgColor'];

        $cacheFile = FileSystem::getFilePathByOriginalName($fileHash, '-'.$widthProps.'-'.$heightProps.'-'.$bgColor, 'cache');
        $cacheFilePath = $cacheFile['path'].'/'.$cacheFile['fileName'];

        $bgColor=static::HexToRGB($bgColor);

        if (FileSystem::fileExists($cacheFile['hash'], 'cache')) {
            if ($return == static::RETURN_IMAGE) {
                $image = static::loadImage($cacheFilePath);
                return $image;
            } elseif ($return == static::RETURN_PATH) {
                $cacheFilePath = str_replace('\\', '/', $cacheFilePath);
                return $cacheFilePath;
            } else {
                $search = str_replace('\\', '/', \Yii::getAlias('@webroot'));
                $source = str_replace('\\', '/', $cacheFilePath);
                return str_replace($search, '', $source);
            }
        }

        if (!FileSystem::createFolderForFile($cacheFile['hash'], 'cache')) {
            return false;
        }

        $fileName = FileSystem::getFilePath($fileHash);
        if (!($image = static::loadImage($fileName))) {
            return false;
        }

        $dim = static::getDimensions($image, $imageProps);

        $resultImage=imagecreatetruecolor($dim['width'], $dim['height']);
        $color=imagecolorallocate($resultImage, $bgColor[0], $bgColor[1], $bgColor[2]);
        imagefill($resultImage, 1, 1, $color);

        if (!imagecopyresampled($resultImage, $image, $dim['x'], $dim['y'], 0, 0,
            $dim['resampledWidth'], $dim['resampledHeight'],
            $dim['originalWidth'], $dim['originalHeight']))
        {
            return false;
        } else {
            $ImageInfo=getimagesize($fileName);
            $imageType=$ImageInfo[2];
            if ($imageType == IMAGETYPE_GIF) {
                imagegif($resultImage, $cacheFilePath);
            } elseif ($imageType == IMAGETYPE_JPEG) {
                imagejpeg($resultImage, $cacheFilePath);
            } elseif ($imageType == IMAGETYPE_PNG) {
                imagepng($resultImage, $cacheFilePath);
            }

            if ($return == static::RETURN_IMAGE) {
                return $resultImage;
            } elseif ($return == static::RETURN_PATH) {
                $cacheFilePath = str_replace('\\', '/', $cacheFilePath);
                return $cacheFilePath;
            } else {
                $search = str_replace('\\', '/', \Yii::getAlias('@webroot'));
                $source = str_replace('\\', '/', $cacheFilePath);
                return str_replace($search, '', $source);
            }
        }
    }
}