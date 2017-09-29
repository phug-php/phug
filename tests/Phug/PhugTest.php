<?php

namespace Phug\Test;

use Phug\Phug;
use Phug\PhugException;

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
        self::assertSame(
            '<p>Hello world!</p>',
            Phug::renderFile(__DIR__.'/../templates/test.pug')
        );
    }

    /**
     * @covers ::render
     */
    public function testRender()
    {
        self::assertSame(
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
        self::assertSame(
            'bar',
            Phug::render('=$foo')
        );
    }

    /**
     * @covers ::getRenderer
     */
    public function testRenderWithOptions()
    {
        self::assertSame(
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
        self::assertSame(
            '<p>haha</p>',
            Phug::render('p=message', [
                'message' => 'Hello',
                'hidden'  => 'haha',
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

        self::assertSame(
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

        self::assertSame(
            '<section><div></div></section>',
            $actual
        );
    }

    /**
     * @covers ::reset
     * @covers ::getRenderer
     * @covers ::normalizeFilterName
     * @covers ::hasFilter
     * @covers ::setFilter
     * @covers ::addFilter
     * @covers ::replaceFilter
     * @covers ::removeFilter
     * @covers ::getFilter
     * @covers ::getFilters
     */
    public function testFilters()
    {
        self::assertFalse(Phug::hasFilter('upper'));
        self::assertFalse(Phug::hasFilter('Upper'));
        Phug::addFilter('upper', function ($contents) {
            return strtoupper($contents);
        });
        self::assertSame('A', call_user_func(Phug::getFilter('upper'), 'a'));
        self::assertTrue(Phug::hasFilter('upper'));
        self::assertTrue(Phug::hasFilter('Upper'));
        self::assertTrue(array_key_exists('upper', Phug::getFilters()));
        self::assertSame(
            'WORD',
            Phug::render(':upper word')
        );
        Phug::removeFilter('upper');
        self::assertFalse(Phug::getRenderer()->hasOption(['filters', 'upper']));
        Phug::reset();
        self::assertFalse(Phug::hasFilter('upper'));
        self::assertFalse(Phug::hasFilter('Upper'));
        self::assertFalse(array_key_exists('upper', Phug::getFilters()));
        $message = null;

        try {
            Phug::setFilter('foo', 'bar');
        } catch (PhugException $exception) {
            $message = $exception->getMessage();
        }
        self::assertSame($message, 'Invalid foo filter given: it must be a callable or a class name.');
        $message = null;

        try {
            Phug::replaceFilter('foo', function () {
            });
        } catch (PhugException $exception) {
            $message = $exception->getMessage();
        }
        self::assertSame($message, 'Filter foo is not set.');
        Phug::addFilter('foo', function () {
        });
        $message = null;

        try {
            Phug::addFilter('foo', function () {
            });
        } catch (PhugException $exception) {
            $message = $exception->getMessage();
        }
        self::assertSame($message, 'Filter foo is already set.');
        Phug::replaceFilter('foo', function () {
            return 'new';
        });
        self::assertSame('new', call_user_func(Phug::getFilter('foo')));
        Phug::removeFilter('foo');
        self::assertFalse(Phug::hasFilter('foo'));
    }

    /**
     * @covers ::reset
     * @covers ::getRenderer
     * @covers ::normalizeKeywordName
     * @covers ::hasKeyword
     * @covers ::setKeyword
     * @covers ::addKeyword
     * @covers ::replaceKeyword
     * @covers ::removeKeyword
     * @covers ::getKeyword
     * @covers ::getKeywords
     */
    public function testKeywords()
    {
        self::assertFalse(Phug::hasKeyword('foo'));
        self::assertFalse(Phug::hasKeyword('Foo'));
        Phug::addKeyword('foo', function () {
            return 'bar';
        });
        self::assertSame('bar', call_user_func(Phug::getKeyword('foo')));
        self::assertTrue(Phug::hasKeyword('foo'));
        self::assertTrue(Phug::hasKeyword('Foo'));
        self::assertTrue(array_key_exists('foo', Phug::getKeywords()));
        self::assertSame(
            'bar',
            Phug::render('foo')
        );
        Phug::reset();
        self::assertFalse(Phug::hasKeyword('foo'));
        self::assertFalse(Phug::hasKeyword('Foo'));
        self::assertFalse(array_key_exists('foo', Phug::getKeywords()));
        self::assertSame(
            '<foo></foo>',
            Phug::render('foo')
        );
        $message = null;

        try {
            Phug::setKeyword('foo', 'bar');
        } catch (PhugException $exception) {
            $message = $exception->getMessage();
        }
        self::assertSame($message, 'Invalid foo keyword given: it must be a callable or a class name.');
        $message = null;

        try {
            Phug::replaceKeyword('foo', function () {
            });
        } catch (PhugException $exception) {
            $message = $exception->getMessage();
        }
        self::assertSame($message, 'Keyword foo is not set.');
        Phug::addKeyword('foo', function () {
        });
        $message = null;

        try {
            Phug::addKeyword('foo', function () {
            });
        } catch (PhugException $exception) {
            $message = $exception->getMessage();
        }
        self::assertSame($message, 'Keyword foo is already set.');
        Phug::replaceKeyword('foo', function () {
            return 'new';
        });
        self::assertSame('new', call_user_func(Phug::getKeyword('foo')));
        Phug::removeKeyword('foo');
        self::assertFalse(Phug::hasKeyword('foo'));
        self::assertSame(
            '<foo></foo>',
            Phug::render('foo')
        );
    }

    /**
     * @covers                   ::setFilter
     * @expectedException        \Phug\PhugException
     * @expectedExceptionMessage Invalid foo filter given:
     */
    public function testWrongFilter()
    {
        Phug::setFilter('foo', 'not-a-filter');
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
        self::assertSame(
            [VerbatimExtension::class],
            Phug::getExtensions()
        );
        Phug::removeExtension(VerbatimExtension::class);
        self::assertFalse(Phug::hasExtension(VerbatimExtension::class));
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
