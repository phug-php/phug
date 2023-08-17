<?php

namespace Phug\Test\Lexer\Token;

use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Util\TestCase;

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

    /**
     * @covers ::__toString
     */
    public function testStringification()
    {
        $start = new InterpolationStartToken();

        self::assertSame('['.InterpolationStartToken::class.']', "$start");
    }
}
