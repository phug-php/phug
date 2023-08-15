<?php

namespace Phug\Test\Util;

use PHPUnit\Framework\TestCase;
use Phug\Formatter\Element\MarkupElement;
use Phug\Util\AttributesStorage;
use Phug\Util\OrderedValue;
use SplObjectStorage;

/**
 * @coversDefaultClass \Phug\Util\AttributesStorage
 */
class AttributesStorageTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::attach
     * @covers ::addAll
     */
    public function testNoHolder()
    {
        $storage = new AttributesStorage(null);
        $storage->attach(new OrderedValue(42, null));
        $next = new SplObjectStorage();
        $next->attach(new OrderedValue('C', 3));
        $next->attach(new OrderedValue('A', 1));
        $storage->addAll($next);

        $output = [];

        foreach ($storage as $value) {
            $output[] = [$value->getOrder(), $value->getValue()];
        }

        self::assertSame([
            [null, 42],
            [3, 'C'],
            [1, 'A'],
        ], $output);
    }

    /**
     * @covers ::__construct
     * @covers ::attach
     * @covers ::addAll
     */
    public function testMarkupHolder()
    {
        $storage = new AttributesStorage(new MarkupElement('div'));
        $storage->attach(new OrderedValue(42, null));
        $next = new SplObjectStorage();
        $next->attach(new OrderedValue('C', -3));
        $next->attach(new OrderedValue('A', null));
        $storage->addAll($next);

        $output = [];

        foreach ($storage as $value) {
            $output[] = [$value->getOrder(), $value->getValue()];
        }

        self::assertSame([
            [0, 42],
            [-3, 'C'],
            [1, 'A'],
        ], $output);
    }
}
