<?php

declare(strict_types=1);

namespace MaxSLab\Filesystem\Local;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use SplFileInfo;

use function is_file;
use function is_dir;
use function fileperms;
use function glob;
use function file_put_contents;
use function file_get_contents;
use function fopen;
use function mime_content_type;
use function filesize;
use function filemtime;
use function copy;
use function rename;
use function dirname;
use function chmod;
use function mkdir;
use function rmdir;
use function unlink;
use function error_get_last;

use const DIRECTORY_SEPARATOR;

/**
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 *
 * @phpstan-type WriteOptionalParams array{directoryPermissions?: int, filePermissions?: int}
 */
class LocalFilesystem
{
    protected int $defaultDirectoryPermissions;

    protected int $defaultFilePermissions;

    /**
     * @param string $location The full path to the directory where
     * the actions will be performed.
     * @param array{
     *    defaultPermissions?: array{
     *        directory?: int,
     *        file?: int
     *    }
     * } $optionalParams
     */
    public function __construct(
        protected string $location,
        array $optionalParams = [],
    ) {
        $this->defaultDirectoryPermissions = $optionalParams['defaultPermissions']['directory'] ?? 0755;
        $this->defaultFilePermissions = $optionalParams['defaultPermissions']['file'] ?? 0644;
    }

