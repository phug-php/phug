<?php

namespace Phug\Test;

use ErrorException;
use JsPhpize\JsPhpizePhug;
use Phug\Compiler\Event\NodeEvent;
use Phug\CompilerInterface;
use Phug\LexerException;
use Phug\Parser\Node\ElementNode;
use Phug\Renderer;
use Phug\Renderer\Adapter\EvalAdapter;
use Phug\Renderer\Adapter\FileAdapter;
use Phug\Renderer\Adapter\StreamAdapter;
use Phug\Renderer\AdapterInterface;
use Phug\RendererException;
use Phug\Test\Utils\BooleanAble;

/**
 * @coversDefaultClass \Phug\Renderer
 */
class RendererTest extends AbstractRendererTest
{
    /**
     * @covers ::compile
     * @covers ::render
     */
    public function testRender()
    {
        $actual = trim($this->renderer->render('#{"p"}.foo Hello'));

        self::assertSame('<p class="foo">Hello</p>', $actual);
    }

    /**
     * @covers ::renderFile
     * @covers ::render
     * @covers ::render
     */
    public function testRenderFile()
    {
        $actual = str_replace(
            ["\r", "\n"],
            '',
            trim($this->renderer->renderFile(__DIR__.'/../cases/code.pug'))
        );
        $expected = str_replace(
            ["\r", "\n"],
            '',
            trim(file_get_contents(__DIR__.'/../cases/code.html'))
        );

        self::assertSame($expected, $actual);
    }

    /**
     * @covers \Phug\Renderer\Partial\FileSystemTrait::fileMatchExtensions
     * @covers \Phug\Renderer\Partial\FileSystemTrait::scanDirectory
     * @covers ::renderAndWriteFile
     * @covers ::renderDirectory
     */
    public function testRenderDirectory()
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'foo'.mt_rand(0, 9999999);
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        list($success, $errors) = $this->renderer->renderDirectory(__DIR__.'/../utils', $directory);

        self::assertSame(2, $errors);
        self::assertSame(5, $success);
        self::assertFileExists($directory.'/subdirectory/scripts.html');
        self::assertFileExists($directory.'/subdirectory/subsubdirectory/basic.html');
        self::assertFileExists($directory.'/subdirectory/subsubdirectory/blanks.html');

        $this->emptyDirectory($directory);
        file_put_contents("$directory/foo.pug", 'p=foo');

        list($success, $errors) = $this->renderer->renderDirectory($directory, ['foo' => 'bar']);

        self::assertSame(0, $errors);
        self::assertSame(1, $success);
        self::assertFileExists("$directory/foo.html");
        self::assertSame('<p>bar</p>', trim(file_get_contents("$directory/foo.html")));

        $this->emptyDirectory($directory);
        file_put_contents("$directory/foo.pug", 'p=foo');

        list($success, $errors) = $this->renderer->renderDirectory($directory, null, ['foo' => 'bar']);

        self::assertSame(0, $errors);
        self::assertSame(1, $success);
        self::assertFileExists("$directory/foo.html");
        self::assertSame('<p>bar</p>', trim(file_get_contents("$directory/foo.html")));

