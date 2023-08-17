<?php

namespace Phug\Test\Adapter;

use DateTime;
use JsPhpize\JsPhpizePhug;
use Phug\Compiler;
use Phug\Renderer;
use Phug\Renderer\Adapter\FileAdapter;
use Phug\Renderer\Adapter\StreamAdapter;
use Phug\Test\AbstractRendererTest;

/**
 * @coversDefaultClass \Phug\Renderer\Adapter\FileAdapter
 */
class FileAdapterTest extends AbstractRendererTest
{
    /**
     * @covers ::<public>
     * @covers ::createTemporaryFile
     * @covers ::getCompiledFile
     * @covers \Phug\Renderer\Partial\RenderingFileTrait::getRenderingFile
     * @covers \Phug\Renderer\Partial\AdapterTrait::getAdapter
     * @covers \Phug\Renderer\AbstractAdapter::getRenderer
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::cacheFileContents
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::reInitCompiler
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::parseCliDirectoriesInput
     */
    public function testRender()
    {
        $renderer = new Renderer([
            'debug'              => false,
            'adapter_class_name' => FileAdapter::class,
        ]);

        self::assertSame('<p>Hello</p>', $renderer->render('p=$message', [
            'message' => 'Hello',
        ]));

        $renderer->render('p Hello');
        /** @var FileAdapter $adapter */
        $adapter = $renderer->getAdapter();

        self::assertInstanceOf(FileAdapter::class, $adapter);
        self::assertSame($renderer, $adapter->getRenderer());
        $path = $adapter->getRenderingFile();
        self::assertFileExists($path);
        self::assertSame('<p>Hello</p>', file_get_contents($path));
    }

    /**
     * @covers ::<public>
     * @covers ::getCachePath
     * @covers ::getRawCachePath
     * @covers ::isCacheUpToDate
     * @covers ::checkPathExpiration
     * @covers ::getCacheDirectory
     * @covers ::getRegistryPath
     * @covers \Phug\Renderer\Partial\RegistryTrait::findCachePathInRegistryFile
     * @covers \Phug\Renderer\Partial\RegistryTrait::findInRegistry
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensions
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensionsOnFileKey
     * @covers \Phug\Renderer\Partial\RegistryTrait::findCachePathInRegistry
     * @covers \Phug\Renderer\Partial\RegistryTrait::getFirstRegistryIndex
     * @covers \Phug\Renderer\AbstractAdapter::<public>
     * @covers \Phug\Renderer\Partial\AdapterTrait::getAdapter
     * @covers \Phug\Renderer\Partial\FileSystemTrait::fileMatchExtensions
     * @covers \Phug\Renderer\Partial\FileSystemTrait::scanDirectory
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::cacheFileContents
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::reInitCompiler
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::parseCliDirectoriesInput
     */
    public function testCache()
    {
        $directory = sys_get_temp_dir().'/pug'.mt_rand(0, 99999999);
        $this->emptyDirectory($directory);
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        $renderer = new Renderer([
            'cache_dir' => $directory,
        ]);
        $path = $directory.DIRECTORY_SEPARATOR.'test.pug';
        file_put_contents($path, 'p=$message');

        self::assertSame('<p>Hi</p>', $renderer->renderFile($path, [
            'message' => 'Hi',
        ]));

        $renderer->getAdapter()->setOption('up_to_date_check', false);
        file_put_contents($path, 'div=$message');

        self::assertSame('<p>Ho</p>', $renderer->renderFile($path, [
            'message' => 'Ho',
        ]));

        $renderer->getAdapter()->setOption('up_to_date_check', true);
        $GLOBALS['debug'] = true;

        self::assertSame('<div>He</div>', $renderer->renderFile($path, [
            'message' => 'He',
        ]));

        $renderer->getAdapter()->setOption('cache_dir', null);

        self::assertSame('<div>Ha</div>', $renderer->renderFile($path, [
            'message' => 'Ha',
        ]));

        $renderer->getAdapter()->setOption('cache_dir', null);
        $renderer->setOption('cache_dir', null);

        self::assertSame('<div>He</div>', $renderer->renderFile($path, [
            'message' => 'He',
        ]));

        $renderer->getAdapter()->setOption('cache_dir', true);

        self::assertSame('<div>Hu</div>', $renderer->renderFile($path, [
            'message' => 'Hu',
        ]));

        /** @var FileAdapter $fileAdapter */
        $fileAdapter = $renderer->getAdapter();
        $path1 = $fileAdapter->cache(
            __DIR__.'/../../cases/attrs.pug',
            file_get_contents(__DIR__.'/../../cases/attrs.pug'),
            function ($path, $input) {
                return "$path\n$input";
            }
        );
        $path2 = $fileAdapter->cache(
            __DIR__.'/../../cases/attrs-data.pug',
            file_get_contents(__DIR__.'/../../cases/attrs-data.pug'),
            function ($path, $input) {
                return "$path\n$input";
            }
        );

        self::assertNotEquals($path1, $path2);

        if (file_exists($path1)) {
            unlink($path1);
        }
        if (file_exists($path2)) {
            unlink($path2);
        }

        $this->emptyDirectory($directory);
        $directory = sys_get_temp_dir().'/pug'.mt_rand(0, 99999999);
        $this->emptyDirectory($directory);
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        $renderer = new Renderer([
            'debug'     => false,
            'paths'     => [__DIR__.'/../../cases'],
            'modules'   => [JsPhpizePhug::class],
            'cache_dir' => $directory,
        ]);
        $attrs = $renderer->renderFile('attrs.pug');
        $attrsData = $renderer->renderFile('attrs-data.pug');
        $attrsAgain = $renderer->renderFile('attrs.pug');
        $files = array_filter(scandir($directory), function ($item) {
            return mb_substr($item, 0, 1) !== '.' && pathinfo($item, PATHINFO_EXTENSION) !== 'txt';
        });
        $this->emptyDirectory($directory);

        self::assertNotEquals($attrs, $attrsData);
        self::assertSame($attrs, $attrsAgain);
        self::assertCount(2, $files);
    }

