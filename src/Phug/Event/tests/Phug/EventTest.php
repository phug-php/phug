<?php

namespace Phug\Test;

use PHPUnit\Framework\TestCase;
use Phug\Event;

/**
 * @coversDefaultClass \Phug\Event
 */
class EventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::setName
     */
    public function testGetSetName()
    {
        $event = new Event('test.event');

        self::assertSame('test.event', $event->getName(), 'get name');

        $event->setName('other.event');

        self::assertSame('other.event', $event->getName(), 'get name after set');
    }

    /**
     * @covers ::__construct
     * @covers ::getTarget
     * @covers ::setTarget
     */
    public function testGetSetTarget()
    {
        $event = new Event('test.event');
        self::assertNull($event->getTarget(), 'target is null when not passed');

        $target = new \stdClass();
        $event = new Event('test.event', $target);

        self::assertSame($target, $event->getTarget(), 'get target');

        $otherTarget = new \stdClass();
        $event->setTarget($otherTarget);

        self::assertSame($otherTarget, $event->getTarget(), 'get target after set');
    }

    /**
     * @covers ::__construct
     * @covers ::getParams
     * @covers ::getParam
     * @covers ::setParams
     */
    public function testGetSetParams()
    {
        $event = new Event('test.event');
        self::assertInternalType('array', $event->getParams());
        self::assertCount(0, $event->getParams(), 'params are empty when not passed');

        $params = ['test_key' => 'test value'];
        $event = new Event('test.event', null, $params);

        self::assertSame(['test_key' => 'test value'], $event->getParams(), 'get params');
        self::assertSame('test value', $event->getParam('test_key'), 'get single param');

        $otherParams = ['other_test_key' => 'other test value'];
        $event->setParams($otherParams);

        self::assertSame(['other_test_key' => 'other test value'], $event->getParams(), 'get params after set');
        self::assertSame('other test value', $event->getParam('other_test_key'), 'get single param after set');
    }

    /**
     * @covers ::__construct
     * @covers ::stopPropagation
     * @covers ::isPropagationStopped
     */
    public function testStopPropagation()
    {
        $event = new Event('test.event');

        self::assertFalse($event->isPropagationStopped(), 'propagation isnt stopped after construct');

        $event->stopPropagation(true);

        self::assertTrue($event->isPropagationStopped(), 'propagation is stopped after set');

        $event->stopPropagation(false);

        self::assertFalse($event->isPropagationStopped(), 'propagation isnt stopped after second set');
    }
}
