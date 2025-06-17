<?php

declare(strict_types=1);

namespace MaxSLab\Filesystem\Local\Tests;

use MaxSLab\Filesystem\Local\LocalFilesystemException;
use MaxSLab\Filesystem\Local\LocalFilesystemHelper;
use MaxSLab\Filesystem\Local\LocalFilesystem;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

use function microtime;
use function umask;
use function file_put_contents;
use function fgets;
use function fclose;
use function time;

/**
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
class LocalFilesystemTest extends TestCase
{
    private const FILE_NAME = 'test.txt';
    private const FILE_CONTENT = "Test\nfile";
    private const FILE_SIZE = 9;
    private const FILE_MIME_TYPE = 'text/plain';

    private const DIRECTORY_NAME = 'test-dir';

    private const COPYING_PATH = 'test-copying/' . self::FILE_NAME;
    private const MOVING_PATH = 'test-moving/' . self::FILE_NAME;

    private const NOT_EXISTING_DIRECTORY_NAME = 'not-existing-dir';
    private const NOT_EXISTING_FILE_NAME = 'not-existing-dir.txt';

    private const GLOB_INVALID_FLAGS = 123456;

    private LocalFilesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new LocalFilesystem($this->getLocation());

        // Fix test on differrent platforms
        umask(0);
    }

    public function testCreatingDirectory(): void
    {
        $this->filesystem->createDirectory(self::DIRECTORY_NAME);
        $this->assertTrue($this->filesystem->directoryExists(self::DIRECTORY_NAME));
    }

    public function testDeletingEmptyDirectory(): void
    {
        $this->filesystem->createDirectory(self::DIRECTORY_NAME);
        $this->assertTrue($this->filesystem->directoryExists(self::DIRECTORY_NAME));

        $this->filesystem->deleteDirectory(self::DIRECTORY_NAME);
        $this->assertFalse($this->filesystem->directoryExists(self::DIRECTORY_NAME));
    }

    public function testDeletingNotEmptyDirectory(): void
    {
        $this->filesystem->createDirectory(self::DIRECTORY_NAME);
        $this->assertTrue($this->filesystem->directoryExists(self::DIRECTORY_NAME));

        file_put_contents(
            $this->filesystem->prepareFullPath(self::DIRECTORY_NAME . '/' . self::FILE_NAME),
            self::FILE_CONTENT,
        );

        $this->filesystem->createDirectory(self::DIRECTORY_NAME . '/' . self::DIRECTORY_NAME);

        $this->filesystem->deleteDirectory(self::DIRECTORY_NAME);
        $this->assertFalse($this->filesystem->directoryExists(self::DIRECTORY_NAME));
    }

    public function testDeletingNotExistingDirectory(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->deleteDirectory(self::NOT_EXISTING_DIRECTORY_NAME);
    }

    public function testReCreationDirectory(): void
    {
        $this->filesystem->createDirectory(self::DIRECTORY_NAME);
        $this->assertTrue($this->filesystem->directoryExists(self::DIRECTORY_NAME));

        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->createDirectory(self::DIRECTORY_NAME);
    }

    public function testSetPermissions(): void
    {
        $this->filesystem->createDirectory(self::DIRECTORY_NAME);
        $this->filesystem->setPermissions(self::DIRECTORY_NAME, 0777);

        $this->assertEquals('0777', LocalFilesystemHelper::filepermsToOctatValue(
            $this->filesystem->getPermissions(self::DIRECTORY_NAME),
        ));
    }

    public function testSetPermissionsOnNotExistingDirectory(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->setPermissions(self::NOT_EXISTING_DIRECTORY_NAME, 0777);
    }

    public function testGetNotExistingFilePermissions(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->getPermissions(self::NOT_EXISTING_FILE_NAME);
    }

    public function testWritingToFile(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->assertTrue($this->filesystem->fileExists(self::FILE_NAME));

        $filePath = self::DIRECTORY_NAME . '/' . self::FILE_NAME;
        $this->filesystem->writeToFile($filePath, self::FILE_CONTENT);
        $this->assertTrue($this->filesystem->fileExists($filePath));
    }

    public function testWritingToFileWheDirectoryExists(): void
    {
        $this->filesystem->createDirectory(self::DIRECTORY_NAME);

        $filePath = self::DIRECTORY_NAME . '/' . self::FILE_NAME;
        $this->filesystem->writeToFile($filePath, self::FILE_CONTENT);
        $this->assertTrue($this->filesystem->fileExists($filePath));
    }

    public function testWritingToFileByFilesystemWithCustomPermissions(): void
    {
        $filesystem = new LocalFilesystem($this->getLocation(), [
            'defaultPermissions' => [
                'directory' => 0777,
                'file' => 0666,
            ],
        ]);

        $filePath = self::DIRECTORY_NAME . '/' . self::FILE_NAME;
        $filesystem->writeToFile($filePath, self::FILE_CONTENT);
        $this->assertTrue($filesystem->fileExists($filePath));

        $this->assertEquals('0777', LocalFilesystemHelper::filepermsToOctatValue(
            $filesystem->getPermissions(self::DIRECTORY_NAME),
        ));

        $this->assertEquals('0666', LocalFilesystemHelper::filepermsToOctatValue(
            $filesystem->getPermissions(self::DIRECTORY_NAME . '/' . self::FILE_NAME),
        ));
    }

    public function testWritingToFileWithPermissions(): void
    {
        $this->filesystem->writeToFile(self::DIRECTORY_NAME . '/' . self::FILE_NAME, self::FILE_CONTENT, [
            'directoryPermissions' => 0777,
            'filePermissions' => 0666,
        ]);

        $this->assertEquals('0777', LocalFilesystemHelper::filepermsToOctatValue(
            $this->filesystem->getPermissions(self::DIRECTORY_NAME),
        ));

        $this->assertEquals('0666', LocalFilesystemHelper::filepermsToOctatValue(
            $this->filesystem->getPermissions(self::DIRECTORY_NAME . '/' . self::FILE_NAME),
        ));
    }

    public function testWritingInvalidContentToFile(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->writeToFile(self::FILE_NAME, new stdClass());
    }

    public function testDeletingFile(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->assertTrue($this->filesystem->fileExists(self::FILE_NAME));

        $this->filesystem->deleteFile(self::FILE_NAME);
        $this->assertFalse($this->filesystem->fileExists(self::FILE_NAME));
    }

    public function testDeletingNotExistingFile(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->deleteFile(self::NOT_EXISTING_FILE_NAME);
    }

    public function testReadingFile(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->assertEquals(self::FILE_CONTENT, $this->filesystem->readFile(self::FILE_NAME));
    }

    public function testReadingNotExistingFile(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->readFile(self::NOT_EXISTING_FILE_NAME);
    }

    public function testReadingFileAsStream(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);

        $contentStream = $this->filesystem->readFileAsStream(self::FILE_NAME);
        $content = '';

        while ($line = fgets($contentStream)) {
            $content .= $line;
        }

        fclose($contentStream);

        $this->assertEquals(self::FILE_CONTENT, $content);
    }

    public function testReadingNotExistingFileAsStream(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->readFileAsStream(self::NOT_EXISTING_FILE_NAME);
    }

    public function testGettingFileSize(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->assertEquals(self::FILE_SIZE, $this->filesystem->getFileSize(self::FILE_NAME));
    }

    public function testGettingNotExistingFileSize(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->getFileSize(self::NOT_EXISTING_FILE_NAME);
    }

    public function testGettingFileMimeType(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->assertEquals(self::FILE_MIME_TYPE, $this->filesystem->getFileMimeType(self::FILE_NAME));
    }

    public function testGettingNotExistingFileMimeType(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->getFileMimeType(self::NOT_EXISTING_FILE_NAME);
    }

    public function testGettingFileLastModifiedTime(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);

        $time = time();
        $result = $this->filesystem->getFileLastModifiedTime(self::FILE_NAME);

        $this->assertTrue($result === $time || $result === $time + 1);
    }

    public function testGettingNotExistingFileLastModifiedTime(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->getFileLastModifiedTime(self::NOT_EXISTING_FILE_NAME);
    }

    public function testListPathnames(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->assertEquals(
            [$this->filesystem->prepareFullPath(self::FILE_NAME)],
            $this->filesystem->listPathnames('*'),
        );

        $this->assertEquals([], $this->filesystem->listPathnames(self::NOT_EXISTING_DIRECTORY_NAME));
    }

    public function testListPathnamesWithInvalidFlags(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->listPathnames('*', self::GLOB_INVALID_FLAGS);
    }

    public function testCopyingFile(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->filesystem->copyFile(self::FILE_NAME, self::COPYING_PATH);

        $this->assertTrue($this->filesystem->fileExists(self::FILE_NAME));
        $this->assertTrue($this->filesystem->fileExists(self::COPYING_PATH));
    }

    public function testCopyingFileWithPermissions(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->filesystem->copyFile(self::FILE_NAME, self::DIRECTORY_NAME . '/' . self::COPYING_PATH, [
            'directoryPermissions' => 0777,
            'filePermissions' => 0666,
        ]);

        $this->assertEquals('0777', LocalFilesystemHelper::filepermsToOctatValue(
            $this->filesystem->getPermissions(self::DIRECTORY_NAME),
        ));

        $this->assertEquals('0666', LocalFilesystemHelper::filepermsToOctatValue(
            $this->filesystem->getPermissions(self::DIRECTORY_NAME . '/' . self::COPYING_PATH),
        ));
    }

    public function testCopyingNotExisitingFile(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->copyFile(self::NOT_EXISTING_FILE_NAME, self::COPYING_PATH);
    }

    public function testMovingFile(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->filesystem->moveFile(self::FILE_NAME, self::MOVING_PATH);

        $this->assertFalse($this->filesystem->fileExists(self::FILE_NAME));
        $this->assertTrue($this->filesystem->fileExists(self::MOVING_PATH));
    }

    public function testMovingFileWithPermissions(): void
    {
        $this->filesystem->writeToFile(self::FILE_NAME, self::FILE_CONTENT);
        $this->filesystem->moveFile(self::FILE_NAME, self::DIRECTORY_NAME . '/' . self::MOVING_PATH, [
            'directoryPermissions' => 0777,
            'filePermissions' => 0666,
        ]);

        $this->assertEquals('0777', LocalFilesystemHelper::filepermsToOctatValue(
            $this->filesystem->getPermissions(self::DIRECTORY_NAME),
        ));

        $this->assertEquals('0666', LocalFilesystemHelper::filepermsToOctatValue(
            $this->filesystem->getPermissions(self::DIRECTORY_NAME . '/' . self::MOVING_PATH),
        ));
    }

    public function testMovingNotExisitingFile(): void
    {
        $this->expectException(LocalFilesystemException::class);
        $this->filesystem->moveFile(self::NOT_EXISTING_FILE_NAME, self::MOVING_PATH);
    }

    public function testDeletingNotExistingDirectoryByFullPath(): void
    {
        $class = new ReflectionClass(LocalFilesystem::class);
        $method = $class->getMethod('deleteDirectoryByFullPath');
        $method->setAccessible(true);

        $this->expectException(LocalFilesystemException::class);
        $method->invokeArgs($this->filesystem, [self::NOT_EXISTING_DIRECTORY_NAME]);
    }

    private function getLocation(): string
    {
        return __DIR__ . '/tmp/' . (string) microtime(true);
    }
}
