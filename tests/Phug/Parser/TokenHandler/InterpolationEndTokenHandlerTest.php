<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AttributeToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\InterpolationEndTokenHandler;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\InterpolationEndTokenHandler
 */
class InterpolationEndTokenHandlerTest extends TestCase
{
    /**
     * @covers                   ::handleInterpolationEndToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass interpolation-end tokens to InterpolationEndTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new InterpolationEndTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }
}
