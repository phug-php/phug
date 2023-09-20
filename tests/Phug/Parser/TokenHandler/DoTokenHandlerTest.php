<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\DoTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\DoTokenHandler
 */
class DoTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleDoToken
     */
    public function testHandleToken()
    {
        $this->assertNodes("\$i = 1\ndo\n  p=++\$i\nwhile \$i < 3", [
            '[DocumentNode]',
            '  [VariableNode]',
            '    [ExpressionNode]',
            '  [DoNode]',
            '    [ElementNode]',
            '      [ExpressionNode]',
            '  [WhileNode]',
        ]);
    }

    /**
     * @covers                   ::handleDoToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass do tokens to DoTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new DoTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
