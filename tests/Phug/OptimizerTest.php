<?php

namespace Phug\Test;

use Phug\Optimizer;
use Phug\Phug;

/**
 * @coversDefaultClass \Phug\Optimizer
 */
class OptimizerTest extends AbstractPhugTest
{
    public function tearDown()
    {
        Phug::reset();
    }

    /**
     * @group i
     * @covers ::__construct
     * @covers ::isExpired
     * @covers ::resolve
     */
    public function testOptions()
    {
        $optimizer = new Optimizer([
            'debug'    => false,
            'basedir'  => __DIR__.'/../templates/dir1',
            'base_dir' => __DIR__.'/../templates/dir2',
        ]);

        self::assertSame(
            realpath(__DIR__.'/../templates/dir1/file1.pug'),
            $optimizer->resolve('file1.pug')
        );
        self::assertSame(
            realpath(__DIR__.'/../templates/dir2/file2.pug'),
            $optimizer->resolve('file2.pug')
        );
        self::assertSame(
            true,
            $optimizer->isExpired('file2.pug')
        );
    }

    /**
     * @group i
     * @covers ::__construct
     * @covers ::isExpired
     * @covers ::resolve
     */
    public function testUpToDateCheck()
    {
        $optimizer = new Optimizer([
            'debug'            => false,
            'base_dir'         => __DIR__.'/../templates/dir2',
            'up_to_date_check' => false,
        ]);

        self::assertSame(
            false,
            $optimizer->isExpired('file2.pug')
        );
    }

    /**
     * @group i
     * @covers ::__construct
     * @covers ::hashPrint
     * @covers ::hasExpiredImport
     * @covers ::isExpired
     * @covers ::displayFile
     * @covers ::renderFile
     */
    public function testCache()
    {
        $cache = sys_get_temp_dir().'/foo'.mt_rand(0, 999999);
        file_exists($cache)
            ? static::emptyDirectory($cache)
            : mkdir($cache);
        $optimizer = new Optimizer([
            'debug'    => false,
            'basedir'  => __DIR__.'/../templates/dir1',
            'base_dir' => __DIR__.'/../templates/dir2',
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

        self::assertContains('<p>A</p>', $contents);
        self::assertContains('<p>B</p>', $contents);

        static::emptyDirectory($cache);
        rmdir($cache);
    }

    /**
     * @group i
     * @covers ::resolve
     * @covers ::hasExpiredImport
     * @covers ::isExpired
     * @covers ::displayFile
     */
    public function testImports()
    {
        $cache = sys_get_temp_dir().'/foo'.mt_rand(0, 999999);
        $templates = sys_get_temp_dir().'/templates'.mt_rand(0, 999999);
        file_exists($cache)
            ? static::emptyDirectory($cache)
            : mkdir($cache);
        file_exists($templates)
            ? static::emptyDirectory($templates)
            : mkdir($templates);
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

        static::emptyDirectory($cache);
        rmdir($cache);
        static::emptyDirectory($templates);
        rmdir($templates);
    }
}
