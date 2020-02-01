<?php


namespace FahrradKruken\YAWP\GTheme;


class Helper
{
    public static function getFileVersion($filePath = '')
    {
        return is_file($filePath) ?
            filemtime($filePath) :
            date('YMd') . '-' . rand(1001, 9999);
    }

    public static function normalizePath($path = '')
    {
        return str_replace(['/\\', '\\/', '\\','//','/',], DIRECTORY_SEPARATOR, $path);
    }
}