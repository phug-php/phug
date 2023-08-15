<?php

namespace Phug\Test\Util;

use PHPUnit\Framework\TestCase;
use Phug\Util\OrderedValue;

/**
 * @coversDefaultClass \Phug\Util\OrderedValue
 */
class OrderedValueTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testObject()
    {
        $value = new OrderedValue(42, 3);

        self::assertSame(42, $value->getValue());
        self::assertSame(3, $value->getOrder());

        $value->setOrder(null);
        $value->setValue('Hello');

        self::assertSame('Hello', $value->getValue());
        self::assertSame(null, $value->getOrder());
    }
}
