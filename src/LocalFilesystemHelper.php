<?php

declare(strict_types=1);

namespace MaxSLab\Filesystem\Local;

use function str_replace;
use function trim;
use function substr;
use function sprintf;

use const DIRECTORY_SEPARATOR;

/**
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
class LocalFilesystemHelper
{
    /**
     * Path normalization.
     *
     * Example:
     * ```php
     * LocalFilesystemHelper::normalizePath('/var//www/html/');
     * ```
     *
     * @return string normalized path
     */
    public static function normalizePath(string $path): string
    {
        return str_replace(['/', '\\', '//', '\\\\'], DIRECTORY_SEPARATOR, trim($path));
    }

    /**
     * Convert numeric mode permissions to octal mode permissions.
     *
     * Example:
     * ```php
     * LocalFilesystem::filepermsToOctatValue(0100644);
     * ```
     *
     * @param int $fileperms numeric mode permissions
     *
     * @return string octal mode permissions
     */
    public static function filepermsToOctalValue(int $fileperms): string
    {
        return substr(sprintf('%o', $fileperms), -4);
    }
}
