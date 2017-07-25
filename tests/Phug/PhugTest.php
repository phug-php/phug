<?php

namespace Phug\Test;

use Phug\Phug;

/**
 * @coversDefaultClass \Phug\Phug
 */
class PhugTest extends AbstractPhugTest
{
    /**
     * @covers ::getRenderer
     * @covers ::renderFile
     */
    public function testRenderFile()
    {
        static::assertSame(
            '<p>Hello world!</p>',
            Phug::renderFile(__DIR__.'/../templates/test.pug')
        );
    }

    /**
     * @covers ::render
     */
    public function testRender()
    {
        static::assertSame(
            '<section><div></div></section>',
            Phug::render('section: div')
        );
    }

    /**
     * @covers ::__callStatic
     */
    public function testCallStatic()
    {
        Phug::share('foo', 'bar');
        static::assertSame(
            'bar',
            Phug::render('=$foo')
        );
    }

    /**
     * @covers ::getRenderer
     */
    public function testRenderWithOptions()
    {
        static::assertSame(
            '<p>Hello</p>',
            Phug::render('p=message', [
                'message' => 'Hello',
            ], [
                'patterns' => [
                    'transform_expression' => function ($expression) {
                        return '$'.$expression;
                    },
                ],
            ])
        );
        static::assertSame(
            '<p>haha</p>',
            Phug::render('p=message', [
                'message' => 'Hello',
                'hidden' => 'haha',
            ], [
                'patterns' => [
                    'transform_expression' => function () {
                        return '$hidden';
                    },
                ],
            ])
        );
    }

    /**
     * @covers ::displayFile
     */
    public function testDisplayFile()
    {
        ob_start();
        Phug::displayFile(__DIR__.'/../templates/test.pug');
        $actual = ob_get_contents();
        ob_end_clean();

        static::assertSame(
            '<p>Hello world!</p>',
            $actual
        );
    }

    /**
     * @covers ::display
     */
    public function testDisplay()
    {
        ob_start();
        Phug::display('section: div');
        $actual = ob_get_contents();
        ob_end_clean();

        static::assertSame(
            '<section><div></div></section>',
            $actual
        );
    }

    /**
     * @covers ::reset
     * @covers ::getRenderer
     * @covers ::normalizeFilterName
     * @covers ::hasFilter
     * @covers ::addFilter
     * @covers ::getFilters
     */
    public function testFilters()
    {
        self::assertFalse(Phug::hasFilter('upper'));
        self::assertFalse(Phug::hasFilter('up-per'));
        Phug::addFilter('upper', function ($contents) {
            return strtoupper($contents);
        });
        self::assertTrue(Phug::hasFilter('upper'));
        self::assertTrue(Phug::hasFilter('up-per'));
        self::assertTrue(array_key_exists('upper', Phug::getFilters()));
        static::assertSame(
            'WORD',
            Phug::render(':upper word')
        );
        Phug::reset();
        self::assertFalse(Phug::hasFilter('upper'));
        self::assertFalse(Phug::hasFilter('up-per'));
        self::assertFalse(array_key_exists('upper', Phug::getFilters()));
    }

    /**
     * @covers                   ::addFilter
     * @expectedException        \Phug\PhugException
     * @expectedExceptionMessage Invalid foo filter given:
     */
    public function testWrongFilter()
    {
        Phug::addFilter('foo', 'not-a-filter');
    }

    /**
     * @covers ::normalizeExtensionClassName
     * @covers ::hasExtension
     * @covers ::addExtension
     * @covers ::getExtensions
     */
    public function testExtensions()
    {
        self::assertFalse(Phug::hasExtension(VerbatimExtension::class));
        Phug::addExtension(VerbatimExtension::class);
        self::assertTrue(Phug::hasExtension(VerbatimExtension::class));
        static::assertSame(
            [VerbatimExtension::class],
            Phug::getExtensions()
        );
    }

    /**
     * @covers                   ::addExtension
     * @expectedException        \Phug\PhugException
     * @expectedExceptionMessage Invalid not-an-extension extension given:
     */
    public function testWrongExtension()
    {
        Phug::addExtension('not-an-extension');
    }
}
