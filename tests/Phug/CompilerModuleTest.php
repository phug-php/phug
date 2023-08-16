<?php

namespace Phug\Test;

use Phug\Compiler;
use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\ElementEvent;
use Phug\Compiler\Event\NodeEvent;
use Phug\Compiler\Event\OutputEvent;
use Phug\CompilerException;
use Phug\Formatter\Element\MarkupElement;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\TextNode;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\AbstractCompilerModule
 */
class CompilerModuleTest extends TestCase
{
    /**
     * @group modules
     *
     * @covers ::<public>
     * @covers \Phug\Compiler\Event\CompileEvent::<public>
     */
    public function testCompileEvent()
    {
        $compiler = new Compiler([
            'on_compile' => function (CompileEvent $event) {
                $event->setInput($event->getInput()."\nfooter");
            },
        ]);

        self::assertSame('<header></header><footer></footer>', $compiler->compile('header'));

        $compiler = new Compiler([
            'on_compile' => function (CompileEvent $event) {
                $event->setPath(str_replace('include-wrong-path', 'foo-bar', $event->getPath()));
            },
        ]);

        $message = '';

        try {
            $compiler->compileFile(__DIR__.'/../templates/include-wrong-path.pug');
        } catch (CompilerException $exception) {
            $message = $exception->getMessage();
        }

        self::assertStringContains('foo-bar.pug', $message);
    }

    /**
     * @group modules
     *
     * @covers ::<public>
     * @covers \Phug\Compiler\Event\OutputEvent::<public>
     */
    public function testOutputEvent()
    {
        $compiler = new Compiler([
            'on_output' => function (OutputEvent $event) {
                $event->setOutput(
                    preg_replace('/<\\?.*?\\?>/', '', $event->getOutput()).
                    mb_strlen($event->getCompileEvent()->getInput())
                );
            },
        ]);

        self::assertSame('<header></header>14', $compiler->compile('header=message'));
    }

    /**
     * @covers \Phug\Compiler\Event\OutputEvent::<public>
     * @covers \Phug\Compiler\Event\OutputEvent::openPhpCode
     * @covers \Phug\Compiler\Event\OutputEvent::closePhpCode
     * @covers \Phug\Compiler\Event\OutputEvent::concatCode
     */
    public function testPrependCode()
    {
        $compiler = new Compiler([
            'on_output' => function (OutputEvent $event) {
                $event->prependCode('?><h1>Title</h1><?php');
            },
        ]);

        self::assertSame('<h1>Title</h1><div></div>', $compiler->compile('div'));

        $compiler = new Compiler([
            'on_output' => function (OutputEvent $event) {
                $event->prependCode('namespace pug;');
            },
        ]);

        self::assertSame('<?php namespace pug; ?><div></div>', $compiler->compile('div'));

        $compiler = new Compiler([
            'on_output' => function (OutputEvent $event) {
                $event->prependCode('echo "Hello";');
            },
        ]);

        self::assertSame('<?php echo "Hello"; ?><div></div>', $compiler->compile(implode("\n", [
            'div',
        ])));

        self::assertSame("<?php namespace pug;\necho \"Hello\"; ?><div></div>", $compiler->compile(implode("\n", [
            '- namespace pug;',
            'div',
        ])));

        self::assertSame("<?php namespace pug;\necho \"Hello\";\necho \"Bye\"; ?><div></div>", $compiler->compile(implode("\n", [
            '- namespace pug; echo "Bye";',
            'div',
        ])));
    }

