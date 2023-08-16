<?php

namespace Phug\Test\Profiler;

use Phug\Renderer\Profiler\EventList;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Renderer\Profiler\EventList
 */
class EventListTest extends TestCase
{
    /**
     * @covers ::<public>
     */
    public function testLock()
    {
        $list = new EventList();

        self::assertFalse($list->isLocked());
        self::assertSame($list, $list->lock());
        self::assertTrue($list->isLocked());
        self::assertSame($list, $list->unlock());
        self::assertFalse($list->isLocked());

        $list = new EventList(['foo' => 'bar']);

        self::assertTrue(isset($list['foo']));

        $list->lock();
        $list->reset();

        self::assertFalse($list->isLocked());
        self::assertFalse(isset($list['foo']));
    }
}
