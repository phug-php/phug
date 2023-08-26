<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\EachTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\EachTokenHandler
 */
class EachTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleEachToken
     */
    public function testHandleToken()
    {
        $this->assertNodes("each \$i in \$foo\n  p=\$i", [
            '[DocumentNode]',
            '  [EachNode]',
            '    [ElementNode]',
            '      [ExpressionNode]',
        ]);
    }

    /**
     * @covers                   ::handleEachToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass each tokens to EachTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new EachTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
