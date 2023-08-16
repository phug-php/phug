<?php

namespace Phug\Util;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Phug\CompatibilityUtil\TestCaseTypeBase;
use ReflectionMethod;

// @codeCoverageIgnoreStart
if (!class_exists(TestCaseTypeBase::class)) {
    $setUp = @new ReflectionMethod(PHPUnitTestCase::class, 'setUp');
    $testCaseInitialization = true;

    require $setUp && method_exists($setUp, 'hasReturnType') && $setUp->hasReturnType()
        ? __DIR__.'/../CompatibilityUtil/TestCaseTyped.php'
        : __DIR__.'/../CompatibilityUtil/TestCaseUntyped.php';

    unset($testCaseInitialization);
}
// @codeCoverageIgnoreEnd

class TestCase extends TestCaseTypeBase
{
    /**
     * @var string
     */
    protected $tempDirectory;

    /**
     * @var string[]
     */
    protected $tempDirectoryFiles;

    /**
     * @before
     */
    public function saveTempDirectoryFilesList()
    {
        $this->tempDirectory = $this->tempDirectory ?: sys_get_temp_dir();

        $this->tempDirectoryFiles = scandir($this->tempDirectory);
    }

    /**
     * @after
     */
    public function cleanupTempDirectory()
    {
        $files = scandir($this->tempDirectory);

        foreach (array_diff($files, $this->tempDirectoryFiles) as $file) {
            $this->removeFile($this->tempDirectory.DIRECTORY_SEPARATOR.$file);
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public static function assertStringContains($needle, $haystack, $message = '')
    {
        if (!method_exists(self::class, 'assertStringContainsString')) {
            self::assertContains($needle, $haystack, $message);

            return;
        }

        self::assertStringContainsString($needle, $haystack, $message);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function assertStringNotContains($needle, $haystack, $message = '')
    {
        if (!method_exists(self::class, 'assertStringNotContainsString')) {
            self::assertNotContains($needle, $haystack, $message);

            return;
        }

        self::assertStringNotContainsString($needle, $haystack, $message);
    }

    protected function removeFile($file)
    {
        if (is_dir($file)) {
            @$this->emptyDirectory($file);
            @rmdir($file);

            return;
        }

        @unlink($file);
    }

    protected function emptyDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..') {
                $this->removeFile($dir.'/'.$file);
            }
        }
    }

    protected function createEmptyDirectory($dir)
    {
        if (file_exists($dir)) {
            if (is_dir($dir)) {
                @$this->emptyDirectory($dir);

                return;
            }

            @unlink($dir);
        }

        @mkdir($dir, 0777, true);
    }
}
