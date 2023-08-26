<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\AttributeEndTokenHandler;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\AttributeEndTokenHandler
 */
class AttributeEndTokenHandlerTest extends TestCase
{
    /**
     * @covers ::handleAttributeEndToken
     */
    public function testHandleToken()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new AttributeEndTokenHandler();
        $handler->handleToken(new AttributeEndToken(), $state);

        self::assertNull($state->getCurrentNode());
    }

    /**
     * @covers                   ::handleAttributeEndToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass attribute-end tokens to AttributeEndTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new AttributeEndTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }
}
