<?php

namespace Phug\Test;

use Phug\Optimizer;

/**
 * @coversDefaultClass \Phug\Optimizer
 */
class OptimizerTest extends AbstractPhugTest
{
    /**
     * @group i
     * @covers ::__construct
     * @covers ::resolve
     */
    public function testOptions()
    {
        $optimizer = new Optimizer([
            'debug' => false,
            'basedir' => __DIR__ .'/../templates/dir1',
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
     * @covers ::resolve
     */
    public function testUpToDateCheck()
    {
        $optimizer = new Optimizer([
            'debug' => false,
            'base_dir' => __DIR__.'/../templates/dir2',
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
            'debug' => false,
            'basedir' => __DIR__ .'/../templates/dir1',
            'base_dir' => __DIR__.'/../templates/dir2',
            'cache' => $cache,
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
}
