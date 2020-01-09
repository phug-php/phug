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
        $calls = [
            'NodeEvent'    => 0,
            'CompileEvent' => 0,
        ];
        $invoker = new Invoker([
            function (NodeEvent $nodeEvent) use (&$calls) {
                $calls['NodeEvent']++;
                $node = $nodeEvent->getNode();

                if ($node instanceof ElementNode) {
                    $node->setName('section');
                }
            },
            function (CompileEvent $compileEvent) use (&$calls) {
                $calls['CompileEvent']++;
                $compileEvent->setInput('foobar');
            },
        ]);

        $node = new ElementNode();
        $node->setName('div');
        $event = new NodeEvent($node);

        $invoker->invoke($event);

        $this->assertSame('section', $event->getNode()->getName());
        $this->assertSame([
            'NodeEvent'    => 1,
            'CompileEvent' => 0,
        ], $calls);

        $event = new CompileEvent('biz');

        $invoker->invoke($event);

        $this->assertSame('foobar', $event->getInput());
        $this->assertSame([
            'NodeEvent'    => 1,
            'CompileEvent' => 0,
        ], $calls);
    }
}
