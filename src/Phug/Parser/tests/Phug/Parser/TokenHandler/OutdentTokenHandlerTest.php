<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\OutdentTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\OutdentTokenHandler
 */
class OutdentTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
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
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass outdent tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new OutdentTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
