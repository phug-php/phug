<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AttributeToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\TagInterpolationEndTokenHandler;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\TagInterpolationEndTokenHandler
 */
class TagInterpolationEndTokenHandlerTest extends TestCase
{
    /**
     * @covers                   ::handleTagInterpolationEndToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass tag-interpolation-end tokens to TagInterpolationEndTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new TagInterpolationEndTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }
}
