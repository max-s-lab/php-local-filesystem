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
    private int $defaultDirectoryPermissions;

    private int $defaultFilePermissions;

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
        private string $location,
        array $optionalParams = [],
    ) {
        $this->defaultDirectoryPermissions = $optionalParams['defaultPermissions']['directory'] ?? 0755;
        $this->defaultFilePermissions = $optionalParams['defaultPermissions']['file'] ?? 0644;
    }

    /**
     * Preparing full path by relative path.
     *
     * Example:
     * ```php
     * // File
     * $result = $filesystem->prepareFullPath('file.txt');
     *
     * // Directory
     * $result = $filesystem->prepareFullPath('directory');
     * ```
     *
     * @param string $path relative path
     *
     * @return string full path
     */
    public function prepareFullPath(string $path): string
    {
        return LocalFilesystemHelper::normalizePath($this->location . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Checking the existence of a file.
     *
     * Example:
     * ```php
     * $result = $filesystem->fileExists('file.txt');
     * ```
     *
     * @param string $path relative path
     *
     * @see [is_file](https://www.php.net/manual/en/function.is-file.php) for more information
     */
    public function fileExists(string $path): bool
    {
        return is_file($this->prepareFullPath($path));
    }

    /**
     * Checking the existence of a directory.
     *
     * Example:
     * ```php
     * $result = $filesystem->directoryExists('directory');
     * ```
     *
     * @param string $path relative path
     *
     * @see [is_dir](https://www.php.net/manual/en/function.is-dir.php) for more information
     */
    public function directoryExists(string $path): bool
    {
        return is_dir($this->prepareFullPath($path));
    }

    /**
     * Setting up permissions.
     *
     * Example:
     * ```php
     * // File
     * $filesystem->setPermissions('file.txt', 0644);
     *
     * // Directory
     * $filesystem->setPermissions('directory', 0755);
     * ```
     *
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @see [chmod](https://www.php.net/manual/en/function.chmod.php) for more information
     */
    public function setPermissions(string $path, int $permissions): void
    {
        $this->setPermissionsByFullPath($this->prepareFullPath($path), $permissions);
    }

    /**
     * Getting permissions.
     *
     * Example:
     * ```php
     * // File
     * $result = $filesystem->getPermissions('file.txt');
     *
     * // Directory
     * $result = $filesystem->getPermissions('directory');
     * ```
     *
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return int the permissions on the file
     *
     * @see [fileperms](https://www.php.net/manual/en/function.fileperms.php) for more information
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
     * Pathnames listing.
     *
     * Example:
     * ```php
     * $result = filesystem->listPathnames('*');
     * ```
     *
     * @param string $pattern relative pattern
     *
     * @throws LocalFilesystemException
     *
     * @return string[] an array containing the matched files/directories, an empty array
     * if no file matched
     *
     * @see [glob](https://www.php.net/manual/en/function.glob.php) for more information
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
     * Example:
     * ```php
     * $filesystem->writeToFile('file.txt', 'Test');
     * ```
     *
     * This method also allows you to set permissions for directories and file:
     * ```php
     * $filesystem->writeToFile('file.txt', 'Test', [
     *     'directoryPermissions' => 0777,
     *     'filePermissions' => 0666,
     * ]);
     * ```
     *
     * You can also use this method to write a stream to a file.
     * To do this, simply replace `'Test'` with a stream.
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
     * Reading a file.
     *
     * Example:
     * ```php
     * $result = $filesystem->readFile('file.txt');
     * ```
     *
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return string file content
     *
     * @see [file_get_contents](https://www.php.net/manual/en/function.file-get-contents.php) for more information
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
     * Streaming file reading.
     *
     * Example:
     * ```php
     * $result = $filesystem->readFileAsStream('file.txt');
     * ```
     *
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return resource stream for reading file content
     *
     * @see [fopen](https://www.php.net/manual/en/function.fopen.php) for more information
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
     * Getting the file size.
     *
     * Example:
     * ```php
     * $result = $filesystem->getFileSize('file.txt');
     * ```
     *
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return int the size of the file in bytes
     *
     * @see [filesize](https://www.php.net/manual/en/function.filesize.php) for more information
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
     * Detect MIME Content-type for a file.
     *
     * Example:
     * ```php
     * $result = $filesystem->getFileMimeType('file.txt');
     * ```
     *
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return string the content type in MIME format, like text/plain or application/octet-stream.
     *
     * @see [mime_content_type](https://www.php.net/manual/en/function.mime-content-type.php) for more information
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
     * Getting the file modification time.
     *
     * Example:
     * ```php
     * $result = $filesystem->getFileLastModifiedTime('file.txt');
     * ```
     *
     * @param string $path relative path
     *
     * @throws LocalFilesystemException
     *
     * @return int the time the file was last modified. The time is returned as a Unix timestamp,
     * which is suitable for the date function.
     *
     * @see [filemtime](https://www.php.net/manual/en/function.filemtime.php) for more information
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
     * Deleting a file.
     *
     * Example:
     * ```php
     * $filesystem->deleteFile('file.txt');
     * ```
     *
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
     * Example:
     * ```php
     * $filesystem->copyFile('file.txt', 'directory/file.txt');
     * ```
     *
     * This method also allows you to set permissions for directories and file:
     * ```php
     * $filesystem->copyFile('file.txt', 'directory/file.txt', [
     *     'directoryPermissions' => 0777,
     *     'filePermissions' => 0666,
     * ]);
     * ```
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
     * Example:
     * ```php
     * $filesystem->moveFile('file.txt', 'directory/file.txt');
     * ```
     *
     * This method also allows you to set permissions for directories and file:
     * ```php
     * $filesystem->moveFile('file.txt', 'directory/file.txt', [
     *     'directoryPermissions' => 0777,
     *     'filePermissions' => 0666,
     * ]);
     * ```
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
     * Recursively creating a directory.
     *
     * Example:
     * ```php
     * $filesystem->createDirectory('directory');
     * ```
     *
     * It also allows you to set permissions for the created directories:
     * ```php
     * $filesystem->createDirectory('directory', 0777);
     * ```
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
     * Example:
     * ```php
     * $filesystem->deleteDirectory('directory');
     * ```
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
        return $params['filePermissions'] ?? $this->defaultFilePermissions;
    }

    private function getLastErrorAsException(): LocalFilesystemException
    {
        return new LocalFilesystemException(error_get_last()['message'] ?? 'unknown error');
    }
}
