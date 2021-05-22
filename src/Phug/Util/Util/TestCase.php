<?php

namespace Phug\Util;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionMethod;

if (!class_exists(TestCaseTypeBase::class)) {
    $setUp = @new ReflectionMethod(PHPUnitTestCase::class, 'setUp');
    $testCaseInitialization = true;
    $compatibilityFolder = __DIR__.'/../../../../compatibility';

    if (!file_exists($compatibilityFolder)) {
        $compatibilityFolder = __DIR__.'/../compatibility';
    }

    require $setUp && method_exists($setUp, 'hasReturnType') && $setUp->hasReturnType()
        ? "$compatibilityFolder/Phug/Util/TestCaseTyped.php"
        : "$compatibilityFolder/Phug/Util/TestCaseUntyped.php";

    unset($testCaseInitialization);
}

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

    protected function removeFile($file)
    {
        if (is_dir($file)) {
            $this->emptyDirectory($file);
            rmdir($file);

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
