<?php

namespace Phug\Test\Event;

use PHPUnit\Framework\TestCase;
use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\NodeEvent;
use Phug\Invoker;
use Phug\Parser\Node\ElementNode;

/**
 * @coversDefaultClass \Phug\Invoker
 */
class InvokerTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $invoker = new Invoker([
            function (NodeEvent $nodeEvent) {
                $node = $nodeEvent->getNode();

                if ($node instanceof ElementNode) {
                    $node->setName('section');
                }
            },
            function (CompileEvent $compileEvent) {
                $compileEvent->setInput('foobar');
            },
        ]);

        $node = new ElementNode;
        $node->setName('div');
        $event = new NodeEvent($node);

        $invoker->invoke($event);
    }
}