    /**
     * @covers ::<public>
     * @covers ::getCachePath
     * @covers ::getRawCachePath
     * @covers ::isCacheUpToDate
     * @covers ::checkPathExpiration
     * @covers ::getCacheDirectory
     * @covers \Phug\Renderer\AbstractAdapter::<public>
     * @covers \Phug\Renderer\Partial\AdapterTrait::getAdapter
     * @covers \Phug\Renderer\Partial\FileSystemTrait::fileMatchExtensions
     * @covers \Phug\Renderer\Partial\FileSystemTrait::scanDirectory
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::cacheFileContents
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::reInitCompiler
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::parseCliDirectoriesInput
     */
    public function testSharedVariablesWithCache()
    {
        $directory = sys_get_temp_dir().'/pug'.mt_rand(0, 99999999);
        $this->emptyDirectory($directory);
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        $renderer = new Renderer([
            'cache_dir' => $directory,
        ]);
        $path = $directory.DIRECTORY_SEPARATOR.'test.pug';
        file_put_contents($path, 'p=$message');

        $renderer->share('message', 'Hi');
        self::assertSame('<p>Hi</p>', $renderer->renderFile($path));

        $renderer->getAdapter()->setOption('up_to_date_check', false);
        file_put_contents($path, 'div=$message');

        $renderer->share('message', 'Ho');
        self::assertSame('<p>Ho</p>', $renderer->renderFile($path));

        $renderer->getAdapter()->setOption('up_to_date_check', true);
        $GLOBALS['debug'] = true;

        $renderer->share('message', 'He');
        self::assertSame('<div>He</div>', $renderer->renderFile($path));

        $renderer->getAdapter()->setOption('cache_dir', null);

        $renderer->share('message', 'Ha');
        self::assertSame('<div>Ha</div>', $renderer->renderFile($path));

        $renderer->getAdapter()->setOption('cache_dir', null);
        $renderer->setOption('cache_dir', null);

        $renderer->share('message', 'He');
        self::assertSame('<div>He</div>', $renderer->renderFile($path));

        $renderer->getAdapter()->setOption('cache_dir', true);

        $renderer->share('message', 'Hu');
        self::assertSame('<div>Hu</div>', $renderer->renderFile($path));

        /** @var FileAdapter $fileAdapter */
        $fileAdapter = $renderer->getAdapter();
        $path1 = $fileAdapter->cache(
            __DIR__.'/../../cases/attrs.pug',
            file_get_contents(__DIR__.'/../../cases/attrs.pug'),
            function ($path, $input) {
                return "$path\n$input";
            }
        );
        $path2 = $fileAdapter->cache(
            __DIR__.'/../../cases/attrs-data.pug',
            file_get_contents(__DIR__.'/../../cases/attrs-data.pug'),
            function ($path, $input) {
                return "$path\n$input";
            }
        );

        self::assertNotEquals($path1, $path2);

        if (file_exists($path1)) {
            unlink($path1);
        }
        if (file_exists($path2)) {
            unlink($path2);
        }

        $this->emptyDirectory($directory);
        $directory = sys_get_temp_dir().'/pug'.mt_rand(0, 99999999);
        $this->emptyDirectory($directory);
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        $renderer = new Renderer([
            'debug'     => false,
            'paths'     => [__DIR__.'/../../cases'],
            'modules'   => [JsPhpizePhug::class],
            'cache_dir' => $directory,
        ]);
        $attrs = $renderer->renderFile('attrs.pug');
        $attrsData = $renderer->renderFile('attrs-data.pug');
        $attrsAgain = $renderer->renderFile('attrs.pug');
        $files = array_filter(scandir($directory), function ($item) {
            return mb_substr($item, 0, 1) !== '.' && pathinfo($item, PATHINFO_EXTENSION) !== 'txt';
        });
        $this->emptyDirectory($directory);

        self::assertNotEquals($attrs, $attrsData);
        self::assertSame($attrs, $attrsAgain);
        self::assertCount(2, $files);
    }

