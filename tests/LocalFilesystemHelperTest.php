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
     * @dataProvider pathProvider
     */
    public function testNormalizePath(string $expectedPath, string $sourcePath): void
    {
        $this->assertEquals($expectedPath, LocalFilesystemHelper::normalizePath($sourcePath));
    }

    /**
     * @return array{string,string}[]
     */
    public static function pathProvider(): array
    {
        $basePath = str_replace('/', DIRECTORY_SEPARATOR, __DIR__);
        $expectedPath = $basePath . DIRECTORY_SEPARATOR . self::TEST_DIRECTORY_NAME;

        return [
            [$expectedPath, "{$basePath}/" . self::TEST_DIRECTORY_NAME],
            [$expectedPath, "{$basePath}\\" . self::TEST_DIRECTORY_NAME],
            [$expectedPath, "{$basePath}//" . self::TEST_DIRECTORY_NAME],
            [$expectedPath, "{$basePath}\\\\" . self::TEST_DIRECTORY_NAME],
            [$expectedPath, "{$basePath}/" . self::TEST_DIRECTORY_NAME . '  '],
        ];
    }
}
