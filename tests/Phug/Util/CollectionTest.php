<?php

namespace Phug\Test\Util;

use Generator;
use Phug\Util\Collection;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Util\Collection
 */
class CollectionTest extends TestCase
{
    protected function getIterator()
    {
        for ($i = 0; $i < 3; $i++) {
            yield $i;
        }
    }

    /**
     * @covers ::__construct
     * @covers ::makeIterable
     * @covers ::isIterable
     * @covers ::getIterable
     */
    public function testGetIterable()
    {
        $collection = new Collection($this->getIterator());
        $iterable = $collection->getIterable();

        self::assertInstanceOf(Generator::class, $iterable);
        self::assertSame('0,1,2', implode(',', iterator_to_array($iterable)));

        $collection = new Collection([3, 4, 5]);
        $iterable = $collection->getIterable();

        self::assertTrue(is_array($iterable));
        self::assertSame('3,4,5', implode(',', $iterable));

        $collection = new Collection(6);
        $iterable = $collection->getIterable();

        self::assertTrue(is_array($iterable));
        self::assertSame('6', implode(',', $iterable));
    }

    /**
     * @covers ::getIterator
     * @covers ::getGenerator
     */
    public function testGetGenerator()
    {
        $collection = new Collection($this->getIterator());
        $iterable = [];

        foreach ($collection as $item) {
            $iterable[] = $item;
        }

        self::assertSame([0, 1, 2], $iterable);

        $collection = new Collection([3, 4, 5]);
        $iterable = [];

        foreach ($collection as $item) {
            $iterable[] = $item;
        }

        self::assertSame([3, 4, 5], $iterable);

        $collection = new Collection(6);
        $iterable = [];

        foreach ($collection as $item) {
            $iterable[] = $item;
        }

        self::assertSame([6], $iterable);
    }

    /**
     * @covers ::map
     * @covers ::yieldMap
     */
    public function testMap()
    {
        $collection = new Collection($this->getIterator());
        $collection = $collection->map(function ($number) {
            return $number * 2;
        });

        self::assertInstanceOf(Collection::class, $collection);
        self::assertSame([0, 2, 4], iterator_to_array($collection));
    }

    /**
     * @covers ::flatMap
     * @covers ::yieldFlatMap
     */
    public function testFlatMap()
    {
        $collection = new Collection($this->getIterator());
        $collection = $collection->flatMap(function ($number) {
            yield $number;

            if ($number) {
                yield -$number;
            }
        });

        self::assertInstanceOf(Collection::class, $collection);
        self::assertSame([0, 1, -1, 2, -2], iterator_to_array($collection));
    }
}