    /**
     * @covers \Phug\Renderer\Adapter\FileAdapter::isCacheUpToDate
     * @covers \Phug\Renderer\Adapter\FileAdapter::checkPathExpiration
     * @covers \Phug\Renderer\Adapter\FileAdapter::hasExpiredImport
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::cacheFileContents
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::reInitCompiler
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::parseCliDirectoriesInput
     */
    public function testCacheOnIncludeChange()
    {
        $directory = sys_get_temp_dir().'/pug'.mt_rand(0, 99999999);
        $this->emptyDirectory($directory);
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        $renderer = new Renderer([
            'cache_dir' => $directory,
        ]);
        $include = $directory.DIRECTORY_SEPARATOR.'test.pug';
        file_put_contents($include, 'p=$message');
        $path = $directory.DIRECTORY_SEPARATOR.'include.pug';
        file_put_contents($path, 'include test');

        self::assertSame('<p>Hi</p>', $renderer->renderFile($path, [
            'message' => 'Hi',
        ]));

        file_put_contents($include, 'div=$message');
        touch($include, time() - 3600);
        touch($path, time() - 3600);
        clearstatcache();

        $html = $renderer->renderFile($path, [
            'message' => 'Ho',
        ]);
        self::assertSame('<p>Ho</p>', $html);

        touch($include, time() + 3600);
        clearstatcache();

        self::assertSame('<div>Ha</div>', $renderer->renderFile($path, [
            'message' => 'Ha',
        ]));

        file_put_contents($include, 'p=$message');
        touch($include, time() - 3600);
        clearstatcache();

        foreach (scandir($directory) as $file) {
            if (substr($file, -22) === '.imports.serialize.txt') {
                unlink($directory.DIRECTORY_SEPARATOR.$file);
            }
        }

        self::assertSame('<p>He</p>', $renderer->renderFile($path, [
            'message' => 'He',
        ]));

        $this->emptyDirectory($directory);
    }

