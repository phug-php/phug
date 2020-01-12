<?php

namespace Phug\Test\Invoker;

use PHPUnit\Framework\TestCase;
use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\NodeEvent;
use Phug\Event\ListenerQueue;
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
     * @covers ::add
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Passed callback #1 should have at least 1 argument and this first argument must have a typehint.
     *
     * @throws ReflectionException
     */
    public function testConstruct()
    {
        new Invoker([
            function ($event) {
            },
        ]);
    }

    /**
     * @covers ::add
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage The #2 value is not callable.
     *
     * @throws ReflectionException
     */
    public function testAdd()
    {
        $invoker = new Invoker([]);

        $invoker->add([
            function (NodeEvent $event) {
            },
            'not-callable',
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::reset
     * @covers ::add
     * @covers ::invoke
     *
     * @throws ReflectionException
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

                return 'n';
            },
            function (CompileEvent $compileEvent) use (&$calls) {
                $calls['CompileEvent']++;
                $compileEvent->setInput('foobar');

                return 'c';
            },
        ]);

        $node = new ElementNode();
        $node->setName('div');
        $event = new NodeEvent($node);

        $result = $invoker->invoke($event);

        /** @var ElementNode $elementNode */
        $elementNode = $event->getNode();

        $this->assertSame('section', $elementNode->getName());
        $this->assertSame([
            'NodeEvent'    => 1,
            'CompileEvent' => 0,
        ], $calls);
        $this->assertSame(['n'], $result);

        $event = new CompileEvent('biz');

        $result = $invoker->invoke($event);

        $this->assertSame('foobar', $event->getInput());
        $this->assertSame([
            'NodeEvent'    => 1,
            'CompileEvent' => 1,
        ], $calls);
        $this->assertSame(['c'], $result);
    }

    public function method(CompileEvent $compileEvent)
    {
        $compileEvent->setInput('oops');
    }

    /**
     * @covers ::__construct
     * @covers ::add
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
     * @covers ::add
     * @covers ::remove
     * @covers ::all
     *
     * @throws ReflectionException
     */
    public function testRemove()
    {
        $nodeEventListener = function (NodeEvent $nodeEvent) {
            $nodeEvent->setName('section');
        };

        $invoker = new Invoker([
            [$this, 'method'],
            $nodeEventListener,
        ]);

        $invoker->remove([[$this, 'method']]);
        $invokables = $invoker->all();

        $this->assertCount(1, $invokables);
        $this->assertInstanceOf(ListenerQueue::class, $invokables[NodeEvent::class]);
        $this->assertCount(1, $invokables[NodeEvent::class]);
        $this->assertSame($nodeEventListener, $invokables[NodeEvent::class]->top());

        $invoker = new Invoker([
            [$this, 'method'],
            $nodeEventListener,
        ]);

        $invoker->remove([$nodeEventListener]);
        $invokables = $invoker->all();

        $this->assertCount(1, $invokables);
        $this->assertInstanceOf(ListenerQueue::class, $invokables[CompileEvent::class]);
        $this->assertCount(1, $invokables[CompileEvent::class]);
        $this->assertSame([$this, 'method'], $invokables[CompileEvent::class]->top());
    }

    /**
     * @covers ::add
     * @covers ::removeByType
     * @covers ::all
     *
     * @throws ReflectionException
     */
    public function testRemoveByType()
    {
        $nodeEventListener = function (NodeEvent $nodeEvent) {
            $nodeEvent->setName('section');
        };

        $invoker = new Invoker([
            [$this, 'method'],
            $nodeEventListener,
        ]);

        $invoker->removeByType(CompileEvent::class);
        $invokables = $invoker->all();

        $this->assertCount(1, $invokables);
        $this->assertInstanceOf(ListenerQueue::class, $invokables[NodeEvent::class]);
        $this->assertCount(1, $invokables[NodeEvent::class]);
        $this->assertSame($nodeEventListener, $invokables[NodeEvent::class]->top());

        $invoker = new Invoker([
            [$this, 'method'],
            $nodeEventListener,
        ]);

        $invoker->removeByType(NodeEvent::class);
        $invokables = $invoker->all();

        $this->assertCount(1, $invokables);
        $this->assertInstanceOf(ListenerQueue::class, $invokables[CompileEvent::class]);
        $this->assertCount(1, $invokables[CompileEvent::class]);
        $this->assertSame([$this, 'method'], $invokables[CompileEvent::class]->top());
    }
}