    /**
     * @covers \Phug\Compiler\Event\OutputEvent::<public>
     * @covers \Phug\Compiler\Event\OutputEvent::openPhpCode
     * @covers \Phug\Compiler\Event\OutputEvent::closePhpCode
     * @covers \Phug\Compiler\Event\OutputEvent::concatCode
     */
    public function testPrependOutput()
    {
        $compiler = new Compiler([
            'on_output' => function (OutputEvent $event) {
                $event->prependOutput('<?php namespace pug; ?>');
            },
        ]);

        self::assertSame('<?php namespace pug; ?><div></div>', $compiler->compile('div'));

        $compiler = new Compiler([
            'on_output' => function (OutputEvent $event) {
                $event->prependOutput('<?php echo "Hello"; ?>');
            },
        ]);

        self::assertSame('<?php echo "Hello"; ?><div></div>', $compiler->compile(implode("\n", [
            'div',
        ])));

        self::assertSame("<?php namespace pug;\necho \"Hello\"; ?><div></div>", $compiler->compile(implode("\n", [
            '- namespace pug;',
            'div',
        ])));

        self::assertSame("<?php namespace pug;\necho \"Hello\";\necho \"Bye\"; ?><div></div>", $compiler->compile(implode("\n", [
            '- namespace pug; echo "Bye";',
            'div',
        ])));

        $compiler = new Compiler([
            'on_output' => function (OutputEvent $event) {
                $event->prependOutput('<p>Hello</p>');
            },
        ]);

        self::assertSame('<p>Hello</p><div></div>', $compiler->compile(implode("\n", [
            'div',
        ])));

        self::assertSame('<?php namespace pug; ?><p>Hello</p><div></div>', $compiler->compile(implode("\n", [
            '- namespace pug;',
            'div',
        ])));

        self::assertSame('<?php namespace pug; ?><p>Hello</p><?php echo "Bye"; ?><div></div>', $compiler->compile(implode("\n", [
            '- namespace pug;echo "Bye";',
            'div',
        ])));
    }

    /**
     * @group modules
     *
     * @covers ::<public>
     * @covers \Phug\Compiler\Event\NodeEvent::<public>
     */
    public function testNodeEvent()
    {
        $compiler = new Compiler([
            'on_node' => function (NodeEvent $event) {
                if (($element = $event->getNode()) instanceof ElementNode) {
                    /* @var ElementNode $element */
                    $element->setName('footer');

                    $event->setNode($element);
                }
            },
        ]);

        self::assertSame('<footer></footer>', $compiler->compile('header'));
    }

    /**
     * @covers \Phug\Compiler\NodeCompiler\CodeNodeCompiler::compileNode
     */
    public function testUntransformableNode()
    {
        $compiler = new Compiler([
            'patterns' => [
                'transform_code' => '$%s',
            ],
            'on_node' => function (NodeEvent $event) {
                $node = $event->getNode();

                if ($node instanceof ElementNode) {
                    $text = new TextNode($node->getToken(), null, $node->getLevel(), $node->getParent(), []);
                    $text->setValue('foo = 9');
                    $code = new CodeNode($node->getToken(), null, $node->getLevel(), $node->getParent(), [
                        $text,
                    ]);

                    $event->setNode($code);
                }
            },
        ]);

        self::assertSame('<?php $foo = 9 ?>', $compiler->compile('header'));

        $compiler = new Compiler([
            'patterns' => [
                'transform_code' => '$%s',
            ],
            'on_node' => function (NodeEvent $event) {
                $node = $event->getNode();

                if ($node instanceof ElementNode) {
                    $text = new TextNode($node->getToken(), null, $node->getLevel(), $node->getParent(), []);
                    $text->setValue('foo = 9');
                    $code = new CodeNode($node->getToken(), null, $node->getLevel(), $node->getParent(), [
                        $text,
                    ]);

                    $code->preventFromTransformation();

                    $event->setNode($code);
                }
            },
        ]);

        self::assertSame('<?php foo = 9 ?>', $compiler->compile('header'));
    }

    /**
     * @group modules
     *
     * @covers ::<public>
     * @covers \Phug\Compiler\Event\ElementEvent::<public>
     */
    public function testElementEvent()
    {
        $compiler = new Compiler([
            'on_element' => function (ElementEvent $event) {
                if (($markup = $event->getElement()) instanceof MarkupElement) {
                    /* @var MarkupElement $markup */
                    $markup->setName('footer');

                    $event->setElement($markup);
                }
            },
        ]);

        self::assertSame('<footer></footer>', $compiler->compile('header'));
    }
}
