<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\ExpressionTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\ExpressionTokenHandler
 */
class ExpressionTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testHandleToken()
    {
        $this->assertNodes('p=foo()', [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ExpressionNode]',
        ]);
        $this->assertNodes("p\n  =foo()", [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ExpressionNode]',
        ]);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass expression tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new ExpressionTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
