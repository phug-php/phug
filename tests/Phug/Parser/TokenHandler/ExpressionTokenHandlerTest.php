<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\ExpressionTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\ExpressionTokenHandler
 */
class ExpressionTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleExpressionToken
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
     * @covers                   ::handleExpressionToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass expression tokens to ExpressionTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new ExpressionTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
