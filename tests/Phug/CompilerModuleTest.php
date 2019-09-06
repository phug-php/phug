<?php

namespace Phug\Test;

use PHPUnit\Framework\TestCase;
use Phug\Compiler;
use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\ElementEvent;
use Phug\Compiler\Event\NodeEvent;
use Phug\Compiler\Event\OutputEvent;
use Phug\CompilerException;
use Phug\Formatter\Element\MarkupElement;
use Phug\Parser\Node\ElementNode;

/**
 * @coversDefaultClass Phug\AbstractCompilerModule
 */
class CompilerModuleTest extends TestCase
{
    /**
     * @group modules
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

        self::assertContains('foo-bar.pug', $message);
    }

    /**
     * @group modules
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
     * @group modules
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
     * @group modules
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
