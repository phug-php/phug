<?php

namespace Phug\Test\Util;

use Phug\Util\TestCase;

/**
 * Class TestCaseTest.
 *
 * @coversDefaultClass \Phug\Util\TestCase
 */
class TestCaseTest extends TestCase
{
    /**
     * @covers ::<public>
     */
    public function testCleanupTempDirectory()
    {
        $foo = sys_get_temp_dir().DIRECTORY_SEPARATOR.'foo';
        $bar = sys_get_temp_dir().DIRECTORY_SEPARATOR.'bar';
        file_put_contents($foo, 'FOO');
        $this->saveTempDirectoryFilesList();
        file_put_contents($bar, 'BAR');
        $this->cleanupTempDirectory();
        clearstatcache();

        self::assertFileExists($foo);
        self::assertFileNotExists($bar);

        unlink($foo);
    }

    /**
     * @covers ::removeFile
     * @covers ::emptyDirectory
     */
    public function testEmptyDirectory()
    {
        $foo = sys_get_temp_dir().DIRECTORY_SEPARATOR.'foo'.mt_rand(0, 9999999);
        $bar = sys_get_temp_dir().DIRECTORY_SEPARATOR.'bar'.mt_rand(0, 9999999);
        file_put_contents($foo, 'FOO');

        self::assertNull($this->emptyDirectory($foo));

        mkdir($bar);
        mkdir("$bar/biz");
        file_put_contents("$bar/xx", 'xx');
        file_put_contents("$bar/biz/yy", 'yy');

        self::assertNull($this->emptyDirectory($bar));
        self::assertSame(['.', '..'], scandir($bar));
    }

    /**
     * @covers ::createEmptyDirectory
     */
    public function testCreateEmptyDirectory()
    {
        $foo = sys_get_temp_dir().DIRECTORY_SEPARATOR.'foo'.mt_rand(0, 9999999);
        $bar = sys_get_temp_dir().DIRECTORY_SEPARATOR.'bar'.mt_rand(0, 9999999);
        $biz = sys_get_temp_dir().DIRECTORY_SEPARATOR.'biz'.mt_rand(0, 9999999);
        file_put_contents($foo, 'FOO');
        mkdir($bar);
        mkdir("$bar/biz");
        file_put_contents("$bar/xx", 'xx');
        file_put_contents("$bar/biz/yy", 'yy');

        $this->createEmptyDirectory($foo);
        $this->createEmptyDirectory($bar);
        $this->createEmptyDirectory($biz);

        self::assertTrue(is_dir($foo));
        self::assertSame(['.', '..'], scandir($foo));

        self::assertTrue(is_dir($bar));
        self::assertSame(['.', '..'], scandir($bar));

        self::assertTrue(is_dir($biz));
        self::assertSame(['.', '..'], scandir($biz));
    }
}
