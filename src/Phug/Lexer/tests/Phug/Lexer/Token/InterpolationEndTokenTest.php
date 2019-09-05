<?php

namespace Phug\Test\Lexer\Token;

use PHPUnit\Framework\TestCase;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;

/**
 * @coversDefaultClass \Phug\Lexer\Token\InterpolationEndToken
 */
class InterpolationEndTokenTest extends TestCase
{
    /**
     * @covers ::<public>
     */
    public function testStart()
    {
        $start = new InterpolationStartToken();
        $end = new InterpolationEndToken();

        self::assertNull($end->getStart());
        $end->setStart($start);
        self::assertSame($start, $end->getStart());
    }
}
