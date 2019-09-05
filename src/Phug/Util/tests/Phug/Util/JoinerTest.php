<?php

namespace Phug\Test\Util;

//@codingStandardsIgnoreStart
use PHPUnit\Framework\TestCase;
use Phug\Util\Joiner;

/**
 * @coversDefaultClass Phug\Util\Joiner
 */
class JoinerTest extends TestCase
{
    protected function getIterator()
    {
        for ($i = 0; $i < 3; $i++) {
            yield $i;
        }
    }

    /**
     * @covers ::__construct
     * @covers ::join
     * @covers ::mapAndJoin
     */
    public function testSuccess()
    {
        $joiner = new Joiner($this->getIterator());

        self::assertSame('0,1,2', $joiner->join(','));

        $joiner = new Joiner($this->getIterator());

        self::assertSame('1||2||3', $joiner->mapAndJoin(function ($i) {
            return $i + 1;
        }, '||'));
    }
}
//@codingStandardsIgnoreEnd