    /**
     * @covers ::isCacheUpToDate
     * @covers ::checkPathExpiration
     * @covers ::hasExpiredImport
     */
    public function testCacheOnExtendsChange()
    {
        $cacheDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pug-cache-'.mt_rand(0, 99999999);
        $templateDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pug-templates-'.mt_rand(0, 99999999);
        foreach ([$cacheDirectory, $templateDirectory] as $directory) {
            $this->emptyDirectory($directory);
            if (!file_exists($directory)) {
                mkdir($directory);
            }
        }
        $renderer = new Renderer([
            'basedir'   => $templateDirectory,
            'cache_dir' => $cacheDirectory,
        ]);
        $layout = 'base.pug';
        file_put_contents(
            $templateDirectory.DIRECTORY_SEPARATOR.$layout,
            "p in base\nblock content"
        );
        $child = 'child.pug';
        file_put_contents(
            $templateDirectory.DIRECTORY_SEPARATOR.$child,
            "extends base\nblock content\n  p in child"
        );
        $render = function ($path) use ($renderer) {
            ob_start();
            $renderer->displayFile($path);
            $html = ob_get_contents();
            ob_end_clean();

            return $html;
        };

        self::assertSame('<p>in base</p><p>in child</p>', $render($child));

        touch($templateDirectory.DIRECTORY_SEPARATOR.$child, time() - 3600);
        file_put_contents(
            $templateDirectory.DIRECTORY_SEPARATOR.$layout,
            "p in base updated!\nblock content"
        );
        touch($templateDirectory.DIRECTORY_SEPARATOR.$layout, time() + 3600);
        clearstatcache();

        self::assertSame('<p>in base updated!</p><p>in child</p>', $render($child));

        foreach (scandir($cacheDirectory) as $file) {
            if (substr($file, -22) === '.imports.serialize.txt') {
                unlink($cacheDirectory.DIRECTORY_SEPARATOR.$file);
            }
        }

        file_put_contents(
            $templateDirectory.DIRECTORY_SEPARATOR.$layout,
            "p in base updated twice!\nblock content"
        );
        touch($templateDirectory.DIRECTORY_SEPARATOR.$layout, time() + 3600);
        clearstatcache();

        self::assertSame('<p>in base updated twice!</p><p>in child</p>', $render($child));

        foreach ([$cacheDirectory, $templateDirectory] as $directory) {
            $this->emptyDirectory($directory);
        }
    }

    /**
     * @covers ::<public>
     * @covers ::getCachePath
     * @covers ::getRawCachePath
     * @covers ::isCacheUpToDate
     * @covers ::checkPathExpiration
     * @covers ::getCacheDirectory
     * @covers \Phug\Renderer\AbstractAdapter::<public>
     * @covers \Phug\Renderer\Partial\FileSystemTrait::fileMatchExtensions
     * @covers \Phug\Renderer\Partial\FileSystemTrait::scanDirectory
     * @covers \Phug\Renderer\Partial\AdapterTrait::getSandboxCall
     * @covers \Phug\Renderer\Partial\AdapterTrait::handleHtmlEvent
     * @covers \Phug\Renderer\Partial\AdapterTrait::callAdapter
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::cacheFileContents
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::reInitCompiler
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::parseCliDirectoriesInput
     */
    public function testCacheWithDisplay()
    {
        $renderer = new Renderer([
            'cache_dir' => sys_get_temp_dir(),
        ]);
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test.pug';
        file_put_contents($path, 'p=$message');

        ob_start();
        $renderer->display('section=$message', [
            'message' => 'Hi',
        ]);
        $actual = str_replace(
            "\r",
            '',
            trim(ob_get_contents())
        );
        ob_end_clean();
        unlink($path);

        self::assertSame('<section>Hi</section>', $actual);
    }

