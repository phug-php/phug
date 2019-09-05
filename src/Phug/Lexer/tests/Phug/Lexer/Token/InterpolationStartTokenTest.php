<?php

namespace Phug\Test\Lexer\Token;

use PHPUnit\Framework\TestCase;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;

/**
 * @coversDefaultClass \Phug\Lexer\Token\InterpolationStartToken
 */
class InterpolationStartTokenTest extends TestCase
{
    /**
     * @covers ::<public>
     */
    public function testEnd()
    {
        $start = new InterpolationStartToken();
        $end = new InterpolationEndToken();

        self::assertNull($start->getEnd());
        $start->setEnd($end);
        self::assertSame($end, $start->getEnd());
    }
}