    /**
     * @param string $path relative path
     */
    public function prepareFullPath(string $path): string
    {
        return LocalFilesystemHelper::normalizePath($this->location . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * @param string $path relative path
     */
    public function fileExists(string $path): bool
    {
        return is_file($this->prepareFullPath($path));
    }

    /**
     * @param string $path relative path
     */
    public function directoryExists(string $path): bool
    {
        return is_dir($this->prepareFullPath($path));
    }

    /**
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     */
    public function setPermissions(string $path, int $permissions): void
    {
        $this->setPermissionsByFullPath($this->prepareFullPath($path), $permissions);
    }

    /**
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     */
    public function getPermissions(string $path): int
    {
        $result = @fileperms($this->prepareFullPath($path));

        if ($result === false) {
            throw $this->getLastErrorAsException();
        }

        return $result;
    }

    /**
     * @param string $pattern relative pattern
     *
     * @throws LocalFilesystemException
     *
     * @return string[] an array containing the matched files/directories, an empty array
     * if no file matched
     */
    public function listPathnames(string $pattern, int $flags = 0): array
    {
        $result = @glob($this->prepareFullPath($pattern), $flags);

        if ($result === false) {
            throw $this->getLastErrorAsException();
        }

        return $result;
    }

    /**
     * Writing content to a file with the creation of a file and a directory for it.
     *
     * @param string $path relative path
     * @param mixed $content
     * @param WriteOptionalParams|array{flags?: int} $optionalParams
     *
     * @throws LocalFilesystemException
     */
    public function writeToFile(string $path, $content, array $optionalParams = []): void
    {
        $path = $this->prepareFullPath($path);

        $this->prepareDirectoryForFile($path, $this->getDirectoryPermissionsFromParams($optionalParams));

        if (@file_put_contents($path, $content, $optionalParams['flags'] ?? 0) === false) {
            throw $this->getLastErrorAsException();
        }

        $this->setPermissionsByFullPath($path, $this->getFilePermissionsFromParams($optionalParams));
    }

    /**
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return string file content
     */
    public function readFile(string $path): string
    {
        $result = @file_get_contents($this->prepareFullPath($path));

        if ($result === false) {
            throw $this->getLastErrorAsException();
        }

        return $result;
    }

    /**
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return resource stream for reading file content
     */
    public function readFileAsStream(string $path)
    {
        $result = @fopen($this->prepareFullPath($path), 'rb');

        if ($result === false) {
            throw $this->getLastErrorAsException();
        }

        return $result;
    }

    /**
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return int the size of the file in bytes
     */
    public function getFileSize(string $path): int
    {
        $result = @filesize($this->prepareFullPath($path));

        if ($result === false) {
            throw $this->getLastErrorAsException();
        }

        return $result;
    }

    /**
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return string the content type in MIME format, like text/plain or application/octet-stream.
     */
    public function getFileMimeType(string $path): string
    {
        $result = @mime_content_type($this->prepareFullPath($path));

        if ($result === false) {
            throw $this->getLastErrorAsException();
        }

        return $result;
    }

    /**
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return int the time the file was last modified. The time is returned as a Unix timestamp,
     * which is suitable for the date function.
     */
    public function getFileLastModifiedTime(string $path): int
    {
        $result = @filemtime($this->prepareFullPath($path));

        if ($result === false) {
            throw $this->getLastErrorAsException();
        }

        return $result;
    }

    /**
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     */
    public function deleteFile(string $path): void
    {
        $this->deleteFileByFullPath($this->prepareFullPath($path));
    }

    /**
     * Copying a file with creating a directory for it.
     *
     * @param string $oldPath relative oldPath
     * @param string $newPath relative newPath
     * @param WriteOptionalParams $optionalParams
     *
     * @throws LocalFilesystemException
     */
    public function copyFile(string $oldPath, string $newPath, array $optionalParams = []): void
    {
        $newPath = $this->prepareFullPath($newPath);

        $this->prepareDirectoryForFile($newPath, $this->getDirectoryPermissionsFromParams($optionalParams));

        if (!@copy($this->prepareFullPath($oldPath), $newPath)) {
            throw $this->getLastErrorAsException();
        }

        $this->setPermissionsByFullPath($newPath, $this->getFilePermissionsFromParams($optionalParams));
    }

    /**
     * Moving a file with creating a directory for it.
     *
     * @param string $oldPath relative oldPath
     * @param string $newPath relative newPath
     * @param WriteOptionalParams $optionalParams
     *
     * @throws LocalFilesystemException
     */
    public function moveFile(string $oldPath, string $newPath, array $optionalParams = []): void
    {
        $newPath = $this->prepareFullPath($newPath);

        $this->prepareDirectoryForFile($newPath, $this->getDirectoryPermissionsFromParams($optionalParams));

        if (!@rename($this->prepareFullPath($oldPath), $newPath)) {
            throw $this->getLastErrorAsException();
        }

        $this->setPermissionsByFullPath($newPath, $this->getFilePermissionsFromParams($optionalParams));
    }

    /**
     * Recursive creating a directory.
     *
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     */
    public function createDirectory(string $path, ?int $permissions = null): void
    {
        $this->createDirectoryByFullPath(
            $this->prepareFullPath($path),
            $permissions ?? $this->defaultDirectoryPermissions,
        );
    }

    /**
     * Recursively deleting a directory along with the contained files and directories.
     *
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     */
    public function deleteDirectory(string $path): void
    {
        $path = $this->prepareFullPath($path);

        if (!is_dir($path)) {
            throw new LocalFilesystemException('The specified path is not a directory.');
        }

        $contents = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $content */
        foreach ($contents as $content) {
            if ($content->isDir()) {
                $this->deleteDirectoryByFullPath($content->getPathname());
            } else {
                $this->deleteFileByFullPath($content->getPathname());
            }
        }

        $this->deleteDirectoryByFullPath($path);
    }

    /**
     * @throws LocalFilesystemException
     */
    private function prepareDirectoryForFile(string $fullFilePath, int $permissions): void
    {
        $dirname = dirname($fullFilePath);

        if (is_dir($dirname)) {
            return;
        }

        $this->createDirectoryByFullPath($dirname, $permissions);
    }

    /**
     * @throws LocalFilesystemException
     */
    private function setPermissionsByFullPath(string $fullPath, int $permissions): void
    {
        if (!@chmod($fullPath, $permissions)) {
            throw $this->getLastErrorAsException();
        }
    }

    /**
     * @throws LocalFilesystemException
     */
    private function createDirectoryByFullPath(string $fullPath, int $permissions): void
    {
        if (!@mkdir($fullPath, $permissions, true)) {
            throw $this->getLastErrorAsException();
        }
    }

    /**
     * @throws LocalFilesystemException
     */
    private function deleteDirectoryByFullPath(string $fullPath): void
    {
        if (!@rmdir($fullPath)) {
            throw $this->getLastErrorAsException();
        }
    }

    /**
     * @throws LocalFilesystemException
     */
    private function deleteFileByFullPath(string $fullPath): void
    {
        if (!@unlink($fullPath)) {
            throw $this->getLastErrorAsException();
        }
    }

    /**
     * @param WriteOptionalParams $params
     */
    private function getDirectoryPermissionsFromParams(array $params): int
    {
        return $params['directoryPermissions'] ?? $this->defaultDirectoryPermissions;
    }

    /**
     * @param WriteOptionalParams $params
     */
    private function getFilePermissionsFromParams(array $params): int
    {
        return $params['filePermissions'] ?? $this->defaultDirectoryPermissions;
    }

    private function getLastErrorAsException(): LocalFilesystemException
    {
        return new LocalFilesystemException(error_get_last()['message'] ?? 'unknown error');
    }
}
