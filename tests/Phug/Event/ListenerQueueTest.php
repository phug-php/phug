<?php

namespace Phug\Test\Event;

use PHPUnit\Framework\TestCase;
use Phug\Event;

/**
 * @coversDefaultClass \Phug\Event\ListenerQueue
 */
class ListenerQueueTest extends TestCase
{
    /**
     * @covers ::compare
     */
    public function testCompare()
    {
        $queue = new Event\ListenerQueue();

        self::assertSame(0, $queue->compare(5, 5), '0 if same value');
        self::assertSame(1, $queue->compare(3, 5), '1 if smaller');
        self::assertSame(-1, $queue->compare(7, 5), '-1 if greater');
    }

    /**
     * @covers ::insert
     */
    public function testInsert()
    {
        $queue = new Event\ListenerQueue();

        self::assertCount(0, $queue, 'count is 0 after construct');
        $queue->insert(function () {
        }, 10);
        self::assertCount(1, $queue, 'count is 1 after first insert');
        $queue->insert(function () {
        }, 10);
        self::assertCount(2, $queue, 'count is 2 after second insert');
        $queue->insert([], 10);
        self::assertCount(2, $queue, 'count is still 2 after empty insert');
        $queue->insert([function () {
        }, function () {
        }], 10);
        self::assertCount(4, $queue, 'count is 4 after double insert');

        $queue = new Event\ListenerQueue();

        $queue->insert([function () {
        }, function () {
        }], 10);
        self::assertCount(2, $queue, 'count is 2 after double insert');

        $date = new \DateTime();
        $queue->insert([$date, 'format'], 10);
        self::assertCount(3, $queue, 'count is 3 after callable array insert');
    }

    /**
     * @covers                   ::insertMultiple
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage insertMultiple only accept array or Traversable as first argument
     */
    public function testInsertMultipleBadType()
    {
        $queue = new Event\ListenerQueue();

        $queue->insertMultiple(function () {
        }, 10);
    }

    /**
     * @covers                   ::insertMultiple
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Callback inserted into ListenerQueue needs to be callable
     */
    public function testInsertMultipleBadSubInsert()
    {
        $queue = new Event\ListenerQueue();

        $queue->insert([new \DateTime(), 'doesNotExist'], 10);
    }

    /**
     * @covers ::insertMultiple
     */
    public function testInsertMultiple()
    {
        $queue = new Event\ListenerQueue();

        $queue->insertMultiple([function () {
        }, function () {
        }], 10);
        self::assertCount(2, $queue, 'count is 2 after double insert');
    }

    public function provideParameterValues()
    {
        return [
            [null],
            [false],
            [true],
            [24],
            [2.4],
            ['test'],
            [new \stdClass()],
            [[1, 2, 3]],
            [[new \DateTime(), 'doesNotExist']],
        ];
    }

    /**
     * @covers ::insert
     * @dataProvider provideParameterValues
     */
    public function testInsertWithNonCallback($parameterValue)
    {
        if (method_exists(self::class, 'expectException')) {
            self::expectException(\InvalidArgumentException::class);
        } else {
            self::setExpectedException(\InvalidArgumentException::class);
        }

        $queue = new Event\ListenerQueue();
        $queue->insert($parameterValue, 0);
    }
}
