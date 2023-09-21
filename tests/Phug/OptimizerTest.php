<?php

namespace Phug\Test;

use Phug\Optimizer;
use Phug\Phug;
use Phug\Reader;
use Phug\RendererException;
use Phug\Test\Utils\Context;
use Phug\Test\Utils\CustomFacade;
use Phug\Test\Utils\CustomRenderer;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Optimizer
 */
class OptimizerTest extends AbstractPhugTest
{
    public function finishTest()
    {
        Phug::reset();
    }

    /**
     * @covers ::__construct
     * @covers ::isExpired
     * @covers ::resolve
     * @covers ::getExtensions
     * @covers \Phug\Renderer\Partial\RegistryTrait::findInRegistry
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensions
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensionsOnFileKey
     */
    public function testOptions()
    {
        $optimizer = new Optimizer([
            'debug'    => false,
            'basedir'  => __DIR__.'/../views/dir1',
            'base_dir' => __DIR__.'/../views/dir2',
        ]);

        self::assertSame(
            realpath(__DIR__.'/../views/dir1/file1.pug'),
            $optimizer->resolve('file1.pug')
        );
        self::assertSame(
            realpath(__DIR__.'/../views/dir2/file2.pug'),
            $optimizer->resolve('file2.pug')
        );
        self::assertSame(
            realpath(__DIR__.'/../views/dir2/file2.pug'),
            $optimizer->resolve('file2')
        );
        self::assertSame(
            true,
            $optimizer->isExpired('file2.pug')
        );
        self::assertSame(
            true,
            $optimizer->isExpired('file2')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::isExpired
     * @covers ::resolve
     * @covers ::getExtensions
     * @covers \Phug\Renderer\Partial\RegistryTrait::findInRegistry
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensions
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensionsOnFileKey
     */
    public function testUpToDateCheck()
    {
        $optimizer = new Optimizer([
            'debug'            => false,
            'base_dir'         => __DIR__.'/../views/dir2',
            'up_to_date_check' => false,
        ]);

        self::assertSame(
            false,
            $optimizer->isExpired('file2.pug')
        );
        self::assertSame(
            false,
            $optimizer->isExpired('file2')
        );
    }

    /**
     * @covers                   \Phug\Phug::cacheDirectory
     *
     * @expectedException        \InvalidArgumentException
     *
     * @expectedExceptionMessage Expected $options to be an array, got: 'biz'
     */
    public function testCacheDirectoryWithWrongOptions()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        Phug::cacheDirectory('foo', 'bar', 'biz');
    }

    /**
     * @covers ::getLocator
     * @covers ::isExpired
     * @covers ::getSourceAndCachePaths
     * @covers ::getCachePath
     * @covers ::getRawCachePath
     * @covers ::getRegistryPath
     * @covers ::getExtensions
     * @covers \Phug\Renderer\Partial\RegistryTrait::findInRegistry
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensions
     * @covers \Phug\Renderer\Partial\RegistryTrait::tryExtensionsOnFileKey
     * @covers \Phug\Renderer\Task\TasksGroup::<public>
     *
     * @throws RendererException
     */
    public function testUpToDateCheckCachePath()
    {
        $baseDir = __DIR__.'/../views/dir2';
        $cache = sys_get_temp_dir().'/foo'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cache);

        $options = [
            'debug'     => false,
            'cache_dir' => $cache,
            'paths'     => [$baseDir],
        ];

        Phug::renderFile('file2.pug', [], $options);

        $options['up_to_date_check'] = false;
        $optimizer = new Optimizer($options);
        $cachePath = null;
        $optimizer->isExpired('file2.pug', $cachePath);

        $contents = @file_get_contents($cachePath);

        $this->emptyDirectory($cache);

        self::assertSame(
            '<p>B</p>',
            $contents
        );

        Phug::textualCacheDirectory($baseDir, $cache, $options);

        $options['up_to_date_check'] = false;
        $optimizer = new Optimizer($options);
        rename(__DIR__.'/../views/dir2/file2.pug', __DIR__.'/file2.pug');
        $cachePath = null;
        $optimizer->isExpired('file2.pug', $cachePath);

        $contents = @file_get_contents($cachePath);

        self::assertSame(
            '<p>B</p>',
            $contents
        );

        $cachePath = null;
        $optimizer->isExpired('file2', $cachePath);

        $contents = @file_get_contents($cachePath);

        self::assertSame(
            '<p>B</p>',
            $contents
        );

        $options['extensions'] = ['', '.view'];
        $optimizer = new Optimizer($options);
        $cachePath = null;

        self::assertFalse($optimizer->isExpired('file2', $cachePath));
        self::assertFileDoesNotExist($cachePath);

        rename(__DIR__.'/file2.pug', __DIR__.'/../views/dir2/file2.pug');
        $options['up_to_date_check'] = true;
        $cachePath = null;
        $optimizer->isExpired('file2.pug', $cachePath);

        $contents = @file_get_contents($cachePath);

        $this->removeFile($cache);

        self::assertSame(
            '<p>B</p>',
            $contents
        );
    }

    /**
     * @covers ::__construct
     * @covers ::hasExpiredImport
     * @covers ::isExpired
     * @covers ::displayFile
     * @covers ::renderFile
     */
    public function testCache()
    {
        $cache = sys_get_temp_dir().'/foo'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cache);
        $optimizer = new Optimizer([
            'debug'    => false,
            'basedir'  => __DIR__.'/../views/dir1',
            'base_dir' => __DIR__.'/../views/dir2',
            'cache'    => $cache,
        ]);

