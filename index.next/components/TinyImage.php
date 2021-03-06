<?php

namespace app\components;

class TinyImage
{
    const RETURN_IMAGE = 1;
    const RETURN_PATH = 2;
    const RETURN_URL = 3;
    // качество генерируемого изображения
    const qualityIMG = 98;

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
     * Вычисление новых размеров изображения и координаты его измененой копии в новом холсте
     * @param $image
     * @param $imageProps
     * @return array
     */
    private static function getDimensions ($image, $imageProps)
    {
        $widthProps = (isset($imageProps['width']) && $imageProps['width'] ? $imageProps['width'] : 0);
        $heightProps = (isset($imageProps['height']) && $imageProps['height'] ? $imageProps['height'] : 0);
        $crop = isset($imageProps['crop']) ? $imageProps['crop'] : false;

        $width=imagesx($image);
        $height=imagesy($image);

        // Вычисляем коэфициенты масштабирования
        $KW = 0;
        $KH = 0;
        if (!$widthProps) {
            $widthProps = $width;
        }
        if (!$heightProps) {
            $heightProps = $height;
        }
        if ($widthProps) {
            $KW=$widthProps/$width;
        }
        if ($heightProps) {
            $KH=$heightProps/$height;
        }

        // Выбираем итоговый коэфициент
        if ($crop) {
            $K = ($KW && $KH ? (($KW < $KH) ? $KH : $KW) : ($KW ? $KW : ($KH ? $KH : 1)));
        } else {
            $K = ($KW && $KH ? (($KW < $KH) ? $KW : $KH) : ($KW ? $KW : ($KH ? $KH : 1)));
        }

        $widthProps = ($widthProps ? $widthProps : round($width*$K));
        $heightProps = ($heightProps ? $heightProps : round($height*$K));

        // Позиция нового ихображения внутри результирующего
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

    public static function resizeImage($fileName, $imageProps)
    {
        $bgColor = isset($imageProps['bgColor']) ? $imageProps['bgColor'] : \Yii::$app->params['defaultImageBgColor'];
        if (!($image = static::loadImage($fileName))) {
            return false;
        }
        $isGray = isset($imageProps['isGray']) ? true : false;
        $isTransparency = isset($imageProps['isTransparency']) ? true : false;

        $dim = static::getDimensions($image, $imageProps);

        $resultImage=imagecreatetruecolor($dim['width'], $dim['height']);

        if($isTransparency) {
            imagealphablending($resultImage, false);
            imagesavealpha($resultImage, true);
        }
        if($isGray) {
            imagefilter($resultImage, IMG_FILTER_GRAYSCALE);
        }

        if (is_string($bgColor)) {
            $bgColor=static::HexToRGB($bgColor);
        }
        $color=imagecolorallocate($resultImage, $bgColor[0], $bgColor[1], $bgColor[2]);
        imagefill($resultImage, 1, 1, $color);
        if (!imagecopyresampled($resultImage, $image, $dim['x'], $dim['y'], 0, 0,
            $dim['resampledWidth'], $dim['resampledHeight'],
            $dim['originalWidth'], $dim['originalHeight']))
        {
            return false;
        }
        return $resultImage;
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
        $crop = isset($imageProps['crop']) && $imageProps['crop'] ? 1 : 0;
        $transparency = isset($imageProps['transparency']) && $imageProps['transparency'] ? 1 : 0;

        $cacheFile = FileSystem::getFilePathByOriginalName($fileHash, '-'.$widthProps.'-'.$heightProps.'-'.$bgColor.($crop ? '-'.$crop : ''), 'cache');
        $cacheFilePath = $cacheFile['path'].'/'.$cacheFile['fileName'];

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

        if (!($resultImage = static::resizeImage($fileName, $imageProps))) {
            return false;
        } else {
            $ImageInfo=getimagesize($fileName);
            $imageType=$ImageInfo[2];
            if ($imageType == IMAGETYPE_GIF) {
                imagegif($resultImage, $cacheFilePath);
            } elseif ($imageType == IMAGETYPE_JPEG) {
                imagejpeg($resultImage, $cacheFilePath, static::qualityIMG);
            } elseif ($imageType == IMAGETYPE_PNG) {
                if ($transparency == 1) {
                    imagealphablending($resultImage, false);
                    imagesavealpha($resultImage, true);
                }
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