<?php

namespace Phug\Test\Compiler\Locator;

use Phug\Compiler\Locator\FileLocator;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\Locator\FileLocator
 */
class FileLocatorTest extends AbstractCompilerTest
{
    /**
     * @covers ::normalize
     * @covers ::getFullPath
     * @covers ::<public>
     */
    public function testLocate()
    {
        $base = __DIR__.'/../../../templates/example-structure';
        $paths = ["$base/layouts", "$base/mixins/lib1", "$base/mixins/lib2", "$base/views"];
        $extensions = ['.pug'];

        $locator = new FileLocator();

        self::assertFileExists($locator->locate('base', $paths, $extensions));
        self::assertStringEndsWith(
            '/views/base.pug',
            str_replace('\\', '/', $locator->locate('base', $paths, $extensions))
        );
        self::assertFileExists($locator->locate('base.pug', $paths, $extensions));
        self::assertStringEndsWith(
            '/views/base.pug',
            str_replace('\\', '/', $locator->locate('base.pug', $paths, $extensions))
        );
        self::assertFileExists($locator->locate('first-module', $paths, $extensions));
        self::assertStringEndsWith(
            '/mixins/lib1/first-module.pug',
            str_replace('\\', '/', $locator->locate('first-module', $paths, $extensions))
        );
        self::assertFileExists($locator->locate('first-module.pug', $paths, $extensions));
        self::assertStringEndsWith(
            '/mixins/lib1/first-module.pug',
            str_replace('\\', '/', $locator->locate('first-module.pug', $paths, $extensions))
        );

        self::assertFileExists($locator->locate('../layouts/base', $paths, $extensions));
        self::assertStringEndsWith(
            '/layouts/base.pug',
            str_replace('\\', '/', $locator->locate('../layouts/base', $paths, $extensions))
        );

        self::assertFileExists($locator->locate('index', $paths, $extensions));
        self::assertStringEndsWith(
            '/views/index.pug',
            str_replace('\\', '/', $locator->locate('index', $paths, $extensions))
        );

        self::assertFileExists($locator->locate('test/index', $paths, $extensions));
        self::assertStringEndsWith(
            '/views/test/index.pug',
            str_replace('\\', '/', $locator->locate('test/index', $paths, $extensions))
        );

        self::assertFileExists($locator->locate('first-module', $paths, $extensions));
        self::assertStringEndsWith(
            '/mixins/lib1/first-module.pug',
            str_replace('\\', '/', $locator->locate('first-module', $paths, $extensions))
        );

        self::assertFileExists($locator->locate('second-module', $paths, $extensions));
        self::assertStringEndsWith(
            '/mixins/lib2/second-module.pug',
            str_replace('\\', '/', $locator->locate('second-module', $paths, $extensions))
        );

        $locator = new FileLocator();
        self::assertSame(__FILE__, $locator->locate(__FILE__, [], []));
        self::assertNull($locator->locate('foo.pug', [], []));
        self::assertNull($locator->locate('foo.pug', [], []));
    }
}
