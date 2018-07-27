<?php
namespace app\components;


class UrlNormalizer extends \yii\web\UrlNormalizer
{
    public function normalizePathInfo($pathInfo, $suffix, &$normalized = false)
    {
        if (empty($pathInfo)) {
            return $pathInfo;
        }

        $sourcePathInfo = $pathInfo;
        if ($this->collapseSlashes) {
            $pathInfo = $this->collapseSlashes($pathInfo);
        }

        if ($this->normalizeTrailingSlash === true) {
            $pathInfo = $this->normalizeTrailingSlash($pathInfo, $suffix);
        }

        $normalized = ($sourcePathInfo !== $pathInfo) && !preg_match("#^/?directrequest/#i", $pathInfo);

        return $pathInfo;
    }

    protected function normalizeTrailingSlash($pathInfo, $suffix)
    {
        if (substr($suffix, -1) === '/' && substr($pathInfo, -1) !== '/') {
            $pathInfo .= '/';
        } elseif (substr($suffix, -1) !== '/' && substr($pathInfo, -1) === '/') {
            $pathInfo = rtrim($pathInfo, '/');
        }

        return $pathInfo;
    }

}