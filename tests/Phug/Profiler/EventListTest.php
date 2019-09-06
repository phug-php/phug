<?php

namespace Phug\Test\Profiler;

use PHPUnit\Framework\TestCase;
use Phug\Renderer\Profiler\EventList;

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
    }
}
