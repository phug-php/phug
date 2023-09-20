<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\VariableTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\VariableTokenHandler
 */
class VariableTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleVariableToken
     */
    public function testHandleToken()
    {
        $this->assertNodes('$p = foo', [
            '[DocumentNode]',
            '  [VariableNode]',
            '    [ExpressionNode]',
        ]);
    }

    /**
     * @covers                   ::handleVariableToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass variable tokens to VariableTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new VariableTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
