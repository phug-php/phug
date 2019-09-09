<?php

namespace Phug\Test;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Renderer;
use Phug\RendererException;

/**
 * @coversDefaultClass Phug\AbstractRendererModule
 */
class RendererModuleTest extends TestCase
{
    public function testModule()
    {
        include_once __DIR__.'/Utils/TestRendererModule.php';
        $module = new TestRendererModule(new Formatter());

        self::assertTrue(is_array($module->getEventListeners()));
    }

    /**
     * @covers \Phug\Renderer::__construct
     * @covers \Phug\Renderer\Event\RenderEvent::<public>
     */
    public function testRenderEvent()
    {
        $renderer = new Renderer([
            'on_render' => function (Renderer\Event\RenderEvent $event) {
                if ($event->getMethod() === 'display') {
                    $event->setMethod('render');
                }
            },
        ]);

        self::assertSame('<div></div>', $renderer->display('div'));

        $renderer = new Renderer([
            'on_render' => function (Renderer\Event\RenderEvent $event) {
                $event->setParameters(array_merge($event->getParameters(), [
                    'foo' => 'new',
                ]));
            },
        ]);

        self::assertSame('<p>new</p><p>baz</p>', $renderer->render("p=\$foo\np=\$bar", [
            'foo' => 'foo',
            'bar' => 'baz',
        ]));

        $renderer = new Renderer([
            'on_render' => function (Renderer\Event\RenderEvent $event) {
                $event->setInput('div: '.$event->getInput());
            },
        ]);

        self::assertSame('<div><p></p></div>', $renderer->render('p'));

        $renderer = new Renderer([
            'on_render' => function (Renderer\Event\RenderEvent $event) {
                $event->setPath($event->getPath().'/basic.pug');
            },
        ]);

        self::assertSame(
            '<html><body><h1>Title</h1></body></html>',
            $renderer->renderFile(__DIR__.'/../cases')
        );
    }

    /**
     * @covers \Phug\Renderer::__construct
     * @covers \Phug\Renderer\Event\HtmlEvent::<public>
     */
    public function testHtmlEvent()
    {
        $source = null;
        $renderer = new Renderer([
            'exit_on_error' => false,
            'on_html'       => function (Renderer\Event\HtmlEvent $event) use (&$source) {
                if ($event->getResult() === '<div></div>') {
                    $event->setError(new \Exception('Empty div'));
                    $source = $event->getRenderEvent()->getInput();
                }
            },
        ]);

        self::assertSame('<p></p>', $renderer->render('p'));
        self::assertNull($source);

        $message = null;

        try {
            $renderer->render('div');
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
        }

        self::assertSame('Empty div', $message);
        self::assertSame('div', $source);

        $renderer = new Renderer([
            'on_html' => function (Renderer\Event\HtmlEvent $event) {
                if ($event->getError()) {
                    $event->setError(null);
                    $event->setResult(false);
                }
            },
        ]);

        self::assertFalse($renderer->render('p=1/0'));

        $renderer = new Renderer([
            'on_html' => function (Renderer\Event\HtmlEvent $event) {
                if ($event->getBuffer() === '<div></div>') {
                    $event->setBuffer('<p>Empty div</p>');
                }
            },
        ]);

        ob_start();
        $renderer->display('div');
        $contents = ob_get_contents();
        ob_end_clean();

        self::assertSame('<p>Empty div</p>', $contents);
    }

    /**
     * @covers \Phug\Renderer::__construct
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::enableModules
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::enableModule
     */
    public function testModulePropagation()
    {
        include_once __DIR__.'/Utils/TestCompilerModule.php';
        include_once __DIR__.'/Utils/TestFormatterModule.php';
        include_once __DIR__.'/Utils/TestParserModule.php';
        include_once __DIR__.'/Utils/TestLexerModule.php';

        $renderer = new Renderer([
            'modules' => [
                TestCompilerModule::class,
                TestFormatterModule::class,
                TestParserModule::class,
                TestLexerModule::class,
            ],
        ]);

        self::assertSame([TestCompilerModule::class], $renderer->getOption('compiler_modules'));
        self::assertSame([TestFormatterModule::class], $renderer->getOption('formatter_modules'));
        self::assertSame([TestParserModule::class], $renderer->getOption('parser_modules'));
        self::assertSame([TestLexerModule::class], $renderer->getOption('lexer_modules'));
    }

    public function testMissingMixin()
    {
        include_once __DIR__.'/Utils/TestCompilerModule.php';
        include_once __DIR__.'/Utils/TestFormatterModule.php';
        include_once __DIR__.'/Utils/TestParserModule.php';
        include_once __DIR__.'/Utils/TestLexerModule.php';

        $renderer = new Renderer([
            'exit_on_error' => false,
            'debug'         => true,
            'color_support' => false,
        ]);
        $exception = null;

        try {
            $renderer->renderFile(__DIR__.'/../fixtures/missing-mixin.pug');
        } catch (\InvalidArgumentException $e) {
            $exception = $e;
        } catch (\Exception $e) {
            $exception = $e;
        } catch (\Throwable $e) {
            $exception = $e;
        }

        self::assertInstanceOf(RendererException::class, $exception);

        $message = str_replace("\r", '', trim($exception->getMessage()));

        self::assertStringStartsWith('InvalidArgumentException in ', $message);

        $message = array_slice(explode("\n", $message), 1);

        self::assertSame([
            'Unknown yolo mixin called. on line 5, offset 9',
            '',
            '    1 | div',
            '    2 |     | Foo',
            '    3 | section',
            '    4 |     .foo',
            '>   5 |         +yolo()',
            '----------------^',
            '    6 | ',
            '    7 |     footer End',
        ], $message);
    }
}