        self::assertSame(
            '<p>A</p>',
            $optimizer->renderFile('file1.pug')
        );
        self::assertSame(
            '<p>B</p>',
            $optimizer->renderFile('file2.pug')
        );

        $contents = '';
        foreach (glob($cache.'/*.php') as $file) {
            $contents .= file_get_contents($file);
        }

        self::assertStringContains('<p>A</p>', $contents);
        self::assertStringContains('<p>B</p>', $contents);

        $this->removeFile($cache);
    }

    /**
     * @covers ::resolve
     * @covers ::hasExpiredImport
     * @covers ::isExpired
     * @covers ::displayFile
     */
    public function testImports()
    {
        $cache = sys_get_temp_dir().'/foo'.mt_rand(0, 999999);
        $templates = sys_get_temp_dir().'/views'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cache);
        $this->createEmptyDirectory($templates);
        file_put_contents($templates.'/foo.txt', 'include bar');
        touch($templates.'/foo.txt', time() - 3600);
        file_put_contents($templates.'/bar.txt', 'div bar');
        touch($templates.'/bar.txt', time() - 3600);
        $optimizer = new Optimizer([
            'debug'      => false,
            'extensions' => ['', '.txt'],
            'paths'      => [$templates],
            'cache'      => $cache,
        ]);

        self::assertSame(
            '<div>bar</div>',
            $optimizer->renderFile('foo')
        );

        file_put_contents($templates.'/bar.txt', 'div biz');
        touch($templates.'/bar.txt', time() - 3600);

        self::assertSame(
            '<div>bar</div>',
            $optimizer->renderFile('foo')
        );

        touch($templates.'/bar.txt', time() + 3600);

        self::assertSame(
            '<div>biz</div>',
            $optimizer->renderFile('foo')
        );

        file_put_contents($templates.'/bar.txt', 'p biz');
        touch($templates.'/bar.txt', time() - 3600);
        array_map('unlink', glob($cache.'/*.imports.serialize.txt'));

        self::assertSame(
            '<p>biz</p>',
            $optimizer->renderFile('foo')
        );
    }

    /**
     * @covers ::displayFile
     */
    public function testCustomRender()
    {
        include_once __DIR__.'/Utils/CustomRenderer.php';
        include_once __DIR__.'/Utils/CustomFacade.php';
        $optimizer = new Optimizer([
            'facade' => CustomFacade::class,
        ]);
        CustomFacade::setOutput('abc');

        self::assertSame(
            'abc',
            $optimizer->renderFile('foo')
        );

        $renderer = new CustomRenderer('def');
        $optimizer = new Optimizer([
            'renderer' => $renderer,
        ]);

        self::assertSame(
            'def',
            $optimizer->renderFile('foo')
        );

        $renderer = new CustomRenderer('ghi');
        $optimizer = new Optimizer([
            'render' => function ($path, $parameters) use ($renderer) {
                $renderer->displayFile($path, $parameters);
            },
        ]);

        self::assertSame(
            'ghi',
            $optimizer->renderFile('foo')
        );

        $optimizer = new Optimizer([
            'renderer_class_name' => CustomRenderer::class,
        ]);

        self::assertSame(
            'array',
            $optimizer->renderFile('foo')
        );

        $optimizer = new Optimizer([
            'facade' => Reader::class,
        ]);

        $error = null;
        ob_start();

        try {
            $optimizer->displayFile('foo');
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        ob_end_clean();

        self::assertSame(
            'No valid render method, renderer engine, renderer class or facade provided.',
            $error
        );
    }

    /**
     * @covers ::call
     * @covers ::displayFile
     */
    public function testStaticCall()
    {
        $cache = sys_get_temp_dir().'/foo'.mt_rand(0, 999999);
        $templates = sys_get_temp_dir().'/templates'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cache);
        $this->createEmptyDirectory($templates);
        file_put_contents($templates.'/foo.pug', '=$self["a"] + $self["b"] + $self["c"]');
        $options = [
            'shared_variables' => ['a' => 1],
            'globals'          => ['b' => 2],
            'self'             => true,
            'debug'            => false,
            'paths'            => [$templates],
            'cache'            => $cache,
        ];
        $optimizer = new Optimizer($options);

        self::assertSame(
            '6',
            $optimizer->renderFile('foo', ['c' => 3])
        );

        touch($templates.'/foo.pug', time() - 3600);

        self::assertSame(
            '6',
            $optimizer->renderFile('foo', ['c' => 3])
        );

        self::assertSame(
            '6',
            Optimizer::call('renderFile', ['foo', ['c' => 3]], $options)
        );
    }

    /**
     * @covers ::call
     * @covers ::displayFile
     */
    public function testThisBinding()
    {
        $cache = sys_get_temp_dir().'/foo'.mt_rand(0, 999999);
        $templates = sys_get_temp_dir().'/templates'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cache);
        $this->createEmptyDirectory($templates);
        file_put_contents($templates.'/foo.pug', '=$this->getInput()');
        $options = [
            'paths' => [$templates],
            'cache' => $cache,
        ];
        $optimizer = new Optimizer($options);

        self::assertSame(
            'abc',
            $optimizer->renderFile('foo', ['this' => new Context('abc')])
        );

        touch($templates.'/foo.pug', time() - 3600);

        self::assertSame(
            'def',
            $optimizer->renderFile('foo', ['this' => new Context('def')])
        );

        touch($templates.'/foo.pug', time() - 3600);

        self::assertSame(
            '25',
            Optimizer::call('renderFile', ['foo', ['this' => new Context(25)]], $options)
        );
    }
}