        $this->emptyDirectory($directory);
        @rmdir($directory);
    }

    /**
     * @covers ::renderAndWriteFile
     */
    public function testRenderAndWriteFile()
    {
        self::assertFalse($this->renderer->renderAndWriteFile(__DIR__.'/../utils/error.pug', static::getReadOnlyDirectory().'/output.html'));
        self::assertFalse($this->renderer->renderAndWriteFile(__DIR__.'/../utils/error.pug', static::getReadOnlyDirectory().'/foobar/output.html'));
    }

    /**
     * @covers ::displayFile
     * @covers ::display
     */
    public function testDisplay()
    {
        ob_start();
        $this->renderer->display('#{"p"}.foo Hello');
        $actual = str_replace(
            "\r",
            '',
            trim(ob_get_contents())
        );
        ob_end_clean();

        self::assertSame('<p class="foo">Hello</p>', $actual);
    }

    /**
     * @covers ::displayFile
     */
    public function testDisplayFile()
    {
        ob_start();
        $this->renderer->displayFile(__DIR__.'/../cases/code.pug');
        $actual = str_replace(
            "\r",
            '',
            trim(ob_get_contents())
        );
        ob_end_clean();
        $expected = str_replace(
            "\r",
            '',
            trim(file_get_contents(__DIR__.'/../cases/code.html'))
        );

        self::assertSame($expected, $actual);
    }

    /**
     * @covers ::compileFile
     * @covers ::compile
     */
    public function testCompile()
    {
        $actual = trim($this->renderer->compile('p Hello'));

        self::assertSame('<p>Hello</p>', $actual);
    }

    /**
     * @covers ::compileFile
     * @covers ::compile
     */
    public function testCompileFile()
    {
        $actual = $this->renderer->compileFile(__DIR__.'/../cases/basic.pug');
        $actual = str_replace(
            "\r",
            '',
            trim($actual)
        );
        $expected = str_replace(
            "\r",
            '',
            trim(file_get_contents(__DIR__.'/../cases/basic.html'))
        );

        self::assertSame($expected, $actual);
    }

    /**
     * @covers ::__construct
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::initCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::synchronizeEvent
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::createCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::getDefaultOptions
     */
    public function testFilter()
    {
        $actual = str_replace(
            "\r",
            '',
            trim($this->renderer->render('script: :cdata foo'))
        );
        self::assertSame('<script><![CDATA[foo]]></script>', $actual);
    }

    /**
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::initCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::synchronizeEvent
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::createCompiler
     */
    public function testInitCompiler()
    {
        $renderer = new Renderer([
            'on_render' => function (Renderer\Event\RenderEvent $event) {
                $event->setInput(str_replace('section', 'blockquote', $event->getInput()));
            },
            'on_html' => function (Renderer\Event\HtmlEvent $event) {
                $event->setResult(str_replace('div', 'p', $event->getResult()));
            },
            'on_node' => function (NodeEvent $event) {
                $node = $event->getNode();
                if ($node instanceof ElementNode && $node->getName() === 'blockquote') {
                    $node->setName('div');
                }
            },
        ]);
        $html = trim($renderer->render('section: span'));

        self::assertSame('<p><span></span></p>', $html);

        $renderer->setOptions([
            'on_render' => function (Renderer\Event\RenderEvent $event) {
                $event->setInput(str_replace('span', 'strong', $event->getInput()));
            },
            'on_html' => function (Renderer\Event\HtmlEvent $event) {
                $event->setResult(str_replace('em', 'i', $event->getResult()));
            },
            'on_node' => function (NodeEvent $event) {
                $node = $event->getNode();
                if ($node instanceof ElementNode && $node->getName() === 'strong') {
                    $node->setName('em');
                }
            },
        ]);
        $renderer->initCompiler();
        $html = trim($renderer->render('section: span'));

        self::assertSame('<section><i></i></section>', $html);
    }

    /**
     * @covers ::__construct
     * @covers ::getCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::initCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::synchronizeEvent
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::createCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::handleOptionAliases
     */
    public function testBasedir()
    {
        $renderer = new Renderer([
            'basedir' => ['a'],
            'paths'   => ['b'],
        ]);
        $paths = $renderer->getCompiler()->getOption('paths');
        self::assertCount(2, $paths);
        self::assertContains('a', $paths);
        self::assertContains('b', $paths);
    }

    /**
     * @covers ::share
     * @covers ::resetSharedVariables
     * @covers ::mergeWithSharedVariables
     */
    public function testShare()
    {
        $this->renderer->share([
            'foo' => 1,
            'bar' => 2,
        ]);
        $this->renderer->share('foo', 4);

        $actual = str_replace(
            "\r",
            '',
            trim($this->renderer->render('=foo + bar'))
        );
        self::assertSame('6', $actual);

        $actual = str_replace(
            "\r",
            '',
            trim($this->renderer->render('=foo - bar'))
        );
        self::assertSame('2', $actual);

        $this->renderer->resetSharedVariables();
        $actual = str_replace(
            "\r",
            '',
            trim($this->renderer->render('=foo || bar ? "ok" : "ko"'))
        );
        self::assertSame('ko', $actual);
    }

    /**
     * @covers ::share
     * @covers ::resetSharedVariables
     * @covers ::mergeWithSharedVariables
     */
    public function testShareRecursion()
    {
        $foo = (object) [
            'biz' => 42,
        ];
        $bar = (object) [];
        $foo->bar = &$bar;
        $bar->foo = &$foo;

        $this->renderer->share('foo', $foo);

        $actual = str_replace(
            "\r",
            '',
            trim($this->renderer->render('=foo.bar.foo.biz'))
        );
        self::assertSame('42', $actual);

        $_SESSION['foo'] = $foo;
        $options = [
            'modules'          => [JsPhpizePhug::class],
            'shared_variables' => ['session' => $_SESSION],
        ];
        $renderer = new Renderer($options);
        $actual = str_replace(
            "\r",
            '',
            trim($renderer->render('=session.foo.bar.foo.biz'))
        );
        self::assertSame('42', $actual);

        $data = ['foo' => 'bar'];
        $session = [
            'biz' => &$data,
        ];

        $pug = new Renderer([
            'shared_variables' => [
                'session' => $session,
            ],
        ]);

        self::assertSame('bar', $pug->render('=$session["biz"]["foo"]'));

        $session = [
            'bar' => &$data,
        ];
        $pug->share('session', $session);

        self::assertSame('false', $pug->render('=isset($session["biz"])'));
        self::assertSame('bar', $pug->render('=$session["bar"]["foo"]'));

        $pug = new Renderer([
            'globals' => [
                'session' => $session,
            ],
        ]);

        self::assertSame('bar', $pug->render('=$session["bar"]["foo"]'));
    }

    public function testInterpolations()
    {
        $actual = str_replace(
            ["\n", "\r"],
            '',
            trim($this->renderer->render('p #[em #[strong Go]]'))
        );
        self::assertSame('<p><em><strong>Go</strong></em></p>', $actual);
    }

    /**
     * @covers ::getCompiler
     * @covers \Phug\Renderer\Partial\AdapterTrait::getAdapter
     * @covers \Phug\Renderer\Partial\AdapterTrait::getSandboxCall
     * @covers \Phug\Renderer\Partial\AdapterTrait::handleHtmlEvent
     * @covers \Phug\Renderer\Partial\AdapterTrait::callAdapter
     * @covers \Phug\Renderer\AbstractAdapter::<public>
     * @covers \Phug\Renderer\Adapter\EvalAdapter::display
     */
    public function testOptions()
    {
        $this->renderer->setOption('pretty', true);

        $actual = str_replace(
            "\r",
            '',
            trim($this->renderer->render('section: div Hello'))
        );
        self::assertSame(
            "<section>\n".
            "  <div>Hello</div>\n".
            '</section>',
            $actual
        );

        $this->renderer->setOption('pretty', false);

        $actual = str_replace(
            "\r",
            '',
            trim($this->renderer->render('section: div Hello'))
        );
        self::assertSame(
            '<section>'.
            '<div>Hello</div>'.
            '</section>',
            $actual
        );

        $template = "p\n    i\n\tb";
        $actual = trim($this->renderer->render($template));
        self::assertSame('<p><i></i><b></b></p>', $actual);

        $this->renderer->setOptionsRecursive([
            'adapter_class_name' => FileAdapter::class,
            'allow_mixed_indent' => false,
        ]);
        $message = '';

        try {
            $this->renderer->render($template);
        } catch (LexerException $error) {
            $message = $error->getMessage();
        }

        self::assertSame(
            'Failed to lex: Invalid indentation, you can use tabs or spaces but not both'."\n".
            'Near: b'."\n".
            'Line: 3'."\n".
            'Offset: 2',
            trim(preg_replace('/\s*\n\s*/', "\n", $message))
        );
    }

    /**
     * @covers ::handleError
     * @covers ::getDebuggedException
     * @covers ::setDebugFile
     * @covers ::setDebugString
     * @covers ::setDebugFormatter
     * @covers ::getDebugFormatter
     * @covers ::hasColorSupport
     * @covers ::getRendererException
     * @covers ::getErrorMessage
     * @covers ::highlightLine
     * @covers ::wrapLineWith
     * @covers \Phug\Renderer\Partial\AdapterTrait::getSandboxCall
     * @covers \Phug\Renderer\Partial\AdapterTrait::handleHtmlEvent
     * @covers \Phug\Renderer\Partial\AdapterTrait::callAdapter
     * @covers \Phug\Renderer\AbstractAdapter::captureBuffer
     */
    public function testHandleErrorInString()
    {
        foreach ([
            FileAdapter::class,
            EvalAdapter::class,
            StreamAdapter::class,
        ] as $adapter) {
            $renderer = new Renderer([
                'exit_on_error'      => false,
                'debug'              => false,
                'adapter_class_name' => $adapter,
            ]);
            $message = null;

            try {
                $renderer->render('div: p=trigger_error("Division by zero")');
            } catch (\Exception $error) {
                $message = $error->getMessage();
            } catch (\Throwable $error) {
                $message = $error->getMessage();
            }

            self::assertContains(
                'Division by zero',
                $message
            );

            self::assertNotContains(
                'trigger_error("Division'.'by'.'zero")',
                str_replace(' ', '', $message)
            );

            $message = null;
            $renderer->setOption('debug', true);

            try {
                $renderer->render('div: p=trigger_error("Division by zero")');
            } catch (RendererException $error) {
                $message = $error->getMessage();
            }

            self::assertContains(
                'Division by zero on line 1',
                $message
            );

            self::assertContains(
                'trigger_error("Division'.'by'.'zero")',
                str_replace(' ', '', $message)
            );

            $message = null;
            $renderer->setOption('color_support', true);

            try {
                $renderer->render('div: p=trigger_error("Division by zero")');
            } catch (RendererException $error) {
                $message = $error->getMessage();
            }

            self::assertContains(
                'Division by zero on line 1',
                $message
            );

            self::assertContains(
                "\033[43;30m>   1 | div: p\033[43;31m=\033[43;30mtrigger_error(\"Division by zero\")\e[0m\n",
                $message
            );
        }
    }

    /**
     * @covers ::handleError
     * @covers ::getDebuggedException
     * @covers ::setDebugFile
     * @covers ::setDebugString
     * @covers ::setDebugFormatter
     * @covers ::getDebugFormatter
     * @covers ::hasColorSupport
     * @covers ::getRendererException
     * @covers ::getErrorMessage
     * @covers ::highlightLine
     * @covers ::wrapLineWith
     * @covers \Phug\Renderer\Partial\AdapterTrait::getSandboxCall
     * @covers \Phug\Renderer\Partial\AdapterTrait::handleHtmlEvent
     * @covers \Phug\Renderer\Partial\AdapterTrait::callAdapter
     * @covers \Phug\Renderer\AbstractAdapter::captureBuffer
     */
    public function testContextLines()
    {
        $renderer = new Renderer([
            'exit_on_error'       => false,
            'debug'               => true,
            'error_context_lines' => 3,
            'color_support'       => false,
            'adapter_class_name'  => EvalAdapter::class,
        ]);
        $message = null;

        try {
            $renderer->render(implode("\n", [
                '// line -5',
                '// line -4',
                '// line -3',
                '// line -2',
                '// line -1',
                'div: p=12/0',
                '// line +1',
                '// line +2',
                '// line +3',
                '// line +4',
                '// line +5',
            ]));
        } catch (\Exception $error) {
            $message = $error->getMessage();
        }

        self::assertContains('Division by zero', $message);
        self::assertContains('div: p=12/0', $message);
        self::assertContains('// line -1', $message);
        self::assertContains('// line +1', $message);
        self::assertContains('// line -2', $message);
        self::assertContains('// line +2', $message);
        self::assertContains('// line -3', $message);
        self::assertContains('// line +3', $message);
        self::assertNotContains('// line -4', $message);
        self::assertNotContains('// line +4', $message);
        self::assertNotContains('// line -5', $message);
        self::assertNotContains('// line +5', $message);
    }

    /**
     * @covers ::handleError
     * @covers \Phug\Renderer\Partial\AdapterTrait::initAdapter
     * @covers \Phug\Renderer\Partial\AdapterTrait::getSandboxCall
     * @covers \Phug\Renderer\Partial\AdapterTrait::handleHtmlEvent
     * @covers \Phug\Renderer\Partial\AdapterTrait::callAdapter
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getDebuggedException
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::setDebugFile
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::setDebugString
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::setDebugFormatter
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getDebugFormatter
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::hasColorSupport
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getRendererException
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getErrorMessage
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::highlightLine
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::wrapLineWith
     * @covers \Phug\Renderer\AbstractAdapter::captureBuffer
     */
    public function testHandleErrorInFile()
    {
        $message = null;
        $renderer = new Renderer([
            'exit_on_error' => false,
            'debug'         => false,
            'pretty'        => true,
            'error_handler' => function ($error) use (&$message) {
                /* @var \Throwable $error */
                $message = $error->getMessage();
            },
        ]);
        $path = realpath(__DIR__.'/../utils/error.pug');
        $renderer->renderFile($path);
        $message = str_replace('implode(): Invalid arguments passed', 'array, string given', $message ?: '');

        self::assertContains(
            'array, string given',
            $message
        );

        self::assertNotContains(
            'on line 3',
            $message
        );

        self::assertNotContains(
            $path,
            $message
        );

        $message = null;
        $renderer->setOption('debug', true);
        $renderer->renderFile($path);
        $message = str_replace('implode(): Invalid arguments passed', 'array, string given', $message ?: '');

        self::assertContains(
            'array, string given on line 3, offset 2',
            $message
        );

        self::assertContains(
            $path,
            $message
        );

        self::assertContains(
            "implode('','')",
            $message
        );
    }

    /**
     * @covers ::__construct
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::initCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::synchronizeEvent
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::createCompiler
     */
    public function testCompilerClassNameException()
    {
        $message = null;

        try {
            new Renderer([
                'exit_on_error'       => false,
                'compiler_class_name' => Renderer::class,
            ]);
        } catch (RendererException $exception) {
            $message = $exception->getMessage();
        }

        self::assertSame('Passed compiler class '.Renderer::class.' is '.
            'not a valid '.CompilerInterface::class, $message);
    }

    /**
     * @covers ::__construct
     * @covers \Phug\Renderer\Partial\AdapterTrait::initAdapter
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::initCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::synchronizeEvent
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::createCompiler
     */
    public function testAdapterClassNameException()
    {
        $message = null;

        try {
            new Renderer([
                'exit_on_error'      => false,
                'adapter_class_name' => Renderer::class,
            ]);
        } catch (RendererException $exception) {
            $message = $exception->getMessage();
        }

        self::assertSame('Passed adapter class '.Renderer::class.' is '.
            'not a valid '.AdapterInterface::class, $message);
    }

    /**
     * @covers ::__construct
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::initCompiler
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::synchronizeEvent
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::createCompiler
     * @covers \Phug\Renderer\Partial\AdapterTrait::getSandboxCall
     * @covers \Phug\Renderer\Partial\AdapterTrait::handleHtmlEvent
     * @covers \Phug\Renderer\Partial\AdapterTrait::callAdapter
     */
    public function testSelfOption()
    {
        $renderer = new Renderer([
            'self'    => false,
            'modules' => [JsPhpizePhug::class],
        ]);

        self::assertSame('bar', $renderer->render('=foo', [
            'foo' => 'bar',
        ]));
        self::assertSame('bar', $renderer->render('=foo', [
            'foo'  => 'bar',
            'self' => [
                'foo' => 'oof',
            ],
        ]));
        self::assertSame('oof', $renderer->render('=self.foo', [
            'foo'  => 'bar',
            'self' => [
                'foo' => 'oof',
            ],
        ]));

        $renderer->setOption('self', true);

        self::assertSame('', $renderer->render('=foo', [
            'foo' => 'bar',
        ]));
        self::assertSame('', $renderer->render('=foo', [
            'foo' => 'bar',
        ]));
        self::assertSame('', $renderer->render('=foo', [
            'foo'  => 'bar',
            'self' => [
                'foo' => 'oof',
            ],
        ]));
        self::assertSame('bar', $renderer->render('=self.foo', [
            'foo'  => 'bar',
            'self' => [
                'foo' => 'oof',
            ],
        ]));

        $renderer->setOption('self', 'locals');

        self::assertSame('', $renderer->render('=foo', [
            'foo' => 'bar',
        ]));
        self::assertSame('', $renderer->render('=self.foo', [
            'foo' => 'bar',
        ]));
        self::assertSame('bar', $renderer->render('=locals.foo', [
            'foo'  => 'bar',
        ]));
    }

    /**
     * @covers ::handleError
     * @covers \Phug\Renderer\Partial\AdapterTrait::getSandboxCall
     * @covers \Phug\Renderer\Partial\AdapterTrait::handleHtmlEvent
     * @covers \Phug\Renderer\Partial\AdapterTrait::callAdapter
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getDebuggedException
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getErrorAsHtml
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::setDebugFile
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::setDebugString
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::setDebugFormatter
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getDebugFormatter
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::hasColorSupport
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getRendererException
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getErrorMessage
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::highlightLine
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::wrapLineWith
     * @covers \Phug\Renderer\AbstractAdapter::captureBuffer
     */
    public function testHandleHtmlError()
    {
        $lastError = null;
        foreach ([
            FileAdapter::class,
            EvalAdapter::class,
            StreamAdapter::class,
        ] as $adapter) {
            $renderer = new Renderer([
                'exit_on_error'      => false,
                'debug'              => true,
                'html_error'         => true,
                'adapter_class_name' => $adapter,
                'error_handler'      => function ($error) use (&$lastError) {
                    $lastError = $error;
                },
            ]);
            $renderer->render('div: p=12/0');

            /* @var RendererException $lastError */
            self::assertInstanceOf(RendererException::class, $lastError);
            $message = $lastError->getMessage();
            self::assertContains('Division by zero on line 1, offset 7', $message);
            self::assertContains('<span class="error-line">'.
                'div: p=<span class="error-offset">1</span>2/0</span>', $message);
        }
    }

    /**
     * @group error
     * @covers ::handleError
     * @covers \Phug\Renderer\Partial\AdapterTrait::getSandboxCall
     * @covers \Phug\Renderer\Partial\AdapterTrait::handleHtmlEvent
     * @covers \Phug\Renderer\Partial\AdapterTrait::callAdapter
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getDebuggedException
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::setDebugFile
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::setDebugString
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::setDebugFormatter
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getDebugFormatter
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::hasColorSupport
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getRendererException
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::getErrorMessage
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::highlightLine
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::wrapLineWith
     * @covers \Phug\Renderer\AbstractAdapter::captureBuffer
     */
    public function testHandleParseError()
    {
        if (version_compare(PHP_VERSION, '7.0.0-dev') < 0) {
            self::markTestSkipped('Parse error can only be caught since PHP 7.');

            return;
        }

        $renderer = new Renderer([
            'exit_on_error'      => false,
            'debug'              => true,
            'adapter_class_name' => FileAdapter::class,
        ]);

        $message = null;

        try {
            $renderer->render(
                "doctype html\n".
                "html\n".
                "  //-\n".
                "    Many\n".
                "    Boring\n".
                "    So\n".
                "    Boring\n".
                "    Comments\n".
                "  head\n".
                "    title Foo\n".
                "  body\n".
                "    section= MyClass:::error()\n".
                "    footer\n".
                "      | End\n".
                "  //-\n".
                "    Many\n".
                "    Boring\n".
                "    So\n".
                "    Boring\n".
                "    Comments\n".
                '  // Too far to be visible error context'
            );
        } catch (RendererException $error) {
            $message = $error->getMessage();
        }

        self::assertContains('ParseError:', $message);
        self::assertContains(' on line 12, offset 12', $message);
        self::assertContains('title Foo', $message);
        self::assertNotContains('Too far to be visible error context', $message);
    }

    public function testHeadIndent()
    {
        $renderer = new Renderer([
            'pretty' => true,
        ]);

        $html = str_replace("\r", '', trim($renderer->render(implode("\n", [
            'doctype html',
            'html',
            '  head',
            '    link(href="style.css")',
            '    script(src="script.js")',
            '    meta(charset="utf-8")',
        ]))));

        self::assertSame(implode("\n", [
            '<!DOCTYPE html>',
            '<html>',
            '  <head>',
            '    <link href="style.css">',
            '    <script src="script.js"></script>',
            '    <meta charset="utf-8">',
            '  </head>',
            '</html>',
        ]), $html);
    }

    /**
     * @group issues
     * https://github.com/pug-php/pug/issues/186
     */
    public function testUndefinedVariableInIfStatement()
    {
        $renderer = new Renderer([
            'debug' => false,
        ]);

        $html = trim($renderer->render(implode("\n", [
            'if $doesNotExist',
            '  p Hello',
            'else',
            '  p Bye',
        ])));

        self::assertSame('<p>Bye</p>', $html);
    }

    /**
     * @group issues
     *
     * @see https://github.com/pug-php/pug/issues/187
     *
     * @covers \Phug\Renderer\Partial\AdapterTrait::getNewSandBox
     */
    public function testUndefinedIndex()
    {
        $renderer = new Renderer([
            'debug' => false,
        ]);
        $tolerantRenderer = new Renderer([
            'debug'           => false,
            'error_reporting' => E_ALL ^ E_USER_NOTICE,
        ]);
        $noWarningRenderer = new Renderer([
            'debug'           => false,
            'error_reporting' => E_ALL ^ E_WARNING,
        ]);
        $customRenderer = new Renderer([
            'debug'           => false,
            'error_reporting' => function ($number, $message) {
                throw new ErrorException($message, $number);
            },
        ]);

        $pugCodes = [
            'p=trigger_error(\'Notice\', E_USER_NOTICE) ? "ok" : "ko"',
            'p=trigger_error(\'Notice\', E_USER_NOTICE) ? "ok" : "ko"',
            'p=trigger_error(\'Notice\', E_USER_NOTICE) ? "ok" : "ko"',
            'p=trigger_error(\'Notice\', E_USER_NOTICE) ? "ok" : "ko"',
        ];

        $level = error_reporting();

        foreach ($pugCodes as $pugCode) {
            error_reporting(E_ALL);

            $severity = null;

            try {
                $renderer->render($pugCode);
            } catch (ErrorException $exp) {
                $severity = $exp->getSeverity();
            }

            self::assertSame(E_USER_NOTICE, $severity);

            error_reporting(E_ALL ^ E_USER_NOTICE);

            $html = trim($renderer->render($pugCode));

            self::assertSame('<p>ok</p>', $html);

            $code = null;

            try {
                $customRenderer->render($pugCode);
            } catch (ErrorException $exp) {
                $code = $exp->getCode();
            }

            self::assertSame(E_USER_NOTICE, $code);

            error_reporting(E_ALL);

            $code = null;

            try {
                $noWarningRenderer->render($pugCode);
            } catch (ErrorException $exp) {
                $code = $exp->getSeverity();
            }

            self::assertSame(E_USER_NOTICE, $code);

            $html = trim($tolerantRenderer->render($pugCode));

            self::assertSame('<p>ok</p>', $html);

            $html = trim($renderer->render(implode("\n", [
                '- error_reporting(E_ALL ^ E_USER_NOTICE)',
                $pugCode,
            ])));

            self::assertSame('<p>ok</p>', $html);

            error_reporting(E_ALL);

            $code = null;

            try {
                $customRenderer->render($pugCode);
            } catch (ErrorException $exp) {
                $code = $exp->getCode();
            }

            self::assertSame(E_USER_NOTICE, $code);
        }

        error_reporting($level);
    }

    public function testWhiteSpace()
    {
        $renderer = new Renderer([
            'pretty' => false,
        ]);

        $html = trim($renderer->render(implode("\n", [
            '| Don\'t',
            '|',
            'button#self-destruct touch',
            '|',
            '| me!',
        ])));

        self::assertRegExp('/^Don\'t\s+<button id="self-destruct">touch<\/button>\s+me!$/', $html);
    }

    /**
     * @group twig
     */
    public function testTwigOutput()
    {
        include_once __DIR__.'/Utils/TwigFormat.php';

        $formatter = null;
        $renderer = new Renderer([
            'debug'          => false,
            'pretty'         => false,
            'default_format' => TwigFormat::class,
            'formats'        => [
                'basic'        => TwigFormat::class,
                'frameset'     => TwigFormat::class,
                'html'         => TwigFormat::class,
                'mobile'       => TwigFormat::class,
                '1.1'          => TwigFormat::class,
                'plist'        => TwigFormat::class,
                'strict'       => TwigFormat::class,
                'transitional' => TwigFormat::class,
                'xml'          => TwigFormat::class,
            ],
        ]);

        $html = str_replace("\n", '', trim($renderer->compile(implode("\n", [
            'ul#users',
            '  - for user in users',
            '    li.user',
            '      // comment',
            '      = user.name',
            '      | Email: #{user.email}',
            '      a(href=user.url) Home page',
        ]))));

        self::assertSame('<ul id="users">{% for user in users %}'.
            '<li class="user">{#  comment #}{{ user.name | e }}Email: {{ user.email | e }}<a href="{{ user.url | e }}">Home page</a></li>'.
            '{% endfor %}</ul>', $html);
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage p= $undefined()
     */
    public function testExtendsUndefinedCall()
    {
        if (version_compare(PHP_VERSION, '7.0.0-dev') < 0) {
            self::markTestSkipped('Parse error can only be caught since PHP 7.');

            return;
        }

        $renderer = new Renderer([
            'exit_on_error' => false,
            'color_support' => false,
            'pretty'        => true,
        ]);

        $renderer->renderFile(__DIR__.'/../call-undefined/extends-call-undefined.pug');
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage div= $undefined()
     */
    public function testUndefinedCallInBlock()
    {
        if (version_compare(PHP_VERSION, '7.0.0-dev') < 0) {
            self::markTestSkipped('Parse error can only be caught since PHP 7.');

            return;
        }

        $renderer = new Renderer([
            'exit_on_error' => false,
            'color_support' => false,
            'pretty'        => true,
        ]);

        $renderer->renderFile(__DIR__.'/../call-undefined/call-undefined-in-block.pug');
    }

    public function testAttributesTransmission()
    {
        $code = implode("\n", [
            'mixin b',
            '  p&attributes($attributes)',
            'mixin a',
            '  +b.bar&attributes($attributes)',
            '+a.foo',
        ]);

        $renderer = new Renderer([
            'debug' => false,
        ]);

        $html = $renderer->render($code);

        self::assertSame('<p class="bar foo"></p>', $html);
    }

    /**
     * @throws RendererException
     */
    public function testMixinNestedScope()
    {
        $code = implode("\n", [
            'mixin paragraph($text)',
            '  p= $text',
            '  block',
            '+paragraph(\'1\')',
            '  +paragraph($text)',
            '+paragraph($text)',
        ]);

        $renderer = new Renderer([
            'debug' => false,
        ]);

        $html = $renderer->render($code, [
            'text' => '2',
        ]);

        self::assertSame('<p>1</p><p>2</p><p>2</p>', $html);

        $code = implode("\n", [
            '- $text = "2"',
            'mixin paragraph($text)',
            '  p= $text',
            '  block',
            '+paragraph(\'1\')',
            '  +paragraph($text)',
            '+paragraph($text)',
        ]);

        $renderer = new Renderer([
            'debug' => false,
        ]);

        $html = $renderer->render($code);

        self::assertSame('<p>1</p><p>2</p><p>2</p>', $html);

        $code = implode("\n", [
            '- $text = "2"',
            'mixin paragraph($text)',
            '  p= $text',
            '  block',
            '+paragraph(\'1\')',
            '  +paragraph($text)',
            '    +paragraph($text)',
            '      +paragraph(\'3\')',
            '+paragraph($text)',
        ]);

        $renderer = new Renderer([
            'debug' => false,
        ]);

        $html = $renderer->render($code);

        self::assertSame('<p>1</p><p>2</p><p>2</p><p>3</p><p>2</p>', $html);

        $code = implode("\n", [
            'body',
            '  if ($test == true)',
            '    h1 Phug',
            '  else',
            '    <!---->',
            '  div test',
        ]);

        $renderer = new Renderer([
            'debug' => false,
        ]);

        $html = $renderer->render($code, ['test' => false]);

        self::assertSame('<body><!----><div>test</div></body>', $html);

        $html = $renderer->render($code, ['test' => true]);

        self::assertSame('<body><h1>Phug</h1><div>test</div></body>', $html);
    }

    public function testConsecutiveRenders()
    {
        $renderer = new Renderer([
            'debug' => false,
        ]);

        $html = $renderer->renderFile(__DIR__.'/../fixtures/append/page.pug');

        $this->assertSameLines(file_get_contents(__DIR__.'/../fixtures/append/page.html'), $html);

        $html = $renderer->renderFile(__DIR__.'/../cases/comments.pug');

        $this->assertSameLines(file_get_contents(__DIR__.'/../cases/comments.html'), $html);
    }

    /**
     * @throws RendererException
     */
    public function testConsecutiveStringRenders()
    {
        $renderer = new Renderer([
            'debug' => false,
        ]);

        $html = $renderer->render('p Hello');

        $this->assertSameLines('<p>Hello</p>', $html);

        $html = $renderer->render('div Bye');

        $this->assertSameLines('<div>Bye</div>', $html);
    }

    /**
     * @throws RendererException
     */
    public function testCacheDirectoryPreserveDependencies()
    {
        $cacheDirectory = sys_get_temp_dir().'/pug-test'.mt_rand(0, 999999);
        $this->createEmptyDirectory($cacheDirectory);

        $templatesDirectory = __DIR__.'/../for-cache';
        $pug = new Renderer([
            'modules'   => [JsPhpizePhug::class],
            'paths'     => [$templatesDirectory],
            'cache_dir' => $cacheDirectory,
        ]);
        $pug->cacheDirectory($templatesDirectory);
        clearstatcache();
        $files = glob("$cacheDirectory/*.php");
        $files = array_values(array_filter($files, function ($name) {
            return basename($name) !== 'registry.php';
        }));
        $file = count($files) ? file_get_contents($files[0]) : null;
        $this->emptyDirectory($cacheDirectory);
        rmdir($cacheDirectory);

        self::assertNotNull($file);

        $foo = ['bar' => 'biz'];
        ob_start();
        eval('?>'.$file);
        $contents = ob_get_contents();
        ob_end_clean();

        self::assertSame('<p>biz</p>', trim($contents));
    }

    /**
     * @throws RendererException
     */
    public function testBooleanCastAbleObject()
    {
        include_once __DIR__.'/Utils/BooleanAble.php';

        $pug = new Renderer([
            'modules' => [JsPhpizePhug::class],
        ]);
        $data = [
            'trueObj'  => new BooleanAble(true),
            'falseObj' => new BooleanAble(false),
            'whileObj' => new BooleanAble(2),
        ];
        $code = implode("\n", [
            'if trueObj',
            '  p if true',
            'if falseObj',
            '  p if false',
            'unless trueObj',
            '  p unless true',
            'unless falseObj',
            '  p unless false',
            'while whileObj',
            '  p while',
        ]);

        self::assertSame('<p>if true</p><p>unless false</p><p>while</p><p>while</p>', trim($pug->render($code, $data)));
    }

    /**
     * @throws RendererException
     */
    public function testIssetCompatibility()
    {
        echo $this->getJsPhpizeVersion();
        exit;
        $jsPhpizeAtLeastTwo = version_compare($this->getJsPhpizeVersion(), '2.0', '>=');

        $handleCode = function (array $lines) use ($jsPhpizeAtLeastTwo) {
            $code = implode("\n", $lines);

            if ($jsPhpizeAtLeastTwo) {
                return $code;
            }

            return strtr($code, ['$' => '']);
        };

        $pug = new Renderer();
        $code = implode("\n", [
            'if isset($value) && $value !== false',
            '  | yes',
            'else',
            '  | no',
        ]);

        self::assertSame('no', trim($pug->render($code)));
        self::assertSame('yes', trim($pug->render($code, ['value' => 1])));

        self::assertSame('abc', trim($pug->render("| a\nif true\n  | b\n| c")));
        self::assertSame('abc', trim($pug->render("| a\nif true\n  | #{'b'}\n| c")));
        self::assertSame('abc', trim($pug->render("| #{'a'}\nif true\n  | #{'b'}\n| c")));
        self::assertSame("a\nb", trim($pug->render("| #{'a'}\n| #{'b'}")));
        self::assertSame("a\nb", trim($pug->render("| #{'a'}\n\n| #{'b'}")));
        self::assertSame('<p>a</p>b', trim($pug->render("p\n  | #{'a'}\n| #{'b'}")));

        $pug = new Renderer([
            'modules' => [JsPhpizePhug::class],
        ]);
        $code = $handleCode([
            'if isset($value) && $value !== false',
            '  | yes',
            'else',
            '  | no',
        ]);

        self::assertSame('no', trim($pug->render($code)));
        self::assertSame('yes', trim($pug->render($code, ['value' => 1])));

        $code = $handleCode([
            'if $foo && $bar[$foo - 1] === "x"',
            '  | yes',
            'else',
            '  | no',
        ]);

        $this->assertSame('no', trim($pug->render($code, ['foo' => 1])));
        $this->assertSame('yes', trim($pug->render($code, ['foo' => 1, 'bar' => ['x']])));

        if ($jsPhpizeAtLeastTwo) {
            $code = $handleCode([
                'if $foo && isset($bar[$foo - 1])',
                '  | yes',
                'else',
                '  | no',
            ]);

            $this->assertSame('no', trim($pug->render($code, ['foo' => 1])));
            $this->assertSame('yes', trim($pug->render($code, ['foo' => 1, 'bar' => ['x']])));
        }
    }

    private function getJsPhpizeVersion()
    {
        $directory = __DIR__.'/../../vendor/composer';
        $packages = @include "$directory/installed.php";

        if (is_array($packages) && isset($packages['versions'])) {
            return $packages['versions']['js-phpize/js-phpize']['version'];
        }

        $packages = @json_decode(
            file_get_contents("$directory/installed.json"),
            true
        ) ?: [];

        if (isset($packages['packages'])) {
            $packages = $packages['packages'];
        }

        foreach ($packages as $package) {
            if (isset($package['name']) && $package['name'] === 'js-phpize/js-phpize') {
                return $package['version_normalized'];
            }
        }

        return '1.0.0';
    }
}
