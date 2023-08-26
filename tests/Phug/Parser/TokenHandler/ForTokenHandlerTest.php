<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\ForTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\ForTokenHandler
 */
class ForTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleForToken
     */
    public function testHandleToken()
    {
        $this->assertNodes("for \$i = 1; \$i < 3; \$i++\n  p=\$i", [
            '[DocumentNode]',
            '  [ForNode]',
            '    [ElementNode]',
            '      [ExpressionNode]',
        ]);
    }

    /**
     * @covers                   ::handleForToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass for tokens to ForTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new ForTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
