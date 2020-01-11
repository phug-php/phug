<?php

namespace Phug\Test\Event;

use PHPUnit\Framework\TestCase;
use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\NodeEvent;
use Phug\Invoker;
use Phug\Parser\Node\ElementNode;
use ReflectionException;
use RuntimeException;

/**
 * @coversDefaultClass \Phug\Invoker
 */
class InvokerTest extends TestCase
{
    /**
     * @covers ::__construct
     *
     * @throws ReflectionException
     */
    public function testConstruct()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Passed callback should at least 1 argument and this first argument must have a typehint.');

        new Invoker([
            function ($event) {
            },
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::invoke
     */
    public function testInvoke()
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
            'CompileEvent' => 1,
        ], $calls);
    }

    public function method(CompileEvent $compileEvent)
    {
        $compileEvent->setInput('oops');
    }

    /**
     * @covers ::__construct
     * @covers ::invoke
     *
     * @throws ReflectionException
     */
    public function testInvokeArrayCallable()
    {
        $invoker = new Invoker([
            [$this, 'method'],
        ]);

        $event = new CompileEvent('biz');

        $invoker->invoke($event);

        $this->assertSame('oops', $event->getInput());
    }

    /**
     * @covers ::removeByType
     * @covers ::all
     *
     * @throws ReflectionException
     */
    public function testRemoveByType()
    {
        $nodeEventListener = function (NodeEvent $nodeEvent) use (&$calls) {
            $nodeEvent->setName('section');
        };

        $invoker = new Invoker([
            [$this, 'method'],
            $nodeEventListener,
        ]);

        $invoker->removeByType(CompileEvent::class);

        $this->assertSame([
            NodeEvent::class => $nodeEventListener,
        ], $invoker->all());

        $invoker = new Invoker([
            [$this, 'method'],
            $nodeEventListener,
        ]);

        $invoker->removeByType(NodeEvent::class);

        $this->assertSame([
            CompileEvent::class => [$this, 'method'],
        ], $invoker->all());
    }
}
