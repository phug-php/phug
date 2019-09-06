<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\DoTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\DoTokenHandler
 */
class DoTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
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
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass do tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new DoTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
