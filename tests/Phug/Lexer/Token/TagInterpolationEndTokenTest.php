<?php

namespace Phug\Test\Lexer\Token;

use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Lexer\Token\TagInterpolationEndToken
 */
class TagInterpolationEndTokenTest extends TestCase
{
    /**
     * @covers ::<public>
     */
    public function testStart()
    {
        $start = new TagInterpolationStartToken();
        $end = new TagInterpolationEndToken();

        self::assertNull($end->getStart());
        $end->setStart($start);
        self::assertSame($start, $end->getStart());
    }
}
