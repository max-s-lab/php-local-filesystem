<?php

namespace MaxSLab\Filesystem\Local;

/**
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
class LocalFilesystemHelper
{
    public static function normalizePath(string $path): string
    {
        return str_replace(['/', '\\', '//', '\\\\'], DIRECTORY_SEPARATOR, trim($path));
    }

    public static function filepermsToOctatValue(int $fileperms): string
    {
        return substr(sprintf('%o', $fileperms), -4);
    }
}