    /**
     * @covers \Phug\Renderer\Partial\CacheTrait::getCacheAdapter
     * @covers \Phug\Renderer\Partial\CacheTrait::cacheDirectory
     * @covers \Phug\Renderer\Partial\AdapterTrait::initAdapter
     * @covers \Phug\Renderer\Partial\AdapterTrait::expectCacheAdapter
     * @covers \Phug\Renderer\Partial\AdapterTrait::setAdapterClassName
     * @covers \Phug\Renderer\Partial\AdapterTrait::getAdapter
     * @covers \Phug\Renderer\Partial\AdapterTrait::getSandboxCall
     * @covers \Phug\Renderer\Partial\AdapterTrait::handleHtmlEvent
     * @covers \Phug\Renderer\Partial\AdapterTrait::callAdapter
     * @covers \Phug\Renderer\Partial\FileSystemTrait::scanDirectory
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::parseCliDirectoriesInput
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::compileAndCache
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::normalizePath
     */
    public function testCacheIncompatibility()
    {
        $cacheDirectory = sys_get_temp_dir().'/c'.mt_rand(0, 9999999);
        @mkdir($cacheDirectory, 0777, true);

        $renderer = new Renderer([
            'adapter_class_name' => StreamAdapter::class,
            'cache_dir'          => $cacheDirectory,
        ]);

        $renderer->render('foo');

        self::assertInstanceOf(FileAdapter::class, $renderer->getAdapter());
        self::assertSame(FileAdapter::class, $renderer->getOption('adapter_class_name'));

        $renderer = new Renderer([
            'adapter_class_name' => StreamAdapter::class,
            'cache_dir'          => $cacheDirectory,
        ]);

        $emptyDirectory = $cacheDirectory.'/d'.mt_rand(0, 9999999);
        @mkdir($emptyDirectory, 0777, true);
        $renderer->cacheDirectory($emptyDirectory);
        @rmdir($emptyDirectory);

        self::assertInstanceOf(FileAdapter::class, $renderer->getAdapter());
        self::assertSame(FileAdapter::class, $renderer->getOption('adapter_class_name'));
    }

    /**
     * @covers ::cache
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::cacheFileContents
     */
    public function testCacheErrorTrace()
    {
        $directory = sys_get_temp_dir().'/pug'.mt_rand(0, 99999999);
        $this->createEmptyDirectory($directory);

        $options = [
            'debug'         => true,
            'cache_dir'     => $directory,
            'error_handler' => function (\Exception $error) use (&$lastError) {
                $lastError = $error->getMessage();
            },
        ];
        $lastError = null;
        $renderer = new Renderer($options);
        $path = $directory.DIRECTORY_SEPARATOR.'test.pug';
        $code = "body\n\n  section\n\n    p=1/\$count\n\n  div\n";
        file_put_contents($path, $code);
        touch($path, time() - 10000);
        clearstatcache();

        $renderer->renderFile($path, [
            'count' => 1,
        ]);

        self::assertSame(null, $lastError);

        $cachedFiles = glob($directory.'/*.php');
        self::assertCount(1, $cachedFiles);

        touch($cachedFiles[0], time() - 5000);
        clearstatcache();

        $lastError = null;
        $GLOBALS['debug'] = true;
        $renderer = new Renderer($options);
        $renderer->renderFile($path, [
            'count' => 0,
        ]);

        self::assertStringContains('on line 5, offset 6', $lastError);
    }

    /**
     * @covers ::<public>
     * @covers ::cache
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::cacheFileContents
     */
    public function testCacheRenderString()
    {
        $directory = sys_get_temp_dir().'/pug'.mt_rand(0, 99999999);
        $this->createEmptyDirectory($directory);

        $options = [
            'debug'         => true,
            'cache_dir'     => $directory,
        ];
        $lastError = null;
        $renderer = new Renderer($options);

        $html = $renderer->render('p Hello');

        self::assertSame('<p>Hello</p>', $html);

        $html = $renderer->render('div Bye');

        self::assertSame('<div>Bye</div>', $html);
    }

    /**
     * @covers                \Phug\Renderer\Partial\FileSystemTrait::scanDirectory
     * @covers                \Phug\Renderer\Partial\CacheTrait::getCacheAdapter
     * @covers                \Phug\Renderer\Partial\CacheTrait::cacheDirectory
     * @covers                \Phug\Renderer\Adapter\FileAdapter::cacheDirectory
     * @covers                \Phug\Renderer\Adapter\FileAdapter::getCacheDirectory
     * @covers                \Phug\Renderer\Partial\Debug\DebuggerTrait::getDebuggedException
     *
     * @expectedException     \RuntimeException
     *
     * @expectedExceptionCode 5
     */
    public function testMissingDirectory()
    {
        $renderer = new Renderer([
            'exit_on_error' => false,
            'cache_dir'     => '///cannot/be/created',
        ]);
        $renderer->render(__DIR__.'/../../cases/attrs.pug');
    }

