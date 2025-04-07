<?php

namespace MaxSLab\Filesystem\Local\Tests;

use MaxSLab\Filesystem\Local\LocalFilesystemHelper;
use PHPUnit\Framework\TestCase;

/**
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
class LocalFilesystemHelperTest extends TestCase
{
    private const TEST_DIRECTORY_NAME = 'test';

    /**
     * @dataProvider provideNormalizePathData
     */
    public function testNormalizePath(string $sourcePath, string $expectedValue): void
    {
        $this->assertEquals($expectedValue, LocalFilesystemHelper::normalizePath($sourcePath));
    }

    /**
     * @dataProvider provideFilepermsToOctatValueData
     */
    public function testFilepermsToOctatValue(int $fileperms, string $expectedValue): void
    {
        $this->assertEquals($expectedValue, LocalFilesystemHelper::filepermsToOctatValue($fileperms));
    }

    /**
     * @return array{string,string}[]
     */
    public static function provideNormalizePathData(): array
    {
        $expectedPath = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            __DIR__
        ) . DIRECTORY_SEPARATOR . self::TEST_DIRECTORY_NAME;

        return [
            [__DIR__ . '/' . self::TEST_DIRECTORY_NAME, $expectedPath],
            [__DIR__ . '/' . self::TEST_DIRECTORY_NAME . '  ', $expectedPath],
            [__DIR__ . '\\' . self::TEST_DIRECTORY_NAME, $expectedPath],
            [__DIR__ . '//' . self::TEST_DIRECTORY_NAME, $expectedPath],
            [__DIR__ . '\\\\' . self::TEST_DIRECTORY_NAME, $expectedPath],
        ];
    }

    /**
     * @return array{int,string}[]
     */
    public static function provideFilepermsToOctatValueData(): array
    {
        return [
            [0100644, '0644'],
            [0040755, '0755'],
            [0100777, '0777'],
            [0120777, '0777'],
        ];
    }
}
