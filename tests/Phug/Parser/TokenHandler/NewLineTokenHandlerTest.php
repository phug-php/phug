<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\NewLineTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\NewLineTokenHandler
 */
class NewLineTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleNewLineToken
     */
    public function testHandleSingleLine()
    {
        $this->assertNodes("p\n  p\n    div\n  ()\n.foo", [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '      [ElementNode]',
            '    [ElementNode]',
            '  [ElementNode]',
        ]);
    }

    /**
     * @covers                   ::handleNewLineToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass new-line tokens to NewLineTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new NewLineTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