    /**
     * @covers                \Phug\Renderer\Partial\FileSystemTrait::scanDirectory
     * @covers                \Phug\Renderer\Partial\CacheTrait::getCacheAdapter
     * @covers                \Phug\Renderer\Partial\CacheTrait::cacheDirectory
     * @covers                \Phug\Renderer\Adapter\FileAdapter::cacheDirectory
     * @covers                \Phug\Renderer\Adapter\FileAdapter::cache
     * @covers                \Phug\Renderer\Adapter\FileAdapter::displayCached
     *
     * @expectedException     \RuntimeException
     *
     * @expectedExceptionCode 6
     */
    public function testReadOnlyDirectory()
    {
        $renderer = new Renderer([
            'exit_on_error' => false,
            'cache_dir'     => static::getReadOnlyDirectory(),
        ]);
        $renderer->render(__DIR__.'/../../cases/attrs.pug');
    }

    /**
     * @covers \Phug\Renderer\Partial\FileSystemTrait::scanDirectory
     * @covers \Phug\Renderer\Partial\FileSystemTrait::scanDirectories
     * @covers \Phug\Renderer\Partial\FileSystemTrait::emptyDirectory
     * @covers \Phug\Renderer\Partial\CacheTrait::getCacheAdapter
     * @covers \Phug\Renderer\Partial\CacheTrait::cacheDirectory
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::handleOptionAliases
     * @covers \Phug\Renderer\Partial\FileSystemTrait::fileMatchExtensions
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::cacheFileContents
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::reInitCompiler
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::parseCliDirectoriesInput
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::normalizePath
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::compileAndCache
     * @covers \Phug\Renderer\Task\TasksGroup::<public>
     * @covers ::cacheDirectory
     */
    public function testCacheDirectory()
    {
        $cacheDirectory = sys_get_temp_dir().'/pug-test'.mt_rand(0, 99999);
        $this->createEmptyDirectory($cacheDirectory);

        $templatesDirectory = __DIR__.'/../../utils';
        $renderer = new Renderer([
            'exit_on_error' => false,
            'basedir'       => $templatesDirectory,
            'cache_dir'     => $cacheDirectory,
        ]);
        list($success, $errors, $errorDetails) = $renderer->cacheDirectory($templatesDirectory);
        $filesCount = count(array_filter(scandir($cacheDirectory), function ($file) {
            return $file !== '.' &&
                $file !== '..' &&
                pathinfo($file, PATHINFO_EXTENSION) !== 'txt' &&
                $file !== 'registry.php';
        }));
        $expectedCount = count(array_filter(array_merge(
            scandir($templatesDirectory),
            scandir($templatesDirectory.'/subdirectory'),
            scandir($templatesDirectory.'/subdirectory/subsubdirectory')
        ), function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'pug';
        }));

        self::assertFileExists("$cacheDirectory/registry.php", 'Cache directory should contain a registry.php file.');

        list($errSuccess, $errErrors, $errErrorDetails) = $renderer->cacheDirectory(__DIR__.'/../../errored');
        $this->removeFile($cacheDirectory);

        self::assertSame(
            $expectedCount,
            $success + $errors,
            'Each .pug file in the directory to cache should generate a success or an error.'
        );
        self::assertSame(
            $success,
            $filesCount,
            'Each file successfully cached should be in the cache directory.'
        );
        self::assertCount($errErrors, $errErrorDetails, 'Each error should match a detailed message.');

