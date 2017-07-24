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
     * @covers ::render
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
     * @covers ::display
     */
    public function testDisplay()
    {
        ob_start();
        Phug::display(__DIR__.'/../templates/test.pug');
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
    public function testDisplayString()
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
        static::assertSame(
            'WORD',
            Phug::render(':upper word')
        );
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
