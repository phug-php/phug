<?php

namespace Phug\Test\Lexer\Token;

use PHPUnit\Framework\TestCase;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;

/**
 * @coversDefaultClass \Phug\Lexer\Token\TagInterpolationStartToken
 */
class TagInterpolationStartTokenTest extends TestCase
{
    /**
     * @covers ::<public>
     */
    public function testEnd()
    {
        $start = new TagInterpolationStartToken();
        $end = new TagInterpolationEndToken();

        self::assertNull($start->getEnd());
        $start->setEnd($end);
        self::assertSame($end, $start->getEnd());
    }
}