        self::assertSame(0, $errSuccess);
        self::assertSame(2, $errErrors);
        self::assertStringEndsWith('errored', $errErrorDetails[0]['directory']);
        self::assertStringEndsWith('errored.pug', $errErrorDetails[0]['inputFile']);
        self::assertStringContains(
            'Inconsistent indentation. Expecting either 0 or 4 spaces/tabs.',
            $errErrorDetails[0]['error']->getMessage()
        );
    }

    /**
     * @covers \Phug\Renderer\Partial\CacheTrait::getCacheAdapter
     * @covers \Phug\Renderer\Partial\CacheTrait::cacheFile
     * @covers \Phug\Renderer\Partial\CacheTrait::cacheFileIfChanged
     * @covers \Phug\Renderer\Adapter\FileAdapter::cacheFile
     * @covers \Phug\Renderer\Adapter\FileAdapter::cacheFileIfChanged
     */
    public function testCacheFile()
    {
        $cacheDirectory = sys_get_temp_dir().'/pug-test'.mt_rand(0, 99999);
        $this->createEmptyDirectory($cacheDirectory);

        $templatesDirectory = __DIR__.'/../../utils';
        $renderer = new Renderer([
            'basedir'   => $templatesDirectory,
            'cache_dir' => $cacheDirectory,
        ]);

        $freshResult = $renderer->cacheFileIfChanged($templatesDirectory.'/subdirectory/scripts.pug');

        foreach (glob($cacheDirectory.'/*.php') as $file) {
            touch($file, time() + 3600);
        }

        $cachedResult = $renderer->cacheFileIfChanged($templatesDirectory.'/subdirectory/scripts.pug');

        foreach (glob($cacheDirectory.'/*.php') as $file) {
            touch($file, time() + 3600);
        }

        $forceRefreshResult = $renderer->cacheFile($templatesDirectory.'/subdirectory/scripts.pug');

        self::assertTrue($freshResult);
        self::assertTrue($cachedResult);
        self::assertTrue($forceRefreshResult);
    }

    /**
     * Test cacheDirectory method dependencies.
     *
     * @covers \Phug\Renderer\Adapter\FileAdapter::cacheDirectory
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::initCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::synchronizeEvent
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::createCompiler
     */
    public function testCacheDirectoryPreserveRendererDependencies()
    {
        $cacheDirectory = sys_get_temp_dir().'/phug-test'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cacheDirectory);

        $templatesDirectory = __DIR__.'/../../for-cache';
        $renderer = new Renderer([
            'modules'   => [JsPhpizePhug::class],
            'paths'     => [$templatesDirectory],
            'cache_dir' => $cacheDirectory,
        ]);
        $renderer->cacheDirectory($templatesDirectory);
        $files = array_values(array_filter(glob("$cacheDirectory/*.php"), function ($file) {
            return basename($file) !== 'registry.php';
        }));
        $file = count($files) ? file_get_contents($files[0]) : null;
        $this->emptyDirectory($cacheDirectory);
        rmdir($cacheDirectory);

        self::assertNotNull($file);

        $foo = ['bar' => 'biz'];
        ob_start();
        eval('?>'.$file);
        $contents = trim(ob_get_contents());
        ob_end_clean();

        self::assertSame('<p>biz</p>', $contents);
    }

    /**
     * Test cacheDirectory method dependencies.
     */
    public function testCacheDirectoryPreserveCompilerDependencies()
    {
        $cacheDirectory = sys_get_temp_dir().'/phug-test'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cacheDirectory);

        $templatesDirectory = __DIR__.'/../../for-cache';
        $renderer = new Renderer([
            'paths'     => [$templatesDirectory],
            'cache_dir' => $cacheDirectory,
        ]);
        $compiler = $renderer->getCompiler();
        $compiler->addModule(new JsPhpizePhug($compiler));
        $renderer->cacheDirectory($templatesDirectory);
        $files = array_values(array_filter(glob("$cacheDirectory/*.php"), function ($file) {
            return basename($file) !== 'registry.php';
        }));
        $file = count($files) ? file_get_contents($files[0]) : null;
        $this->removeFile($cacheDirectory);

        self::assertNotNull($file);

        $foo = ['bar' => 'biz'];
        ob_start();
        eval('?>'.$file);
        $contents = trim(ob_get_contents());
        ob_end_clean();

        self::assertSame('<p>biz</p>', $contents);
    }

    /**
     * @covers ::registerCachedFile
     * @covers ::getRegistryPath
     * @covers ::cacheDirectory
     * @covers ::isCacheUpToDate
     * @covers ::checkPathExpiration
     * @covers \Phug\Renderer\Partial\RegistryTrait::findCachePathInRegistryFile
     * @covers \Phug\Renderer\Partial\RegistryTrait::findCachePathInRegistry
     * @covers \Phug\Renderer\Partial\RegistryTrait::findInRegistry
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensions
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensionsOnFileKey
     * @covers \Phug\Renderer\Partial\RegistryTrait::getFirstRegistryIndex
     * @covers \Phug\Renderer\Partial\RegistryTrait::getRegistryPathChunks
     * @covers \Phug\Renderer\Partial\AdapterTrait::initAdapterLinkToCompiler
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::cacheFileContents
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::reInitCompiler
     * @covers \Phug\Renderer\Partial\FileAdapterCacheToolsTrait::parseCliDirectoriesInput
     */
    public function testRemoveViewsDirectoryAfterCaching()
    {
        $cacheDirectory = sys_get_temp_dir().'/phug-cache-'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cacheDirectory);
        $appViewsDirectory = sys_get_temp_dir().'/phug-app-views-'.mt_rand(0, 999999);
        $this->createEmptyDirectory("$appViewsDirectory/foo/bar");
        $vendorViewsDirectory = sys_get_temp_dir().'/phug-vendor-views-'.mt_rand(0, 999999);
        $this->createEmptyDirectory("$vendorViewsDirectory/lib");

        file_put_contents("$appViewsDirectory/foo/bar/page.pug", "h1 Page\ninclude /lib/widget");
        file_put_contents("$vendorViewsDirectory/lib/widget.pug", 'div widget');

        $renderer = new Renderer([
            'up_to_date_check' => false,
            'paths'            => [$appViewsDirectory, $vendorViewsDirectory],
            'cache_dir'        => $cacheDirectory,
        ]);
        $renderer->cacheDirectory("[$appViewsDirectory,$vendorViewsDirectory]");
        $this->removeFile($appViewsDirectory);
        $this->removeFile($vendorViewsDirectory);

        $html = $renderer->renderFile('foo/bar/page.pug');

        self::assertSame('<h1>Page</h1><div>widget</div>', $html);
    }

    /**
     * @covers ::getRegistryPath
     * @covers ::locate
     * @covers \Phug\Renderer\Partial\RegistryTrait::findInRegistry
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensions
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensionsOnFileKey
     * @covers \Phug\Renderer\Partial\RegistryTrait::findCachePathInRegistry
     * @covers \Phug\Renderer\Partial\RegistryTrait::getFirstRegistryIndex
     */
    public function testUpperLocator()
    {
        $compiler = new Compiler();

        if (!($compiler instanceof Compiler\WithUpperLocatorInterface)) {
            self::markTestSkipped('Requires WithUpperLocatorInterface interface.');
        }

        $cacheDirectory = sys_get_temp_dir().'/phug-cache-'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cacheDirectory);
        $compiler->setUpperLocator(new FileAdapter($this->renderer, [
            'up_to_date_check' => false,
            'cache_dir'        => $cacheDirectory,
        ]));
        $setRegistry = function ($registry) use ($cacheDirectory) {
            file_put_contents("$cacheDirectory/registry.php", '<?php return '.var_export($registry, true).';');
        };
        $setRegistry([]);

        self::assertNull($compiler->locate('foo'));

        $setRegistry([
            'f:foo' => 'xy',
        ]);

        self::assertSame("$cacheDirectory/xy", $compiler->locate('foo'));

        $setRegistry([
            'f:foo' => [],
        ]);

        self::assertNull($compiler->locate('foo'));
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Renderer\AbstractAdapter::execute
     */
    public function testThisOverride()
    {
        $cacheDirectory = sys_get_temp_dir().'/phug-cache-'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cacheDirectory);
        $renderer = new Renderer([
            'cache_dir'          => $cacheDirectory,
            'adapter_class_name' => FileAdapter::class,
        ]);

        self::assertSame('<p>2020-02</p>', $renderer->render('p=$this->format("Y-m")', [
            'this' => new DateTime('2020-02-05'),
        ]));

        $file = sys_get_temp_dir().'/pug-'.mt_rand(0, 999999).'.pug';
        file_put_contents($file, 'p=$this->format("Y-m")');
        $render1 = $renderer->renderFile($file, [
            'this' => new DateTime('2020-02-05'),
        ]);
        touch($file, time() - 3600);
        $render2 = $renderer->renderFile($file, [
            'this' => new DateTime('2020-02-05'),
        ]);
        unlink($file);

        self::assertSame('<p>2020-02</p>', $render1);
        self::assertSame('<p>2020-02</p>', $render2);
    }
}
